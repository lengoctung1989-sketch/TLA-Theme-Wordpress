<?php
/**
 * Plugin Name:  Button call/zalo - TungLeAds
 * Description:  Widget liên hệ nổi (Gọi điện · Zalo · Facebook · Link tuỳ chỉnh) neo sát lề phải, giữ nguyên thiết kế “Tùng Lê Ads — Contact Floating Widget v1.3”. Nhập nút ở Settings → Button Call/Zalo (màu nút/màu chữ + ảnh icon riêng cho từng nút, custom CSS/JS).
 * Version:      1.1.2
 * Requires PHP: 8.2
 * Author:       Tung Le Ads
 * Author URI:   https://tungleads.com/
 * Text Domain:  button-call-zalo-tungleads
 *
 * @package TL\Ads\ContactButton
 */

defined( 'ABSPATH' ) || exit;

const TLCZ_OPTION   = 'tlcz_settings';  // Option duy nhất chứa toàn bộ cài đặt.
const TLCZ_GROUP    = 'tlcz_settings_group';
const TLCZ_SLUG     = 'tlcz-contact';   // Slug trang Settings.
const TLCZ_MAX_ROWS = 8;                // Chặn trên số nút (chống nhập lố).

/**
 * Giá trị mặc định — ĐÚNG 4 nút như thiết kế v1.3 Tùng gửi (2 Gọi + 2 Zalo).
 *
 * 2026-09-16 (v1.0.1): bỏ khoá `hide_theme_fab` — theme Cao Phát đã **xoá hẳn** `.cp-fab`
 * (CP1.3) nên tuỳ chọn ẩn nó không còn gì để ẩn.
 *
 * @return array{enabled:int,custom_css:string,custom_js:string,buttons:array<int,array<string,mixed>>}
 */
function tlcz_defaults(): array {
	return array(
		'enabled'    => 1,
		'custom_css' => '',
		'custom_js'  => '',
		'buttons'    => array(
			array(
				'type'    => 'phone',
				'label'   => 'Gọi ngay',
				'value'   => '0834.021.021',
				'enabled' => 1,
			),
			array(
				'type'    => 'phone',
				'label'   => 'Gọi ngay',
				'value'   => '0834.484.484',
				'enabled' => 1,
			),
			array(
				'type'    => 'zalo',
				'label'   => 'Zalo',
				'value'   => '0834.021.021',
				'enabled' => 1,
			),
			array(
				'type'    => 'zalo',
				'label'   => 'Zalo',
				'value'   => '0834.484.484',
				'enabled' => 1,
			),
		),
	);
}

/**
 * Kiểu nút hợp lệ (dùng cho cả sanitize, render lẫn select trong admin).
 *
 * v1.1.0: thêm **Facebook** (m.me — chat Messenger) và **Link tuỳ chỉnh** (link bất kỳ).
 */
function tlcz_types(): array {
	return array(
		'phone'    => __( 'Gọi điện (tel:)', 'button-call-zalo-tungleads' ),
		'zalo'     => __( 'Zalo (zalo.me)', 'button-call-zalo-tungleads' ),
		'facebook' => __( 'Facebook (m.me)', 'button-call-zalo-tungleads' ),
		'custom'   => __( 'Link tuỳ chỉnh', 'button-call-zalo-tungleads' ),
	);
}

/** Nhãn nhỏ mặc định theo kiểu (ô “Nhãn nhỏ” để trống ⇒ dùng mấy chữ này). */
function tlcz_default_label( string $type ): string {
	$map = array(
		'phone'    => 'Gọi ngay',
		'zalo'     => 'Zalo',
		'facebook' => 'Facebook',
		'custom'   => 'Liên hệ',
	);

	return $map[ $type ] ?? $map['phone'];
}

/** Gợi ý giá trị cần nhập theo kiểu (placeholder ở trang cài đặt). */
function tlcz_value_placeholder( string $type ): string {
	$map = array(
		'phone'    => '0834.021.021',
		'zalo'     => '0834.021.021',
		'facebook' => 'caophatdoor  hoặc  https://facebook.com/caophatdoor',
		'custom'   => 'https://…  ·  mailto:a@b.com  ·  sms:0909…',
	);

	return $map[ $type ] ?? $map['phone'];
}

/**
 * Cài đặt đã trộn default (option lưu thiếu khoá vẫn chạy đúng).
 *
 * @return array{enabled:int,custom_css:string,custom_js:string,buttons:array<int,array<string,mixed>>}
 */
function tlcz_settings(): array {
	$saved = get_option( TLCZ_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = tlcz_defaults();

	$out['enabled']    = isset( $saved['enabled'] ) ? (int) $saved['enabled'] : $out['enabled'];
	$out['custom_css'] = isset( $saved['custom_css'] ) ? (string) $saved['custom_css'] : $out['custom_css'];
	$out['custom_js']  = isset( $saved['custom_js'] ) ? (string) $saved['custom_js'] : $out['custom_js'];

	if ( isset( $saved['buttons'] ) && is_array( $saved['buttons'] ) ) {
		$out['buttons'] = array_values( $saved['buttons'] );
	}

	return $out;
}

/**
 * Bỏ mọi ký tự KHÔNG phải số — dùng cho `tel:` và `https://zalo.me/<số>`.
 *
 * Người dùng nhập “0834.021.021” (có dấu chấm) vẫn ra `tel:0834021021`; số nhập kiểu
 * “+84 834 021 021” cũng thành “84834021021”.
 */
function tlcz_digits( string $value ): string {
	return (string) preg_replace( '/\D/', '', $value );
}

/**
 * Danh sách nút SẼ in: bỏ nút bị tắt và nút chưa nhập số, tối đa TLCZ_MAX_ROWS.
 *
 * @return array<int,array{type:string,label:string,value:string}>
 */
function tlcz_active_buttons(): array {
	$rows  = array();
	$types = tlcz_types();

	foreach ( tlcz_settings()['buttons'] as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$type  = isset( $row['type'] ) && isset( $types[ (string) $row['type'] ] ) ? (string) $row['type'] : 'phone';
		$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
		$value = isset( $row['value'] ) ? trim( (string) $row['value'] ) : '';
		$on    = ! isset( $row['enabled'] ) || (int) $row['enabled'] > 0;

		if ( '' === $value || ! $on ) {
			continue;
		}
		// `phone` / `zalo` là số ⇒ phải có chữ số. Facebook & custom có thể là username/URL nên không chặn.
		if ( in_array( $type, array( 'phone', 'zalo' ), true ) && '' === tlcz_digits( $value ) ) {
			continue;
		}

		$color      = isset( $row['color'] ) ? (string) sanitize_hex_color( (string) $row['color'] ) : '';
		$text_color = isset( $row['text_color'] ) ? (string) sanitize_hex_color( (string) $row['text_color'] ) : '';

		$rows[] = array(
			'type'       => $type,
			'label'      => '' !== $label ? $label : tlcz_default_label( $type ),
			'value'      => $value,
			'color'      => null === $color ? '' : $color,
			'text_color' => null === $text_color ? '' : $text_color,
			'icon_id'    => isset( $row['icon_id'] ) ? (int) $row['icon_id'] : 0,
		);

		if ( count( $rows ) >= TLCZ_MAX_ROWS ) {
			break;
		}
	}

	return $rows;
}

/**
 * URL của 1 nút theo **kiểu**:
 * - `phone`    → `tel:<chỉ chữ số>`
 * - `zalo`     → `https://zalo.me/<chỉ chữ số>`
 * - `facebook` → nhập username/ID trang ⇒ `https://m.me/<username>` (mở chat Messenger);
 *                dán link đầy đủ (`http…`) ⇒ GIỮ NGUYÊN link đó.
 * - `custom`   → dán link bất kỳ (`https:` `mailto:` `tel:` `sms:`…); thiếu scheme ⇒ tự thêm `https://`.
 *
 * Muốn đổi link của 1 nút mà không sửa dữ liệu đã nhập:
 *     add_filter( 'tlcz_button_url', fn( $url, $btn ) => 'https://zalo.me/oa-cao-phat', 10, 2 );
 *
 * @param array{type:string,label:string,value:string} $btn
 */
function tlcz_button_url( array $btn ): string {
	$value  = trim( (string) $btn['value'] );
	$digits = tlcz_digits( $value );

	switch ( $btn['type'] ) {
		case 'zalo':
			$url = 'https://zalo.me/' . $digits;
			break;

		case 'facebook':
			$url = 0 === stripos( $value, 'http' )
				? $value
				: 'https://m.me/' . ltrim( $value, '@/' );
			break;

		case 'custom':
			// Có scheme (https:, mailto:, tel:, sms:…) thì giữ nguyên, không thì mặc định https.
			$url = preg_match( '#^[a-z][a-z0-9+.\-]*:#i', $value )
				? $value
				: 'https://' . ltrim( $value, '/' );
			break;

		default:
			$url = 'tel:' . $digits;
	}

	/** @param string $url @param array<string,string> $btn */
	return (string) apply_filters( 'tlcz_button_url', $url, $btn );
}

/**
 * Icon mặc định theo kiểu, TRẢ phần bên trong `.wd-contact-icon`.
 *
 * `phone` = SVG điện thoại của thiết kế; `zalo` / `facebook` = ô tròn chữ cái (đúng ngôn ngữ thiết
 * kế v1.3 — Zalo dùng ô chữ “Z”); `custom` = icon link/quả cầu. Ảnh tải lên (nếu có) sẽ THAY THẾ
 * toàn bộ phần này — xem `tlcz_icon_markup()`.
 */
function tlcz_icon_default( string $type ): string {
	switch ( $type ) {
		case 'zalo':
			return '<span class="wd-contact-zalo-text">Z</span>';

		case 'facebook':
			return '<span class="wd-contact-fb-text">f</span>';

		case 'custom':
			return '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/>'
				. '<path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20"/></svg>';

		default:
			/* “Đang gọi”: 2 gợn sóng (Feather phone-call) + ống nghe — Tùng chốt 2026-09-16 (phương án C). */
			return '<svg viewBox="0 0 24 24" aria-hidden="true">'
				. '<path d="M15.05 5A5 5 0 0 1 19 8.95"/>'
				. '<path d="M15.05 1A9 9 0 0 1 23 8.94"/>'
				. '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6'
				. 'A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81'
				. '2 2 0 0 1-.45 2.11L8.09 9.91 a16 16 0 0 0 6 6l1.27-1.27 a2 2 0 0 1 2.11-.45'
				. '12.84 12.84 0 0 0 2.81.7 A2 2 0 0 1 22 16.92z"/></svg>';
	}
}

/** Phần trong `.wd-contact-icon`: ảnh đã tải lên (nếu có) hay icon mặc định của kiểu. */
function tlcz_icon_markup( array $btn ): string {
	$icon_id = isset( $btn['icon_id'] ) ? (int) $btn['icon_id'] : 0;

	if ( $icon_id > 0 ) {
		$img = wp_get_attachment_image(
			$icon_id,
			'thumbnail',
			false,
			array(
				'alt'     => '',
				'loading' => 'lazy',
				'decoding' => 'async',
			)
		);
		if ( '' !== $img ) {
			return $img;
		}
	}

	return tlcz_icon_default( (string) $btn['type'] );
}

/** Có in widget ở trang đang xem không (lọc được bằng `tlcz_should_render`). */
function tlcz_should_render(): bool {
	$settings = tlcz_settings();
	$render   = ! is_admin() && $settings['enabled'] > 0 && array() !== tlcz_active_buttons();

	/** @param bool $render */
	return (bool) apply_filters( 'tlcz_should_render', $render );
}

/*
 * ---------------------------------------------------------------------------
 * FRONTEND — nạp CSS/JS + in widget trước `</body>`.
 * CSS/JS để RIÊNG file trong `assets/` (cache theo `filemtime()`, không có build step);
 * chỉ nạp khi widget thật sự sẽ in ⇒ trang không có nút nào thì 0 request thêm.
 * ---------------------------------------------------------------------------
 */

/** Nạp CSS/JS frontend (chỉ khi widget sẽ in). */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! tlcz_should_render() ) {
			return;
		}

		$css_path = __DIR__ . '/assets/contact-widget.css';
		$js_path  = __DIR__ . '/assets/contact-widget.js';

		wp_enqueue_style(
			'tlcz-widget',
			plugins_url( 'assets/contact-widget.css', __FILE__ ),
			array(),
			file_exists( $css_path ) ? (string) filemtime( $css_path ) : '1.0.0'
		);
		wp_enqueue_script(
			'tlcz-widget',
			plugins_url( 'assets/contact-widget.js', __FILE__ ),
			array(),
			file_exists( $js_path ) ? (string) filemtime( $js_path ) : '1.0.0',
			true
		);
	}
);

/**
 * In widget ở `wp_footer` prio **5**.
 *
 * Prio 5 (KHÔNG phải 20): `wp_print_footer_scripts` cũng nằm ở prio 20 và core đăng ký trước plugin
 * ⇒ nếu để cùng prio 20 thì SCRIPT in ra TRƯỚC markup widget, JS chạy khi widget chưa có trong DOM.
 * (Đã dính thật ở v1.0.0: sự kiện `wd-contact:click` không bắn.) JS vẫn uỷ nhiệm từ `document` để
 * không phụ thuộc thứ tự.
 *
 * Markup giữ NGUYÊN cấu trúc + class của thiết kế “Tùng Lê Ads v1.3” (`wd-contact-*`).
 * Thêm `aria-label` cho section và từng nút vì khi chưa hover nút chỉ hiện icon (trình đọc
 * màn hình không có gì để đọc), kèm `data-tlcz-*` cho JS/tracking.
 */
add_action( 'wp_footer', 'tlcz_render', 5 );

/** In widget liên hệ (Gọi · Zalo · Facebook · link tuỳ chỉnh). */
function tlcz_render(): void {
	if ( ! tlcz_should_render() ) {
		return;
	}

	$buttons = tlcz_active_buttons();
	?>
<section class="wd-contact-widget" aria-label="<?php esc_attr_e( 'Thông tin liên hệ', 'button-call-zalo-tungleads' ); ?>">
	<?php foreach ( $buttons as $btn ) : ?>
		<?php
		$type    = (string) $btn['type'];
		$digits  = tlcz_digits( (string) $btn['value'] );
		$is_ext  = in_array( $type, array( 'zalo', 'facebook', 'custom' ), true ); // 3 kiểu này mở tab mới.
		$classes = 'wd-contact-item wd-contact-' . $type;
		$style   = array();

		// Màu tuỳ chỉnh (nếu có) truyền qua CSS variable ⇒ CSS chỉ cần 2 rule `.wd-has-bg` / `.wd-has-fg`.
		if ( '' !== (string) $btn['color'] ) {
			$classes .= ' wd-has-bg';
			$style[]  = '--wd-bg: ' . (string) $btn['color'];
		}
		if ( '' !== (string) $btn['text_color'] ) {
			$classes .= ' wd-has-fg';
			$style[]  = '--wd-fg: ' . (string) $btn['text_color'];
		}

		$aria = array(
			'phone'    => sprintf( /* translators: %s: số điện thoại. */ __( 'Gọi %s', 'button-call-zalo-tungleads' ), $btn['value'] ),
			'zalo'     => sprintf( /* translators: %s: số Zalo. */ __( 'Liên hệ Zalo %s', 'button-call-zalo-tungleads' ), $btn['value'] ),
			'facebook' => sprintf( /* translators: %s: tài khoản/trang Facebook. */ __( 'Liên hệ Facebook %s', 'button-call-zalo-tungleads' ), $btn['value'] ),
		);
		$aria_label = $aria[ $type ] ?? sprintf( /* translators: %s: đường dẫn/giá trị nút. */ __( 'Mở liên kết %s', 'button-call-zalo-tungleads' ), $btn['value'] );
		?>
	<a
		class="<?php echo esc_attr( $classes ); ?>"
		href="<?php echo esc_url( tlcz_button_url( $btn ) ); ?>"
		<?php if ( $is_ext ) : ?>
			target="_blank"
			rel="noopener noreferrer"
		<?php endif; ?>
		<?php if ( array() !== $style ) : ?>
			style="<?php echo esc_attr( implode( '; ', $style ) ); ?>"
		<?php endif; ?>
		aria-label="<?php echo esc_attr( $aria_label ); ?>"
		data-tlcz-type="<?php echo esc_attr( $type ); ?>"
		data-tlcz-value="<?php echo esc_attr( $digits ); ?>"
	>
		<span class="wd-contact-icon"><?php echo tlcz_icon_markup( $btn ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ảnh do `wp_get_attachment_image()` sinh hoặc SVG hằng. ?></span>

		<span class="wd-contact-label">
			<small><?php echo esc_html( $btn['label'] ); ?></small>
			<strong><?php echo esc_html( $btn['value'] ); ?></strong>
		</span>
	</a>
	<?php endforeach; ?>
</section>
	<?php
}

/*
 * ---------------------------------------------------------------------------
 * CUSTOM CSS / JS — do người dùng nhập ở Settings → Button Call/Zalo.
 * CHỈ thuộc plugin này và CHỈ in khi widget đang hiển thị (tắt widget = không in gì).
 * Không phải CSS/JS của theme — muốn đổi giao diện theme thì sửa trong child theme.
 * ---------------------------------------------------------------------------
 */

/** Custom CSS ở `wp_head` (prio 99 — sau CSS của plugin/theme để đè được). */
add_action(
	'wp_head',
	static function (): void {
		$css = (string) tlcz_settings()['custom_css'];

		if ( '' === trim( $css ) || ! tlcz_should_render() ) {
			return;
		}

		echo '<style id="tlcz-custom-css">' . "\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS do admin nhập, đã lọc chuỗi đóng thẻ khi lưu (`tlcz_sanitize_code()`).
	},
	99
);

/** Custom JS ngay sau widget: `wp_footer` prio 6 (markup ở prio 5, `wp_print_footer_scripts` ở 20). */
add_action(
	'wp_footer',
	static function (): void {
		$js = (string) tlcz_settings()['custom_js'];

		if ( '' === trim( $js ) || ! tlcz_should_render() ) {
			return;
		}

		echo '<script id="tlcz-custom-js">' . "\n" . $js . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JS do admin nhập, đã lọc chuỗi đóng thẻ khi lưu (`tlcz_sanitize_code()`).
	},
	6
);

/*
 * ---------------------------------------------------------------------------
 * ADMIN — Settings → Button Call/Zalo.
 * Một option duy nhất `tlcz_settings` (mảng): `enabled` · `buttons[]`.
 * Bảng nút là REPEATER (thêm/xoá dòng bằng `assets/admin.js`); lưu qua `options.php`
 * (nonce + quyền do Settings API lo) ⇒ mọi dữ liệu đều đi qua `tlcz_sanitize()`.
 * ---------------------------------------------------------------------------
 */

/** Menu admin: Settings → Button Call/Zalo. */
add_action(
	'admin_menu',
	static function (): void {
		add_options_page(
			__( 'Button Call/Zalo', 'button-call-zalo-tungleads' ),
			__( 'Button Call/Zalo', 'button-call-zalo-tungleads' ),
			'manage_options',
			TLCZ_SLUG,
			'tlcz_settings_page'
		);
	}
);

/** Đăng ký option `tlcz_settings`. */
add_action(
	'admin_init',
	static function (): void {
		register_setting(
			TLCZ_GROUP,
			TLCZ_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'tlcz_sanitize',
				'default'           => tlcz_defaults(),
			)
		);
	}
);

/** Nạp JS thêm/xoá dòng + color picker + thư viện Media — chỉ ở đúng trang cài đặt. */
add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		if ( 'settings_page_' . TLCZ_SLUG !== $hook ) {
			return;
		}

		// Color picker (Iris) của WordPress — cho 2 ô “Màu nút” / “Màu chữ” của mỗi nút.
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Thư viện Media — cho nút “Chọn ảnh icon”.
		wp_enqueue_media();

		$path = __DIR__ . '/assets/admin.js';
		wp_enqueue_script(
			'tlcz-admin',
			plugins_url( 'assets/admin.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0',
			true
		);
	}
);

/**
 * Dòng ghi công dùng chung cho **các plugin của theme** (Tùng yêu cầu 2026-09-16):
 * `Phiên bản <x.y.z> | Bởi <a>Tung Le Ads</a>` — version lấy ĐỘNG từ header plugin nên không lệch
 * khi bump version. Hiện ở cuối trang Settings của mỗi plugin.
 */
function tlcz_credit_line(): string {
	$data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$ver  = isset( $data['Version'] ) && '' !== $data['Version'] ? (string) $data['Version'] : '';

	return sprintf(
		/* translators: %s: số phiên bản của plugin. */
		esc_html__( 'Phiên bản %s', 'button-call-zalo-tungleads' ),
		esc_html( $ver )
	) . ' | ' . sprintf(
		/* translators: %s: tên tác giả (có link website). */
		esc_html__( 'Bởi %s', 'button-call-zalo-tungleads' ),
		'<a href="https://tungleads.com/" target="_blank" rel="noopener">Tung Le Ads</a>'
	);
}

/**
 * Làm sạch dữ liệu form trước khi lưu.
 *
 * - `enabled`: checkbox ⇒ thiếu key = 0.
 * - `buttons`: BỎ dòng chưa nhập số (dòng vừa bấm “Thêm nút” mà không điền), cắt tối đa
 *   TLCZ_MAX_ROWS, `array_values` để option luôn là list phẳng (không lỗ index sau khi xoá).
 * - Không còn dòng nào sau khi lọc = quay về 4 số mặc định — cùng quy ước với plugin
 *   `tl-site-caophat` (“xoá trắng rồi lưu = về mặc định”), tránh việc widget tự biến mất
 *   mà người dùng không hiểu vì sao.
 *
 * @param mixed $input Dữ liệu POST từ form.
 * @return array{enabled:int,custom_css:string,custom_js:string,buttons:array<int,array<string,mixed>>}
 */
function tlcz_sanitize( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$types = array_keys( tlcz_types() );
	$out   = array(
		'enabled'    => empty( $input['enabled'] ) ? 0 : 1,
		'custom_css' => tlcz_sanitize_code( isset( $input['custom_css'] ) ? (string) $input['custom_css'] : '', 'style' ),
		'custom_js'  => tlcz_sanitize_code( isset( $input['custom_js'] ) ? (string) $input['custom_js'] : '', 'script' ),
		'buttons'    => array(),
	);

	$rows = isset( $input['buttons'] ) && is_array( $input['buttons'] ) ? $input['buttons'] : array();

	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) || count( $out['buttons'] ) >= TLCZ_MAX_ROWS ) {
			continue;
		}

		$type  = isset( $row['type'] ) ? sanitize_key( (string) $row['type'] ) : 'phone';
		$value = isset( $row['value'] ) ? sanitize_text_field( (string) $row['value'] ) : '';

		if ( ! in_array( $type, $types, true ) ) {
			$type = 'phone';
		}
		if ( '' === trim( $value ) ) {
			continue;
		}

		$out['buttons'][] = array(
			'type'       => $type,
			'label'      => isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '',
			'value'      => $value,
			'color'      => (string) sanitize_hex_color( isset( $row['color'] ) ? (string) $row['color'] : '' ),
			'text_color' => (string) sanitize_hex_color( isset( $row['text_color'] ) ? (string) $row['text_color'] : '' ),
			'icon_id'    => isset( $row['icon_id'] ) ? absint( $row['icon_id'] ) : 0,
			'enabled'    => empty( $row['enabled'] ) ? 0 : 1,
		);
	}

	if ( array() === $out['buttons'] ) {
		$out['buttons'] = tlcz_defaults()['buttons'];
	}

	return $out;
}

/**
 * Làm sạch ô Custom CSS / Custom JS.
 *
 * CSS/JS cần giữ ký tự `{ } < >` nên KHÔNG dùng `wp_kses`/`sanitize_textarea_field`. Cách an toàn
 * tối thiểu: bỏ mọi chuỗi có thể ĐÓNG thẻ đang chứa nó (`</style` / `</script`) để không thoát ra
 * ngoài; ô này chỉ người có `manage_options` mới sửa được (form là `options.php`).
 *
 * @param string $code Nội dung người dùng nhập.
 * @param string $tag  `style` hoặc `script`.
 */
function tlcz_sanitize_code( string $code, string $tag ): string {
	$code = wp_unslash( $code );
	$code = str_ireplace( array( '</' . $tag, '<!--' ), array( '', '' ), $code );

	return trim( $code );
}

/**
 * In 1 dòng nút trong bảng repeater.
 *
 * @param string              $index Index trong mảng `buttons` — hoặc `__i__` khi dùng làm template cho JS.
 * @param array<string,mixed> $row   Dữ liệu dòng.
 */
function tlcz_row_html( string $index, array $row ): void {
	$name       = TLCZ_OPTION . '[buttons][' . $index . ']';
	$type       = isset( $row['type'] ) ? (string) $row['type'] : 'phone';
	$label      = isset( $row['label'] ) ? (string) $row['label'] : '';
	$value      = isset( $row['value'] ) ? (string) $row['value'] : '';
	$color      = isset( $row['color'] ) ? (string) $row['color'] : '';
	$text_color = isset( $row['text_color'] ) ? (string) $row['text_color'] : '';
	$icon_id    = isset( $row['icon_id'] ) ? (int) $row['icon_id'] : 0;
	$icon_url   = $icon_id > 0 ? (string) wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
	$on         = ! isset( $row['enabled'] ) || (int) $row['enabled'] > 0;
	?>
	<tr class="tlcz-row">
		<td>
			<select class="tlcz-type" name="<?php echo esc_attr( $name ); ?>[type]">
				<?php foreach ( tlcz_types() as $key => $type_label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" data-ph="<?php echo esc_attr( tlcz_value_placeholder( (string) $key ) ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $type_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php echo esc_attr( tlcz_default_label( $type ) ); ?>">
		</td>
		<td>
			<input type="text" class="regular-text tlcz-value" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( tlcz_value_placeholder( $type ) ); ?>">
		</td>
		<td class="tlcz-colors">
			<label class="tlcz-color-line">
				<span><?php esc_html_e( 'Nút', 'button-call-zalo-tungleads' ); ?></span>
				<input type="text" class="tlcz-color" name="<?php echo esc_attr( $name ); ?>[color]" value="<?php echo esc_attr( $color ); ?>">
			</label>
			<label class="tlcz-color-line">
				<span><?php esc_html_e( 'Chữ', 'button-call-zalo-tungleads' ); ?></span>
				<input type="text" class="tlcz-color" name="<?php echo esc_attr( $name ); ?>[text_color]" value="<?php echo esc_attr( $text_color ); ?>">
			</label>
		</td>
		<td class="tlcz-icon-cell">
			<span class="tlcz-icon-preview">
				<?php if ( '' !== $icon_url ) : ?>
					<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="40" height="40">
				<?php endif; ?>
			</span>
			<input type="hidden" class="tlcz-icon-id" name="<?php echo esc_attr( $name ); ?>[icon_id]" value="<?php echo esc_attr( (string) $icon_id ); ?>">
			<button type="button" class="button button-small tlcz-pick"><?php esc_html_e( 'Chọn ảnh', 'button-call-zalo-tungleads' ); ?></button>
			<button type="button" class="button-link tlcz-clear-icon"<?php echo '' === $icon_url ? ' hidden' : ''; ?>><?php esc_html_e( 'Xoá ảnh', 'button-call-zalo-tungleads' ); ?></button>
		</td>
		<td>
			<label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $on ); ?>> <?php esc_html_e( 'Bật', 'button-call-zalo-tungleads' ); ?></label>
		</td>
		<td>
			<button type="button" class="button-link tlcz-remove" aria-label="<?php esc_attr_e( 'Xoá nút này', 'button-call-zalo-tungleads' ); ?>">✕</button>
		</td>
	</tr>
	<?php
}

/** Giao diện trang cài đặt. */
function tlcz_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = tlcz_settings();
	$rows     = $settings['buttons'];

	// Luôn có ít nhất 1 dòng để nhập (option có thể đang rỗng).
	if ( array() === $rows ) {
		$rows = array(
			array(
				'type'    => 'phone',
				'label'   => '',
				'value'   => '',
				'enabled' => 1,
			),
		);
	}
	?>
	<div class="wrap">
		<style>
			.tlcz-color-line { display: flex; align-items: center; gap: 6px; margin: 0 0 6px; }
			.tlcz-color-line > span { width: 30px; color: #50575e; font-size: 12px; }
			.tlcz-colors .wp-picker-container { display: inline-block; }
			.tlcz-icon-cell img { max-width: 40px; max-height: 40px; border-radius: 6px; vertical-align: middle; margin-right: 6px; }
			.tlcz-icon-cell .button-link { margin-left: 6px; }
			.tlcz-credit { margin-top: 18px; color: #646970; font-style: italic; }
		</style>
		<h1><?php echo esc_html__( 'Button Call/Zalo', 'button-call-zalo-tungleads' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Widget liên hệ nổi (neo sát lề phải, mở rộng khi hover) in ở cuối MỌI trang. Nhập số có dấu chấm vẫn dùng được: link “tel:” và “zalo.me” tự bỏ mọi ký tự không phải số.', 'button-call-zalo-tungleads' ); ?>
		</p>
		<p class="description tlcz-dev">
			<?php
			printf(
				/* translators: %1$s: tên tác giả. %2$s: link website (markup). */
				esc_html__( 'Plugin được phát triển bởi: %1$s | %2$s', 'button-call-zalo-tungleads' ),
				esc_html__( 'Tùng Lê Ads', 'button-call-zalo-tungleads' ),
				'<a href="https://tungleads.com/" target="_blank" rel="noopener">www.tungleads.com</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup cố định.
			);
			?>
		</p>

		<form action="options.php" method="post">
			<?php settings_fields( TLCZ_GROUP ); ?>

			<h2><?php esc_html_e( 'Chung', 'button-call-zalo-tungleads' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Hiện widget', 'button-call-zalo-tungleads' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( TLCZ_OPTION ); ?>[enabled]" value="1" <?php checked( $settings['enabled'], 1 ); ?>>
							<?php esc_html_e( 'Hiện nút Gọi/Zalo ở mọi trang', 'button-call-zalo-tungleads' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Nút liên hệ', 'button-call-zalo-tungleads' ); ?></h2>
			<p class="description">
				<?php esc_html_e( '“Nhãn nhỏ” = dòng chữ phía trên giá trị (ví dụ “Gọi ngay”, “Zalo”); để trống sẽ tự dùng nhãn mặc định của kiểu.', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Giá trị tuỳ theo kiểu — Gọi/Zalo: số điện thoại · Facebook: username/ID trang (hoặc dán link fanpage) · Link tuỳ chỉnh: link bất kỳ (https:, mailto:, tel:, sms:…).', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Màu sắc để TRỐNG = dùng màu mặc định của kiểu (đỏ cho Gọi · xanh cho Zalo · xanh dương cho Facebook · xám cho Link).', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Ảnh icon tải lên sẽ THAY icon mặc định của nút (nên dùng ảnh vuông ~100×100px, nền trong suốt).', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Nút ĐẦU TIÊN nếu là loại “Gọi điện” sẽ có hiệu ứng pulse (đúng thiết kế). Bỏ tick “Bật” để tạm ẩn 1 nút.', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Tất cả nút đều bị tắt = widget tự ẩn. Xoá hết dòng rồi lưu = quay về 4 số mặc định.', 'button-call-zalo-tungleads' ); ?>
			</p>

			<table class="widefat striped tlcz-rows" id="tlcz-rows" data-next="<?php echo esc_attr( (string) count( $rows ) ); ?>" data-max="<?php echo esc_attr( (string) TLCZ_MAX_ROWS ); ?>">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Kiểu', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Nhãn nhỏ', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Giá trị (số / link)', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Màu sắc', 'button-call-zalo-tungleads' ); ?> <span class="description"><?php esc_html_e( '(nút / chữ)', 'button-call-zalo-tungleads' ); ?></span></th>
						<th scope="col"><?php esc_html_e( 'Ảnh icon', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Trạng thái', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Xoá', 'button-call-zalo-tungleads' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $rows as $tlcz_index => $tlcz_row ) {
						tlcz_row_html( (string) $tlcz_index, (array) $tlcz_row );
					}
					?>
				</tbody>
			</table>

			<p>
				<button type="button" class="button tlcz-add">+ <?php esc_html_e( 'Thêm nút', 'button-call-zalo-tungleads' ); ?></button>
				<span class="description">
					<?php
					printf(
						/* translators: %d: số nút tối đa. */
						esc_html__( 'Tối đa %d nút.', 'button-call-zalo-tungleads' ),
						(int) TLCZ_MAX_ROWS
					);
					?>
				</span>
			</p>

			<h2><?php esc_html_e( 'Custom CSS / JS', 'button-call-zalo-tungleads' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Chỉ có tác dụng cho plugin này, và chỉ được in ra khi widget đang hiển thị (tắt widget = không in gì).', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Nên scope CSS bằng `.wd-contact-widget`, ví dụ: .wd-contact-widget { top: 40% }', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'JS chạy sau khi widget được in; sự kiện `wd-contact:click` (bubble, kèm detail.type + detail.phone) dùng được để gắn tracking.', 'button-call-zalo-tungleads' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="tlcz-custom-css"><?php esc_html_e( 'Custom CSS', 'button-call-zalo-tungleads' ); ?></label></th>
					<td>
						<textarea id="tlcz-custom-css" name="<?php echo esc_attr( TLCZ_OPTION ); ?>[custom_css]" rows="7" class="large-text code" spellcheck="false" placeholder=".wd-contact-widget { top: 45%; }"><?php echo esc_textarea( (string) $settings['custom_css'] ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="tlcz-custom-js"><?php esc_html_e( 'Custom JS', 'button-call-zalo-tungleads' ); ?></label></th>
					<td>
						<textarea id="tlcz-custom-js" name="<?php echo esc_attr( TLCZ_OPTION ); ?>[custom_js]" rows="7" class="large-text code" spellcheck="false" placeholder="document.addEventListener('wd-contact:click', function (e) { console.log(e.detail); });"><?php echo esc_textarea( (string) $settings['custom_js'] ); ?></textarea>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>

			<p class="tlcz-credit"><?php echo tlcz_credit_line(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần, chỉ có 1 link cố định. ?></p>
		</form>
	</div>

	<template id="tlcz-row-tpl">
		<?php
		tlcz_row_html(
			'__i__',
			array(
				'type'    => 'phone',
				'label'   => '',
				'value'   => '',
				'enabled' => 1,
			)
		);
		?>
	</template>
	<?php
}
