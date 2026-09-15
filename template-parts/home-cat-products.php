<?php
/**
 * CP2.4 — Các khối "Sản phẩm theo danh mục" trên trang chủ (REPEATER trong Customizer).
 *
 * Không nhận `$args`: đọc TOÀN BỘ khối từ theme_mod `cp_pblocks` (JSON — Admin tự thêm/xoá/kéo thả
 * trong "Customize → Trang chủ — Khối sản phẩm theo danh mục") rồi render lần lượt.
 * Khối chưa chọn danh mục (hoặc danh mục đã xoá / không còn sản phẩm) → bỏ qua, không in gì.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$cp_blocks = json_decode( (string) get_theme_mod( 'cp_pblocks', '' ), true );
if ( ! is_array( $cp_blocks ) || ! $cp_blocks ) {
	return;
}

$cp_rendered = 0;

foreach ( $cp_blocks as $cp_block ) {
	if ( ! is_array( $cp_block ) ) {
		continue;
	}

	$cp_term = get_term( absint( $cp_block['cat'] ?? 0 ), 'product_cat' );
	if ( ! $cp_term instanceof WP_Term ) {
		continue;
	}

	$cp_layout = (string) ( $cp_block['layout'] ?? 'cols-4' );
	$cp_layout = in_array( $cp_layout, array( 'cols-4', 'cols-3', 'cols-2', 'scroll' ), true ) ? $cp_layout : 'cols-4';

	$cp_limit = max( 1, min( 20, absint( $cp_block['count'] ?? 8 ) ) );

	/* CP2.7 — sản phẩm Admin chọn riêng (CSV ID) hiển thị TRƯỚC, đúng thứ tự đã kéo thả. */
	$cp_picked = array_filter( array_map( 'absint', explode( ',', (string) ( $cp_block['products'] ?? '' ) ) ) );
	$cp_products = array();

	foreach ( $cp_picked as $cp_picked_id ) {
		$cp_product = wc_get_product( $cp_picked_id );
		if ( $cp_product instanceof WC_Product && 'publish' === $cp_product->get_status() ) {
			$cp_products[] = $cp_product;
		}
	}

	/* Phần còn thiếu của `count` mới lấy thêm từ danh mục (bỏ qua sản phẩm đã chọn). */
	$cp_missing = $cp_limit - count( $cp_products );
	if ( $cp_missing > 0 ) {
		$cp_fill = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => $cp_missing,
				'orderby'  => 'date',
				'order'    => 'date-asc' === ( $cp_block['order'] ?? 'date-desc' ) ? 'ASC' : 'DESC',
				'category' => array( $cp_term->slug ),
				'exclude'  => $cp_picked, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.post__not_in
			)
		);
		$cp_products = array_merge( $cp_products, $cp_fill );
	}

	if ( empty( $cp_products ) ) {
		continue;
	}

	++$cp_rendered;
	/* Nền xen kẽ theo thứ tự khối ĐÃ RENDER (khối 1 trắng · 2 kem · 3 trắng…) */
	$cp_alt = 0 === $cp_rendered % 2 ? '' : ' alt';

	$cp_title = trim( (string) ( $cp_block['title'] ?? '' ) );
	$cp_title = '' !== $cp_title ? $cp_title : $cp_term->name;
	?>
	<section class="cp-section<?php echo esc_attr( $cp_alt ); ?> cp-cat-block cp-cat-block--<?php echo esc_attr( $cp_layout ); ?>">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<h2><?php echo esc_html( $cp_title ); ?></h2>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( (string) get_term_link( $cp_term ) ); ?>"><?php esc_html_e( 'Xem thêm →', 'tungleads-theme' ); ?></a>
			</div>
			<?php if ( 'scroll' === $cp_layout ) : ?>
				<?php cp_scroller_open(); // CP2.8 — nút cuộn ‹ (chỉ bố cục "Cuộn ngang") ?>
			<?php endif; ?>
			<div class="cp-products">
				<?php foreach ( $cp_products as $cp_product ) : ?>
					<?php cp_product_card( $cp_product ); ?>
				<?php endforeach; ?>
			</div>
			<?php if ( 'scroll' === $cp_layout ) : ?>
				<?php cp_scroller_close(); // CP2.8 — nút cuộn › + đóng .cp-scroller ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
