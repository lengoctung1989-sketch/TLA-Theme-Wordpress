<?php
/**
 * CP5.2 — Trang chi tiết tin tức (bài viết, post type `post`).
 *
 * Override `single.php` của theme cha CHỈ cho bài viết thường; `single-dichvu.php` (CPT dịch vụ)
 * cụ thể hơn nên vẫn do theme cha lo.
 *
 * Tái dùng nguyên lưới 2 cột + sidebar của CP5.1 (`.cp-news-layout` / `.cp-news-main` /
 * `.cp-news-aside` + `cp_news_sidebar()`) — nhờ vậy breakpoint ≤1024/≤768 không phải viết lại.
 *
 * Trước CP5.2 trang này dùng `single.php` của theme cha (`.tl-single__*`, cột nội dung 720px giữa
 * trang, không sidebar, không skin Cao Phát).
 *
 * CP5.4 — KHÔNG in ảnh đại diện đầu bài (mặc định tắt, bật lại bằng filter `cp_article_show_thumb`).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main">

	<section class="cp-section cp-article-section">
		<div class="cp-container">
			<div class="cp-news-layout">

				<div class="cp-news-main">
					<?php
					while ( have_posts() ) :
						the_post();
						cp_news_single_breadcrumb();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'cp-article' ); ?>>

							<header class="cp-article__head">
								<h1 class="cp-article__title"><?php the_title(); ?></h1>
								<?php cp_news_article_meta(); ?>
							</header>

							<?php
							/*
							 * CP5.4 — ẢNH ĐẠI DIỆN ĐẦU BÀI: MẶC ĐỊNH **KHÔNG IN** (Tùng chốt 2026-09-16).
							 *
							 * Trước CP5.4 chỗ này in `<figure class="cp-article__thumb">` (ảnh `large`, `eager` +
							 * `fetchpriority="high"` vì là LCP). Lý do bỏ — đo 2026-09-16: **3/7 bài** có ảnh đại diện
							 * lỗi file (`naturalWidth = 0×0`) ⇒ chỉ còn **khung xám 990×620** giữa tiêu đề và nội dung.
							 *
							 * BẬT LẠI không cần sửa file:
							 *     add_filter( 'cp_article_show_thumb', '__return_true' );
							 * CSS `.cp-article__thumb` / `.cp-article__thumb img` (caophat.css) vẫn giữ nguyên cho ca
							 * bật lại ⇒ KHÔNG phải CSS mồ côi.
							 *
							 * `page.php` (trang tĩnh — CP6.1) VẪN in ảnh đại diện; muốn bỏ luôn ở đó thì nói.
							 */
							?>
							<?php if ( apply_filters( 'cp_article_show_thumb', false ) && has_post_thumbnail() ) : ?>
								<?php /* Ảnh đầu bài = LCP → `eager` + `fetchpriority=high`, `sizes` khai đúng
								         bề ngang cột nội dung (bài 2 cột: ~992px). */ ?>
								<figure class="cp-article__thumb">
									<?php
									the_post_thumbnail(
										'large',
										array(
											'sizes'         => '(max-width: 768px) 100vw, (max-width: 1200px) 900px, 992px',
											'loading'       => 'eager',
											'fetchpriority' => 'high',
											'decoding'      => 'async',
										)
									);
									?>
								</figure>
							<?php endif; ?>

							<div class="cp-article__content">
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

							<?php cp_news_article_tags(); ?>
						</article>
						<?php
						cp_news_article_nav();

						/* 2026-09-15 — KHÔNG in khối bình luận (`.tl-comments` của theme cha) trên trang chi
						   tiết tin tức, theo yêu cầu Tùng: ẩn mục "Để lại một bình luận". Kiểm DB trước khi
						   bỏ: không bài tin nào có bình luận thật (toàn bộ 7 comment trong DB đều là ghi chú
						   đơn hàng gắn vào `shop_order` đã xoá) nên không giấu mất nội dung nào; bỏ cả truy
						   vấn `comments_template()` (nhanh hơn ẩn bằng CSS).
						   Muốn hiện lại: gọi `comments_template()` tại đây — theme cha đã có `comments.php`.
						   Các template khác (`single-dichvu.php`, `page.php`, `archive.php`…) KHÔNG bị đổi. */
					endwhile;
					?>
				</div>

				<aside class="cp-news-aside">
					<?php cp_news_sidebar(); ?>
				</aside>

			</div>
		</div>
	</section>

	<?php cp_news_related(); ?>

</main>
<?php
get_footer();
