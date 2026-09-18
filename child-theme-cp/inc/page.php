<?php
/**
 * CP6.1 + CP6.2 — Trang nội dung tĩnh (Admin → Trang).
 *
 * `page.php` của child gọi các hàm ở đây. Tái dùng có chủ đích (không viết lại CSS):
 *   - lưới 2 cột + cột phải: `.cp-news-layout` / `.cp-news-main` / `.cp-news-aside` (CP5.1)
 *   - typography rich-text: `.cp-article__content` (CP5.2) — trang gắn thêm `.cp-page__content`
 *   - hộp cột phải: `.cp-side-box` / `.cp-side-cats` (CP3.2) + `cp_single_support_box()` + `cp_trust_box()`
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

/**
 * CP6.1 — Trang do WooCommerce quản lý: giỏ hàng · thanh toán · đơn đã nhận · tài khoản.
 *
 * Với các trang này `page.php` CHỈ gọi `the_content()`: CP3.4–CP3.6 đã có filter `the_content`
 * bọc hero + container; bọc thêm lần nữa sẽ ra 2 hero.
 */
function cp_is_woo_endpoint_page(): bool {
	return cp_is_cart_page()
		|| cp_is_checkout_page()
		|| cp_is_order_received_page()
		|| ( function_exists( 'is_account_page' ) && is_account_page() );
}

/** CP6.1 — Breadcrumb "Trang chủ / … trang cha … / tiêu đề trang" (trang có thể phân cấp nhiều cấp). */
function cp_page_breadcrumb(): void {
	$cp_post_id   = (int) get_the_ID();
	$cp_ancestors = array_reverse( get_post_ancestors( $cp_post_id ) );

	echo '<nav class="cp-breadcrumb" aria-label="' . esc_attr__( 'Đường dẫn', 'tungleads-theme' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Trang chủ', 'tungleads-theme' ) . '</a>';

	foreach ( $cp_ancestors as $cp_ancestor_id ) {
		echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
		echo '<a href="' . esc_url( (string) get_permalink( $cp_ancestor_id ) ) . '">'
			. esc_html( get_the_title( $cp_ancestor_id ) ) . '</a>';
	}

	echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
	echo '<span class="cp-breadcrumb__here">' . esc_html( get_the_title( $cp_post_id ) ) . '</span>';
	echo '</nav>';
}

/**
 * CP6.2 — Hộp "Trong mục này": cây trang của nhánh chứa trang đang xem.
 *
 * Chỉ in khi trang đang xem CÓ trang cha hoặc CÓ trang con — trang đứng riêng thì sub-nav vô nghĩa.
 * `wp_list_pages()` tự gắn `current_page_item` cho trang đang xem và `current_page_ancestor` cho
 * trang cha → tái dùng `.cp-side-cats` của CP3.2, chỉ cần thêm 2 rule nhỏ cho 2 trạng thái đó.
 */
function cp_page_subnav_box(): void {
	$cp_post_id  = (int) get_the_ID();
	$cp_parent   = wp_get_post_parent_id( $cp_post_id );
	$cp_children = get_children(
		array(
			'post_parent' => $cp_post_id,
			'post_type'   => 'page',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);

	if ( ! $cp_parent && ! $cp_children ) {
		return;
	}

	// Gốc của nhánh = trang cha cao nhất (`get_post_ancestors` trả gần → xa nên lấy phần tử CUỐI).
	$cp_root = $cp_post_id;
	if ( $cp_parent ) {
		$cp_ancestors = get_post_ancestors( $cp_post_id );
		$cp_root      = $cp_ancestors ? (int) end( $cp_ancestors ) : (int) $cp_parent;
	}

	// `child_of` KHÔNG in chính trang gốc → in tay ở trên, rồi in các trang con (depth 2 = cháu).
	$cp_items = wp_list_pages(
		array(
			'child_of'    => $cp_root,
			'depth'       => 2,
			'title_li'    => '',
			'sort_column' => 'menu_order, post_title',
			'echo'        => false,
		)
	);
	if ( ! is_string( $cp_items ) || false === strpos( $cp_items, '<li' ) ) {
		return;
	}

	echo '<div class="cp-side-box cp-page-nav-box"><h4>' . esc_html__( 'Trong mục này', 'tungleads-theme' ) . '</h4>';
	echo '<ul class="cp-side-cats cp-page-nav">';
	printf(
		'<li class="cp-page-nav__root%1$s"><a href="%2$s">%3$s</a></li>',
		$cp_root === $cp_post_id ? ' current_page_item' : '',
		esc_url( (string) get_permalink( $cp_root ) ),
		esc_html( get_the_title( $cp_root ) )
	);
	echo $cp_items; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup do `wp_list_pages()` sinh.
	echo '</ul></div>';
}

/** CP6.1 — Cột phải trang nội dung: sub-nav (nếu phân cấp) + "Hỗ trợ trực tuyến" + "Cam kết Cao Phát". */
function cp_page_sidebar(): void {
	cp_page_subnav_box();

	if ( function_exists( 'cp_single_support_box' ) ) {
		cp_single_support_box();
	}
	if ( function_exists( 'cp_trust_box' ) ) {
		cp_trust_box();
	}
}
