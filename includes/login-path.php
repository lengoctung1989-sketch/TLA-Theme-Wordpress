<?php
/**
 * ĐỔI ĐƯỜNG DẪN ĐĂNG NHẬP (bảo mật) — v1.1.0 (Tùng yêu cầu 2026-09-17).
 *
 * Mục đích: `wp-admin/` + `wp-login.php` là 2 đường dẫn BỊ bot dò nhiều nhất. Tính năng này đổi
 * trang đăng nhập sang một đường dẫn riêng do quản trị đặt (`/dang-nhap-caophat`…), còn 2 đường
 * dẫn cũ thì trả **404** (hoặc chuyển về trang chủ) với KHÁCH CHƯA ĐĂNG NHẬP.
 *
 * NGUYÊN TẮC AN TOÀN (đọc trước khi sửa):
 *  • **Người ĐÃ đăng nhập vẫn dùng `/wp-admin/` bình thường** — mọi link trong bảng điều khiển
 *    (`admin_url()`) không đổi, nên không phá dashboard. Chỉ KHÁCH bị chặn.
 *  • **Không đụng** AJAX / admin-post / REST / cron / wp-cli / file tĩnh của wp-admin
 *    (xem `tlpi_login_path_allowed()`) — nếu chặn nhầm là vỡ form, vỡ trang đăng nhập (trang login
 *    tải CSS/JS từ `/wp-admin/css|js/…` ⇒ PHẢI cho qua).
 *  • Đường dẫn mới phục vụ **chính `wp-login.php`** (require file core) ⇒ MỌI action hoạt động y
 *    như trước: login · logout · lostpassword · resetpass (`?action=rp`) · postpass · register…
 *  • Link do WP sinh ra (`wp_login_url()`, `wp_logout_url()`, `wp_lostpassword_url()`,
 *    `wp_registration_url()`) tự trỏ đường dẫn mới qua filter `site_url`/`network_site_url`.
 *  • **CỨU HỘ khi quên đường dẫn:** `wp option delete tlpi_login` (hoặc `wp option update tlpi_login
 *    '{"on":""}' --format=json`) ⇒ tắt tính năng ngay; không có wp-cli thì xoá/đổi tên thư mục
 *    plugin qua FTP cũng đủ.
 *
 * @package TL\Utilities
 */

defined( 'ABSPATH' ) || exit;

const TLPI_LOGIN_OPTION = 'tlpi_login';

/** Slug KHÔNG được dùng (đường dẫn hệ thống / quá dễ đoán). */
function tlpi_login_reserved(): array {
	return array(
		'wp-admin',
		'wp-login',
		'wp-login.php',
		'wp-content',
		'wp-includes',
		'wp-json',
		'wp-cron.php',
		'xmlrpc.php',
		'admin',
		'administrator',
		'login',
		'dang-nhap',
		'dashboard',
		'feed',
		'sitemap',
		'index.php',
	);
}

/** Giá trị mặc định. */
function tlpi_login_defaults(): array {
	return array(
		'on'       => '',      // '1' = bật.
		'slug'     => '',      // đường dẫn đăng nhập mới, KHÔNG có dấu `/`.
		'old_path' => '404',   // `404` = ẩn hoàn toàn · `home` = chuyển về trang chủ.
	);
}

/** Cấu hình hiện tại (đã trộn mặc định). */
function tlpi_login(): array {
	$saved = tlpi_option( TLPI_LOGIN_OPTION, 'tlcp_login', array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = tlpi_login_defaults();

	foreach ( $out as $key => $default ) {
		if ( array_key_exists( $key, $saved ) ) {
			$out[ $key ] = $saved[ $key ];
		}
	}

	$out['slug'] = (string) $out['slug'];

	return $out;
}

/**
 * Đường dẫn đang cấu hình (đã chuẩn hoá), '' nếu chưa đặt.
 *
 * @return string
 */
function tlpi_login_slug(): string {
	$slug = strtolower( trim( tlpi_login()['slug'] ) );
	$slug = (string) preg_replace( '/[^a-z0-9-]/', '', str_replace( array( ' ', '_' ), '-', $slug ) );

	return trim( $slug, '-' );
}

/** Có bật + có slug hợp lệ (không nằm trong danh sách cấm) + site dùng PERMALINK ĐẸP? */
function tlpi_login_active(): bool {
	$o    = tlpi_login();
	$slug = tlpi_login_slug();

	if ( empty( $o['on'] ) || '' === $slug || in_array( $slug, tlpi_login_reserved(), true ) ) {
		return false;
	}

	// Đường dẫn mới là URL “đẹp” ⇒ chỉ chạy khi permalink KHÁC “Mặc định”.
	// (Permalink mặc định = `?p=123` thì `/dang-nhap/` bị Apache trả 404 trước khi tới WordPress.)
	return '' !== (string) get_option( 'permalink_structure' );
}

/** URL đăng nhập hiện tại (đường dẫn mới nếu bật, không thì `wp-login.php` mặc định). */
function tlpi_login_url(): string {
	return tlpi_login_active() ? home_url( '/' . tlpi_login_slug() . '/' ) : site_url( 'wp-login.php', 'login' );
}

/** Sanitize: cờ bật/tắt + slug (bỏ ký tự lạ) + cách xử lý đường dẫn cũ. */
function tlpi_login_sanitize( $value ): array {
	$out   = tlpi_login_defaults();
	$value = is_array( $value ) ? $value : array();

	$out['on'] = empty( $value['on'] ) ? '' : '1';

	$slug = isset( $value['slug'] ) ? (string) $value['slug'] : '';
	$slug = strtolower( trim( $slug ) );
	$slug = (string) preg_replace( '/[^a-z0-9-]/', '', str_replace( array( ' ', '_' ), '-', $slug ) );
	$slug = trim( $slug, '-' );

	if ( '' !== $slug && in_array( $slug, tlpi_login_reserved(), true ) ) {
		add_settings_error(
			TLPI_LOGIN_OPTION,
			'tlpi_login_reserved',
			sprintf(
				/* translators: %s: đường dẫn vừa nhập. */
				__( 'Đường dẫn “%s” bị chặn (trùng đường dẫn hệ thống hoặc quá dễ đoán) — đã giữ giá trị cũ.', 'pl-tien-ich-tungleads' ),
				$slug
			)
		);
		$slug = tlpi_login_slug();
	}

	$out['slug'] = $slug;

	if ( ! empty( $out['on'] ) && '' === $slug ) {
		$out['on'] = '';
		add_settings_error(
			TLPI_LOGIN_OPTION,
			'tlpi_login_empty',
			__( 'Chưa nhập đường dẫn đăng nhập ⇒ tính năng được TẮT (tránh tự khoá mình khỏi trang quản trị).', 'pl-tien-ich-tungleads' )
		);
	}

	$out['old_path'] = ( isset( $value['old_path'] ) && 'home' === $value['old_path'] ) ? 'home' : '404';

	return $out;
}


/* ============================ CHẠY ============================ */

/**
 * Đường dẫn của request hiện tại, tính TƯƠNG ĐỐI với `home_url()` (không có `/` đầu-cuối).
 * Hỗ trợ cả WordPress nằm trong thư mục con.
 *
 * @return string
 */
function tlpi_login_request_path(): string {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$path = rawurldecode( $path );

	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$base = '/' . trim( $base, '/' );
	if ( '/' !== $base && 0 === strpos( $path, $base ) ) {
		$path = substr( $path, strlen( $base ) );
	}

	return strtolower( trim( $path, '/' ) );
}

/**
 * Request này KHÔNG được chặn (dù là khách) — chặn nhầm là vỡ site:
 * AJAX · admin-post · cron · REST · XML-RPC · wp-cli · file tĩnh của `wp-admin`
 * (trang đăng nhập tải CSS/JS từ `/wp-admin/css|js/…` ⇒ thiếu là trang login trắng bệch).
 */
function tlpi_login_path_allowed( string $path ): bool {
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return true;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return true;
	}
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return true;
	}
	if ( preg_match( '#^wp-admin/(admin-ajax\.php|admin-post\.php|load-styles\.php|load-scripts\.php)$#', $path ) ) {
		return true;
	}
	if ( preg_match( '#^wp-admin/(css|js|images|fonts)/#', $path ) ) {
		return true;
	}

	return false;
}

/** Đường dẫn CŨ cần ẩn với khách: `wp-login.php` + `wp-admin/…`. */
function tlpi_login_is_old_path( string $path ): bool {
	return 'wp-login.php' === $path || 'wp-admin' === $path || 0 === strpos( $path, 'wp-admin/' );
}

/** Đường dẫn mới ⇒ phục vụ CHÍNH `wp-login.php` của core (mọi action chạy y như trước). */
function tlpi_login_serve(): void {
	define( 'TLPI_IS_LOGIN', true ); // để mô-đun khác (tracking) biết đang ở trang đăng nhập.

	require_once ABSPATH . 'wp-login.php';
	exit;
}

/** Chặn khách: 404 (mặc định) hoặc chuyển về trang chủ — KHÔNG lộ gì về trang đăng nhập. */
function tlpi_login_block(): void {
	if ( 'home' === tlpi_login()['old_path'] ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}

	status_header( 404 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	header( 'Content-Type: text/html; charset=utf-8' );
	?><!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>404 — <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<style>
		body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f6f4f1; color: #26221e;
			font: 16px/1.6 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; text-align: center; padding: 24px; }
		h1 { font-size: 64px; margin: 0 0 6px; letter-spacing: 2px; }
		p { margin: 0 0 18px; color: #6f6a63; }
		a { display: inline-block; padding: 12px 22px; border-radius: 999px; background: #c8471f; color: #fff; text-decoration: none; font-weight: 700; }
	</style>
</head>
<body>
	<div>
		<h1>404</h1>
		<p><?php esc_html_e( 'Không tìm thấy trang bạn yêu cầu.', 'pl-tien-ich-tungleads' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Về trang chủ', 'pl-tien-ich-tungleads' ); ?></a>
	</div>
</body>
</html>
	<?php
	exit;
}

/**
 * Điểm vào: chạy ở `init` prio 1 — SỚM HƠN `auth_redirect()` của wp-admin (nên khách vào
 * `/wp-admin/` bị xử lý theo cấu hình, không bị đá sang wp-login.php).
 */
add_action(
	'init',
	static function (): void {
		if ( ! tlpi_login_active() ) {
			return;
		}
		if ( is_admin() && is_user_logged_in() ) {
			return; // Người đã đăng nhập: KHÔNG can thiệp.
		}

		$path = tlpi_login_request_path();
		if ( '' === $path || tlpi_login_path_allowed( $path ) ) {
			return;
		}

		if ( $path === tlpi_login_slug() ) {
			tlpi_login_serve();
		}

		if ( tlpi_login_is_old_path( $path ) && ! is_user_logged_in() ) {
			tlpi_login_block();
		}
	},
	1
);

/* ============================ LINK DO WP SINH RA ============================ */

/**
 * Đổi `wp-login.php` trong URL thành đường dẫn mới — CHỈ dùng cho 3 filter dưới, KHÔNG filter
 * `site_url` toàn cục (sẽ phá `admin_url()`/REST/link trong dashboard).
 *
 * @param string $url URL gốc.
 * @return string
 */
function tlpi_login_rewrite_url( $url ) {
	if ( ! tlpi_login_active() || ! is_string( $url ) || false === strpos( $url, 'wp-login.php' ) ) {
		return $url;
	}

	$query = '';
	if ( false !== strpos( $url, '?' ) ) {
		$query = '?' . substr( $url, strpos( $url, '?' ) + 1 );
	}

	return home_url( '/' . tlpi_login_slug() . '/' ) . $query;
}

add_filter( 'site_url', 'tlpi_login_rewrite_url', 10 );
add_filter( 'network_site_url', 'tlpi_login_rewrite_url', 10 );
add_filter( 'wp_redirect', 'tlpi_login_rewrite_url', 10 );

/* ============================ GIAO DIỆN SETTINGS ============================ */

/** Mục “Đường dẫn đăng nhập” trong Settings → PL Tiện Ích (gọi từ `tlpi_support_page()`). */
function tlpi_login_settings_ui(): void {
	$o    = tlpi_login();
	$slug = tlpi_login_slug();
	$url  = tlpi_login_url();
	$dep  = '' === (string) get_option( 'permalink_structure' );

	?>
	<hr style="margin:28px 0 0;">
	<h2><?php esc_html_e( 'Đường dẫn đăng nhập', 'pl-tien-ich-tungleads' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Đổi trang đăng nhập sang đường dẫn riêng và ẩn wp-admin / wp-login.php với KHÁCH chưa đăng nhập (bot dò 2 đường dẫn này liên tục). Người ĐÃ đăng nhập vẫn dùng wp-admin bình thường.', 'pl-tien-ich-tungleads' ); ?>
	</p>

	<?php if ( $dep ) : ?>
		<p style="color:#b32d2e;font-weight:600;">
			<?php esc_html_e( '⚠️ Tính năng này CẦN “Permalink đẹp”: vào Settings → Permalinks, chọn kiểu khác “Mặc định” rồi Lưu. Hiện đang để “Mặc định” nên tính năng tự TẮT (tránh đổi link xong không vào được trang quản trị).', 'pl-tien-ich-tungleads' ); ?>
		</p>
	<?php endif; ?>

	<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin:10px 0 0;">
		<input type="checkbox" name="<?php echo esc_attr( TLPI_LOGIN_OPTION . '[on]' ); ?>" value="1" <?php checked( ! empty( $o['on'] ) ); ?>>
		<?php esc_html_e( 'Bật đổi đường dẫn đăng nhập', 'pl-tien-ich-tungleads' ); ?>
	</label>

	<p style="margin:12px 0 6px;">
		<label style="display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap;">
			<?php esc_html_e( 'Đường dẫn mới', 'pl-tien-ich-tungleads' ); ?>
			<code><?php echo esc_html( trailingslashit( home_url() ) ); ?></code>
			<input type="text" class="regular-text code" name="<?php echo esc_attr( TLPI_LOGIN_OPTION . '[slug]' ); ?>" value="<?php echo esc_attr( (string) $o['slug'] ); ?>" placeholder="dang-nhap-caophat">
		</label>
	</p>
	<p class="description" style="margin:0 0 8px;">
		<?php esc_html_e( 'Chỉ dùng chữ thường, số và dấu gạch ngang. ĐỪNG dùng từ dễ đoán (admin, login…) — hệ thống chặn sẵn các từ đó.', 'pl-tien-ich-tungleads' ); ?>
	</p>

	<p style="margin:0 0 6px;">
		<label style="display:inline-flex;align-items:center;gap:8px;">
			<?php esc_html_e( 'Khi khách vào wp-admin / wp-login.php', 'pl-tien-ich-tungleads' ); ?>
			<select name="<?php echo esc_attr( TLPI_LOGIN_OPTION . '[old_path]' ); ?>">
				<option value="404" <?php selected( '404', $o['old_path'] ); ?>><?php esc_html_e( 'Trả về 404 (ẩn hoàn toàn)', 'pl-tien-ich-tungleads' ); ?></option>
				<option value="home" <?php selected( 'home', $o['old_path'] ); ?>><?php esc_html_e( 'Chuyển về trang chủ (302)', 'pl-tien-ich-tungleads' ); ?></option>
			</select>
		</label>
	</p>

	<p class="description" style="margin-top:10px;">
		<?php esc_html_e( 'Đường dẫn đăng nhập hiện tại:', 'pl-tien-ich-tungleads' ); ?>
		<code><?php echo esc_url( $url ); ?></code><br>
		<span style="color:#b32d2e;">
			<?php esc_html_e( '⚠️ LƯU LẠI đường dẫn này (bookmark) TRƯỚC khi bật. Quên thì cứu hộ bằng wp-cli: wp option delete tlpi_login — hoặc xoá/đổi tên thư mục plugin qua FTP.', 'pl-tien-ich-tungleads' ); ?>
		</span>
	</p>
	<?php
}

