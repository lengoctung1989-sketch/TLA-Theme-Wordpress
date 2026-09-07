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

/**
 * Script tách dải thumbnail gallery ra ô riêng + nút mũi tên — chỉ trang chi tiết SP.
 */
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! cp_is_single_product() ) {
			return;
		}
		$rel  = '/assets/single-gallery.js';
		$path = get_stylesheet_directory() . $rel;
		wp_enqueue_script(
			'cp-single-gallery',
			get_stylesheet_directory_uri() . $rel,
			array(),
			file_exists( $path ) ? (string) filemtime( $path ) : wp_get_theme()->get( 'Version' ),
			true
		);
	},
	20
);

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

			// Bọc giá trong hộp sáng + badge % giảm.
			add_action( 'woocommerce_single_product_summary', 'cp_single_price_box_open', 9 );
			add_action( 'woocommerce_single_product_summary', 'cp_single_price_box_close', 11 );

			// Nút "MUA HÀNG" (gọi điện) cạnh nút thêm giỏ; nút hotline phụ full-width.
			add_action( 'woocommerce_before_add_to_cart_button', 'cp_single_buynow_btn', 5 );
			add_action( 'woocommerce_after_add_to_cart_button', 'cp_single_hotline_btn', 20 );

			// Mũi tên điều hướng cho gallery flexslider.
			add_filter( 'woocommerce_single_product_carousel_options', 'cp_gallery_carousel_options' );

			// Tách "sản phẩm liên quan" khỏi div.product để đặt full-width nền xám.
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

			// Tách cụm tabs mô tả/đánh giá ra ô riêng (render lại trong cp_single_close).
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
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
	echo '<section class="cp-section cp-single-section"><div class="cp-container">';
	woocommerce_breadcrumb(
		array(
			'wrap_before' => '<nav class="cp-breadcrumb cp-breadcrumb--bar">',
			'wrap_after'  => '</nav>',
		)
	);
	echo '<div class="cp-single-layout"><div class="cp-single-main">';
}

function cp_single_close(): void {
	// Cụm tabs mô tả/đánh giá — ô nội dung riêng, tách khỏi thẻ sản phẩm.
	if ( function_exists( 'woocommerce_output_product_data_tabs' ) ) {
		echo '<div class="cp-single-tabs cp-card">';
		woocommerce_output_product_data_tabs();
		echo '</div>';
	}
	echo '</div>'; // .cp-single-main

	// Cột phải: danh mục sản phẩm + hỗ trợ trực tuyến (sibling của .cp-single-main trong lưới 9/3).
	echo '<aside class="cp-single-side">';
	cp_product_cat_box();
	cp_single_support_box();
	echo '</aside>';
	echo '</div>'; // .cp-single-layout
	echo '</div></section>'; // .cp-container / .cp-single-section

	// Sản phẩm liên quan — dải full-width nền xám (carousel cuộn ngang).
	if ( function_exists( 'woocommerce_output_related_products' ) ) {
		echo '<section class="cp-section cp-related-band"><div class="cp-container">';
		woocommerce_output_related_products();
		echo '</div></section>';
	}

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

/** Nút "MUA HÀNG" (gọi điện) — ô lớn cạnh nút "Thêm vào giỏ". */
function cp_single_buynow_btn(): void {
	printf(
		'<a class="cp-buynow" href="tel:%s"><strong>%s</strong><span>%s</span></a>',
		esc_attr( cp_hotline_tel() ),
		esc_html__( 'Mua hàng', 'tungleads-theme' ),
		esc_html__( 'Gọi điện xác nhận và giao hàng tận nơi', 'tungleads-theme' )
	);
}

/**
 * Giá gốc & giá bán (xử lý cả biến thể: lấy khoảng giảm lớn nhất).
 *
 * @return array{0: float, 1: float} [giá gốc, giá bán]
 */
function cp_regular_sale_price( WC_Product $product ): array {
	if ( $product->is_type( 'variable' ) ) {
		return array(
			(float) $product->get_variation_regular_price( 'max' ),
			(float) $product->get_variation_sale_price( 'min' ),
		);
	}
	return array( (float) $product->get_regular_price(), (float) $product->get_sale_price() );
}

/** % giảm giá (0 nếu không giảm hợp lệ). */
function cp_sale_percent( ?WC_Product $product ): int {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}
	list( $regular, $sale ) = cp_regular_sale_price( $product );
	if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
		return 0;
	}
	return (int) round( ( $regular - $sale ) / $regular * 100 );
}

/**
 * Badge sale trên ảnh: đổi "Giảm giá!" -> "-N%" (mọi nơi: shop, single, liên quan).
 *
 * @param string     $html
 * @param \WP_Post    $post
 * @param WC_Product  $product
 */
add_filter(
	'woocommerce_sale_flash',
	static function ( $html, $post, $product ) {
		$pct = cp_sale_percent( $product instanceof WC_Product ? $product : null );
		return '<span class="onsale">'
			. ( $pct > 0 ? '-' . esc_html( (string) $pct ) . '%' : esc_html__( 'SALE', 'tungleads-theme' ) )
			. '</span>';
	},
	10,
	3
);

/** Mở hộp giá (giá do WooCommerce in ở prio 10; tag "Tiết kiệm" in ở _close). */
function cp_single_price_box_open(): void {
	echo '<div class="cp-price-box">';
}

/** Đóng hộp giá + tag "Tiết kiệm <số tiền>" (đặt SAU .price để nằm dưới giá). */
function cp_single_price_box_close(): void {
	global $product;

	if ( $product instanceof WC_Product && $product->is_on_sale() ) {
		list( $regular, $sale ) = cp_regular_sale_price( $product );
		$diff = $regular - $sale;
		if ( $diff > 0 ) {
			echo '<span class="cp-price-off">'
				. esc_html__( 'Tiết kiệm', 'tungleads-theme' ) . ' '
				. wp_kses_post( wc_price( $diff ) )
				. '</span>';
		}
	}

	echo '</div>';
}

/** Bật mũi tên prev/next cho carousel ảnh sản phẩm (flexslider của WooCommerce). */
function cp_gallery_carousel_options( $options ) {
	$options['directionNav'] = true;
	return $options;
}

/** Box "Danh mục sản phẩm" cho sidebar. */
function cp_product_cat_box(): void {
	echo '<div class="cp-side-box"><h4>' . esc_html__( 'Danh mục sản phẩm', 'tungleads-theme' ) . '</h4>';
	echo '<ul class="cp-side-cats">';
	wp_list_categories(
		array(
			'taxonomy'   => 'product_cat',
			'title_li'   => '',
			'show_count' => true,
			'hide_empty' => true,
			'depth'      => 1,
		)
	);
	echo '</ul></div>';
}

/** Box "Hỗ trợ trực tuyến" cho sidebar. */
function cp_single_support_box(): void {
	$tel  = cp_hotline_tel();
	$disp = cp_hotline_display();
	$icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';

	echo '<div class="cp-side-box cp-side-support">';
	echo '<h4>' . esc_html__( 'Hỗ trợ trực tuyến', 'tungleads-theme' ) . '</h4>';
	echo '<p class="cp-side-support-label">' . esc_html__( 'Tư vấn bán hàng', 'tungleads-theme' ) . '</p>';
	echo '<a class="cp-side-support-tel" href="tel:' . esc_attr( $tel ) . '">'
		. '<span class="ic" aria-hidden="true">' . $icon . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		. esc_html( $disp ) . '</a>';
	echo '<p class="cp-side-support-note">' . esc_html__( 'Gọi ngay để nhận báo giá & khảo sát miễn phí.', 'tungleads-theme' ) . '</p>';
	echo '</div>';
}
