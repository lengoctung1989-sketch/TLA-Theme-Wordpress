<?php
/**
 * Skin WooCommerce theo mockup Cao Phát — TOÀN BỘ bằng hook, KHÔNG copy template.
 *
 *   CP3.1 — trang cửa hàng / lưu trữ product_cat|product_tag
 *   CP3.2 — trang chi tiết sản phẩm
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

/** Trang shop + lưu trữ taxonomy sản phẩm. */
function cp_is_shop_archive(): bool {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
}

/** Trang chi tiết 1 sản phẩm. */
function cp_is_single_product(): bool {
	return function_exists( 'is_product' ) && is_product();
}

add_action(
	'template_redirect',
	static function (): void {
		$archive = cp_is_shop_archive();
		$single  = cp_is_single_product();
		if ( ! $archive && ! $single ) {
			return;
		}

		/* ---- Loop item → .cp-card (dùng cho lưới shop VÀ sản phẩm liên quan) ---- */
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'cp_loop_media_open', 5 );
		add_action( 'woocommerce_before_shop_loop_item_title', 'cp_loop_body_open', 20 );
		add_action( 'woocommerce_shop_loop_item_title', 'cp_loop_title', 10 );
		add_action( 'woocommerce_after_shop_loop_item', 'cp_loop_body_close', 20 );
		add_filter( 'woocommerce_loop_add_to_cart_link', 'cp_loop_detail_link', 10, 2 );

		/* ---- Wrapper chung: bỏ wrapper + breadcrumb mặc định ---- */
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		if ( $archive ) {
			add_action( 'woocommerce_before_main_content', 'cp_shop_open', 10 );
			add_action( 'woocommerce_after_main_content', 'cp_shop_close', 10 );
		}

		if ( $single ) {
			add_action( 'woocommerce_before_main_content', 'cp_single_open', 10 );
			add_action( 'woocommerce_after_main_content', 'cp_single_close', 10 );
			add_action( 'woocommerce_single_product_summary', 'cp_single_cat_label', 4 );
			add_action( 'woocommerce_after_add_to_cart_button', 'cp_single_hotline_btn', 20 );
		}
	}
);

/* ============================ CP3.1 — SHOP ============================ */

function cp_shop_open(): void {
	echo '<section class="cp-pagehero"><div class="cp-container">';
	woocommerce_breadcrumb(
		array(
			'wrap_before' => '<nav class="cp-breadcrumb">',
			'wrap_after'  => '</nav>',
		)
	);
	echo '<h1>' . esc_html( wp_strip_all_tags( (string) woocommerce_page_title( false ) ) ) . '</h1>';
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

function cp_shop_close(): void {
	echo '</div></div></div></section>';
}

/* -------- loop item (shop + related) -------- */

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

/* ========================= CP3.2 — SINGLE ========================= */

function cp_single_open(): void {
	echo '<section class="cp-pagehero"><div class="cp-container">';
	echo '<h1>' . esc_html__( 'Chi tiết sản phẩm', 'tungleads-theme' ) . '</h1>';
	woocommerce_breadcrumb(
		array(
			'wrap_before' => '<nav class="cp-breadcrumb">',
			'wrap_after'  => '</nav>',
		)
	);
	echo '</div></section>';
	echo '<section class="cp-section"><div class="cp-container">';
}

function cp_single_close(): void {
	echo '</div></section>';

	// Dải CTA cuối trang.
	$tel  = cp_hotline_tel();
	$disp = cp_hotline_display();
	echo '<section class="cp-section alt"><div class="cp-container"><div class="cp-cta"><div>';
	echo '<h3>' . esc_html__( 'Cần tư vấn chọn cửa phù hợp?', 'tungleads-theme' ) . '</h3>';
	echo '<p>' . esc_html__( 'Đội ngũ Cao Phát sẵn sàng đo đạc, tư vấn và báo giá miễn phí tận nơi.', 'tungleads-theme' ) . '</p>';
	echo '</div><a class="cp-btn cp-btn-accent" href="tel:' . esc_attr( $tel ) . '">'
		. esc_html( sprintf( /* translators: %s: hotline */ __( 'Gọi ngay %s', 'tungleads-theme' ), $disp ) )
		. '</a></div></div></section>';
}

/** Nhãn danh mục phía trên tiêu đề sản phẩm. */
function cp_single_cat_label(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		echo '<span class="cp-card-cat">' . esc_html( $terms[0]->name ) . '</span>';
	}
}

/** Nút "Gọi ngay" cạnh nút thêm giỏ hàng. */
function cp_single_hotline_btn(): void {
	printf(
		'<a class="cp-btn cp-btn-accent cp-single-hotline" href="tel:%s">%s</a>',
		esc_attr( cp_hotline_tel() ),
		esc_html( sprintf( /* translators: %s: hotline */ __( 'Gọi ngay %s', 'tungleads-theme' ), cp_hotline_display() ) )
	);
}
