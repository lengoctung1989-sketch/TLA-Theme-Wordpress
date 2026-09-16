<?php
/**
 * CP7.1 — Trang KHÔNG TÌM THẤY (HTTP 404).
 *
 * Trước CP7.1 trang 404 rơi vào `index.php` của theme CHA — đo 2026-09-16 (`/khong-ton-tai-xyz/`):
 * `<main class="tl-main tl-container">` với **0** phần tử `.cp-*` ⇒ không có skin Cao Phát, khách gõ
 * sai URL (hoặc bấm link cũ) là thấy một trang "lạ" ngay giữa site.
 *
 * Nay: hero `.cp-pagehero` + breadcrumb + H1 + thân trong thẻ trắng `.cp-page` (tái dùng typography
 * `.cp-article__content` của CP5.2) + cột phải `.cp-news-aside` (3 hộp của CP3.2/CP5.1).
 * Mục tiêu: giữ khách ở lại — có ô tìm kiếm sản phẩm, 4 nút nhanh, hotline, danh mục SP.
 *
 * Khối hiển thị ở `inc/system.php` (tag `// CP7.1`).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cp_shop = function_exists( 'wc_get_page_permalink' ) ? (string) wc_get_page_permalink( 'shop' ) : home_url( '/' );
$cp_news = cp_news_archive_url();
?>
<main id="main">

	<section class="cp-pagehero">
		<div class="cp-container">
			<?php cp_system_breadcrumb( __( 'Không tìm thấy trang', 'tungleads-theme' ) ); ?>
			<h1><?php esc_html_e( 'Không tìm thấy trang', 'tungleads-theme' ); ?></h1>
			<p class="cp-pagehero__meta"><?php esc_html_e( 'Đường dẫn này không tồn tại, hoặc nội dung đã được chuyển đi.', 'tungleads-theme' ); ?></p>
		</div>
	</section>

	<section class="cp-section cp-page-section">
		<div class="cp-container">
			<div class="cp-news-layout">

				<div class="cp-news-main">
					<article class="cp-page cp-404">
						<p class="cp-404__lead"><?php esc_html_e( 'Anh/chị thử tìm lại sản phẩm theo tên hoặc mã cửa:', 'tungleads-theme' ); ?></p>

						<?php cp_inline_search_form(); ?>

						<p class="cp-404__or"><?php esc_html_e( 'Hoặc xem nhanh:', 'tungleads-theme' ); ?></p>
						<p class="cp-404__links">
							<a class="cp-btn cp-btn-primary" href="<?php echo esc_url( $cp_shop ); ?>"><?php esc_html_e( 'Tất cả sản phẩm', 'tungleads-theme' ); ?></a>
							<a class="cp-btn cp-btn-accent" href="tel:<?php echo esc_attr( cp_hotline_tel() ); ?>">
								<?php
								printf(
									/* translators: %s: số hotline đang hiển thị trên site. */
									esc_html__( 'Gọi %s', 'tungleads-theme' ),
									esc_html( cp_hotline_display() )
								);
								?>
							</a>
							<a class="cp-btn cp-btn-ghost" href="<?php echo esc_url( $cp_news ); ?>"><?php esc_html_e( 'Tin tức', 'tungleads-theme' ); ?></a>
							<a class="cp-btn cp-btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Trang chủ', 'tungleads-theme' ); ?></a>
						</p>
					</article>
				</div>

				<aside class="cp-news-aside">
					<?php
					cp_single_support_box();
					cp_product_cats_box();
					cp_trust_box();
					?>
				</aside>

			</div>
		</div>
	</section>

</main>
<?php
get_footer();
