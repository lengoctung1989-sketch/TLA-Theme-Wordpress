<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Integrations\WooCommerce;

use TL\Theme\Core\Contracts\FeatureInterface;
use TL\Theme\Core\Theme;
use TL\Theme\Features\Enqueue;
use TL\Theme\SiteMode\SiteModeResolver;

/**
 * Cửa ngõ tích hợp WooCommerce. Blocks-first: KHÔNG override markup, ưu tiên hook.
 * Boot khi site mode ∈ {ecommerce, hybrid} VÀ WooCommerce đang active.
 *
 * P4.1 woocommerce-feature
 */
final class WooCommerceFeature implements FeatureInterface {

	/**
	 * Chỉ điều kiện tĩnh: site mode + plugin active (feature-detection, không version-check).
	 */
	public function shouldBoot(): bool {
		return SiteModeResolver::isEcommerce( Theme::mode() ) && class_exists( 'WooCommerce' );
	}

	public function boot(): void {
		// Đang trong after_setup_theme — hợp lệ cho add_theme_support().
		$this->themeSupports();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueStyles' ) );

		// Theme không có sidebar.php (Blocks-first) — gỡ sidebar classic của WooCommerce
		// để tránh cảnh báo deprecated get_sidebar(). Là hook, không override file.
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		( new ProductArchive() )->register();
	}

	public function themeSupports(): void {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 400,
				'single_image_width'    => 800,
				'product_grid'          => array(
					'default_columns' => 3,
					'default_rows'    => 3,
				),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	public function enqueueStyles(): void {
		Enqueue::enqueueEntry( Enqueue::HANDLE . '-woo', 'woocommerce.js' );
	}
}
