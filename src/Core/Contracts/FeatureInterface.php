<?php
/**
 * @package TL\Theme
 */

declare(strict_types=1);

namespace TL\Theme\Core\Contracts;

/**
 * Mỗi Feature là một đơn vị chức năng của theme.
 *
 * P1.2 feature-registry
 */
interface FeatureInterface {

	/**
	 * Chỉ kiểm tra điều kiện tĩnh/môi trường (site mode, plugin active).
	 * KHÔNG chứa điều kiện động theo request (đang ở trang nào, giỏ hàng...).
	 */
	public function shouldBoot(): bool;

	/**
	 * Đăng ký hook. KHÔNG thực thi logic theo request ngay tại đây —
	 * add_action() vào hook phù hợp (init, wp_enqueue_scripts, woocommerce_init...).
	 */
	public function boot(): void;
}
