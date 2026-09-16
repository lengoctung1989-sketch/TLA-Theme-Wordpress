<?php
/**
 * CP5.3 — Thân trang LƯU TRỮ TIN TỨC, dùng CHUNG cho 2 template:
 *   - chuyên mục (`category.php` — CP5.1) · thẻ (`tag.php` — CP5.3).
 *
 * Trước CP5.3 hai trang buộc phải copy nhau; thực tế chỉ khác nhãn chữ (thẻ ⇒ hero/breadcrumb khác,
 * câu "chưa có bài viết" khác, thêm hộp "Từ khoá phổ biến") nên gom về đây. `category.php` và
 * `tag.php` chỉ còn `get_header()` → partial → `get_footer()`.
 *
 * KHÔNG đọc `$args`: nhánh chuyên mục/thẻ tự suy từ đối tượng đang được truy vấn (`get_queried_object()`)
 * → không thể truyền sai nhánh. Khối hiển thị nằm ở `inc/news.php` (tag `// CP5.1` / `// CP5.3`).
 *
 * Class CSS tái dùng NGUYÊN của CP5.1 (`.cp-pagehero`, `.cp-news-layout`, `.cp-news-card`, `.cp-side-box`…)
 * → trang thẻ khớp hẳn skin Cao Phát mà không thêm CSS trùng.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_term   = get_queried_object();
$cp_term   = $cp_term instanceof WP_Term ? $cp_term : null;
$cp_is_tag = $cp_term instanceof WP_Term && 'post_tag' === $cp_term->taxonomy;
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
						/* translators: %s: số bài viết trong chuyên mục / thẻ. */
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
						<p class="cp-news-empty">
							<?php
							echo esc_html(
								$cp_is_tag
									? __( 'Thẻ này chưa có bài viết nào.', 'tungleads-theme' )
									: __( 'Chuyên mục này chưa có bài viết nào.', 'tungleads-theme' )
							);
							?>
						</p>
					<?php endif; ?>

					<?php cp_news_desc( $cp_term instanceof WP_Term ? (string) $cp_term->description : '' ); ?>
				</div>

				<aside class="cp-news-aside">
					<?php
					cp_news_sidebar();

					// CP5.3 — hộp "Từ khoá phổ biến" CHỈ ở trang thẻ; trang chuyên mục giữ nguyên
					// (thẻ là mục con của chuyên mục nên ở đây có thêm đường đi nội bộ sang thẻ khác).
					if ( $cp_is_tag ) {
						cp_news_tags_box();
					}
					?>
				</aside>

			</div>
		</div>
	</section>

</main>
