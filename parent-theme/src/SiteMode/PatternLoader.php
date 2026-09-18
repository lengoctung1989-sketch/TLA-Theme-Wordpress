<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\SiteMode;

use TL\Theme\Core\Theme;

/**
 * Đăng ký block pattern theo site mode. WordPress chỉ tự quét `patterns/*.php` ở cấp gốc,
 * KHÔNG đệ quy — nên các thư mục con phải tự đăng ký ở đây.
 *
 *   patterns/shared/  → luôn nạp
 *   patterns/service/ → chỉ khi mode ∈ {service, hybrid}
 *   patterns/shop/    → chỉ khi mode ∈ {ecommerce, hybrid}
 *
 * (Thay cho ServiceModePatterns.php + EcommerceModePatterns.php trong bản phác thảo — 1 file là đủ.)
 *
 * P3.5 patterns
 */
final class PatternLoader {

	public function register(): void {
		add_action( 'init', array( $this, 'registerPatterns' ) );
	}

	public function registerPatterns(): void {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$mode = Theme::mode();
		$dirs = array( 'shared' );

		if ( SiteModeResolver::isService( $mode ) ) {
			$dirs[] = 'service';
		}
		if ( SiteModeResolver::isEcommerce( $mode ) ) {
			$dirs[] = 'shop';
		}

		foreach ( $dirs as $dir ) {
			foreach ( (array) glob( TL_THEME_DIR . '/patterns/' . $dir . '/*.php' ) as $file ) {
				$this->registerFile( (string) $file );
			}
		}
	}

	private function registerFile( string $file ): void {
		$data = get_file_data(
			$file,
			array(
				'title'       => 'Title',
				'slug'        => 'Slug',
				'categories'  => 'Categories',
				'description' => 'Description',
			)
		);

		if ( empty( $data['slug'] ) ) {
			return;
		}

		ob_start();
		include $file;

		register_block_pattern(
			$data['slug'],
			array(
				'title'       => '' !== $data['title'] ? $data['title'] : $data['slug'],
				'content'     => (string) ob_get_clean(),
				'description' => $data['description'],
				'categories'  => array_values( array_filter( array_map( 'trim', explode( ',', $data['categories'] ) ) ) ),
			)
		);
	}
}
