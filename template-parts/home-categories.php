<?php
/**
 * Lưới danh mục sản phẩm trên trang chủ (product_cat).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

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

if ( is_wp_error( $cp_terms ) || empty( $cp_terms ) ) {
	return;
}

$cp_fallback = get_stylesheet_directory_uri() . '/assets/images/door-hdf.png';
?>
<div class="cp-cats">
	<?php
	foreach ( $cp_terms as $cp_term ) :
		$cp_thumb_id = (int) get_term_meta( $cp_term->term_id, 'thumbnail_id', true );
		$cp_src      = $cp_thumb_id ? wp_get_attachment_image_url( $cp_thumb_id, 'large' ) : $cp_fallback;
		?>
		<a class="cp-cat" href="<?php echo esc_url( get_term_link( $cp_term ) ); ?>">
			<img src="<?php echo esc_url( $cp_src ); ?>" alt="<?php echo esc_attr( $cp_term->name ); ?>" loading="lazy">
			<span class="cp-cat-overlay">
				<b><?php echo esc_html( $cp_term->name ); ?></b>
				<?php if ( $cp_term->description ) : ?>
					<span><?php echo esc_html( wp_trim_words( $cp_term->description, 8 ) ); ?></span>
				<?php else : ?>
					<span><?php echo esc_html( sprintf( /* translators: %d: số sản phẩm */ _n( '%d sản phẩm', '%d sản phẩm', $cp_term->count, 'tungleads-theme' ), $cp_term->count ) ); ?></span>
				<?php endif; ?>
			</span>
		</a>
	<?php endforeach; ?>
</div>
