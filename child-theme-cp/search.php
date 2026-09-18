<?php
/**
 * CP7.2 — Trang KẾT QUẢ TÌM KIẾM (tìm kiếm CHUNG: `?s=…`, không lọc `post_type`).
 *
 * ⚠️ Tìm SẢN PHẨM **KHÔNG** dùng template này: ô search trên header luôn gửi kèm
 * `post_type=product` (CP1.6) → WooCommerce tự dùng `archive-product.php` và đi qua skin shop
 * của CP3.1 (`.cp-shop`, `.cp-shop-toolbar`, `.cp-side-box` — đo 2026-09-16 thấy đúng). Template này
 * chỉ phục vụ tìm kiếm chung (khách gõ `?s=…` trực tiếp, hoặc click link search cũ của Google) —
 * trước CP7.2 những URL đó rơi vào `index.php` của theme CHA: `<main class="tl-main tl-container">`
 * với **0** phần tử `.cp-*`.
 *
 * Nay: hero `.cp-pagehero` (breadcrumb + H1 + số kết quả) + lưới 2 cột `.cp-news-layout`; kết quả
 * render bằng `cp_search_card()` (sản phẩm kèm GIÁ, còn lại là thẻ tin CP5.1); rỗng thì hiện form tìm
 * lại. Khối hiển thị ở `inc/system.php` (tag `// CP7.2`).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cp_query = get_search_query();
$cp_total = isset( $GLOBALS['wp_query']->found_posts ) ? (int) $GLOBALS['wp_query']->found_posts : 0;
?>
<main id="main">

	<section class="cp-pagehero">
		<div class="cp-container">
			<?php cp_system_breadcrumb( __( 'Tìm kiếm', 'tungleads-theme' ) ); ?>
			<h1>
				<?php
				if ( '' !== $cp_query ) {
					printf(
						/* translators: %s: từ khoá khách gõ vào ô tìm kiếm. */
						esc_html__( 'Kết quả cho “%s”', 'tungleads-theme' ),
						esc_html( $cp_query )
					);
				} else {
					esc_html_e( 'Tìm kiếm', 'tungleads-theme' );
				}
				?>
			</h1>
			<p class="cp-pagehero__meta">
				<?php
				printf(
					/* translators: %s: số kết quả tìm được (in đậm). */
					esc_html__( '%s kết quả', 'tungleads-theme' ),
					'<b>' . esc_html( number_format_i18n( $cp_total ) ) . '</b>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần.
				);
				?>
			</p>
		</div>
	</section>

	<section class="cp-section cp-news-section">
		<div class="cp-container">
			<div class="cp-news-layout">

				<div class="cp-news-main">
					<?php if ( have_posts() ) : ?>
						<div class="cp-news-list">
							<?php
							while ( have_posts() ) :
								the_post();
								cp_search_card();
							endwhile;
							?>
						</div>
						<?php cp_news_pagination(); ?>
					<?php else : ?>
						<p class="cp-news-empty">
							<?php
							printf(
								/* translators: %s: từ khoá khách gõ vào ô tìm kiếm. */
								esc_html__( 'Không có nội dung nào khớp với “%s”.', 'tungleads-theme' ),
								esc_html( $cp_query )
							);
							?>
						</p>
						<p class="cp-404__lead"><?php esc_html_e( 'Thử lại bằng từ khoá khác:', 'tungleads-theme' ); ?></p>
						<?php cp_inline_search_form(); ?>
					<?php endif; ?>
				</div>

				<aside class="cp-news-aside">
					<?php
					cp_single_support_box();
					cp_product_cats_box();
					cp_news_latest_box();
					cp_trust_box();
					?>
				</aside>

			</div>
		</div>
	</section>

</main>
<?php
get_footer();
