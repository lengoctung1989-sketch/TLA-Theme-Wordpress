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

/**
 * Hotline hiển thị + số gọi (tel:). Dùng ở header, footer, FAB, pattern.
 */
function cp_hotline_display(): string {
	return (string) apply_filters( 'cp_hotline_display', '0834.021.021' );
}
function cp_hotline_tel(): string {
	return (string) apply_filters( 'cp_hotline_tel', '0834021021' );
}

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
			'cp_cta_title'  => array( __( 'CTA — tiêu đề', 'tungleads-theme' ), 'text' ),
			'cp_cta_desc'   => array( __( 'CTA — mô tả', 'tungleads-theme' ), 'textarea' ),
		);

		foreach ( $fields as $id => $field ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => '',
					'transport'         => 'refresh',
					'sanitize_callback' => 'cp_hero_title' === $id ? 'wp_kses_post' : ( 'textarea' === $field[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
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
