<?php
/**
 * Skin trang cửa hàng / lưu trữ product_cat|product_tag theo mockup Cao Phát.
 * Toàn bộ bằng hook — KHÔNG copy template WooCommerce.
 *
 * CP3.1 shop-danh-mục
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Chỉ áp dụng cho trang shop + lưu trữ taxonomy sản phẩm (không đụng single product).
 */
function cp_is_shop_archive(): bool {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
}

add_action(
	'template_redirect',
	static function (): void {
		if ( ! cp_is_shop_archive() ) {
			return;
		}

		// Bỏ wrapper + breadcrumb mặc định của WooCommerce.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		add_action( 'woocommerce_before_main_content', 'cp_shop_open', 10 );
		add_action( 'woocommerce_after_main_content', 'cp_shop_close', 10 );

		// Loop item → cấu trúc .cp-card (không dùng link bọc ngoài của WooCommerce).
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

		add_action( 'woocommerce_before_shop_loop_item_title', 'cp_loop_media_open', 5 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'cp_loop_body_open', 20 );
		add_action( 'woocommerce_shop_loop_item_title', 'cp_loop_title', 10 );
		add_action( 'woocommerce_after_shop_loop_item', 'cp_loop_body_close', 20 );

		add_filter( 'woocommerce_loop_add_to_cart_link', 'cp_loop_detail_link', 10, 2 );
	}
);

/**
 * Mở: pagehero + layout 2 cột (sidebar danh mục + vùng sản phẩm).
 */
function cp_shop_open(): void {
	echo '<section class="cp-pagehero"><div class="cp-container">';
	woocommerce_breadcrumb(
		array(
			'wrap_before' => '<nav class="cp-breadcrumb">',
			'wrap_after'  => '</nav>',
		)
	);
	echo '<h1>' . esc_html( wp_strip_all_tags( woocommerce_page_title( false ) ) ) . '</h1>';
	echo '</div></section>';

	echo '<section class="cp-section"><div class="cp-container"><div class="cp-shop">';
	echo '<aside class="cp-sidebar"><h4>' . esc_html__( 'Danh mục sản phẩm', 'tungleads-theme' ) . '</h4><ul>';
	wp_list_categories(
		array(
			'taxonomy'   => 'product_cat',
			'title_li'   => '',
			'show_count' => true,
			'hide_empty' => true,
			'depth'      => 1,
		)
	);
	echo '</ul></aside><div class="cp-shop-main">';
}

/**
 * Đóng: vùng sản phẩm + layout + section.
 */
function cp_shop_close(): void {
	echo '</div></div></div></section>';
}

function cp_loop_media_open(): void {
	global $product;
	$url = $product instanceof WC_Product ? get_permalink( $product->get_id() ) : '#';
	echo '<div class="cp-card-media"><a href="' . esc_url( $url ) . '">';
}

function cp_loop_body_open(): void {
	global $product;
	echo '</a></div><div class="cp-card-body">';
	if ( $product instanceof WC_Product ) {
		$names = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
		if ( ! empty( $names[0] ) ) {
			echo '<span class="cp-card-cat">' . esc_html( $names[0] ) . '</span>';
		}
	}
}

function cp_loop_title(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	printf(
		'<h3 class="cp-card-title"><a href="%s">%s</a></h3>',
		esc_url( get_permalink( $product->get_id() ) ),
		esc_html( $product->get_name() )
	);
}

function cp_loop_body_close(): void {
	echo '</div>';
}

/**
 * Nút giỏ hàng trong loop → link "Xem chi tiết" theo mockup.
 *
 * @param string     $html
 * @param WC_Product $product
 */
function cp_loop_detail_link( $html, $product ): string {
	return sprintf(
		'<a href="%s" class="cp-card-btn">%s</a>',
		esc_url( get_permalink( $product->get_id() ) ),
		esc_html__( 'Xem chi tiết', 'tungleads-theme' )
	);
}
