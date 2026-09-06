<?php
/**
 * @package TL\Theme
 *
 * P3.1 base-templates
 */

defined( 'ABSPATH' ) || exit;
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

<header class="tl-site-header">
	<div class="tl-container">
		<a class="tl-site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php bloginfo( 'name' ); ?>
		</a>
		<nav class="tl-site-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'tungleads-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => '',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>
	</div>
</header>
