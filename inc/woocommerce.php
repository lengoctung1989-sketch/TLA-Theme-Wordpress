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

// CP3.1 — 20 sản phẩm/trang (4 cột × 5 dòng). Phải đặt sớm: WooCommerce đọc
// filter này ở pre_get_posts (trước template_redirect). Prio 20 để thắng default 10 của theme cha.
add_filter( 'loop_shop_per_page', static fn () => 20, 20 );

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

			// Bỏ H1 trùng (pagehero đã in tiêu đề) + gỡ mô tả khỏi header mặc định
			// để render lại DƯỚI lưới sản phẩm (có nút thu gọn) trong cp_shop_desc_below().
			add_filter( 'woocommerce_show_page_title', '__return_false' );
			remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
			remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
			add_action( 'woocommerce_after_main_content', 'cp_shop_desc_below', 5 );

			// Thanh công cụ: [số kết quả] … [nút khoảng giá] [sắp xếp]. WooCommerce in
			// result_count @20, catalog_ordering @30 trên hook này → bọc @19/@35.
			add_action( 'woocommerce_before_shop_loop', 'cp_shop_toolbar_open', 19 );
			add_action( 'woocommerce_before_shop_loop', 'cp_price_filter_bar', 25 );
			add_action( 'woocommerce_before_shop_loop', 'cp_shop_toolbar_close', 35 );
			// Khi lọc ra 0 sản phẩm, hook trên không chạy → vẫn in nút giá để người dùng bỏ lọc.
			add_action( 'woocommerce_no_products_found', 'cp_price_filter_bar', 5 );
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

			// Bỏ H2 "Mô tả" lặp trong panel (nhãn tab đã có chữ này).
			add_filter( 'woocommerce_product_description_heading', '__return_null' );

			// Mô tả ngắn: hộp nổi bật + thu gọn sẵn (nút Xem thêm / Thu gọn).
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			add_action( 'woocommerce_single_product_summary', 'cp_single_short_desc', 20 );
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
	echo '<aside class="cp-shop-aside">';
	cp_product_cat_box(); // tái dùng style sidebar của trang chi tiết SP (.cp-side-box / .cp-side-cats).
	echo '</aside><div class="cp-shop-main">';
}

/** Ô "Cam kết Cao Phát" ở cột phải trang chi tiết SP — icon + dòng chữ hoa. */
function cp_trust_box(): void {
	// [nhãn, path SVG 24×24 stroke]. Sửa nội dung qua filter cp_trust_items.
	$items = (array) apply_filters(
		'cp_trust_items',
		array(
			array( __( 'Sang trọng bền đẹp', 'tungleads-theme' ), 'M6 3h12l4 6-10 13L2 9zM11 3 8 9l4 13 4-13-3-6M2 9h20' ),
			array( __( 'Chịu nước tuyệt đối', 'tungleads-theme' ), 'M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z' ),
			array( __( 'Đa dạng kiểu mẫu', 'tungleads-theme' ), 'm12 2 10 5-10 5L2 7zM2 12l10 5 10-5M2 17l10 5 10-5' ),
			array( __( 'Giá gốc tại xưởng', 'tungleads-theme' ), 'M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82zM7 7h.01' ),
			array( __( 'Bảo hành 3 năm', 'tungleads-theme' ), 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4' ),
		)
	);
	if ( ! $items ) {
		return;
	}

	echo '<div class="cp-side-box cp-trust-box"><h4>' . esc_html__( 'Cam kết Cao Phát', 'tungleads-theme' ) . '</h4>';
	echo '<div class="cp-trust-list">';
	foreach ( $items as $it ) {
		printf(
			'<div class="cp-trust-item"><span class="cp-trust-item__ic" aria-hidden="true">'
			. '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="%s"/></svg>'
			. '</span><span class="cp-trust-item__label">%s</span></div>',
			esc_attr( (string) ( $it[1] ?? '' ) ),
			esc_html( (string) ( $it[0] ?? '' ) )
		);
	}
	echo '</div></div>';
}

function cp_shop_close(): void {
	echo '</div></div></div></section>';
}

/**
 * Mô tả danh mục / trang cửa hàng — render DƯỚI lưới sản phẩm, thu gọn sẵn + nút "Xem thêm".
 * Nội dung lấy từ 2 hàm mặc định đã gỡ khỏi header ở template_redirect.
 */
function cp_shop_desc_below(): void {
	if ( ! function_exists( 'woocommerce_taxonomy_archive_description' ) ) {
		return;
	}

	ob_start();
	woocommerce_taxonomy_archive_description();
	woocommerce_product_archive_description();
	$html = trim( (string) ob_get_clean() );
	if ( '' === $html ) {
		return;
	}

	echo '<div class="cp-shop-desc">';
	echo '<div class="cp-shop-desc__body">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button type="button" class="cp-shop-desc__toggle" aria-expanded="false">'
		. '<span class="cp-shop-desc__more">' . esc_html__( 'Xem thêm', 'tungleads-theme' ) . '</span>'
		. '<span class="cp-shop-desc__less">' . esc_html__( 'Thu gọn', 'tungleads-theme' ) . '</span>'
		. '</button>';
	echo '</div>';
	?>
<script>
(function () {
	var box = document.currentScript.previousElementSibling;
	var btn = box && box.querySelector('.cp-shop-desc__toggle');
	if ( ! btn ) { return; }
	btn.addEventListener('click', function () {
		var open = box.classList.toggle('is-open');
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	});
})();
</script>
	<?php
}

function cp_shop_toolbar_open(): void {
	echo '<div class="cp-shop-toolbar">';
}

function cp_shop_toolbar_close(): void {
	echo '</div>';
}

/**
 * Nút lọc theo khoảng giá — dùng query var min_price / max_price sẵn có của WooCommerce.
 * Bấm nút đang chọn = bỏ lọc. Giữ nguyên tham số orderby hiện tại.
 */
function cp_price_filter_bar(): void {
	$ranges = array(
		array( 'label' => __( 'Từ 1 – 3 triệu', 'tungleads-theme' ), 'min' => 1000000, 'max' => 3000000 ),
		array( 'label' => __( 'Từ 3 – 5 triệu', 'tungleads-theme' ), 'min' => 3000000, 'max' => 5000000 ),
		array( 'label' => __( 'Trên 5 triệu', 'tungleads-theme' ), 'min' => 5000000, 'max' => 0 ),
	);

	$base = is_shop() ? get_permalink( wc_get_page_id( 'shop' ) ) : get_term_link( get_queried_object() );
	if ( ! $base || is_wp_error( $base ) ) {
		return;
	}

	$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cur_min = isset( $_GET['min_price'] ) ? (int) $_GET['min_price'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cur_max = isset( $_GET['max_price'] ) ? (int) $_GET['max_price'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$link = static function ( array $args ) use ( $base, $orderby ): string {
		if ( '' !== $orderby ) {
			$args['orderby'] = $orderby;
		}
		return $args ? add_query_arg( $args, $base ) : $base;
	};

	echo '<div class="cp-price-filter">';
	echo '<span class="cp-price-filter__label">' . esc_html__( 'Khoảng giá:', 'tungleads-theme' ) . '</span>';

	$all_active = ( 0 === $cur_min && 0 === $cur_max );
	printf(
		'<a class="cp-price-filter__btn%s" href="%s">%s</a>',
		$all_active ? ' is-active' : '',
		esc_url( $link( array() ) ),
		esc_html__( 'Tất cả', 'tungleads-theme' )
	);

	foreach ( $ranges as $r ) {
		$active = ( $cur_min === $r['min'] && $cur_max === $r['max'] );
		$args   = array();
		if ( ! $active ) {
			$args['min_price'] = $r['min'];
			if ( $r['max'] > 0 ) {
				$args['max_price'] = $r['max'];
			}
		}
		printf(
			'<a class="cp-price-filter__btn%s" href="%s">%s</a>',
			$active ? ' is-active' : '',
			esc_url( $link( $args ) ),
			esc_html( $r['label'] )
		);
	}

	echo '</div>';
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
	// Bảng "Thông số kỹ thuật" — trong cột chính, trên ô mô tả/tabs.
	cp_single_spec_table();

	// Cụm tabs mô tả/đánh giá — ô nội dung riêng trong cột chính.
	if ( function_exists( 'woocommerce_output_product_data_tabs' ) ) {
		echo '<div class="cp-single-tabs cp-card">';
		woocommerce_output_product_data_tabs();
		echo '</div>';
	}

	echo '</div>'; // .cp-single-main

	// Cột phải (sibling của .cp-single-main trong lưới 9/3).
	echo '<aside class="cp-single-side">';
	cp_single_support_box();
	cp_trust_box();
	cp_single_new_products_box();
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
	echo '</div><a class="cp-btn cp-btn-accent" href="tel:' . esc_attr( $tel ) . '"><span>'
		. esc_html__( 'Gọi ngay', 'tungleads-theme' )
		. ' <span class="cp-tel-num">' . esc_html( $disp ) . '</span></span>'
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

/**
 * CP3.2 — Bảng "Thông số kỹ thuật" dưới ô mô tả.
 * Đọc post meta `_tlcp_spec_*` do plugin tl-site-caophat lưu. Trường rỗng thì bỏ qua;
 * không có trường nào → không in gì. Nhãn + icon là phần trình bày nên định ở child theme.
 */
function cp_single_spec_table(): void {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	// key meta (bỏ tiền tố _tlcp_spec_) => [nhãn, path SVG 24×24 stroke].
	$rows = array(
		'size'      => array( __( 'Kích thước', 'tungleads-theme' ), 'M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7' ),
		'door_type' => array( __( 'Loại cửa', 'tungleads-theme' ), 'M4 21h16M6 21V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17M14 12h.01' ),
		'leaf'      => array( __( 'Cánh', 'tungleads-theme' ), 'm12 2 10 5-10 5L2 7l10-5zM2 12l10 5 10-5M2 17l10 5 10-5' ),
		'frame'     => array( __( 'Khung', 'tungleads-theme' ), 'M3 3h18v18H3zM3 9h18M9 3v18' ),
		'features'  => array( __( 'Tính năng', 'tungleads-theme' ), 'm12 3 2.6 5.9L21 9.7l-4.7 4 1.5 6.3L12 16.6 6.2 20l1.5-6.3L3 9.7l6.4-.8L12 3z' ),
		'origin'    => array( __( 'Xuất xứ', 'tungleads-theme' ), 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z' ),
		'warranty'  => array( __( 'Bảo hành', 'tungleads-theme' ), 'M12 22s8-3.6 8-10V5l-8-3-8 3v7c0 6.4 8 10 8 10zM9 12l2 2 4-4' ),
		'note'      => array( __( 'Ghi chú', 'tungleads-theme' ), 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 2v6h6M8 13h8M8 17h5' ),
	);

	$items = array();
	foreach ( $rows as $key => $def ) {
		$val = trim( (string) $product->get_meta( '_tlcp_spec_' . $key ) );
		if ( '' !== $val ) {
			$items[] = array( $def[0], $def[1], $val );
		}
	}
	if ( ! $items ) {
		return;
	}

	echo '<div class="cp-spec cp-card">';
	echo '<h3 class="cp-spec__title">' . esc_html__( 'Thông số kỹ thuật', 'tungleads-theme' ) . '</h3>';
	echo '<dl class="cp-spec__grid">';
	foreach ( $items as $it ) {
		list( $label, $path, $val ) = $it;
		printf(
			'<div class="cp-spec__row"><span class="cp-spec__ic" aria-hidden="true">'
			. '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="%s"/></svg>'
			. '</span><span class="cp-spec__text"><dt class="cp-spec__label">%s</dt><dd class="cp-spec__value">%s</dd></span></div>',
			esc_attr( $path ),
			esc_html( $label ),
			nl2br( esc_html( $val ) )
		);
	}
	echo '</dl></div>';
}

/**
 * CP3.2 — Mô tả ngắn: hộp nổi bật, thu gọn sẵn + nút Xem thêm / Thu gọn.
 * Thay `woocommerce_template_single_excerpt`. Giữ class gốc để CSS/JS khác không vỡ.
 */
function cp_single_short_desc(): void {
	global $post;
	$html = $post ? apply_filters( 'woocommerce_short_description', $post->post_excerpt ) : '';
	if ( '' === trim( (string) $html ) ) {
		return;
	}

	echo '<div class="cp-short-desc woocommerce-product-details__short-description">';
	echo '<div class="cp-short-desc__body">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button type="button" class="cp-short-desc__toggle" aria-expanded="false">'
		. '<span class="cp-short-desc__more">' . esc_html__( 'Xem thêm', 'tungleads-theme' ) . '</span>'
		. '<span class="cp-short-desc__less">' . esc_html__( 'Thu gọn', 'tungleads-theme' ) . '</span>'
		. '</button>';
	echo '</div>';
	?>
<script>
(function () {
	var box  = document.currentScript.previousElementSibling;
	var btn  = box && box.querySelector('.cp-short-desc__toggle');
	var body = box && box.querySelector('.cp-short-desc__body');
	if (!btn || !body) { return; }
	// Nội dung vốn ngắn hơn mức thu gọn → không cần nút.
	if (body.scrollHeight <= body.clientHeight + 4) { btn.hidden = true; return; }
	btn.addEventListener('click', function () {
		var open = box.classList.toggle('is-open');
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	});
})();
</script>
	<?php
}

/** Hàng nút liên hệ: "Gọi ngay" (cam) + "Chat Zalo" (xanh) — cùng dòng, dưới nút mua. */
function cp_single_hotline_btn(): void {
	$tel  = cp_hotline_tel();
	$disp = cp_hotline_display();
	$zalo = (string) apply_filters( 'cp_zalo_url', 'https://zalo.me/' . preg_replace( '/\D/', '', $tel ) );

	$ic_phone = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
	$ic_zalo  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

	echo '<div class="cp-contact-row">';
	printf(
		'<a class="cp-contact-btn cp-contact-btn--call" href="tel:%s"><span class="cp-contact-btn__ic" aria-hidden="true">%s</span><span>%s <span class="cp-tel-num">%s</span></span></a>',
		esc_attr( $tel ),
		$ic_phone, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Gọi ngay', 'tungleads-theme' ),
		esc_html( $disp )
	);
	printf(
		'<a class="cp-contact-btn cp-contact-btn--zalo" href="%s" target="_blank" rel="nofollow noopener"><span class="cp-contact-btn__ic" aria-hidden="true">%s</span><span>%s</span></a>',
		esc_url( $zalo ),
		$ic_zalo, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Chat Zalo', 'tungleads-theme' )
	);
	echo '</div>';
}

/**
 * CP3.2 — Nhãn nút mua: "Thêm vào giỏ hàng" → "Thêm giỏ hàng".
 * Chữ hoa ("THÊM GIỎ HÀNG") do CSS `text-transform: uppercase` của
 * `.single_add_to_cart_button` lo, nên chuỗi i18n giữ dạng thường.
 */
add_filter(
	'woocommerce_product_single_add_to_cart_text',
	static function () {
		return __( 'Thêm giỏ hàng', 'tungleads-theme' );
	}
);

/** Nút "MUA HÀNG" (gọi điện) — ô lớn cạnh nút "Thêm giỏ hàng". */
function cp_single_buynow_btn(): void {
	$ic_bag = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0"/></svg>';
	printf(
		'<a class="cp-buynow" href="tel:%s"><span class="cp-buynow__ic" aria-hidden="true">%s</span><span class="cp-buynow__txt"><strong>%s</strong><span>%s</span></span></a>',
		esc_attr( cp_hotline_tel() ),
		$ic_bag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

/** Box "Sản phẩm mới" cho cột phải chi tiết SP — 5 SP mới nhất (trừ SP đang xem). */
function cp_single_new_products_box(): void {
	global $product;
	$exclude = $product instanceof WC_Product ? array( $product->get_id() ) : array();

	$items = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'exclude' => $exclude,
		)
	);
	if ( ! $items ) {
		return;
	}

	echo '<div class="cp-side-box"><h4>' . esc_html__( 'Sản phẩm mới', 'tungleads-theme' ) . '</h4>';
	echo '<ul class="cp-side-news">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s"><span class="cp-side-news__thumb">%s</span>'
			. '<span class="cp-side-news__text"><span class="cp-side-news__name">%s</span>'
			. '<span class="cp-side-news__price">%s</span></span></a></li>',
			esc_url( get_permalink( $item->get_id() ) ),
			$item->get_image( 'woocommerce_gallery_thumbnail' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item->get_name() ),
			wp_kses_post( $item->get_price_html() )
		);
	}
	echo '</ul></div>';
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

	// Nguồn dữ liệu: plugin tl-site-caophat (Settings → Cao Phát). Plugin tắt → dùng danh
	// sách mặc định tại chỗ để box không trắng; filter `cp_support_branches` vẫn ghi đè được.
	$branches = function_exists( 'tlcp_support_branches' )
		? (array) tlcp_support_branches()
		: array(
			array(
				'name' => __( 'CN Quận 7', 'tungleads-theme' ),
				'tel'  => '0834.484.484',
			),
			array(
				'name' => __( 'CN Bình Tân', 'tungleads-theme' ),
				'tel'  => '0834.713.713',
			),
			array(
				'name' => __( 'CN Bến Cát', 'tungleads-theme' ),
				'tel'  => '0814.627.610',
			),
			array(
				'name' => __( 'Giải đáp thắc mắc', 'tungleads-theme' ),
				'tel'  => '0834.627.627',
			),
		);
	$branches = (array) apply_filters( 'cp_support_branches', $branches );
	if ( $branches ) {
		echo '<ul class="cp-side-branches">';
		foreach ( $branches as $branch ) {
			$name = isset( $branch['name'] ) ? trim( (string) $branch['name'] ) : '';
			$num  = isset( $branch['tel'] ) ? trim( (string) $branch['tel'] ) : '';
			if ( '' === $name || '' === $num ) {
				continue;
			}
			printf(
				'<li><a href="tel:%s"><span class="cp-side-branch__name">%s</span><span class="cp-side-branch__num">%s</span></a></li>',
				esc_attr( preg_replace( '/\D/', '', $num ) ),
				esc_html( $name ),
				esc_html( $num )
			);
		}
		echo '</ul>';
	}

	echo '<p class="cp-side-support-note">' . esc_html__( 'Gọi ngay để nhận báo giá & khảo sát miễn phí.', 'tungleads-theme' ) . '</p>';
	echo '</div>';
}
