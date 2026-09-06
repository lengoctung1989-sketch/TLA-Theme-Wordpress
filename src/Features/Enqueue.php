<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Nạp asset theo Vite manifest. Dev: HMR qua Vite dev server. Prod: file hash + ES module.
 * `enqueueEntry()` là static để module khác (VD Integrations\WooCommerce) tái dùng cùng cơ chế.
 *
 * P2.2 enqueue
 */
final class Enqueue implements FeatureInterface {

	public const HANDLE    = 'tl-theme';
	private const DEV_HOST = 'http://127.0.0.1:5173';
	private const ENTRY    = 'main.js';

	public function shouldBoot(): bool {
		return true;
	}

	public function boot(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'script_loader_tag', array( $this, 'scriptTag' ), 10, 2 );
	}

	public function enqueue(): void {
		if ( self::isDev() ) {
			// Vite dev server: không version (phục vụ HMR), có chủ đích.
			wp_enqueue_script( self::HANDLE . '-client', self::DEV_HOST . '/@vite/client', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
		self::enqueueEntry( self::HANDLE, self::ENTRY );
	}

	/**
	 * Enqueue một entry của Vite (dev: từ dev server; prod: file hash + CSS kèm theo từ manifest).
	 */
	public static function enqueueEntry( string $handle, string $entry ): void {
		if ( self::isDev() ) {
			wp_enqueue_script( $handle, self::DEV_HOST . '/' . $entry, array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			return;
		}

		$manifest = self::manifest();
		if ( ! isset( $manifest[ $entry ]['file'] ) ) {
			return;
		}

		$dist = TL_THEME_URI . '/assets/dist/';

		foreach ( (array) ( $manifest[ $entry ]['css'] ?? array() ) as $i => $css ) {
			wp_enqueue_style( $handle . '-' . $i, $dist . $css, array(), TL_THEME_VERSION );
		}

		wp_enqueue_script( $handle, $dist . $manifest[ $entry ]['file'], array(), TL_THEME_VERSION, true );
	}

	public static function isDev(): bool {
		return defined( 'TL_THEME_DEV' ) && TL_THEME_DEV;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	private static function manifest(): array {
		$path = TL_THEME_DIR . '/assets/dist/.vite/manifest.json';
		if ( ! is_readable( $path ) ) {
			return array();
		}
		// Đọc file manifest cục bộ trong theme, không phải URL từ xa.
		$json = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return is_array( $json ) ? $json : array();
	}

	/**
	 * Mọi script của theme (`tl-theme*`) chạy dạng ES module.
	 *
	 * @param string $tag    Thẻ script.
	 * @param string $handle Handle.
	 */
	public function scriptTag( $tag, $handle ): string {
		if ( ! str_starts_with( (string) $handle, self::HANDLE ) || str_contains( $tag, 'type="module"' ) ) {
			return $tag;
		}
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
}
