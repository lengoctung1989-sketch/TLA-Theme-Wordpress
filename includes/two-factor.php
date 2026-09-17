<?php
/**
 * XÁC MINH 2 LỚP (2FA) BẰNG APP XÁC THỰC — v1.2.0 (Tùng chốt 2026-09-17: phương án **B — TOTP**).
 *
 * Dùng chuẩn **TOTP (RFC 6238)** như Google Authenticator / Authy / 1Password: KHÔNG phụ thuộc email,
 * không gọi dịch vụ ngoài. Mã 6 số, đổi mỗi 30 giây, nhận cả mã của bước trước/sau (±30s) cho đồng hồ
 * lệch nhau.
 *
 * LUỒNG ĐĂNG NHẬP (2 bước):
 *   1. Nhập `log` + `pwd` như thường → `authenticate` (prio 99) thấy cần 2FA ⇒ dựng “phiên chờ” (transient
 *      10 phút) và trả WP_Error `tlpi_2fa_required` ⇒ hook `wp_login_failed` **chuyển hướng** sang màn
 *      nhập mã (KHÔNG hiện lỗi mật khẩu — mật khẩu đã đúng).
 *   2. Màn `wp-login.php?action=tlpi_totp&token=…`: nhập mã 6 số (hoặc **mã dự phòng**) ⇒ đúng thì
 *      `wp_set_auth_cookie()` + `do_action('wp_login')` + chuyển tới `redirect_to`.
 *   • Tài khoản CHƯA có mã bí mật ⇒ màn này **tự sinh mã + hướng dẫn** rồi xác minh (bắt buộc ghi danh).
 *
 * AN TOÀN / CHỐNG TỰ KHOÁ:
 *   • “Nhớ thiết bị” (mặc định 30 ngày, đổi được): cookie HMAC buộc với user + mã bí mật ⇒ **đổi/đặt lại
 *     2FA là mọi thiết bị cũ hết hiệu lực**.
 *   • Nhập sai quá 5 lần ⇒ huỷ phiên chờ (phải nhập lại mật khẩu).
 *   • **CỨU HỘ:** `wp user meta delete <ID> _tlpi_totp_secret` (hoặc `_tlpi_totp_want`) — người có quyền
 *     `edit_user` cũng bấm được “Đặt lại 2FA” trong trang Hồ sơ.
 *   • 2FA của plugin này áp cho **luồng đăng nhập bằng form**. API dùng Application Password / XML-RPC
 *     KHÔNG bị chặn (muốn chặn thì tắt Application Passwords của WordPress).
 *
 * @package TL\Utilities
 */

defined( 'ABSPATH' ) || exit;

const TLPI_2FA_OPTION   = 'tlpi_2fa';
const TLPI_2FA_META     = '_tlpi_totp_secret';   // mã bí mật (đã mã hoá).
const TLPI_2FA_CODES    = '_tlpi_totp_codes';    // danh sách hash mã dự phòng.
const TLPI_2FA_WANT     = '_tlpi_totp_want';     // '1' = người dùng TỰ nguyện bật.
const TLPI_2FA_COOKIE   = 'tlpi_2fa_dev';
const TLPI_2FA_WINDOW   = 1;                     // nhận mã của ±1 bước 30s.
const TLPI_2FA_TRIES    = 5;                     // số lần nhập sai tối đa mỗi phiên.

/** Giá trị mặc định (option). */
function tlpi_2fa_defaults(): array {
	return array(
		'on'      => '',
		'roles'   => array( 'administrator' ),
		'remember' => 30,   // số ngày “nhớ thiết bị”; 0 = tắt.
	);
}

/** Cấu hình hiện tại. */
function tlpi_2fa(): array {
	$saved = tlpi_option( TLPI_2FA_OPTION, 'tlcp_2fa', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = tlpi_2fa_defaults();

	foreach ( $out as $key => $default ) {
		if ( array_key_exists( $key, $saved ) ) {
			$out[ $key ] = $saved[ $key ];
		}
	}

	$out['roles'] = array_values( array_filter( array_map( 'sanitize_key', (array) $out['roles'] ) ) );

	return $out;
}

/** Sanitize option 2FA. */
function tlpi_2fa_sanitize( $value ): array {
	$out   = tlpi_2fa_defaults();
	$value = is_array( $value ) ? $value : array();

	$out['on'] = empty( $value['on'] ) ? '' : '1';

	$roles = isset( $value['roles'] ) ? array_map( 'sanitize_key', (array) $value['roles'] ) : array();
	$roles = array_values( array_filter( $roles, 'get_role' ) );
	$out['roles'] = $roles;

	$days           = isset( $value['remember'] ) ? absint( $value['remember'] ) : 30;
	$out['remember'] = min( 365, $days );

	return $out;
}

/** Danh sách vai trò để tick trong Settings. */
function tlpi_2fa_roles(): array {
	$roles = wp_roles()->roles;
	$out   = array();

	foreach ( $roles as $slug => $role ) {
		$out[ $slug ] = translate_user_role( $role['name'] );
	}

	return $out;
}

/** Tài khoản này có BẮT BUỘC xác minh 2 lớp? (đang bật + vai trò nằm trong danh sách, hoặc tự nguyện) */
function tlpi_2fa_required( WP_User $user ): bool {
	if ( empty( tlpi_2fa()['on'] ) ) {
		return false;
	}

	if ( '1' === (string) get_user_meta( $user->ID, TLPI_2FA_WANT, true ) ) {
		return true;
	}

	$roles = tlpi_2fa()['roles'];

	return (bool) array_intersect( (array) $user->roles, $roles );
}

/** Đã ghi danh (có mã bí mật) chưa? */
function tlpi_2fa_enrolled( int $user_id ): bool {
	return '' !== tlpi_2fa_secret_get( $user_id );
}

/* ============================ MÃ HOÁ MÃ BÍ MẬT ============================ */

/**
 * Khoá mã hoá dẫn xuất từ `AUTH_KEY` + `SECURE_AUTH_SALT` (đổi 2 hằng số này trong wp-config ⇒ mọi
 * mã bí mật cũ không giải mã được nữa, coi như chưa ghi danh — cần ghi danh lại).
 *
 * @return string 32 byte.
 */
function tlpi_2fa_key(): string {
	return hash( 'sha256', 'tlpi2fa|' . ( defined( 'AUTH_KEY' ) ? AUTH_KEY : '' ) . '|' . ( defined( 'SECURE_AUTH_SALT' ) ? SECURE_AUTH_SALT : '' ), true );
}

/** Mã hoá mã bí mật để lưu user meta (sodium secretbox; thiếu sodium thì lưu plain + cờ `plain:`). */
function tlpi_2fa_encrypt( string $plain ): string {
	if ( '' === $plain ) {
		return '';
	}
	if ( function_exists( 'sodium_crypto_secretbox' ) ) {
		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		return 'sb:' . base64_encode( $nonce . sodium_crypto_secretbox( $plain, $nonce, tlpi_2fa_key() ) );
	}

	return 'plain:' . $plain;
}

/** Giải mã mã bí mật đã lưu. */
function tlpi_2fa_decrypt( string $stored ): string {
	if ( '' === $stored ) {
		return '';
	}
	if ( 0 === strpos( $stored, 'plain:' ) ) {
		return substr( $stored, 6 );
	}
	if ( 0 !== strpos( $stored, 'sb:' ) || ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
		return '';
	}

	$raw = base64_decode( substr( $stored, 3 ), true );
	if ( false === $raw || strlen( $raw ) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
		return '';
	}

	$nonce  = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	$cipher = substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
	$plain  = sodium_crypto_secretbox_open( $cipher, $nonce, tlpi_2fa_key() );

	return is_string( $plain ) ? $plain : '';
}

/* ============================ TOTP (RFC 6238) ============================ */

/** Bảng chữ Base32 (RFC 4648). */
function tlpi_2fa_b32_alphabet(): string {
	return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
}

/** Sinh mã bí mật mới: 20 byte ngẫu nhiên → 32 ký tự Base32 (đúng chuẩn app xác thực). */
function tlpi_2fa_secret_new(): string {
	return tlpi_2fa_b32_encode( random_bytes( 20 ) );
}

/** Nhị phân → Base32. */
function tlpi_2fa_b32_encode( string $bin ): string {
	$alphabet = tlpi_2fa_b32_alphabet();
	$bits     = '';
	$out      = '';

	for ( $i = 0; $i < strlen( $bin ); $i++ ) {
		$bits .= str_pad( decbin( ord( $bin[ $i ] ) ), 8, '0', STR_PAD_LEFT );
	}
	foreach ( str_split( $bits, 5 ) as $chunk ) {
		$out .= $alphabet[ (int) bindec( str_pad( $chunk, 5, '0', STR_PAD_RIGHT ) ) ];
	}

	return $out;
}

/** Base32 → nhị phân (bỏ ký tự lạ, chấp nhận chữ thường + dấu cách). */
function tlpi_2fa_b32_decode( string $b32 ): string {
	$alphabet = tlpi_2fa_b32_alphabet();
	$b32      = strtoupper( preg_replace( '/[^A-Za-z2-7]/', '', $b32 ) );
	$bits     = '';

	foreach ( str_split( $b32 ) as $char ) {
		$pos = strpos( $alphabet, $char );
		if ( false === $pos ) {
			continue;
		}
		$bits .= str_pad( decbin( $pos ), 5, '0', STR_PAD_LEFT );
	}

	$out = '';
	foreach ( str_split( $bits, 8 ) as $byte ) {
		if ( strlen( $byte ) < 8 ) {
			break;
		}
		$out .= chr( (int) bindec( $byte ) );
	}

	return $out;
}

/** Mã TOTP 6 số cho 1 mốc thời gian (`$step` = số bước 30 giây từ epoch). */
function tlpi_2fa_code_at( string $secret_b32, int $step ): string {
	$key  = tlpi_2fa_b32_decode( $secret_b32 );
	$hash = hash_hmac( 'sha1', pack( 'J', $step ), $key, true ); // 'J' = 64-bit big-endian (RFC 4226).
	$off  = ord( $hash[19] ) & 0x0F;
	$part = ( ( ord( $hash[ $off ] ) & 0x7F ) << 24 ) | ( ord( $hash[ $off + 1 ] ) << 16 ) | ( ord( $hash[ $off + 2 ] ) << 8 ) | ord( $hash[ $off + 3 ] );

	return str_pad( (string) ( $part % 1000000 ), 6, '0', STR_PAD_LEFT );
}

/** Mã TOTP hiện tại (dùng cho test/`wp eval`). */
function tlpi_2fa_code_now( string $secret_b32 ): string {
	return tlpi_2fa_code_at( $secret_b32, (int) floor( time() / 30 ) );
}

/** Kiểm mã người dùng nhập: chấp nhận ±`TLPI_2FA_WINDOW` bước 30 giây (đồng hồ lệch nhau). */
function tlpi_2fa_verify( string $secret_b32, string $code ): bool {
	$code = preg_replace( '/\D/', '', $code );
	if ( 6 !== strlen( (string) $code ) ) {
		return false;
	}

	$now = (int) floor( time() / 30 );
	for ( $i = -TLPI_2FA_WINDOW; $i <= TLPI_2FA_WINDOW; $i++ ) {
		if ( hash_equals( tlpi_2fa_code_at( $secret_b32, $now + $i ), (string) $code ) ) {
			return true;
		}
	}

	return false;
}

/** URI `otpauth://` để mở app xác thực (bấm trên điện thoại) hoặc nhập tay vào app. */
function tlpi_2fa_uri( string $secret_b32, WP_User $user ): string {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	return 'otpauth://totp/' . rawurlencode( $site . ':' . $user->user_login )
		. '?secret=' . rawurlencode( $secret_b32 )
		. '&issuer=' . rawurlencode( $site )
		. '&algorithm=SHA1&digits=6&period=30';
}

/* ============================ USER META: mã bí mật · mã dự phòng ============================ */

/** Lấy mã bí mật (Base32) của user; '' nếu chưa ghi danh. */
function tlpi_2fa_secret_get( int $user_id ): string {
	return tlpi_2fa_decrypt( (string) get_user_meta( $user_id, TLPI_2FA_META, true ) );
}

/** Lưu mã bí mật (đã mã hoá) — gọi khi ghi danh xong. */
function tlpi_2fa_secret_set( int $user_id, string $secret_b32 ): void {
	update_user_meta( $user_id, TLPI_2FA_META, tlpi_2fa_encrypt( $secret_b32 ) );
}

/** ĐẶT LẠI 2FA của user: xoá mã bí mật + mã dự phòng (thiết bị đã nhớ cũng hết hiệu lực vì HMAC gắn với mã bí mật). */
function tlpi_2fa_reset( int $user_id ): void {
	delete_user_meta( $user_id, TLPI_2FA_META );
	delete_user_meta( $user_id, TLPI_2FA_CODES );
}

/** Hash 1 mã dự phòng — BUỘC với user nên mã của người này không dùng được cho người khác. */
function tlpi_2fa_code_hash( int $user_id, string $code ): string {
	return hash_hmac( 'sha256', $user_id . '|' . strtoupper( trim( $code ) ), wp_salt( 'auth' ) );
}

/**
 * Sinh 10 mã dự phòng mới (8 ký tự), LƯU HASH, trả về danh sách để hiển thị MỘT LẦN.
 *
 * @return string[]
 */
function tlpi_2fa_codes_new( int $user_id ): array {
	$plain = array();
	$hash  = array();

	for ( $i = 0; $i < 10; $i++ ) {
		$code    = strtoupper( wp_generate_password( 8, false, false ) );
		$plain[] = $code;
		$hash[]  = tlpi_2fa_code_hash( $user_id, $code );
	}

	update_user_meta( $user_id, TLPI_2FA_CODES, $hash );

	return $plain;
}

/** Kiểm mã dự phòng; ĐÚNG thì xoá luôn (mỗi mã dùng 1 lần) và trả true. */
function tlpi_2fa_code_use( int $user_id, string $code ): bool {
	$code = strtoupper( trim( $code ) );
	if ( '' === $code ) {
		return false;
	}

	$stored = (array) get_user_meta( $user_id, TLPI_2FA_CODES, true );
	$needle = tlpi_2fa_code_hash( $user_id, $code );
	$left   = array();

	foreach ( $stored as $one ) {
		if ( hash_equals( (string) $one, $needle ) ) {
			$found = true;
			continue; // không đưa vào $left ⇒ mã đã dùng bị xoá.
		}
		$left[] = $one;
	}

	if ( empty( $found ) ) {
		return false;
	}

	update_user_meta( $user_id, TLPI_2FA_CODES, $left );

	return true;
}

/* ============================ “NHỚ THIẾT BỊ” ============================ */

/** Chuỗi HMAC cho cookie nhớ thiết bị (buộc với user + mã bí mật hiện tại). */
function tlpi_2fa_device_sig( int $user_id, int $expire ): string {
	return hash_hmac( 'sha256', $user_id . '|' . $expire . '|' . md5( tlpi_2fa_secret_get( $user_id ) ), wp_salt( 'auth' ) );
}

/** Đặt cookie nhớ thiết bị (chỉ gọi khi đã đăng nhập thành công qua 2FA). */
function tlpi_2fa_device_set( int $user_id ): void {
	$days = (int) tlpi_2fa()['remember'];
	if ( $days <= 0 ) {
		return;
	}

	$expire = time() + ( $days * DAY_IN_SECONDS );
	$value  = $user_id . '|' . $expire . '|' . tlpi_2fa_device_sig( $user_id, $expire );

	setcookie( TLPI_2FA_COOKIE, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	$_COOKIE[ TLPI_2FA_COOKIE ] = $value;
}

/** Cookie nhớ thiết bị còn hợp lệ cho user này? (Đặt lại 2FA là false ngay.) */
function tlpi_2fa_device_ok( int $user_id ): bool {
	$days = (int) tlpi_2fa()['remember'];
	if ( $days <= 0 || empty( $_COOKIE[ TLPI_2FA_COOKIE ] ) ) {
		return false;
	}

	$parts = explode( '|', (string) wp_unslash( $_COOKIE[ TLPI_2FA_COOKIE ] ) );
	if ( 3 !== count( $parts ) ) {
		return false;
	}

	list( $uid, $expire, $sig ) = $parts;
	$uid    = (int) $uid;
	$expire = (int) $expire;

	if ( $uid !== $user_id || $expire < time() ) {
		return false;
	}

	return hash_equals( tlpi_2fa_device_sig( $user_id, $expire ), (string) $sig );
}

/* ============================ LUỒNG ĐĂNG NHẬP (2 bước) ============================ */

/** Tạo “phiên chờ” bước 2 (transient 10 phút) và trả token. */
function tlpi_2fa_session_start( WP_User $user, bool $remember, string $redirect, bool $enroll ): string {
	$token = wp_generate_password( 32, false, false );

	set_transient(
		'tlpi_2fa_' . $token,
		array(
			'uid'      => (int) $user->ID,
			'remember' => $remember ? 1 : 0,
			'redirect' => $redirect,
			'enroll'   => $enroll ? 1 : 0,
			'secret'   => $enroll ? tlpi_2fa_secret_new() : '',
			'tries'    => 0,
		),
		10 * MINUTE_IN_SECONDS
	);

	return $token;
}

/** Đọc phiên chờ; false nếu hết hạn / token sai. */
function tlpi_2fa_session_get( string $token ) {
	if ( '' === $token ) {
		return false;
	}

	$data = get_transient( 'tlpi_2fa_' . $token );
	if ( ! is_array( $data ) || empty( $data['uid'] ) || ! get_user_by( 'id', (int) $data['uid'] ) ) {
		return false;
	}

	return $data;
}

/** URL màn nhập mã cho 1 token. */
function tlpi_2fa_url( string $token ): string {
	return add_query_arg(
		array(
			'action' => 'tlpi_totp',
			'token'  => $token,
		),
		wp_login_url()
	);
}

/** Đích quay về sau khi xong 2FA: `redirect_to` → referer (nếu không phải trang login) → admin. */
function tlpi_2fa_target(): string {
	if ( ! empty( $_REQUEST['redirect_to'] ) ) {
		return esc_url_raw( wp_unslash( (string) $_REQUEST['redirect_to'] ) );
	}

	$ref = wp_get_referer();
	if ( $ref && false === strpos( $ref, 'wp-login.php' ) && false === strpos( $ref, tlpi_login_slug() ) ) {
		return esc_url_raw( $ref );
	}

	return admin_url();
}

/** BƯỚC 1 — mật khẩu ĐÚNG nhưng cần 2FA ⇒ KHÔNG cho đăng nhập, dựng phiên chờ + báo lỗi riêng. */
add_filter( 'authenticate', 'tlpi_2fa_gate', 99, 3 );
function tlpi_2fa_gate( $user, $username, $password ) {
	if ( ! ( $user instanceof WP_User ) || empty( tlpi_2fa()['on'] ) ) {
		return $user;
	}
	if ( ! tlpi_2fa_required( $user ) ) {
		return $user;
	}
	if ( tlpi_2fa_device_ok( (int) $user->ID ) ) {
		return $user; // Thiết bị này đã xác minh trong hạn “nhớ thiết bị”.
	}

	$token = tlpi_2fa_session_start(
		$user,
		! empty( $_REQUEST['rememberme'] ),
		tlpi_2fa_target(),
		! tlpi_2fa_enrolled( (int) $user->ID )
	);

	return new WP_Error( 'tlpi_2fa_required', '', array( 'token' => $token ) );
}

/** BƯỚC 1b — `wp_login_failed` chạy TRƯỚC khi trang login in ra ⇒ chuyển sang màn nhập mã. */
add_action( 'wp_login_failed', 'tlpi_2fa_redirect_step2', 10, 2 );
function tlpi_2fa_redirect_step2( $username, $error ) {
	if ( ! is_wp_error( $error ) || 'tlpi_2fa_required' !== $error->get_error_code() ) {
		return;
	}

	$data  = (array) $error->get_error_data( 'tlpi_2fa_required' );
	$token = isset( $data['token'] ) ? (string) $data['token'] : '';
	if ( '' === $token ) {
		return;
	}

	wp_safe_redirect( tlpi_2fa_url( $token ) );
	exit;
}

/** Xong 2FA ⇒ đăng nhập thật (set cookie + `wp_login`) rồi chuyển tới `redirect_to`. */
function tlpi_2fa_finish( WP_User $user, array $data, string $token ): void {
	delete_transient( 'tlpi_2fa_' . $token );
	tlpi_2fa_device_set( (int) $user->ID );

	wp_set_current_user( (int) $user->ID );
	wp_set_auth_cookie( (int) $user->ID, ! empty( $data['remember'] ), is_ssl() );
	do_action( 'wp_login', $user->user_login, $user );

	wp_safe_redirect( wp_validate_redirect( (string) $data['redirect'], admin_url() ) );
	exit;
}

/**
 * BƯỚC 2 — màn `wp-login.php?action=tlpi_totp&token=…`: hiện form nhập mã, kiểm mã, hoàn tất.
 * Hook `login_form_{action}` chạy TRƯỚC `switch` của wp-login.php nên ta tự lo toàn bộ màn này.
 */
add_action( 'login_form_tlpi_totp', 'tlpi_2fa_step2' );
function tlpi_2fa_step2() {
	$token = isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['token'] ) ) : '';
	$data  = tlpi_2fa_session_get( $token );

	if ( ! $data ) {
		tlpi_2fa_render( 'expired', array() );
	}

	$user   = get_user_by( 'id', (int) $data['uid'] );
	$enroll = ! empty( $data['enroll'] );
	$secret = (string) $data['secret'];

	if ( $enroll && '' === $secret ) {
		$secret         = tlpi_2fa_secret_new();
		$data['secret'] = $secret;
		set_transient( 'tlpi_2fa_' . $token, $data, 10 * MINUTE_IN_SECONDS );
	}

	// (a) Đã lưu mã dự phòng ⇒ hoàn tất đăng nhập. CHỈ khi vừa ghi danh xong (cờ `codes_ready`)
	//     — nếu không, kẻ có token cũng bỏ qua được bước nhập mã.
	if ( isset( $_POST['tlpi_done'] ) && ! empty( $data['codes_ready'] ) ) {
		tlpi_2fa_finish( $user, $data, $token );
	}

	// (b) Người dùng gửi mã.
	if ( isset( $_POST['tlpi_code'] ) ) {
		$code = sanitize_text_field( wp_unslash( (string) $_POST['tlpi_code'] ) );

		if ( $enroll ) {
			if ( tlpi_2fa_verify( $secret, $code ) ) {
				tlpi_2fa_secret_set( (int) $user->ID, $secret );

				$codes = tlpi_2fa_codes_new( (int) $user->ID );

				$data['codes_ready'] = 1; // cho phép bấm “Tôi đã lưu mã”.
				set_transient( 'tlpi_2fa_' . $token, $data, 10 * MINUTE_IN_SECONDS );

				tlpi_2fa_render(
					'codes',
					array(
						'user'  => $user,
						'token' => $token,
						'codes' => $codes,
					)
				);
			}
		} elseif ( tlpi_2fa_verify( tlpi_2fa_secret_get( (int) $user->ID ), $code ) || tlpi_2fa_code_use( (int) $user->ID, $code ) ) {
			tlpi_2fa_finish( $user, $data, $token );
		}

		$tries = (int) $data['tries'] + 1;
		if ( $tries >= TLPI_2FA_TRIES ) {
			delete_transient( 'tlpi_2fa_' . $token );
			tlpi_2fa_render( 'lockout', array() );
		}

		$data['tries'] = $tries;
		set_transient( 'tlpi_2fa_' . $token, $data, 10 * MINUTE_IN_SECONDS );

		tlpi_2fa_render(
			'form',
			array(
				'user'   => $user,
				'token'  => $token,
				'secret' => $enroll ? $secret : '',
				'enroll' => $enroll,
				'error'  => sprintf(
					/* translators: %d: số lần còn lại. */
					__( 'Mã không đúng. Còn %d lần thử.', 'pl-tien-ich-tungleads' ),
					TLPI_2FA_TRIES - $tries
				),
			)
		);
	}

	tlpi_2fa_render(
		'form',
		array(
			'user'   => $user,
			'token'  => $token,
			'secret' => $enroll ? $secret : '',
			'enroll' => $enroll,
			'error'  => '',
		)
	);
}

/* ============================ MÀN HÌNH BƯỚC 2 ============================ */

/**
 * In màn bước 2 bằng `login_header()`/`login_footer()` của wp-login.php ⇒ đúng giao diện core
 * (đang ở trong request wp-login.php nên 2 hàm này có sẵn).
 *
 * @param string $state `form` | `codes` | `expired` | `lockout`.
 * @param array  $args  user · token · secret · enroll · error · codes.
 */
function tlpi_2fa_render( string $state, array $args ): void {
	$user  = isset( $args['user'] ) && $args['user'] instanceof WP_User ? $args['user'] : null;
	$token = (string) ( $args['token'] ?? '' );
	$error = (string) ( $args['error'] ?? '' );

	$titles = array(
		'form'    => __( 'Xác minh 2 lớp', 'pl-tien-ich-tungleads' ),
		'codes'   => __( 'Mã dự phòng', 'pl-tien-ich-tungleads' ),
		'expired' => __( 'Phiên đã hết hạn', 'pl-tien-ich-tungleads' ),
		'lockout' => __( 'Tạm khoá xác minh', 'pl-tien-ich-tungleads' ),
	);
	$title = isset( $titles[ $state ] ) ? $titles[ $state ] : $titles['form'];

	login_header( $title, '', '' !== $error ? new WP_Error( 'tlpi_2fa', $error ) : '' );
	?>
	<style>
		.tlpi-2fa-secret { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 12px 14px; margin: 0 0 14px; }
		.tlpi-2fa-secret code { display: inline-block; font-size: 16px; letter-spacing: 1px; word-break: break-all; background: #f6f7f7; padding: 6px 8px; border-radius: 3px; }
		.tlpi-2fa-codes { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 14px; margin: 0 0 14px; font-family: monospace; font-size: 15px; }
		.tlpi-2fa-note { color: #646970; font-size: 13px; margin: 0 0 14px; }
	</style>
	<?php

	if ( 'form' === $state ) {
		$enroll = ! empty( $args['enroll'] );
		$secret = (string) ( $args['secret'] ?? '' );

		if ( $enroll ) {
			echo '<div class="tlpi-2fa-secret"><p style="margin:0 0 8px;font-weight:600;">'
				. esc_html__( 'Mở app xác thực (Google Authenticator / Authy / 1Password) rồi nhập mã bí mật này:', 'pl-tien-ich-tungleads' ) . '</p>';
			echo '<code>' . esc_html( trim( chunk_split( $secret, 4, ' ' ) ) ) . '</code>';
			if ( $user ) {
				echo '<p class="tlpi-2fa-note" style="margin:10px 0 0;">'
					. esc_html__( 'Trên điện thoại có thể bấm link này để mở thẳng app:', 'pl-tien-ich-tungleads' )
					. ' <a href="' . esc_url( tlpi_2fa_uri( $secret, $user ) ) . '">' . esc_html__( 'Mở app xác thực', 'pl-tien-ich-tungleads' ) . '</a></p>';
			}
			echo '</div>';
		}
		?>
		<form name="tlpi2faform" method="post" action="<?php echo esc_url( tlpi_2fa_url( $token ) ); ?>">
			<p>
				<label for="tlpi_code"><?php esc_html_e( 'Mã 6 số trong app (hoặc mã dự phòng)', 'pl-tien-ich-tungleads' ); ?></label>
				<input type="text" name="tlpi_code" id="tlpi_code" class="input" value="" size="20"
					inputmode="numeric" autocomplete="one-time-code" autocapitalize="off" spellcheck="false" autofocus>
			</p>
			<p class="submit">
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Xác minh và đăng nhập', 'pl-tien-ich-tungleads' ); ?></button>
			</p>
			<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
		</form>
		<?php if ( ! $enroll ) : ?>
			<p class="tlpi-2fa-note"><?php esc_html_e( 'Mất điện thoại? Dùng 1 trong 10 mã dự phòng đã lưu khi bật 2FA.', 'pl-tien-ich-tungleads' ); ?></p>
		<?php endif; ?>
		<p class="tlpi-2fa-note">
			<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( '← Đăng nhập lại', 'pl-tien-ich-tungleads' ); ?></a>
		</p>
		<?php
		login_footer();
		exit;
	}

	if ( 'codes' === $state ) {
		echo '<p class="tlpi-2fa-note">'
			. esc_html__( 'LƯU 10 mã dự phòng này NGAY (mỗi mã dùng 1 lần, để dùng khi mất điện thoại). Mã chỉ hiện 1 lần.', 'pl-tien-ich-tungleads' )
			. '</p><div class="tlpi-2fa-codes">';
		foreach ( (array) ( $args['codes'] ?? array() ) as $one ) {
			echo '<span>' . esc_html( (string) $one ) . '</span>';
		}
		echo '</div>';
		?>
		<form method="post" action="<?php echo esc_url( tlpi_2fa_url( $token ) ); ?>">
			<p class="submit">
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Tôi đã lưu mã — vào bảng điều khiển', 'pl-tien-ich-tungleads' ); ?></button>
			</p>
			<input type="hidden" name="tlpi_done" value="1">
			<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
		</form>
		<?php
		login_footer();
		exit;
	}

	$msg = 'lockout' === $state
		? __( 'Bạn đã nhập sai quá nhiều lần. Vì an toàn, phiên xác minh đã bị huỷ — hãy đăng nhập lại bằng mật khẩu.', 'pl-tien-ich-tungleads' )
		: __( 'Phiên xác minh đã hết hạn (quá 10 phút). Hãy đăng nhập lại bằng mật khẩu.', 'pl-tien-ich-tungleads' );

	echo '<p class="tlpi-2fa-note">' . esc_html( $msg ) . '</p>';
	echo '<p><a class="button button-primary button-large" href="' . esc_url( wp_login_url() ) . '">'
		. esc_html__( 'Đăng nhập lại', 'pl-tien-ich-tungleads' ) . '</a></p>';

	login_footer();
	exit;
}



/* ============================ HỒ SƠ NGƯỜI DÙNG ============================ */

/** Khối 2FA trong trang Hồ sơ (dùng cho cả hồ sơ mình và hồ sơ người khác). */
function tlpi_2fa_profile_box( WP_User $user, bool $is_self ): void {
	$enrolled = tlpi_2fa_enrolled( (int) $user->ID );
	$forced   = tlpi_2fa_required( $user );
	$show     = get_transient( 'tlpi_2fa_show_' . (int) $user->ID );
	?>
	<h2><?php esc_html_e( 'Xác minh 2 lớp (2FA)', 'pl-tien-ich-tungleads' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Trạng thái', 'pl-tien-ich-tungleads' ); ?></th>
			<td>
				<strong><?php echo $enrolled ? esc_html__( 'Đã bật', 'pl-tien-ich-tungleads' ) : esc_html__( 'Chưa bật', 'pl-tien-ich-tungleads' ); ?></strong>
				<?php if ( $forced ) : ?>
					— <?php esc_html_e( 'tài khoản này BẮT BUỘC xác minh khi đăng nhập.', 'pl-tien-ich-tungleads' ); ?>
				<?php endif; ?>
				<p class="description"><?php esc_html_e( 'Dùng app xác thực (Google Authenticator / Authy / 1Password). Chưa bật mà tài khoản bắt buộc ⇒ lần đăng nhập kế tiếp sẽ hiện mã bí mật để nhập vào app.', 'pl-tien-ich-tungleads' ); ?></p>

				<?php if ( $show ) : ?>
					<?php delete_transient( 'tlpi_2fa_show_' . (int) $user->ID ); ?>
					<p style="background:#fff;border:1px solid #c3c4c7;border-radius:4px;padding:10px 12px;max-width:560px;">
						<strong><?php esc_html_e( '10 mã dự phòng mới (chỉ hiện 1 lần — lưu ngay):', 'pl-tien-ich-tungleads' ); ?></strong><br>
						<span style="font-family:monospace;font-size:15px;"><?php echo esc_html( implode( '  ·  ', array_map( 'strval', (array) $show ) ) ); ?></span>
					</p>
				<?php endif; ?>

				<p>
					<?php if ( $enrolled ) : ?>
						<button type="submit" class="button" name="tlpi_2fa_regen" value="1"><?php esc_html_e( 'Tạo lại 10 mã dự phòng', 'pl-tien-ich-tungleads' ); ?></button>
					<?php endif; ?>
					<?php if ( $is_self || current_user_can( 'edit_user', $user->ID ) ) : ?>
						<button type="submit" class="button" name="tlpi_2fa_reset" value="1"
							onclick="return confirm('<?php echo esc_js( __( 'Đặt lại 2FA? Lần đăng nhập sau sẽ phải ghi danh lại (và mọi thiết bị đã nhớ hết hiệu lực).', 'pl-tien-ich-tungleads' ) ); ?>');">
							<?php esc_html_e( 'Đặt lại 2FA', 'pl-tien-ich-tungleads' ); ?>
						</button>
					<?php endif; ?>
				</p>
				<p class="description"><?php esc_html_e( 'Đặt lại = xoá mã bí mật + mã dự phòng. Dùng khi đổi/mất điện thoại.', 'pl-tien-ich-tungleads' ); ?></p>
			</td>
		</tr>
		<?php if ( $is_self ) : ?>
			<tr>
				<th><?php esc_html_e( 'Tự nguyện bật', 'pl-tien-ich-tungleads' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="tlpi_2fa_want" value="1" <?php checked( '1', (string) get_user_meta( $user->ID, TLPI_2FA_WANT, true ) ); ?>>
						<?php esc_html_e( 'Yêu cầu xác minh 2 lớp cho tài khoản này (kể cả khi quản trị chưa bật cho cả vai trò)', 'pl-tien-ich-tungleads' ); ?>
					</label>
				</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php
}

/** Hồ sơ của CHÍNH mình. */
add_action(
	'show_user_profile',
	static function ( $user ): void {
		if ( $user instanceof WP_User ) {
			tlpi_2fa_profile_box( $user, true );
		}
	}
);

/** Trang sửa hồ sơ NGƯỜI KHÁC (chỉ hiện trạng thái + nút đặt lại). */
add_action(
	'edit_user_profile',
	static function ( $user ): void {
		if ( $user instanceof WP_User && current_user_can( 'edit_user', $user->ID ) ) {
			tlpi_2fa_profile_box( $user, false );
		}
	}
);

/** Lưu tuỳ chọn “tự nguyện” + xử lý “đặt lại” / “tạo lại mã dự phòng” (chạy trong `edit_user()`). */
function tlpi_2fa_profile_handle( int $user_id, bool $is_self ): void {
	if ( $is_self && ( $user_id === get_current_user_id() ) ) {
		if ( ! empty( $_POST['tlpi_2fa_want'] ) ) {
			update_user_meta( $user_id, TLPI_2FA_WANT, '1' );
		} else {
			delete_user_meta( $user_id, TLPI_2FA_WANT );
		}
	}

	if ( ! empty( $_POST['tlpi_2fa_reset'] ) ) {
		tlpi_2fa_reset( $user_id );
	}

	if ( ! empty( $_POST['tlpi_2fa_regen'] ) && tlpi_2fa_enrolled( $user_id ) ) {
		set_transient( 'tlpi_2fa_show_' . $user_id, tlpi_2fa_codes_new( $user_id ), MINUTE_IN_SECONDS );
	}
}

add_action(
	'personal_options_update',
	static function ( $user_id ): void {
		tlpi_2fa_profile_handle( (int) $user_id, true );
	}
);

add_action(
	'edit_user_profile_update',
	static function ( $user_id ): void {
		tlpi_2fa_profile_handle( (int) $user_id, false );
	}
);

/* ============================ GIAO DIỆN SETTINGS ============================ */

/** Mục “Xác minh 2 lớp (2FA)” trong Settings → PL Tiện Ích (gọi từ `tlpi_support_page()`). */
function tlpi_2fa_settings_ui(): void {
	$o     = tlpi_2fa();
	$roles = tlpi_2fa_roles();
	?>
	<hr style="margin:28px 0 0;">
	<h2><?php esc_html_e( 'Xác minh 2 lớp (2FA)', 'pl-tien-ich-tungleads' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Bắt buộc nhập mã 6 số từ app xác thực (Google Authenticator / Authy / 1Password) sau khi nhập đúng mật khẩu — theo chuẩn TOTP, KHÔNG cần email, không gọi dịch vụ ngoài.', 'pl-tien-ich-tungleads' ); ?><br>
		<?php esc_html_e( 'Tài khoản chưa có mã bí mật sẽ được hướng dẫn tạo ngay ở lần đăng nhập kế tiếp (kèm 10 mã dự phòng in 1 lần).', 'pl-tien-ich-tungleads' ); ?>
	</p>

	<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:10px 0 0;">
		<input type="checkbox" name="<?php echo esc_attr( TLPI_2FA_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $o['on'] ) ); ?>>
		<?php esc_html_e( 'Bật xác minh 2 lớp', 'pl-tien-ich-tungleads' ); ?>
	</label>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Bắt buộc với vai trò', 'pl-tien-ich-tungleads' ); ?></h3>
	<fieldset style="margin:0;">
		<?php foreach ( $roles as $slug => $name ) : ?>
			<label style="display:inline-flex;align-items:center;gap:6px;margin:0 16px 6px 0;">
				<input type="checkbox" name="<?php echo esc_attr( TLPI_2FA_OPTION . '[roles][]' ); ?>" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $o['roles'], true ) ); ?>>
				<?php echo esc_html( $name ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<p class="description"><?php esc_html_e( 'Mặc định chỉ “Quản trị viên”. Người dùng khác có thể TỰ nguyện bật ở trang Hồ sơ của mình.', 'pl-tien-ich-tungleads' ); ?></p>

	<h3 style="margin:20px 0 4px;"><?php esc_html_e( 'Nhớ thiết bị', 'pl-tien-ich-tungleads' ); ?></h3>
	<p style="margin:0 0 6px;">
		<label style="display:inline-flex;align-items:center;gap:8px;">
			<?php esc_html_e( 'Số ngày', 'pl-tien-ich-tungleads' ); ?>
			<input type="number" min="0" max="365" step="1" style="width:90px;" name="<?php echo esc_attr( TLPI_2FA_OPTION . '[remember]' ); ?>" value="<?php echo esc_attr( (string) $o['remember'] ); ?>">
			<span class="description"><?php esc_html_e( 'Sau khi xác minh, thiết bị đó không hỏi lại trong N ngày. Nhập 0 để TẮT (hỏi mỗi lần đăng nhập).', 'pl-tien-ich-tungleads' ); ?></span>
		</label>
	</p>

	<p class="description" style="margin-top:10px;">
		<?php esc_html_e( 'Cứu hộ khi mất điện thoại / hỏng app:', 'pl-tien-ich-tungleads' ); ?><br>
		<code>wp user meta delete &lt;ID&gt; _tlpi_totp_secret</code> — <?php esc_html_e( 'hoặc người có quyền vào Hồ sơ của tài khoản đó bấm “Đặt lại 2FA”.', 'pl-tien-ich-tungleads' ); ?><br>
		<?php esc_html_e( 'Lưu ý: 2FA áp cho luồng đăng nhập bằng form; API bằng Application Password / XML-RPC KHÔNG bị chặn (tắt Application Passwords nếu muốn siết).', 'pl-tien-ich-tungleads' ); ?>
	</p>
	<?php
}



