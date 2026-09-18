<?php
/**
 * CP6.1 — Trang nội dung tĩnh (trang tạo trong Admin → Trang).
 *
 * Trước CP6.1 trang dùng `page.php` của theme cha: `.tl-main` KHÔNG có `.cp-container`, KHÔNG in
 * H1 (tiêu đề trang không xuất hiện ở đâu), nội dung bị bó trong `.tl-page__content` 720px của
 * theme cha và không có skin Cao Phát.
 *
 * Tái dùng: hero `.cp-pagehero` + `.cp-breadcrumb` (CP3.1), lưới 2 cột `.cp-news-layout` (CP5.1),
 * typography `.cp-article__content` (CP5.2), hộp cột phải (CP3.2). KHÔNG in khối bình luận
 * (giống CP5.2 — trang hiện đều đóng bình luận).
 *
 * CP6.3 — KHÔNG in ảnh đại diện trang nữa (Tùng chốt 2026-09-16, nối tiếp CP5.4 của bài viết):
 * khối `<figure class="cp-article__thumb">` đã **XOÁ HẲN** khỏi file. Lý do (đo 2026-09-16):
 * **2/2 trang** có ảnh đại diện đều **lỗi file** (`naturalWidth = 0×0`) ⇒ chỉ còn khung xám
 * `--cp-surface` cao **451–620px** ngay trên nội dung. Muốn in lại: xem lại khối cũ bằng
 * `git log -S'cp-article__thumb' -- page.php` (CSS `.cp-article__thumb*` vẫn giữ trong
 * `caophat.css` cho ca bật lại ở bài viết).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Giỏ hàng / thanh toán / đơn đã nhận / tài khoản: CP3.4–CP3.6 bọc hero + container qua filter
// `the_content` → ở đây chỉ in nội dung, KHÔNG bọc thêm (nếu không sẽ ra 2 hero).
$cp_is_woo_page = cp_is_woo_endpoint_page();
?>
<main id="main">
	<?php if ( $cp_is_woo_page ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	<?php else : ?>

		<section class="cp-pagehero">
			<div class="cp-container">
				<?php cp_page_breadcrumb(); ?>
				<h1><?php the_title(); ?></h1>
			</div>
		</section>

		<section class="cp-section cp-page-section">
			<div class="cp-container">
				<div class="cp-news-layout">

					<div class="cp-news-main">
						<?php
						while ( have_posts() ) :
							the_post();
							?>
							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cp-page' ); ?>>
								<?php
								/* CP6.3 — ẢNH ĐẠI DIỆN TRANG: KHÔNG in (Tùng chốt 2026-09-16).
								   Khối `<figure class="cp-article__thumb">` + `the_post_thumbnail( 'large', … )`
								   đã bị XOÁ khỏi đây, KHÔNG giữ lại sau điều kiện `if`. Nối tiếp CP5.4 (bài viết
								   cũng không in ảnh đại diện — bên đó markup còn nhưng bị chặn bởi filter
								   `cp_article_show_thumb`). Muốn có lại ở trang: `git log -S'cp-article__thumb'
								   -- page.php` để lấy lại khối cũ. */
								?>

								<?php /* `.cp-article__content` = typography rich-text của CP5.2 — cố ý tái dùng
								           để trang và bài viết không lệch kiểu chữ, không nhân đôi CSS. */ ?>
								<div class="cp-page__content cp-article__content">
									<?php
									the_content();
									wp_link_pages(
										array(
											'before' => '<nav class="cp-article__pages">',
											'after'  => '</nav>',
										)
									);
									?>
								</div>
							</article>
							<?php
						endwhile;
						?>
					</div>

					<aside class="cp-news-aside">
						<?php cp_page_sidebar(); ?>
					</aside>

				</div>
			</div>
		</section>

	<?php endif; ?>
</main>
<?php
get_footer();
