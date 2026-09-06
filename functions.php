<?php
/**
 * Child theme Cao Phát Door — chỉ phần skin riêng của site.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

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
		wp_enqueue_style(
			'cp-style',
			get_stylesheet_directory_uri() . '/assets/caophat.css',
			array( 'cp-fonts' ),
			wp_get_theme()->get( 'Version' )
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
 * In 1 card sản phẩm theo markup .cp-card. Dùng ở các khối trang chủ.
 */
function cp_product_card( \WC_Product $product ): void {
	$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	?>
	<article class="cp-card">
		<div class="cp-card-media">
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="cp-badge"><?php esc_html_e( 'Giảm giá', 'tungleads-theme' ); ?></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
		<div class="cp-card-body">
			<?php if ( ! empty( $cats[0] ) ) : ?>
				<span class="cp-card-cat"><?php echo esc_html( $cats[0] ); ?></span>
			<?php endif; ?>
			<h3 class="cp-card-title">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>
			<div class="cp-price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<a class="cp-card-btn" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php esc_html_e( 'Xem chi tiết', 'tungleads-theme' ); ?></a>
		</div>
	</article>
	<?php
}
