<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Core;

use TL\Theme\Features\Admin;
use TL\Theme\Features\Enqueue;
use TL\Theme\Features\Performance;
use TL\Theme\Features\Security;
use TL\Theme\Features\SEO;
use TL\Theme\Features\Setup;
use TL\Theme\Integrations\WooCommerce\WooCommerceFeature;
use TL\Theme\SiteMode\PatternLoader;
use TL\Theme\SiteMode\SiteModeResolver;

/**
 * Điểm vào của theme. Hook toàn bộ khởi tạo vào after_setup_theme.
 *
 * P1.1 theme-bootstrap
 */
final class Theme {

	private static string $mode = 'service';

	public static function boot(): void {
		add_action( 'after_setup_theme', array( self::class, 'onAfterSetupTheme' ), 5 );
	}

	/**
	 * Chạy sau plugins_loaded nên class_exists('WooCommerce') đáng tin cậy,
	 * đồng thời là thời điểm hợp lệ cho add_theme_support().
	 */
	public static function onAfterSetupTheme(): void {
		self::$mode = SiteModeResolver::fromConfig( TL_THEME_DIR . '/config/site-config.php' );

		$registry = new FeatureRegistry();
		$registry->add( new Setup() );
		$registry->add( new Enqueue() );
		$registry->add( new Performance() );
		$registry->add( new Security() );
		$registry->add( new SEO() );
		$registry->add( new Admin() );
		$registry->add( new WooCommerceFeature() );
		$registry->bootAll();

		( new PatternLoader() )->register();
	}

	public static function mode(): string {
		return self::$mode;
	}
}
