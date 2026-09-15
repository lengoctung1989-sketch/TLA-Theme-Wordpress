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

							<?php if ( has_post_thumbnail() ) : ?>
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
