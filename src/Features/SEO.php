<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Fallback SEO tối thiểu. Nếu có plugin SEO (Yoast/RankMath/AIOSEO/SEOPress/TSF) đang active,
 * Feature này KHÔNG boot — theme không output title-tag/OG/JSON-LD để tránh trùng lặp.
 * Không plugin: chỉ bật title-tag. Canonical đã có sẵn qua `rel_canonical` mặc định của wp_head.
 *
 * P2.5 seo
 */
final class SEO implements FeatureInterface {

	/**
	 * Chỉ điều kiện tĩnh: có plugin SEO hay không.
	 */
	public function shouldBoot(): bool {
		return ! self::hasSeoPlugin();
	}

	public function boot(): void {
		add_theme_support( 'title-tag' );
	}

	/**
	 * Feature-detection theo class/constant công khai của plugin — không version-check cứng.
	 */
	public static function hasSeoPlugin(): bool {
		return defined( 'WPSEO_VERSION' )              // Yoast SEO.
			|| class_exists( 'RankMath' )              // Rank Math.
			|| defined( 'AIOSEO_VERSION' )             // All in One SEO.
			|| defined( 'SEOPRESS_VERSION' )           // SEOPress.
			|| class_exists( '\\The_SEO_Framework\\Load' ); // The SEO Framework.
	}
}
