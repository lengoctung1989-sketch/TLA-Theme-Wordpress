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
								<?php if ( has_post_thumbnail() ) : ?>
									<figure class="cp-article__thumb">
										<?php
										the_post_thumbnail(
											'large',
											array(
												'sizes'    => '(max-width: 768px) 100vw, (max-width: 1200px) 900px, 992px',
												'decoding' => 'async',
											)
										);
										?>
									</figure>
								<?php endif; ?>

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
