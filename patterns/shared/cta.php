<?php
/**
 * Title: Dải kêu gọi hành động (CTA)
 * Slug: tungleads-theme/cta
 * Categories: tungleads
 * Description: Dải CTA dùng chung: tiêu đề ngắn + mô tả + nút. Dùng token màu/spacing của theme.
 *
 * @package TL\Theme
 *
 * P3.5 patterns
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2} -->
	<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Sẵn sàng bắt đầu?', 'tungleads-theme' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><?php esc_html_e( 'Liên hệ để được tư vấn giải pháp phù hợp với nhu cầu của bạn.', 'tungleads-theme' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"primary"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-background wp-element-button" href="#"><?php esc_html_e( 'Liên hệ ngay', 'tungleads-theme' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
