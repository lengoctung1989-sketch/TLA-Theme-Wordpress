<?php
/**
 * Title: Lưới 3 dịch vụ
 * Slug: tungleads-theme/services-list
 * Categories: tungleads
 * Description: Lưới 3 cột giới thiệu dịch vụ. Dành cho site mode service/hybrid.
 *
 * @package TL\Theme
 *
 * P3.5 patterns
 */

defined( 'ABSPATH' ) || exit;

$tl_items = array(
	__( 'Dịch vụ 1', 'tungleads-theme' ),
	__( 'Dịch vụ 2', 'tungleads-theme' ),
	__( 'Dịch vụ 3', 'tungleads-theme' ),
);
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"textAlign":"center","level":2} -->
	<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Dịch vụ của chúng tôi', 'tungleads-theme' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
	<div class="wp-block-columns">
		<?php foreach ( $tl_items as $tl_item ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"fontSize":"lg"} -->
			<h3 class="wp-block-heading has-lg-font-size"><?php echo esc_html( $tl_item ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'Mô tả ngắn gọn về dịch vụ này.', 'tungleads-theme' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
