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
		// Admin: `wp-admin/includes/admin-filters.php` (nạp SAU `init`) gắn LẠI 2 hook emoji ⇒ phải gỡ lần nữa ở
		// `admin_init` (chạy sau file đó). Thiếu dòng này: front-end sạch nhưng **wp-admin vẫn nạp twemoji** ⇒ mọi
		// emoji trong admin bị đổi thành `<img>` trỏ CDN hỏng (xem docblock `disableEmoji()` — sự cố 2026-09-18).
		add_action( 'admin_init', array( $this, 'disableEmoji' ) );
		add_filter( 'wp_preload_resources', array( $this, 'preloadResources' ) );
	}

	/**
	 * Gỡ toàn bộ wp-emoji (twemoji) khỏi front-end, admin, embed và feed — không dùng tới, tốn 1 request + inline script.
	 *
	 * ⚠️ VÌ SAO PHẢI GỌI Ở CẢ `init` LẪN `admin_init` (đo 2026-09-18):
	 *  • Front-end: hook ở `wp-includes/default-filters.php:359` ⇒ gỡ ở `init` là đủ.
	 *  • Admin: hook đó nằm ở `wp-admin/includes/admin-filters.php:59` và file này được nạp **sau** `init`
	 *    ⇒ gỡ ở `init` xong bị gắn lại ⇒ admin vẫn chạy twemoji. Phải gỡ lại ở `admin_init`.
	 *  • Hệ quả khi lọt: `_wpemojiSettings.svgUrl` = `false` (xem ghi chú dưới) ⇒ thư viện twemoji rơi về base mặc
	 *    định của nó = `https://cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/` rồi build `<base><code>.svg`
	 *    ⇒ **404** (đường dẫn đúng phải có `svg/` hoặc `72x72/*.png`) ⇒ emoji hiện thành **ảnh vỡ** trong wp-admin.
	 *
	 * ⚠️ KHÔNG đặt `add_filter( 'emoji_svg_url', '__return_false' )` nữa: URL rỗng làm twemoji dùng base mặc định
	 * của thư viện (CDN jsdelivr, đường dẫn sai ⇒ 404). Gỡ sạch script rồi thì URL không còn ý nghĩa, nhưng nếu
	 * một plugin khác lỡ nạp emoji ở màn nào đó thì core vẫn giữ mặc định `s.w.org/.../svg/` (hoạt động tốt).
	 */
	public function disableEmoji(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'embed_head', 'print_emoji_detection_script' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'removeTinymceEmoji' ) );
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
