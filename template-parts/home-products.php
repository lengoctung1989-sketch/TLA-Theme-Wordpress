<?php
/**
 * Lưới sản phẩm trang chủ. $args['type'] = 'best' (bán chạy) | 'sale' (đang giảm).
 *
 * CP2.2 home-blocks
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$cp_type = isset( $args['type'] ) ? (string) $args['type'] : 'best';

$cp_query = array(
	'status'  => 'publish',
	'limit'   => 4,
	'orderby' => 'date',
	'order'   => 'DESC',
);

if ( 'best' === $cp_type ) {
	$cp_query['orderby']  = 'meta_value_num';
	$cp_query['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
} elseif ( 'sale' === $cp_type ) {
	$cp_ids = wc_get_product_ids_on_sale();
	if ( empty( $cp_ids ) ) {
		return;
	}
	$cp_query['include'] = array_slice( $cp_ids, 0, 12 );
	$cp_query['orderby'] = 'rand';
}

$cp_products = wc_get_products( $cp_query );

if ( empty( $cp_products ) ) {
	return;
}
?>
<div class="cp-products">
	<?php
	foreach ( $cp_products as $cp_product ) {
		cp_product_card( $cp_product );
	}
	?>
</div>
