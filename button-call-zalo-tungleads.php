<?php
/**
 * Plugin Name:  Button call/zalo - TungLeAds
 * Description:  Widget liên hệ nổi (Gọi điện + Zalo) neo sát lề phải, giữ nguyên thiết kế “Tùng Lê Ads — Contact Floating Widget v1.3”. Số điện thoại / Zalo nhập ở Settings → Button Call/Zalo.
 * Version:      1.0.1
 * Requires PHP: 8.2
 * Author:       Tung Le Ads
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
 * @return array{enabled:int,buttons:array<int,array<string,mixed>>}
 */
function tlcz_defaults(): array {
	return array(
		'enabled' => 1,
		'buttons' => array(
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

/** Kiểu nút hợp lệ (dùng cho cả sanitize lẫn render). */
function tlcz_types(): array {
	return array(
		'phone' => __( 'Gọi điện (tel:)', 'button-call-zalo-tungleads' ),
		'zalo'  => __( 'Zalo (zalo.me)', 'button-call-zalo-tungleads' ),
	);
}

/**
 * Cài đặt đã trộn default (option lưu thiếu khoá vẫn chạy đúng).
 *
 * @return array{enabled:int,buttons:array<int,array<string,mixed>>}
 */
function tlcz_settings(): array {
	$saved = get_option( TLCZ_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	$out   = tlcz_defaults();

	$out['enabled'] = isset( $saved['enabled'] ) ? (int) $saved['enabled'] : $out['enabled'];

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

		if ( '' === $value || '' === tlcz_digits( $value ) || ! $on ) {
			continue;
		}

		$rows[] = array(
			'type'  => $type,
			'label' => '' !== $label ? $label : ( 'zalo' === $type ? 'Zalo' : 'Gọi ngay' ),
			'value' => $value,
		);

		if ( count( $rows ) >= TLCZ_MAX_ROWS ) {
			break;
		}
	}

	return $rows;
}

/**
 * URL của 1 nút: `phone` → `tel:<số>`; `zalo` → `https://zalo.me/<số>`.
 *
 * Muốn link Zalo OA / link riêng cho từng nút:
 *     add_filter( 'tlcz_button_url', fn( $url, $btn ) => 'https://zalo.me/oa-cua-hang', 10, 2 );
 *
 * @param array{type:string,label:string,value:string} $btn
 */
function tlcz_button_url( array $btn ): string {
	$digits = tlcz_digits( $btn['value'] );
	$url    = 'zalo' === $btn['type'] ? 'https://zalo.me/' . $digits : 'tel:' . $digits;

	/** @param string $url @param array<string,string> $btn */
	return (string) apply_filters( 'tlcz_button_url', $url, $btn );
}

/** Icon điện thoại (SVG 24×24, stroke — đúng icon trong thiết kế). */
function tlcz_icon_phone(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true">'
		. '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6'
		. 'A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81'
		. '2 2 0 0 1-.45 2.11L8.09 9.91 a16 16 0 0 0 6 6l1.27-1.27 a2 2 0 0 1 2.11-.45'
		. '12.84 12.84 0 0 0 2.81.7 A2 2 0 0 1 22 16.92z"/></svg>';
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

/** In widget liên hệ (gọi điện + Zalo). */
function tlcz_render(): void {
	if ( ! tlcz_should_render() ) {
		return;
	}

	$buttons = tlcz_active_buttons();
	?>
<section class="wd-contact-widget" aria-label="<?php esc_attr_e( 'Thông tin liên hệ', 'button-call-zalo-tungleads' ); ?>">
	<?php foreach ( $buttons as $btn ) : ?>
		<?php
		$is_zalo = 'zalo' === $btn['type'];
		$digits  = tlcz_digits( $btn['value'] );
		$aria    = $is_zalo
			/* translators: %s: số điện thoại / Zalo. */
			? sprintf( __( 'Liên hệ Zalo %s', 'button-call-zalo-tungleads' ), $btn['value'] )
			/* translators: %s: số điện thoại. */
			: sprintf( __( 'Gọi %s', 'button-call-zalo-tungleads' ), $btn['value'] );
		?>
	<a
		class="wd-contact-item <?php echo esc_attr( $is_zalo ? 'wd-contact-zalo' : 'wd-contact-phone' ); ?>"
		href="<?php echo esc_url( tlcz_button_url( $btn ) ); ?>"
		<?php if ( $is_zalo ) : ?>
			target="_blank"
			rel="noopener noreferrer"
		<?php endif; ?>
		aria-label="<?php echo esc_attr( $aria ); ?>"
		data-tlcz-type="<?php echo esc_attr( $btn['type'] ); ?>"
		data-tlcz-value="<?php echo esc_attr( $digits ); ?>"
	>
		<span class="wd-contact-icon">
			<?php if ( $is_zalo ) : ?>
				<span class="wd-contact-zalo-text">Z</span>
			<?php else : ?>
				<?php echo tlcz_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hằng, không chứa input người dùng. ?>
			<?php endif; ?>
		</span>

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

/** Nạp JS thêm/xoá dòng — chỉ ở đúng trang cài đặt. */
add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		if ( 'settings_page_' . TLCZ_SLUG !== $hook ) {
			return;
		}
		$path = __DIR__ . '/assets/admin.js';
		wp_enqueue_script(
			'tlcz-admin',
			plugins_url( 'assets/admin.js', __FILE__ ),
			array(),
			file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0',
			true
		);
	}
);

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
 * @return array{enabled:int,buttons:array<int,array<string,mixed>>}
 */
function tlcz_sanitize( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$types = array_keys( tlcz_types() );
	$out   = array(
		'enabled' => empty( $input['enabled'] ) ? 0 : 1,
		'buttons' => array(),
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
			'type'    => $type,
			'label'   => isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '',
			'value'   => $value,
			'enabled' => empty( $row['enabled'] ) ? 0 : 1,
		);
	}

	if ( array() === $out['buttons'] ) {
		$out['buttons'] = tlcz_defaults()['buttons'];
	}

	return $out;
}

/**
 * In 1 dòng nút trong bảng repeater.
 *
 * @param string              $index Index trong mảng `buttons` — hoặc `__i__` khi dùng làm template cho JS.
 * @param array<string,mixed> $row   Dữ liệu dòng.
 */
function tlcz_row_html( string $index, array $row ): void {
	$name  = TLCZ_OPTION . '[buttons][' . $index . ']';
	$type  = isset( $row['type'] ) ? (string) $row['type'] : 'phone';
	$label = isset( $row['label'] ) ? (string) $row['label'] : '';
	$value = isset( $row['value'] ) ? (string) $row['value'] : '';
	$on    = ! isset( $row['enabled'] ) || (int) $row['enabled'] > 0;
	?>
	<tr class="tlcz-row">
		<td>
			<select name="<?php echo esc_attr( $name ); ?>[type]">
				<?php foreach ( tlcz_types() as $key => $type_label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $type_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
		<td>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Gọi ngay', 'button-call-zalo-tungleads' ); ?>">
		</td>
		<td>
			<input type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo esc_attr( $value ); ?>" placeholder="0834.021.021">
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
		<h1><?php echo esc_html__( 'Button Call/Zalo', 'button-call-zalo-tungleads' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Widget liên hệ nổi (neo sát lề phải, mở rộng khi hover) in ở cuối MỌI trang. Nhập số có dấu chấm vẫn dùng được: link “tel:” và “zalo.me” tự bỏ mọi ký tự không phải số.', 'button-call-zalo-tungleads' ); ?>
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
				<?php esc_html_e( '“Nhãn nhỏ” = dòng chữ phía trên số (ví dụ “Gọi ngay”, “Zalo”); để trống sẽ tự dùng “Gọi ngay” / “Zalo”.', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Nút ĐẦU TIÊN nếu là loại “Gọi điện” sẽ có hiệu ứng pulse (đúng thiết kế). Bỏ tick “Bật” để tạm ẩn 1 nút.', 'button-call-zalo-tungleads' ); ?><br>
				<?php esc_html_e( 'Tất cả nút đều bị tắt = widget tự ẩn. Xoá hết dòng rồi lưu = quay về 4 số mặc định.', 'button-call-zalo-tungleads' ); ?>
			</p>

			<table class="widefat striped tlcz-rows" id="tlcz-rows" data-next="<?php echo esc_attr( (string) count( $rows ) ); ?>" data-max="<?php echo esc_attr( (string) TLCZ_MAX_ROWS ); ?>">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Kiểu', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Nhãn nhỏ', 'button-call-zalo-tungleads' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Số điện thoại / Zalo', 'button-call-zalo-tungleads' ); ?></th>
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

			<?php submit_button(); ?>
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
