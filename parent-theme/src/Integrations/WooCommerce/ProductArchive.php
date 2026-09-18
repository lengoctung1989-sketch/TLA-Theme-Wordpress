<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Integrations\WooCommerce;

/**
 * Tuỳ biến trang danh sách sản phẩm QUA HOOK — không copy `woocommerce/archive-product.php`.
 * Site con override trực tiếp qua chính `loop_shop_columns` / `loop_shop_per_page`.
 *
 * P4.2 product-archive
 */
final class ProductArchive {

	public function register(): void {
		add_filter( 'loop_shop_columns', static fn () => 3 );
		add_filter( 'loop_shop_per_page', static fn () => 12 );
	}
}
