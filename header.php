<?php
/**
 * Header Cao Phát Door: topbar + header sticky (logo + menu + hotline).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_tel  = cp_hotline_tel();
$cp_disp = cp_hotline_display();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Bỏ qua tới nội dung', 'tungleads-theme' ); ?></a>

<div class="cp-topbar">
	<div class="cp-container">
		<ul>
			<li><?php esc_html_e( 'Miễn phí vận chuyển nội thành', 'tungleads-theme' ); ?></li>
			<li><?php esc_html_e( 'Thanh toán linh hoạt', 'tungleads-theme' ); ?></li>
			<li><?php esc_html_e( 'Bảo hành 24 tháng', 'tungleads-theme' ); ?></li>
		</ul>
		<div class="cp-topbar-right">
			<span><?php esc_html_e( 'Báo giá online 24/7:', 'tungleads-theme' ); ?></span>
			<a href="tel:<?php echo esc_attr( $cp_tel ); ?>"><strong><?php echo esc_html( $cp_disp ); ?></strong></a>
		</div>
	</div>
</div>

<header class="cp-header">
	<div class="cp-container">
		<a class="cp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="cp-logo-mark">CP</span>
				<span class="cp-logo-text">
					<b><?php bloginfo( 'name' ); ?></b>
					<span><?php esc_html_e( 'Thế giới cửa gỗ', 'tungleads-theme' ); ?></span>
				</span>
			<?php endif; ?>
		</a>

		<nav class="cp-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'tungleads-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => '',
					'items_wrap'     => '<ul>%3$s</ul>',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="cp-header-cta">
			<a class="cp-hotline" href="tel:<?php echo esc_attr( $cp_tel ); ?>">
				<span class="ic" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</span>
				<span>
					<small><?php esc_html_e( 'Tư vấn miễn phí', 'tungleads-theme' ); ?></small>
					<b><?php echo esc_html( $cp_disp ); ?></b>
				</span>
			</a>
			<button class="cp-burger" type="button" aria-label="<?php esc_attr_e( 'Mở menu', 'tungleads-theme' ); ?>"><span></span><span></span><span></span></button>
		</div>
	</div>
</header>
