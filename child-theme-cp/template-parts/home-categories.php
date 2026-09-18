<?php
/**
 * Lưới danh mục sản phẩm trên trang chủ (product_cat).
 *
 * CP2.2 home-blocks
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

$cp_layout = (string) get_theme_mod( 'cp_cats_layout', 'cols-3' );
$cp_layout = in_array( $cp_layout, array( 'cols-6', 'cols-4', 'cols-3', 'horizontal' ), true ) ? $cp_layout : 'cols-3';

/* CP2.6 — Admin chọn danh mục + kéo thả thứ tự trong Customizer (`cp_cats_selected` = CSV term_id).
   Chưa tick gì → fallback giữ nguyên hành vi cũ: 6 danh mục nhiều sản phẩm nhất. */
$cp_ids   = array_filter( array_map( 'absint', explode( ',', (string) get_theme_mod( 'cp_cats_selected', '' ) ) ) );
$cp_terms = array();

if ( $cp_ids ) {
	foreach ( $cp_ids as $cp_id ) {
		$cp_term = get_term( $cp_id, 'product_cat' );
		if ( $cp_term instanceof WP_Term ) {
			$cp_terms[] = $cp_term; // giữ ĐÚNG thứ tự admin đã kéo thả
		}
	}
} else {
	$cp_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => 6,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	if ( is_wp_error( $cp_terms ) ) {
		return;
	}
}

if ( empty( $cp_terms ) ) {
	return;
}

/* Ảnh dự phòng cho danh mục chưa có thumbnail: WebP 24KB thay cho PNG 656KB */
$cp_fallback = get_stylesheet_directory_uri() . '/assets/images/door-hdf.webp';
?>
<?php cp_scroller_open(); // CP2.8 — nút cuộn ‹ ; mobile: dải này là 1 HÀNG 3 CỘT + cuộn ngang ?>
<div class="cp-cats cp-cats--<?php echo esc_attr( $cp_layout ); ?>">
	<?php
	foreach ( $cp_terms as $cp_term ) :
		$cp_thumb_id = (int) get_term_meta( $cp_term->term_id, 'thumbnail_id', true );
		$cp_src      = $cp_thumb_id ? wp_get_attachment_image_url( $cp_thumb_id, 'large' ) : $cp_fallback;
		?>
		<a class="cp-cat" href="<?php echo esc_url( get_term_link( $cp_term ) ); ?>">
			<img src="<?php echo esc_url( $cp_src ); ?>" alt="<?php echo esc_attr( $cp_term->name ); ?>" loading="lazy">
			<span class="cp-cat-overlay">
				<b><?php echo esc_html( $cp_term->name ); ?></b>
				<?php
				/* 2026-09-15 (yêu cầu Tùng): BỎ dòng đếm "%d sản phẩm" dưới tiêu đề danh mục.
				   Danh mục có mô tả thì vẫn hiện mô tả (8 từ đầu); không có mô tả → chỉ còn tiêu đề. */
				if ( $cp_term->description ) :
					?>
					<span><?php echo esc_html( wp_trim_words( $cp_term->description, 8 ) ); ?></span>
				<?php endif; ?>
			</span>
		</a>
	<?php endforeach; ?>
</div>
<?php cp_scroller_close(); // CP2.8 — nút cuộn › + đóng `.cp-scroller` ?>

