<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Features;

use TL\Theme\Core\Contracts\FeatureInterface;

/**
 * Hardening ở phạm vi theme: ẩn thông tin phiên bản, helper escaping/sanitize dùng chung
 * cho template. KHÔNG set security header / disable XML-RPC (việc của hosting/security plugin).
 *
 * P2.4 security
 */
final class Security implements FeatureInterface {

	public function shouldBoot(): bool {
		return true;
	}

	public function boot(): void {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	/**
	 * Lọc HTML nội dung do editor/template nhúng — chỉ cho phép tag inline an toàn.
	 * Dùng khi cần in chuỗi có <strong>/<em>/<a> mà không mở full wp_kses_post.
	 */
	public static function ksesInline( string $html ): string {
		return wp_kses(
			$html,
			array(
				'a'      => array(
					'href'   => array(),
					'title'  => array(),
					'rel'    => array(),
					'target' => array(),
				),
				'strong' => array(),
				'em'     => array(),
				'b'      => array(),
				'i'      => array(),
				'span'   => array( 'class' => array() ),
				'br'     => array(),
			)
		);
	}
}
