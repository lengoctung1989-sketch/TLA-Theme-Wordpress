<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Tinh chỉnh hiệu năng ở tầng trình bày: bỏ emoji, preload stylesheet chính của theme.
 * KHÔNG đụng page cache / HTTP cache header / XML-RPC (việc của hosting/CDN).
 *
 * P2.3 performance
 */
final class Performance implements FeatureInterface {

	public function shouldBoot(): bool {
		return true;
	}

	public function boot(): void {
		add_action( 'init', array( $this, 'disableEmoji' ) );
		add_filter( 'wp_preload_resources', array( $this, 'preloadResources' ) );
	}

	/**
	 * Gỡ toàn bộ wp-emoji khỏi front-end và feed — không dùng tới, tốn 1 request + inline script.
	 */
	public function disableEmoji(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'removeTinymceEmoji' ) );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	/**
	 * @param array<int,string> $plugins
	 * @return array<int,string>
	 */
	public function removeTinymceEmoji( $plugins ): array {
		return array_diff( (array) $plugins, array( 'wpemoji' ) );
	}

	/**
	 * Preload stylesheet chính của theme.
	 *
	 * @param array<int,array<string,string>> $resources
	 * @return array<int,array<string,string>>
	 */
	public function preloadResources( $resources ): array {
		$resources = is_array( $resources ) ? $resources : array();

		if ( wp_style_is( 'tl-theme-0' ) ) {
			$style       = wp_styles()->registered['tl-theme-0'];
			$resources[] = array(
				'href'  => $style->src . ( $style->ver ? '?ver=' . $style->ver : '' ),
				'as'    => 'style',
				'type'  => 'text/css',
				'media' => 'all',
			);
		}

		return $resources;
	}
}
