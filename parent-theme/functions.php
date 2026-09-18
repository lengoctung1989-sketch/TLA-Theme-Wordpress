<?php
/**
 * TLA Theme bootstrap. Chỉ load Composer autoload + Core\Theme::boot().
 *
 * @package TL\Theme
 */

defined( 'ABSPATH' ) || exit;

// P1.1 theme-bootstrap — điểm vào duy nhất: load autoload + Core\Theme::boot().

define( 'TL_THEME_DIR', get_template_directory() );
define( 'TL_THEME_URI', get_template_directory_uri() );
define( 'TL_THEME_VERSION', '0.1.0' );

if ( ! defined( 'TL_THEME_DEV' ) ) {
	define( 'TL_THEME_DEV', 'development' === wp_get_environment_type() );
}

$tl_theme_autoload = TL_THEME_DIR . '/vendor/autoload.php';

if ( ! is_readable( $tl_theme_autoload ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>';
			esc_html_e( 'TLA Theme: chưa có autoloader. Chạy "composer install" trong thư mục theme.', 'tungleads-theme' );
			echo '</p></div>';
		}
	);
	return;
}

require $tl_theme_autoload;

\TL\Theme\Core\Theme::boot();
