<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\SiteMode;

/**
 * Đọc config/site-config.php và chuẩn hoá site mode.
 *
 * P1.3 site-mode
 */
final class SiteModeResolver {

	public const SERVICE   = 'service';
	public const ECOMMERCE = 'ecommerce';
	public const HYBRID    = 'hybrid';

	private const VALID = array( self::SERVICE, self::ECOMMERCE, self::HYBRID );

	public static function fromConfig( string $path ): string {
		$config = is_readable( $path ) ? (array) require $path : array();
		$mode   = isset( $config['mode'] ) ? (string) $config['mode'] : self::SERVICE;

		return in_array( $mode, self::VALID, true ) ? $mode : self::SERVICE;
	}

	public static function isEcommerce( string $mode ): bool {
		return self::ECOMMERCE === $mode || self::HYBRID === $mode;
	}

	public static function isService( string $mode ): bool {
		return self::SERVICE === $mode || self::HYBRID === $mode;
	}
}
