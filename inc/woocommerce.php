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
 * CP3.3: kèm script popup "Đặt hàng nhanh" (chỉ khi plugin tl-site-caophat đang bật).
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

		if ( ! function_exists( 'tlcp_quick_order_nonce' ) ) {
			return;
		}
		$qo_rel  = '/assets/quick-order.js';
		$qo_path = get_stylesheet_directory() . $qo_rel;
		wp_enqueue_script(
			'cp-quick-order',
			get_stylesheet_directory_uri() . $qo_rel,
			array(),
			file_exists( $qo_path ) ? (string) filemtime( $qo_path ) : wp_get_theme()->get( 'Version' ),
			true
		);
		wp_localize_script(
			'cp-quick-order',
			'cpQuickOrder',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'sending' => __( 'Đang gửi đơn…', 'tungleads-theme' ),
					'error'   => __( 'Chưa gửi được đơn. Vui lòng thử lại hoặc gọi hotline.', 'tungleads-theme' ),
				),
			)
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
		/* 20:05 ngày 2026-09-14 — card KHÔNG còn nút nào: bỏ luôn nút "Thêm vào giỏ hàng" mặc định
		   của WooCommerce (trước đây filter `woocommerce_loop_add_to_cart_link` đổi nút đó thành
		   "Xem chi tiết"). Ảnh + tiêu đề trong card vẫn là link tới trang sản phẩm. */
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

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
	echo '</a></div><div class="cp-card-body">';
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
 * CP3.1 — Ảnh thiếu `alt`: lấy tên sản phẩm (bài viết cha của ảnh) làm alt.
 * Đo 21:30 ngày 2026-09-14: 29/46 ảnh ở trang danh mục không có alt → hại SEO + screen reader.
 */
add_filter(
	'wp_get_attachment_image_attributes',
	static function ( $attr, $attachment ) {
		if ( ! empty( $attr['alt'] ) || empty( $attachment->post_parent ) ) {
			return $attr;
		}
		$cp_title = get_the_title( $attachment->post_parent );
		if ( $cp_title ) {
			$attr['alt'] = $cp_title;
		}
		return $attr;
	},
	10,
	2
);

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
 * CP3.3 — Nút "MUA HÀNG" (ô lớn cạnh nút "Thêm vào giỏ").
 * Có JS + plugin bật → mở popup đặt nhanh; JS tắt/plugin tắt → link gọi hotline.
 */
function cp_single_buynow_btn(): void {
	global $product;

	$ic_bag = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0"/></svg>';

	$data = '';
	if ( function_exists( 'tlcp_quick_order_nonce' ) && $product instanceof WC_Product && $product->is_purchasable() ) {
		$data = ' data-cp-quick-order="' . esc_attr( (string) $product->get_id() ) . '"';
	}

	printf(
		'<a class="cp-buynow" href="tel:%s"%s><span class="cp-buynow__ic" aria-hidden="true">%s</span><span class="cp-buynow__txt"><strong>%s</strong><span>%s</span></span></a>',
		esc_attr( cp_hotline_tel() ),
		$data, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã esc_attr khi dựng.
		$ic_bag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Mua hàng', 'tungleads-theme' ),
		esc_html__( 'Gọi điện xác nhận và giao hàng tận nơi', 'tungleads-theme' )
	);
}

/**
 * CP3.3 — Popup "Đặt hàng nhanh" (chỉ trang chi tiết SP).
 * In ở `wp_footer` để không phụ thuộc vị trí trong luồng template.
 * Chỉ in khi plugin `tl-site-caophat` đang bật (thiếu hàm nonce = thiếu handler AJAX).
 */
function cp_quick_order_popup(): void {
	if ( ! cp_is_single_product() || ! function_exists( 'tlcp_quick_order_nonce' ) ) {
		return;
	}

	global $product;
	if ( ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
		return;
	}
	?>
<div class="cp-quick-order" id="cp-quick-order" hidden>
	<div class="cp-quick-order__overlay" data-cp-qo-close></div>
	<div class="cp-quick-order__dialog" role="dialog" aria-modal="true" aria-labelledby="cp-qo-title">
		<button type="button" class="cp-quick-order__close" data-cp-qo-close aria-label="<?php esc_attr_e( 'Đóng', 'tungleads-theme' ); ?>">&times;</button>
		<h3 class="cp-quick-order__title" id="cp-qo-title"><?php esc_html_e( 'Đặt hàng nhanh', 'tungleads-theme' ); ?></h3>

		<form class="cp-quick-order__form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-cp-qo-form>
			<input type="hidden" name="action" value="cp_quick_order">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( tlcp_quick_order_nonce() ); ?>">
			<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product->get_id() ); ?>">
			<input type="text" name="cp_hp" class="cp-quick-order__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

			<div class="cp-quick-order__product">
				<span class="cp-quick-order__thumb"><?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></span>
				<span class="cp-quick-order__meta">
					<span class="cp-quick-order__name"><?php echo esc_html( $product->get_name() ); ?></span>
					<span class="cp-quick-order__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				</span>
				<label class="cp-quick-order__qty">
					<span class="cp-quick-order__qty-label"><?php esc_html_e( 'SL', 'tungleads-theme' ); ?></span>
					<input type="number" name="qty" value="1" min="1" max="99" inputmode="numeric" aria-label="<?php esc_attr_e( 'Số lượng', 'tungleads-theme' ); ?>">
				</label>
			</div>

			<label class="cp-quick-order__field">
				<span><?php esc_html_e( 'Họ tên người nhận', 'tungleads-theme' ); ?> <em>*</em></span>
				<input type="text" name="name" required autocomplete="name">
			</label>
			<label class="cp-quick-order__field">
				<span><?php esc_html_e( 'Số điện thoại', 'tungleads-theme' ); ?> <em>*</em></span>
				<input type="tel" name="phone" required inputmode="tel" autocomplete="tel">
			</label>
			<label class="cp-quick-order__field">
				<span><?php esc_html_e( 'Địa chỉ nhận hàng', 'tungleads-theme' ); ?> <em>*</em></span>
				<input type="text" name="address" required autocomplete="street-address">
			</label>
			<label class="cp-quick-order__field">
				<span><?php esc_html_e( 'Ghi chú (không bắt buộc)', 'tungleads-theme' ); ?></span>
				<textarea name="note" rows="2"></textarea>
			</label>

			<button type="submit" class="cp-quick-order__submit"><span class="cp-quick-order__submit-ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></span><?php esc_html_e( 'Gửi đơn hàng', 'tungleads-theme' ); ?></button>
			<p class="cp-quick-order__msg" role="status" aria-live="polite"></p>
			<p class="cp-quick-order__note"><?php esc_html_e( 'Cao Phát sẽ gọi xác nhận đơn trước khi giao hàng.', 'tungleads-theme' ); ?></p>
		</form>
	</div>
</div>
	<?php
}
// Prio 5: phải in TRƯỚC `wp_print_footer_scripts` (prio 20) để script tìm thấy popup.
add_action( 'wp_footer', 'cp_quick_order_popup', 5 );

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

/* ============================ CP3.4 — CART ============================ */

/** Trang giỏ hàng (`/cart/`). */
function cp_is_cart_page(): bool {
	return function_exists( 'is_cart' ) && is_cart();
}

/** Trang thanh toán (`/checkout/`) — loại trừ trang "đơn đã nhận" (`/checkout/order-received/`). */
function cp_is_checkout_page(): bool {
	return function_exists( 'is_checkout' ) && is_checkout()
		&& ! ( function_exists( 'is_order_received_page' ) && is_order_received_page() );
}

/**
 * CP3.6 — Trang hoàn tất đơn hàng (`/checkout/order-received/`).
 * WooCommerce trả `is_checkout() === true` cho endpoint này, nhưng ta cố ý loại nó khỏi
 * `cp_is_checkout_page()` để không chèn hero "Thanh toán"; trang này có hero riêng.
 */
function cp_is_order_received_page(): bool {
	return function_exists( 'is_order_received_page' ) && is_order_received_page();
}

/**
 * CP3.6 — Nút "Về trang chủ" ở cuối trang hoàn tất đơn.
 * Gắn vào `woocommerce_thankyou` (chạy bên trong nội dung shortcode, sau card chi tiết đơn).
 */
add_action( 'woocommerce_thankyou', 'cp_thankyou_home_button', 30 );
function cp_thankyou_home_button(): void {
	if ( ! cp_is_order_received_page() ) {
		return;
	}

	echo '<div class="cp-thankyou-actions"><a class="cp-btn cp-btn-primary" href="' . esc_url( home_url( '/' ) ) . '">'
		. esc_html__( 'Về trang chủ', 'tungleads-theme' ) . '</a></div>';
}

/**
 * CP3.4 + CP3.5 — Bọc nội dung shortcode WooCommerce trong hero + container của site.
 * `page.php` của theme cha không có container, cũng không in tiêu đề trang.
 *
 * @param string $title   Tiêu đề H1 của trang.
 * @param string $class   Class đặt trên `.cp-container` (vd `cp-cart`, `cp-checkout`).
 * @param string $content Nội dung gốc của trang.
 */
function cp_woo_page_wrap( string $title, string $class, string $content ): string {
	ob_start();
	woocommerce_breadcrumb(
		array(
			'wrap_before' => '<nav class="cp-breadcrumb">',
			'wrap_after'  => '</nav>',
		)
	);
	$crumb = (string) ob_get_clean();

	$hero = '<section class="cp-pagehero"><div class="cp-container">' . $crumb
		. '<h1>' . esc_html( $title ) . '</h1></div></section>';

	return $hero . '<section class="cp-section ' . esc_attr( $class ) . '-section"><div class="cp-container ' . esc_attr( $class ) . '">'
		. $content . '</div></section>';
}

/**
 * CP3.4 + CP3.5 — Gắn hero/container cho trang có shortcode WooCommerce.
 * Guard: chỉ main query trong vòng lặp (không đụng excerpt/widget/query phụ).
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( cp_is_cart_page() ) {
			return cp_woo_page_wrap( __( 'Giỏ hàng', 'tungleads-theme' ), 'cp-cart', $content );
		}
		if ( cp_is_order_received_page() ) {
			// CP3.6 — phải kiểm tra TRƯỚC `cp_is_checkout_page()` (trang này là checkout endpoint).
			return cp_woo_page_wrap( __( 'Đặt hàng thành công', 'tungleads-theme' ), 'cp-thankyou', $content );
		}
		if ( cp_is_checkout_page() ) {
			return cp_woo_page_wrap( __( 'Thanh toán', 'tungleads-theme' ), 'cp-checkout', $content );
		}
		return $content;
	},
	5
);

/**
 * CP3.5 — Tiêu đề "Đơn hàng của bạn" nằm TRONG thẻ `#order_review`.
 *
 * WooCommerce 11 in `<h3 id="order_review_heading">` RA NGOÀI `#order_review`
 * (`templates/checkout/form-checkout.php` dòng 54) để phục vụ layout 2 cột → tiêu đề bị
 * tách khỏi thẻ trắng. Ta ẩn bản ngoài bằng CSS và in bản của mình bằng hook chạy
 * BÊN TRONG `#order_review` (prio 5 — trước bảng đơn hàng ở prio 10).
 *
 * An toàn với AJAX: `update_order_review` chỉ thay fragment `.woocommerce-checkout-review-order-table`
 * (`WC_AJAX`, `includes/class-wc-ajax.php`), KHÔNG thay cả `#order_review` → tiêu đề không mất,
 * và AJAX gọi thẳng `woocommerce_order_review()` nên cũng không bị in trùng.
 */
function cp_order_review_title(): void {
	// Dùng lại đúng chuỗi của WooCommerce để ăn theo bản dịch tiếng Việt ("Đơn hàng của bạn").
	echo '<h3 class="cp-order-review__title">' . esc_html__( 'Your order', 'woocommerce' ) . '</h3>';
}
add_action( 'woocommerce_checkout_order_review', 'cp_order_review_title', 5 );

/**
 * CP3.5 — Rút gọn form "Thông tin thanh toán" (yêu cầu của Tùng).
 *
 * 1. Gộp "Họ" + "Tên" → 1 trường "Họ và tên": bỏ `billing_last_name`, tên đầy đủ lưu ở
 *    `billing_first_name` — cùng cách CP3.3 lưu tên khách đặt nhanh.
 *    `get_formatted_billing_full_name()` ghép họ + tên nên vẫn in ra đúng tên đầy đủ.
 * 2. Bỏ 4 trường: Tên công ty · Quốc gia/Khu vực · Căn hộ-dãy phòng · Mã bưu điện.
 * 3. Email: không bắt buộc (vẫn kiểm tra định dạng nếu khách có nhập).
 * 4. Sắp thứ tự + độ rộng cho lưới 2 cột: Họ và tên (cả hàng) → SĐT | Email → Địa chỉ → Thành phố.
 * 5. Nhãn ô ghi chú đổi thành "Ghi chú (không bắt buộc)" cho giống popup đặt hàng nhanh (CP3.3).
 *
 * ⚠️ Hệ quả của việc bỏ "bắt buộc" ở email: đơn của khách vãng lai sẽ KHÔNG có email → không
 * gửi được mail xác nhận cho khách, và cổng thanh toán online (VNPay/MoMo/Stripe…) thường báo
 * lỗi thiếu email. Site hiện chỉ dùng COD nên chấp nhận được; nếu nối cổng thanh toán online
 * thì phải bật lại `required` cho email.
 */
function cp_checkout_trim_billing_fields( array $fields ): array {
	if ( empty( $fields['billing'] ) ) {
		return $fields;
	}

	// 1. Bỏ các trường không dùng.
	foreach ( array( 'billing_company', 'billing_country', 'billing_address_2', 'billing_postcode', 'billing_last_name' ) as $key ) {
		unset( $fields['billing'][ $key ] );
	}

	// 2. Gộp họ + tên.
	if ( isset( $fields['billing']['billing_first_name'] ) ) {
		$fields['billing']['billing_first_name']['label']       = __( 'Họ và tên', 'tungleads-theme' );
		$fields['billing']['billing_first_name']['placeholder'] = __( 'Nhập họ và tên', 'tungleads-theme' );
	}

	// 3. Email không bắt buộc.
	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['required'] = false;
		$fields['billing']['billing_email']['label']    = __( 'Địa chỉ email (không bắt buộc)', 'tungleads-theme' );
	}

	// 4. Thứ tự + độ rộng: `form-row-wide` chiếm cả hàng, `-first`/`-last` mỗi cái nửa hàng.
	$layout = array(
		'billing_first_name' => array( 10, 'form-row-wide' ),
		'billing_phone'      => array( 20, 'form-row-first' ),
		'billing_email'      => array( 30, 'form-row-last' ),
		'billing_address_1'  => array( 40, 'form-row-wide' ),
		'billing_city'       => array( 50, 'form-row-wide' ),
	);

	foreach ( $layout as $key => list( $priority, $width ) ) {
		if ( ! isset( $fields['billing'][ $key ] ) ) {
			continue;
		}

		// Giữ các class khác của WooCommerce (`address-field`, `validate-phone`…) — chỉ thay class độ rộng.
		$class   = array_diff( (array) ( $fields['billing'][ $key ]['class'] ?? array() ), array( 'form-row-first', 'form-row-last', 'form-row-wide' ) );
		$class[] = $width;

		$fields['billing'][ $key ]['priority'] = $priority;
		$fields['billing'][ $key ]['class']    = array_values( $class );
	}

	// 5. Ô ghi chú — nhãn giống popup đặt hàng nhanh.
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label'] = __( 'Ghi chú (không bắt buộc)', 'tungleads-theme' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'cp_checkout_trim_billing_fields', 20 );

/**
 * CP3.5 — Quốc gia đã bị gỡ khỏi form nhưng VẪN phải lưu vào đơn.
 *
 * Không chèn lại thì đơn sẽ trống `billing_country` → sai phí vận chuyển/thuế và lệch số liệu
 * báo cáo. Giá trị lấy theo quốc gia cơ sở của shop (WooCommerce → Cài đặt chung), không hard-code.
 * Filter này còn có tác dụng phụ cần thiết: `WC_Checkout::update_session()` chạy SAU filter và
 * ghi `billing_country` vào customer → bước tính lại vận chuyển vẫn khớp zone.
 */
function cp_checkout_force_base_country( array $data ): array {
	$base    = function_exists( 'wc_get_base_location' ) ? wc_get_base_location() : array();
	$country = ! empty( $base['country'] ) ? $base['country'] : 'VN';

	$data['billing_country'] = $country;

	// Khi khách KHÔNG tick "Giao hàng đến một địa chỉ khác", WooCommerce tự copy billing → shipping
	// ngay trong `get_posted_data()` (`class-wc-checkout.php:846`), nhưng bản copy đó chạy TRƯỚC filter
	// này và đọc `$data['billing_country']` — key này đã bị gỡ khỏi form nên rỗng → `shipping_country`
	// rỗng → `validate_checkout()` chặn với lỗi "Xin hãy nhập một địa chỉ để tiếp tục."
	// Vì vậy phải tự điền `shipping_country` khi nó đang trống (khách chọn quốc gia giao khác thì giữ nguyên).
	if ( empty( $data['shipping_country'] ) ) {
		$data['shipping_country'] = $country;
	}

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'cp_checkout_force_base_country' );

/* ======================= CP1.7 — Icon giỏ hàng trên header ======================= */

/** Số sản phẩm đang có trong giỏ (0 nếu WooCommerce chưa sẵn sàng). */
function cp_cart_count(): int {
	return ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/**
 * CP1.7 — In icon giỏ hàng (link + số lượng). Dùng ở header VÀ trong fragment AJAX nên
 * markup phải giống hệt nhau ở cả 2 đường.
 */
function cp_header_cart_link(): void {
	if ( ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}

	$count = cp_cart_count();
	?>
	<a class="cp-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: số sản phẩm trong giỏ */ __( 'Giỏ hàng, %d sản phẩm', 'tungleads-theme' ), $count ) ); ?>">
		<svg class="cp-cart-link__ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
		<?php if ( $count > 0 ) : ?>
			<span class="cp-cart-link__count"><?php echo esc_html( (string) $count ); ?></span>
		<?php endif; ?>
	</a>
	<?php
}

/**
 * CP1.7 — Thêm sản phẩm vào giỏ bằng AJAX thì WooCommerce thay fragment theo selector khoá mảng
 * → trả lại đúng khối `.cp-cart-link` để số lượng trên header tự nhảy, không cần tải lại trang.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( array $fragments ): array {
		ob_start();
		cp_header_cart_link();
		$fragments['a.cp-cart-link'] = (string) ob_get_clean();

		return $fragments;
	}
);

/**
 * CP3.4 — Nhãn tên gói vận chuyển trong bảng tổng tiền hiện tiếng Anh "Shipment"
 * (gói ngôn ngữ tiếng Việt hiện tại chưa dịch). WooCommerce sinh chuỗi này bằng
 * `_x( 'Shipment', 'shipping packages', 'woocommerce' )` → phải dùng filter
 * `gettext_with_context` (KHÔNG phải `gettext`), và khớp cả context.
 */
add_filter(
	'gettext_with_context',
	static function ( $translated, $original, $context, $domain ) {
		if ( 'woocommerce' === $domain && 'Shipment' === $original && 'shipping packages' === $context ) {
			return __( 'Vận chuyển', 'tungleads-theme' );
		}
		return $translated;
	},
	10,
	4
);
