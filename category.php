<?php
/**
 * CP5.1 — Trang danh mục tin tức (lưu trữ chuyên mục bài viết).
 *
 * Override của child CHỈ áp cho chuyên mục (category). Các lưu trữ khác — tag, tác giả, ngày,
 * định dạng — vẫn dùng `archive.php` của theme cha.
 *
 * Khối hiển thị nằm ở `inc/news.php` (tag `// CP5.1`); file này chỉ sắp layout.
 * Trước CP5.1 trang này dùng `archive.php` của theme cha: không có `.cp-container`, không hero,
 * danh sách là `.tl-post-card` (không ảnh) → lệch hẳn skin Cao Phát.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cp_term = get_queried_object();
$cp_term = $cp_term instanceof WP_Term ? $cp_term : null;
?>
<main id="main">

	<section class="cp-pagehero">
		<div class="cp-container">
			<?php cp_news_breadcrumb(); ?>
			<h1><?php echo esc_html( $cp_term instanceof WP_Term ? $cp_term->name : wp_strip_all_tags( (string) get_the_archive_title() ) ); ?></h1>
			<?php if ( $cp_term instanceof WP_Term ) : ?>
				<p class="cp-pagehero__meta">
					<?php
					printf(
						/* translators: %s: số bài viết trong chuyên mục. */
						esc_html__( '%s bài viết', 'tungleads-theme' ),
						'<b>' . esc_html( number_format_i18n( (int) $cp_term->count ) ) . '</b>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- chuỗi đã escape từng phần.
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="cp-section cp-news-section">
		<div class="cp-container">
			<div class="cp-news-layout">

				<div class="cp-news-main">
					<?php if ( have_posts() ) : ?>
						<div class="cp-news-list">
							<?php
							$cp_index = 0;
							while ( have_posts() ) :
								the_post();
								// Bài đầu của TRANG 1 phóng to (ảnh trên / chữ dưới); các trang sau đồng đều.
								cp_news_card( 0 === $cp_index && ! is_paged() );
								++$cp_index;
							endwhile;
							?>
						</div>
						<?php cp_news_pagination(); ?>
					<?php else : ?>
						<p class="cp-news-empty"><?php esc_html_e( 'Chuyên mục này chưa có bài viết nào.', 'tungleads-theme' ); ?></p>
					<?php endif; ?>

					<?php cp_news_desc( $cp_term instanceof WP_Term ? (string) $cp_term->description : '' ); ?>
				</div>

				<aside class="cp-news-aside">
					<?php cp_news_sidebar(); ?>
				</aside>

			</div>
		</div>
	</section>

</main>
<?php
get_footer();
