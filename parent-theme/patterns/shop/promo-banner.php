<?php
/**
 * Title: Banner khuyến mãi cửa hàng
 * Slug: tungleads-theme/promo-banner
 * Categories: tungleads
 * Description: Banner dẫn tới trang cửa hàng. Dành cho site mode ecommerce/hybrid.
 *
 * @package TL\Theme
 *
 * P3.5 patterns
 */

defined( 'ABSPATH' ) || exit;

$tl_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"textAlign":"center","level":2,"textColor":"base"} -->
	<h2 class="wp-block-heading has-text-align-center has-base-color has-text-color"><?php esc_html_e( 'Ưu đãi đang diễn ra', 'tungleads-theme' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"base"} -->
	<p class="has-text-align-center has-base-color has-text-color"><?php esc_html_e( 'Khám phá các sản phẩm nổi bật với giá tốt nhất.', 'tungleads-theme' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"backgroundColor":"accent"} -->
		<div class="wp-block-button"><a class="wp-block-button__link has-accent-background-color has-background wp-element-button" href="<?php echo esc_url( $tl_shop_url ); ?>"><?php esc_html_e( 'Mua sắm ngay', 'tungleads-theme' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
