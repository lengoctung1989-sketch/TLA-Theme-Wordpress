<?php
/**
 * Child theme Cao Phát Door — bootstrap + helper dùng chung.
 *
 * CP1.1 bootstrap
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

// CP3.1 — skin trang cửa hàng / danh mục sản phẩm (hook, không copy template).
require get_stylesheet_directory() . '/inc/woocommerce.php';

// CP5.1 — trang danh mục tin tức (khối hiển thị dùng cho `category.php`).
require get_stylesheet_directory() . '/inc/news.php';

// CP6.1 + CP6.2 — trang nội dung tĩnh (dùng cho `page.php`).
require get_stylesheet_directory() . '/inc/page.php';

// CP2.6 — Customizer: khối "Danh mục nổi bật" (chọn + kéo thả thứ tự + bố cục).
require get_stylesheet_directory() . '/inc/customizer.php';

/**
 * Hotline hiển thị + số gọi (tel:). Dùng ở header, footer, FAB, trang sản phẩm.
 *
 * CP1.3 — giá trị lấy từ Customizer ("Footer Cao Phát" → Hotline toàn site), filter vẫn ghi đè được
 * (giữ nguyên hành vi cũ cho code/config đang dùng filter). Ô để trống trong Customizer → mặc định.
 */
function cp_hotline_display(): string {
	$cp_value = trim( (string) get_theme_mod( 'cp_hotline_display', '0834.021.021' ) );
	return (string) apply_filters( 'cp_hotline_display', '' !== $cp_value ? $cp_value : '0834.021.021' );
}
function cp_hotline_tel(): string {
	$cp_value = trim( (string) get_theme_mod( 'cp_hotline_tel', '0834021021' ) );
	// Số để gọi (`tel:`) chỉ giữ chữ số và dấu `+` — số hiển thị có thể viết "0834.021.021".
	$cp_value = '' !== $cp_value ? (string) preg_replace( '/[^0-9+]/', '', $cp_value ) : '0834021021';
	return (string) apply_filters( 'cp_hotline_tel', '' !== $cp_value ? $cp_value : '0834021021' );
}

/**
 * CP1.3 — Giá trị MẶC ĐỊNH của các thiết lập "khung" footer (Customizer).
 *
 * 1 NGUỒN SỰ THẬT cho cả 3 chỗ: `default` của setting (`inc/customizer.php`) · fallback khi ô để trống
 * (`footer.php` qua `cp_footer_get()`) · tài liệu. Sửa ở đây là cả 3 theo.
 *
 * @return array<string,string|bool>
 */
function cp_footer_defaults(): array {
	return array(
		// Số cột hiển thị: 3 = chỉ dùng Cột 1–3 (dữ liệu Cột 4 vẫn giữ, đổi lại 4 là hiện ra).
		'cols'       => '4',
		// Hiện logo (Site Identity) phía trên Cột 1.
		'logo'       => true,
		'bg_color'   => '#211d19',
		'bg_image'   => '',
		// Độ đậm của LỚP MÀU đè lên ảnh nền (%): 100 = chỉ thấy màu, 0 = chỉ thấy ảnh.
		'bg_opacity' => '70',
		// `{year}` `{site}` `{hotline}` `{hotline_tel}` `{email}` — thay lúc in bằng `cp_footer_tokens()`.
		'copyright'  => '© {year} {site}',
		// KHÔNG có `credit`: dòng "Thiết kế bởi tungleads.com" nay là markup cố định ở `footer.php`
		// (kèm link tungleads.com) theo yêu cầu Tùng 2026-09-16 — không còn chỉnh từ Customizer.
	);
}

/**
 * CP1.3 — Mặc định TIÊU ĐỀ + NỘI DUNG từng cột footer (mỗi cột = tiêu đề + 1 ô soạn thảo văn bản).
 *
 * `source` = `editor` (ô soạn thảo) hoặc `menu` (menu "Footer" ở Appearance → Menus). Mặc định TẤT CẢ
 * các cột đều là `editor` — đúng yêu cầu "mỗi cột có tiêu đề + ô soạn thảo"; ai muốn 1 cột tự lấy từ
 * menu thì đổi "Nguồn nội dung" của cột đó sang "Menu". (Vị trí menu `footer` hiện chưa được gán menu
 * nào ở local nên nếu để mặc định là `menu` thì cột chỉ có tiêu đề mà không có gì bên dưới.)
 *
/**
 * CP1.3 — Mặc định TIÊU ĐỀ + NỘI DUNG từng cột footer (mỗi cột = tiêu đề + 1 ô soạn thảo văn bản).
 *
 * `source` = `editor` (ô soạn thảo) hoặc `menu` (menu "Footer" ở Appearance → Menus). Mặc định TẤT CẢ
 * các cột đều là `editor` — đúng yêu cầu "mỗi cột có tiêu đề + ô soạn thảo"; ai muốn 1 cột tự lấy từ
 * menu thì đổi "Nguồn nội dung" của cột đó sang "Menu".
 *
 * 2026-09-15 (yêu cầu Tùng): **KHÔNG còn tiêu đề / nội dung mặc định** — ô nào để trống thì cột đó
 * TRỐNG. (Trước đây ô rỗng sẽ rơi về chữ mẫu "Sản phẩm / Hỗ trợ / Liên hệ" — Tùng không muốn dữ liệu
 * mẫu tự hiện.) `footer.php` chỉ in tiêu đề / khối nội dung khi chuỗi KHÁC rỗng nên cột trống không in gì.
 *
 * @return array<int,array<string,string>>
 */
function cp_footer_columns_defaults(): array {
	$cp_empty = array(
		'source'  => 'editor',
		'title'   => '',
		'content' => '',
	);
	return array(
		1 => $cp_empty,
		2 => $cp_empty,
		3 => $cp_empty,
		4 => $cp_empty,
	);
}

/**
 * CP1.3 — Đọc 1 giá trị "khung" footer: theme_mod `cp_footer_<key>`; để trống → mặc định.
 *
 * @param string $key Khoá trong `cp_footer_defaults()`.
 */
function cp_footer_get( string $key ): string {
	$cp_defaults = cp_footer_defaults();
	$cp_value    = trim( (string) get_theme_mod( 'cp_footer_' . $key, '' ) );
	return '' !== $cp_value ? $cp_value : (string) ( $cp_defaults[ $key ] ?? '' );
}

/**
 * CP1.3 — Đọc 1 giá trị bật/tắt (checkbox) của footer; chưa từng lưu → theo mặc định.
 *
 * @param string $key Khoá trong `cp_footer_defaults()`.
 */
function cp_footer_flag( string $key ): bool {
	$cp_defaults = cp_footer_defaults();
	$cp_raw      = get_theme_mod( 'cp_footer_' . $key, null );
	if ( null === $cp_raw ) {
		return (bool) ( $cp_defaults[ $key ] ?? false );
	}
	return wp_validate_boolean( $cp_raw );
}

/**
 * CP1.3 — Đọc tiêu đề / nội dung / nguồn của 1 cột footer; để trống → mặc định của cột đó.
 *
 * @param int    $n     Số thứ tự cột (1–4).
 * @param string $field `title` · `content` · `source`.
 */
function cp_footer_col_get( int $n, string $field ): string {
	$cp_defaults = cp_footer_columns_defaults();
	$cp_default  = (string) ( $cp_defaults[ $n ][ $field ] ?? '' );
	// `content` là HTML đã qua `wp_kses_post` lúc lưu → chỉ trim, không lọc lại bằng sanitize_text_field.
	$cp_value = trim( (string) get_theme_mod( 'cp_footer_col' . $n . '_' . $field, '' ) );
	return '' !== $cp_value ? $cp_value : $cp_default;
}

/**
 * CP1.3 — Thay token trong chữ footer: `{year}` `{site}` `{hotline}` `{hotline_tel}` `{email}`.
 *
 * Nhờ token mà đổi hotline / tên site / email quản trị ở Customizer là chữ trong cột tự đổi theo.
 */
function cp_footer_tokens( string $text ): string {
	return str_replace(
		array( '{year}', '{site}', '{hotline}', '{hotline_tel}', '{email}' ),
		array( gmdate( 'Y' ), (string) get_bloginfo( 'name' ), cp_hotline_display(), cp_hotline_tel(), (string) get_option( 'admin_email' ) ),
		$text
	);
}

/**
 * CP1.3 — Như `cp_footer_tokens()` nhưng đọc theo khoá mặc định (dùng cho bản quyền / ghi công).
 *
 * @param string $key Khoá trong `cp_footer_defaults()`.
 */
function cp_footer_text( string $key ): string {
	return cp_footer_tokens( cp_footer_get( $key ) );
}

/**
 * CP1.3 — Danh sách cột sẽ render (đã cắt theo "Số cột": 3 hoặc 4).
 *
 * @return array<int,array<string,string>>
 */
function cp_footer_columns(): array {
	$cp_count = '3' === cp_footer_get( 'cols' ) ? 3 : 4;
	$cp_out   = array();
	for ( $cp_n = 1; $cp_n <= $cp_count; $cp_n++ ) {
		$cp_source = cp_footer_col_get( $cp_n, 'source' );
		$cp_out[]  = array(
			'n'       => (string) $cp_n,
			'title'   => cp_footer_col_get( $cp_n, 'title' ),
			'source'  => in_array( $cp_source, array( 'editor', 'menu' ), true ) ? $cp_source : 'editor',
			'content' => cp_footer_tokens( cp_footer_col_get( $cp_n, 'content' ) ),
		);
	}
	return $cp_out;
}

/**
 * CP1.3 — Host được phép nhúng bằng `<iframe>` trong nội dung ô soạn thảo footer.
 *
 * Chỉ để nhúng BẢN ĐỒ / VIDEO (Google Maps, YouTube, OpenStreetMap). So sánh sau khi bỏ `www.`.
 */
function cp_footer_iframe_host_ok( string $host ): bool {
	$cp_ok = array(
		'google.com',
		'google.com.vn',
		'maps.google.com',
		'youtube.com',
		'youtube-nocookie.com',
		'youtu.be',
		'openstreetmap.org',
	);
	return in_array( strtolower( preg_replace( '#^www\.#i', '', trim( $host ) ) ), $cp_ok, true );
}

/**
 * CP1.3 — KSES cho nội dung ô soạn thảo footer: y như `wp_kses_post` NHƯNG cho phép `<iframe>`.
 *
 * Vì sao cần: Tùng dán mã nhúng **Google Maps** vào cột “VỊ TRÍ CỬA HÀNG” nhưng `wp_kses_post` KHÔNG có
 * `iframe` trong bảng cho phép (đo 2026-09-15: `IN: <iframe …></iframe><p>VT</p>` → `OUT: <p>VT</p>`) ⇒
 * nội dung bị xoá ở **CẢ 2 lớp** (sanitize lúc lưu ở `inc/customizer.php` + lúc in ra ở `footer.php`)
 * nên cột trống trơn. Hàm này được dùng ở CẢ 2 chỗ đó.
 *
 * An toàn: chỉ thêm ĐÚNG thẻ `iframe` với thuộc tính nhúng thông dụng — **KHÔNG** thêm `script` / `style` /
 * `embed` / `object`; sau khi lọc còn kiểm lại `src` phải là **https** và host thuộc danh sách tin cậy
 * (`cp_footer_iframe_host_ok()`), không đạt thì bỏ hẳn thẻ iframe đó (giữ phần chữ bên trong).
 */
function cp_footer_kses_content( string $html ): string {
	$cp_tags           = wp_kses_allowed_html( 'post' );
	$cp_tags['iframe'] = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'style'           => true,
		'class'           => true,
		'title'           => true,
		'loading'         => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'frameborder'     => true,
		'referrerpolicy'  => true,
	);
	$cp_out = wp_kses( $html, $cp_tags );

	// Thẻ mở lẻ cũng khớp (`(?:.*?</iframe>)?`) để không lọt iframe host lạ khi mã nhúng thiếu thẻ đóng.
	return (string) preg_replace_callback(
		'#<iframe\b[^>]*>(?:.*?</iframe>)?#is',
		static function ( $cp_m ): string {
			if ( preg_match( '#src\s*=\s*["\']https://([^/"\']+)#i', $cp_m[0], $cp_u ) && cp_footer_iframe_host_ok( $cp_u[1] ) ) {
				return $cp_m[0];
			}
			return '';
		},
		$cp_out
	);
}

/**
 * CP1.3 — Nền footer Tùng chọn là SÁNG hay TỐI (để đổi màu chữ của theme cho đọc được).
 *
 * Chỉ xét `cp_footer_bg_color`: cả `.cp-footer` lẫn lớp màu `::before` đều dùng đúng màu này, ảnh nền
 * chỉ hiện ~(100 − opacity)% nên không đổi kết luận. Ngưỡng 0.55 = độ sáng cảm nhận (0 = đen, 1 = trắng).
 *
 * @param string $hex Màu nền footer dạng `#rgb` hoặc `#rrggbb`.
 */
function cp_footer_bg_is_light( string $hex ): bool {
	$cp_hex = ltrim( trim( $hex ), '#' );
	if ( 3 === strlen( $cp_hex ) ) {
		$cp_hex = $cp_hex[0] . $cp_hex[0] . $cp_hex[1] . $cp_hex[1] . $cp_hex[2] . $cp_hex[2];
	}
	if ( 6 !== strlen( $cp_hex ) || ! ctype_xdigit( $cp_hex ) ) {
		return false; // Không đọc được → giữ bảng màu nền tối (mặc định cũ).
	}
	$cp_r   = hexdec( substr( $cp_hex, 0, 2 ) );
	$cp_g   = hexdec( substr( $cp_hex, 2, 2 ) );
	$cp_b   = hexdec( substr( $cp_hex, 4, 2 ) );
	$cp_lum = ( 0.2126 * $cp_r + 0.7152 * $cp_g + 0.0722 * $cp_b ) / 255;
	return $cp_lum > 0.55;
}

/**
 * CP1.3 — In CSS cho nền footer (màu + ảnh + độ đậm lớp màu) vào <head>.
 *
 * Ảnh nền đặt trên chính `.cp-footer`, còn LỚP MÀU đè lên ảnh là `.cp-footer::before` với
 * `opacity: var(--cp-footer-bg-op)` (CSS ở `assets/caophat.css`). Không chọn ảnh → lớp màu trùng màu
 * nền nên giao diện y như trước.
 *
 * 2026-09-15 (yêu cầu Tùng): nền SÁNG → đổi `--cp-footer-title/fg/muted/line` sang tông ĐẬM, nếu không
 * thì chữ do theme quyết định (tiêu đề cột, dòng bản quyền, chữ phụ) là màu sáng trên nền sáng = vô
 * hình (đo được contrast **1.18**). Nền TỐI → KHÔNG in gì, CSS giữ nguyên bảng màu cũ.
 */
function cp_footer_style(): void {
	$cp_img = cp_footer_get( 'bg_image' );
	$cp_op  = max( 0, min( 100, (int) cp_footer_get( 'bg_opacity' ) ) );
	$cp_bg  = cp_footer_get( 'bg_color' );
	$cp_css = '.cp-footer{--cp-footer-bg:' . esc_attr( $cp_bg ) . ';--cp-footer-bg-op:' . esc_attr( (string) ( $cp_op / 100 ) );
	if ( cp_footer_bg_is_light( $cp_bg ) ) {
		$cp_css .= ';--cp-footer-title:#14120f;--cp-footer-fg:#2b2723;--cp-footer-muted:#57504a;--cp-footer-line:rgba(0,0,0,.12)';
	}
	$cp_css .= ';}';
	if ( '' !== $cp_img ) {
		$cp_css .= '.cp-footer{background-image:url("' . esc_url( $cp_img ) . '");}';
	}
	echo '<style id="cp-footer-css">' . $cp_css . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- đã escape từng phần.
}
add_action( 'wp_head', 'cp_footer_style', 100 );

/**
 * Asset: font Be Vietnam Pro + CSS caophat. Nạp SAU bundle của theme cha.
 */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style(
			'cp-fonts',
			'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap',
			array(),
			null
		);
		$cp_style_path = get_stylesheet_directory() . '/assets/caophat.css';
		wp_enqueue_style(
			'cp-style',
			get_stylesheet_directory_uri() . '/assets/caophat.css',
			array( 'cp-fonts' ),
			file_exists( $cp_style_path ) ? (string) filemtime( $cp_style_path ) : wp_get_theme()->get( 'Version' )
		);

		// CP1.5 — menu mobile: burger mở panel + accordion menu con.
		$cp_nav_js = get_stylesheet_directory() . '/assets/nav-menu.js';
		wp_enqueue_script(
			'cp-nav-menu',
			get_stylesheet_directory_uri() . '/assets/nav-menu.js',
			array(),
			file_exists( $cp_nav_js ) ? (string) filemtime( $cp_nav_js ) : wp_get_theme()->get( 'Version' ),
			true
		);

		// CP2.8 — nút cuộn ‹ › cho bố cục "Cuộn ngang" trên trang chủ.
		$cp_scroller_js = get_stylesheet_directory() . '/assets/scroller.js';
		wp_enqueue_script(
			'cp-scroller',
			get_stylesheet_directory_uri() . '/assets/scroller.js',
			array(),
			file_exists( $cp_scroller_js ) ? (string) filemtime( $cp_scroller_js ) : wp_get_theme()->get( 'Version' ),
			true
		);

		// CP2.9 — khối tab sản phẩm trên trang chủ: đổi tab (chuột + bàn phím).
		$cp_tabs_js = get_stylesheet_directory() . '/assets/tabs.js';
		wp_enqueue_script(
			'cp-tabs',
			get_stylesheet_directory_uri() . '/assets/tabs.js',
			array(),
			file_exists( $cp_tabs_js ) ? (string) filemtime( $cp_tabs_js ) : wp_get_theme()->get( 'Version' ),
			true
		);
	},
	20
);

/**
 * Theme cha đã lo title-tag/appearance-tools. Child chỉ thêm custom-logo.
 */
add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 48,
				'width'       => 200,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
);

/**
 * Body class `cp` để CSS caophat (body.cp ...) áp dụng.
 *
 * @param string[] $classes
 * @return string[]
 */
add_filter(
	'body_class',
	static function ( array $classes ): array {
		$classes[] = 'cp';
		return $classes;
	}
);

/**
 * Customizer: mục "Trang chủ Cao Phát" — chữ + ảnh volatile của hero/CTA.
 * Layout và khối động vẫn ở front-page.php.
 */
add_action(
	'customize_register',
	static function ( \WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'cp_home',
			array(
				'title'    => __( 'Trang chủ Cao Phát', 'tungleads-theme' ),
				'priority' => 30,
			)
		);

		$fields = array(
			'cp_hero_badge' => array( __( 'Hero — nhãn nhỏ', 'tungleads-theme' ), 'text' ),
			'cp_hero_title' => array( __( 'Hero — tiêu đề (cho phép thẻ <em>)', 'tungleads-theme' ), 'textarea' ),
			'cp_hero_desc'  => array( __( 'Hero — mô tả', 'tungleads-theme' ), 'textarea' ),
			/* 2026-09-15 (yêu cầu Tùng): nhập được 2 nút hero + 2 ô số liệu "10+ năm" / "5.000+".
			   `*_url` = link của nút (type `url` → sanitize `esc_url_raw`, giữ được `tel:`). */
			'cp_hero_btn1'        => array( __( 'Hero — nút 1', 'tungleads-theme' ), 'text' ),
			'cp_hero_btn1_url'    => array( __( 'Hero — nút 1: link (trống = trang cửa hàng)', 'tungleads-theme' ), 'url' ),
			'cp_hero_btn2'        => array( __( 'Hero — nút 2', 'tungleads-theme' ), 'text' ),
			'cp_hero_btn2_url'    => array( __( 'Hero — nút 2: link (trống = gọi hotline)', 'tungleads-theme' ), 'url' ),
			'cp_hero_stat1_value' => array( __( 'Hero — số liệu 1: số', 'tungleads-theme' ), 'text' ),
			'cp_hero_stat1_label' => array( __( 'Hero — số liệu 1: chú thích', 'tungleads-theme' ), 'text' ),
			'cp_hero_stat2_value' => array( __( 'Hero — số liệu 2: số', 'tungleads-theme' ), 'text' ),
			'cp_hero_stat2_label' => array( __( 'Hero — số liệu 2: chú thích', 'tungleads-theme' ), 'text' ),
			'cp_cta_title'  => array( __( 'CTA — tiêu đề', 'tungleads-theme' ), 'text' ),
			'cp_cta_desc'   => array( __( 'CTA — mô tả', 'tungleads-theme' ), 'textarea' ),
		);

		foreach ( $fields as $id => $field ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => '',
					'transport'         => 'refresh',
					'sanitize_callback' => 'cp_hero_title' === $id ? 'wp_kses_post' : ( 'textarea' === $field[1] ? 'sanitize_textarea_field' : ( 'url' === $field[1] ? 'esc_url_raw' : 'sanitize_text_field' ) ),
				)
			);
			$wp_customize->add_control(
				$id,
				array(
					'section' => 'cp_home',
					'label'   => $field[0],
					'type'    => $field[1],
				)
			);
		}

		$wp_customize->add_setting(
			'cp_hero_image',
			array(
				'default'           => '',
				'transport'         => 'refresh',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new \WP_Customize_Image_Control(
				$wp_customize,
				'cp_hero_image',
				array(
					'section' => 'cp_home',
					'label'   => __( 'Hero — ảnh', 'tungleads-theme' ),
				)
			)
		);
	}
);

/**
 * In 1 card sản phẩm theo markup .cp-card. Dùng ở các khối trang chủ.
 */
function cp_product_card( \WC_Product $product ): void {
	?>
	<article class="cp-card">
		<div class="cp-card-media">
			<?php
			$cp_off = function_exists( 'cp_sale_percent' ) ? cp_sale_percent( $product ) : 0;
			if ( $cp_off > 0 ) :
				?>
				<span class="cp-badge">-<?php echo esc_html( (string) $cp_off ); ?>%</span>
			<?php elseif ( $product->is_on_sale() ) : ?>
				<span class="cp-badge"><?php esc_html_e( 'SALE', 'tungleads-theme' ); ?></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
		<div class="cp-card-body">
			<h3 class="cp-card-title">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>
			<div class="cp-price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
	</article>
	<?php
}

/**
 * CP2.8 — Nút cuộn ‹ › cho bố cục "Cuộn ngang" của khối trang chủ.
 *
 * Chỉ gọi khi bố cục là `scroll`: mở `.cp-scroller` + nút trái (trước list) và đóng bằng nút phải.
 * CSS ẩn thanh trượt của dải; JS `assets/scroller.js` xử lý cuộn + ẩn nút ở 2 đầu.
 */
function cp_scroller_open(): void {
	echo '<div class="cp-scroller">';
	echo '<button type="button" class="cp-scroller__btn cp-scroller__btn--prev" aria-label="'
		. esc_attr__( 'Cuộn sang trái', 'tungleads-theme' ) . '" aria-hidden="true" tabindex="-1">‹</button>';
}

/** CP2.8 — Đóng `.cp-scroller` (đi kèm `cp_scroller_open()`). */
function cp_scroller_close(): void {
	echo '<button type="button" class="cp-scroller__btn cp-scroller__btn--next" aria-label="'
		. esc_attr__( 'Cuộn sang phải', 'tungleads-theme' ) . '" aria-hidden="true" tabindex="-1">›</button>';
	echo '</div>';
}

/* ==========================================================================
   CP2.9 — Khối "tab sản phẩm" trên trang chủ (4 tab CỐ ĐỊNH).
   Dùng chung cho Customizer (`inc/customizer.php`) và `template-parts/home-tabs.php`.
   ========================================================================== */

/**
 * CP2.9 — Danh sách tab cố định: khoá = nguồn sản phẩm, giá trị = tiêu đề mặc định.
 *
 * @return array<string, array{title: string}>
 */
function cp_product_tabs(): array {
	return array(
		'new'  => array( 'title' => __( 'Sản phẩm mới', 'tungleads-theme' ) ),
		'best' => array( 'title' => __( 'Sản phẩm bán chạy', 'tungleads-theme' ) ),
		'hot'  => array( 'title' => __( 'Sản phẩm hot', 'tungleads-theme' ) ),
		'sale' => array( 'title' => __( 'Sản phẩm khuyến mãi', 'tungleads-theme' ) ),
	);
}

/**
 * CP2.9 — Bố cục chọn được cho mỗi tab (giống hệt khối "Sản phẩm theo danh mục" CP2.4).
 *
 * @return array<string, string>
 */
function cp_tab_layouts(): array {
	return array(
		'cols-4' => __( 'Lưới 4 cột', 'tungleads-theme' ),
		'cols-3' => __( 'Lưới 3 cột', 'tungleads-theme' ),
		'cols-2' => __( 'Lưới 2 cột', 'tungleads-theme' ),
		'scroll' => __( 'Cuộn ngang', 'tungleads-theme' ),
	);
}

/**
 * CP2.9 — Sản phẩm của 1 tab.
 *
 * `new` = mới nhất theo ngày đăng · `best` = bán chạy (`total_sales` giảm dần — cùng cách lấy với
 * khối "Bán chạy" của CP2.2) · `hot` = **cờ "Nổi bật" của WooCommerce** (Sản phẩm → tick Nổi bật)
 * vì WooCommerce không có chỉ số "hot" tự tính · `sale` = đang giảm giá (`wc_get_product_ids_on_sale()`).
 *
 * @param string $source `new|best|hot|sale`.
 * @param int    $limit  Số sản phẩm (tự kẹp trong 1–20).
 * @return \WC_Product[]
 */
function cp_tab_products( string $source, int $limit ): array {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$limit = max( 1, min( 20, $limit ) );
	$args  = array(
		'status'  => 'publish',
		'limit'   => $limit,
		'orderby' => 'date',
		'order'   => 'DESC',
	);

	switch ( $source ) {
		case 'best':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			break;
		case 'hot':
			$args['featured'] = true;
			break;
		case 'sale':
			$cp_sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
			if ( ! $cp_sale_ids ) {
				return array();
			}
			$args['include'] = array_slice( array_map( 'absint', $cp_sale_ids ), 0, 50 );
			break;
		case 'new':
		default:
			break;
	}

	$cp_products = wc_get_products( $args );
	$cp_products = is_array( $cp_products ) ? $cp_products : array();

	/**
	 * CP2.9 — Đổi cách lấy sản phẩm cho 1 tab (VD muốn "hot" theo lượt xem).
	 *
	 * @param \WC_Product[] $cp_products
	 * @param string        $source
	 * @param int           $limit
	 */
	return (array) apply_filters( 'cp_tab_products', $cp_products, $source, $limit );
}

/**
 * CP2.9 — Icon SVG (inline) của từng tab, in TRƯỚC nhãn tab.
 *
 * Nét mảnh 24×24 `stroke: currentColor` giống bộ icon đang dùng ở hero/feature (front-page.php).
 *
 * @param string $source `new|best|hot|sale`.
 * @return string Markup SVG tĩnh (không chứa dữ liệu người dùng).
 */
function cp_tab_icon( string $source ): string {
	$cp_icons = array(
		/* Mới: 2 tia sáng 4 cánh (sparkle) */
		'new'  => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/>'
			. '<path d="M18.5 16.5l.7 1.8 1.8.7-1.8.7-.7 1.8-.7-1.8-1.8-.7 1.8-.7z"/>',
		/* Bán chạy: biểu đồ đi lên */
		'best' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
		/* Hot: ngọn lửa */
		'hot'  => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
		/* Khuyến mãi: thẻ giảm giá */
		'sale' => '<path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>'
			. '<circle cx="7" cy="7" r="1.5"/>',
	);

	$cp_body = $cp_icons[ $source ] ?? '';

	if ( '' === $cp_body ) {
		return '';
	}

	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
		. 'stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">' . $cp_body . '</svg>';
}

/**
 * CP4.1 — Ảnh chèn trong NỘI DUNG (mô tả danh mục / mô tả sản phẩm / bài viết) thiếu hẳn
 * thuộc tính `alt`: thêm `alt=""` để HTML hợp lệ và screen reader không đọc tên file.
 * Đo 21:40 ngày 2026-09-14: 28/28 ảnh trong mô tả danh mục "Cửa Phòng Ngủ" không có `alt`
 * (ảnh card sản phẩm đã đủ nhờ filter `wp_get_attachment_image_attributes` ở inc/woocommerce.php).
 * Muốn alt MÔ TẢ thật (tốt hơn cho SEO ảnh) thì điền trong trình soạn thảo.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! is_string( $content ) || false === strpos( $content, '<img' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $content;
		}
		$cp_tags = new WP_HTML_Tag_Processor( $content );
		while ( $cp_tags->next_tag( 'img' ) ) {
			if ( null === $cp_tags->get_attribute( 'alt' ) ) {
				$cp_tags->set_attribute( 'alt', '' );
			}
		}
		return $cp_tags->get_updated_html();
	},
	20
);
