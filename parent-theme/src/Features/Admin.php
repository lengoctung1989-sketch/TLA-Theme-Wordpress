<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Tinh chỉnh phía admin/editor trong phạm vi theme. GĐ1: đăng ký category cho block pattern
 * (patterns/ ở GĐ4 sẽ dùng). KHÔNG đăng ký CPT/taxonomy — việc của tl-site-plugin.
 *
 * P2.6 admin
 */
final class Admin implements FeatureInterface {

	public function shouldBoot(): bool {
		return true;
	}

	public function boot(): void {
		add_action( 'init', array( $this, 'registerPatternCategory' ) );
	}

	public function registerPatternCategory(): void {
		if ( ! function_exists( 'register_block_pattern_category' ) ) {
			return;
		}

		register_block_pattern_category(
			'tungleads',
			array( 'label' => __( 'TLA Theme', 'tungleads-theme' ) )
		);
	}
}
