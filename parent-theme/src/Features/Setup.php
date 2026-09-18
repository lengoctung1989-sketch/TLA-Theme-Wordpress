<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Theme supports, nav menus, textdomain.
 *
 * P2.1 setup
 */
final class Setup implements FeatureInterface {

	public function shouldBoot(): bool {
		return true;
	}

	public function boot(): void {
		// Đang ở trong after_setup_theme nên gọi trực tiếp cho đúng thời điểm.
		$this->themeSupports();
		add_action( 'init', array( $this, 'registerMenus' ) );
	}

	public function themeSupports(): void {
		load_theme_textdomain( 'tungleads-theme', TL_THEME_DIR . '/languages' );

		// 'title-tag' do SEO.php (P2.5) tự thêm khi KHÔNG có plugin SEO — tránh trùng lặp.
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'appearance-tools' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support(
			'html5',
			array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);
	}

	public function registerMenus(): void {
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'tungleads-theme' ),
				'footer'  => __( 'Footer Menu', 'tungleads-theme' ),
			)
		);
	}
}
