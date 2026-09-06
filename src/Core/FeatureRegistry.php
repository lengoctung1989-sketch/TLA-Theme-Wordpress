<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Core;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Giữ danh sách Feature, gọi shouldBoot() rồi boot() cho từng cái.
 *
 * P1.2 feature-registry
 */
final class FeatureRegistry {

	/**
	 * @var FeatureInterface[]
	 */
	private array $features = array();

	public function add( FeatureInterface $feature ): void {
		$this->features[] = $feature;
	}

	public function bootAll(): void {
		foreach ( $this->features as $feature ) {
			if ( $feature->shouldBoot() ) {
				$feature->boot();
			}
		}
	}
}
