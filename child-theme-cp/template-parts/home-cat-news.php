<?php
/**
 * CP2.5 — Các khối "Tin tức theo chuyên mục" trên trang chủ (REPEATER trong Customizer).
 *
 * Không nhận `$args`: đọc TOÀN BỘ khối từ theme_mod `cp_nblocks` (JSON — Admin tự thêm/xoá/kéo thả
 * trong "Customize → Trang chủ — Khối tin tức theo chuyên mục") rồi render lần lượt.
 * Thẻ bài viết dùng lại `.cp-news-mini` của CP5.2.
 * Riêng bố cục `featured` ("Nổi bật" — yêu cầu Tùng 2026-09-15) dùng markup riêng `.cp-news-feature`:
 * bài 1 = thẻ lớn bên TRÁI, `count − 1` bài còn lại = danh sách `.cp-news-row` bên PHẢI.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_blocks = json_decode( (string) get_theme_mod( 'cp_nblocks', '' ), true );
if ( ! is_array( $cp_blocks ) || ! $cp_blocks ) {
	return;
}

$cp_rendered = 0;

foreach ( $cp_blocks as $cp_block ) {
	if ( ! is_array( $cp_block ) ) {
		continue;
	}

	$cp_term = get_term( absint( $cp_block['cat'] ?? 0 ), 'category' );
	if ( ! $cp_term instanceof WP_Term ) {
		continue;
	}

	$cp_layout = (string) ( $cp_block['layout'] ?? 'cols-3' );
	$cp_layout = in_array( $cp_layout, array( 'cols-3', 'cols-2', 'scroll', 'featured' ), true ) ? $cp_layout : 'cols-3';

	$cp_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, min( 12, absint( $cp_block['count'] ?? 3 ) ) ),
			'cat'                 => (int) $cp_term->term_id,
			'orderby'             => 'date',
			'order'               => 'date-asc' === ( $cp_block['order'] ?? 'date-desc' ) ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $cp_query->have_posts() ) {
		continue;
	}

	++$cp_rendered;
	$cp_alt = 0 === $cp_rendered % 2 ? '' : ' alt';

	$cp_title = trim( (string) ( $cp_block['title'] ?? '' ) );
	$cp_title = '' !== $cp_title ? $cp_title : $cp_term->name;
	?>
	<section class="cp-section<?php echo esc_attr( $cp_alt ); ?> cp-cat-block cp-cat-block--<?php echo esc_attr( $cp_layout ); ?>">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php esc_html_e( 'Tin tức', 'tungleads-theme' ); ?></span>
					<h2><?php echo esc_html( $cp_title ); ?></h2>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( (string) get_term_link( $cp_term ) ); ?>"><?php esc_html_e( 'Xem thêm →', 'tungleads-theme' ); ?></a>
			</div>
			<?php if ( 'scroll' === $cp_layout ) : ?>
				<?php cp_scroller_open(); // CP2.8 — nút cuộn ‹ (chỉ bố cục "Cuộn ngang") ?>
			<?php endif; ?>
			<?php /* CP2.5 — Bố cục "Nổi bật": bài 1 = thẻ LỚN bên trái (ảnh 16:9 + nhãn chuyên mục + tiêu đề + ngày + trích đoạn),
			         các bài còn lại ("Số lượng" − 1) = danh sách bên phải (ảnh nhỏ + tiêu đề + ngày, gạch phân cách).
			         Tách riêng khỏi `.cp-cat-news` (lưới/cuộn ngang của CP2.4) — khác hẳn cấu trúc nên không dùng chung được. */ ?>
			<?php if ( 'featured' === $cp_layout ) : ?>
				<div class="cp-news-feature">
					<?php
					// Bài 1 — thẻ lớn bên trái. `the_post()` ở đây tiêu thụ bài đầu, vòng lặp dưới lấy tiếp bài 2…
					$cp_query->the_post();

					$cp_cats    = get_the_category();
					$cp_cat     = ! empty( $cp_cats ) ? $cp_cats[0] : null;
					$cp_excerpt = trim( wp_strip_all_tags( (string) get_the_excerpt() ) );
					if ( '' === $cp_excerpt ) {
						$cp_excerpt = wp_strip_all_tags( (string) get_the_content() );
					}
					$cp_excerpt = wp_trim_words( $cp_excerpt, 28, '…' );
					?>
					<article class="cp-news-feature__main">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="cp-news-feature__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php
								the_post_thumbnail(
									'large',
									array(
										'alt'      => '',
										'loading'  => 'lazy',
										'decoding' => 'async',
										'sizes'    => '(max-width: 1024px) 100vw, (max-width: 1440px) 52vw, 700px',
									)
								);
								?>
							</a>
						<?php endif; ?>
						<div class="cp-news-feature__body">
							<div class="cp-news-feature__meta">
								<?php if ( $cp_cat instanceof WP_Term ) : ?>
									<a class="cp-news-feature__cat" href="<?php echo esc_url( (string) get_category_link( $cp_cat ) ); ?>"><?php echo esc_html( $cp_cat->name ); ?></a>
								<?php endif; ?>
								<time class="cp-news-feature__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
							</div>
							<h3 class="cp-news-feature__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php if ( '' !== $cp_excerpt ) : ?>
								<p class="cp-news-feature__excerpt"><?php echo esc_html( $cp_excerpt ); ?></p>
							<?php endif; ?>
						</div>
					</article>
					<?php if ( $cp_query->post_count > 1 ) : ?>
						<div class="cp-news-feature__list">
							<?php
							while ( $cp_query->have_posts() ) :
								$cp_query->the_post();

								/* Bài KHÔNG có ảnh đại diện → 1 cột: thiếu class này, `.cp-news-row__body` rơi vào cột
								   `108px` (đo ở 1440: body rộng **108** thay vì 547.5, chữ bị dồn 65px cao). */
								$cp_row_class = has_post_thumbnail() ? 'cp-news-row' : 'cp-news-row cp-news-row--noimg';
								?>
								<article class="<?php echo esc_attr( $cp_row_class ); ?>">
									<?php if ( has_post_thumbnail() ) : ?>
										<a class="cp-news-row__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
											<?php
											the_post_thumbnail(
												'medium',
												array(
													'alt'      => '',
													'loading'  => 'lazy',
													'decoding' => 'async',
													'sizes'    => '108px',
												)
											);
											?>
										</a>
									<?php endif; ?>
									<div class="cp-news-row__body">
										<h3 class="cp-news-row__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<time class="cp-news-row__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
									</div>
								</article>
								<?php
							endwhile;
							?>
						</div>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php else : ?>
			<div class="cp-cat-news">
				<?php
				while ( $cp_query->have_posts() ) :
					$cp_query->the_post();
					?>
					<article class="cp-news-mini">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="cp-news-mini__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php
								the_post_thumbnail(
									'medium',
									array(
										'alt'     => '',
										'loading' => 'lazy',
										'sizes'   => '(max-width: 768px) 100vw, (max-width: 1024px) 45vw, 360px',
									)
								);
								?>
							</a>
						<?php endif; ?>
						<h3 class="cp-news-mini__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<time class="cp-news-mini__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></time>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<?php endif; ?>
			<?php if ( 'scroll' === $cp_layout ) : ?>
				<?php cp_scroller_close(); // CP2.8 — nút cuộn › + đóng .cp-scroller ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
