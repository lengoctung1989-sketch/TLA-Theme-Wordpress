<?php
/**
 * CP5.1 — Trang danh mục tin tức (lưu trữ chuyên mục bài viết).
 *
 * `category.php` của child chỉ gọi lại các hàm ở đây — KHÔNG copy template của theme cha.
 * Tái dùng class CSS có sẵn: `.cp-pagehero`, `.cp-breadcrumb` (CP3.1), `.cp-side-box`,
 * `.cp-side-cats`, `.cp-side-news` (CP3.2) và khối thu gọn `.cp-shop-desc` (CP3.1).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL trang danh sách tin tức — dùng cho link "Xem tất cả →" ở khối tin trang chủ.
 *
 * Site chưa đặt "Trang bài viết" (Settings → Reading) nên `page_for_posts` = 0 và link cũ trỏ
 * về trang chủ. Fallback: chuyên mục `tin-tuc`; không có nữa thì về trang chủ.
 * Ghi đè bằng filter `cp_news_archive_url`.
 */
function cp_news_archive_url(): string {
	$page_id = (int) get_option( 'page_for_posts' );
	$url     = $page_id > 0 ? (string) get_permalink( $page_id ) : '';

	if ( '' === $url ) {
		$cp_cat = get_category_by_slug( 'tin-tuc' );
		if ( $cp_cat instanceof WP_Term ) {
			$url = (string) get_category_link( $cp_cat );
		}
	}

	return (string) apply_filters( 'cp_news_archive_url', '' !== $url ? $url : home_url( '/' ) );
}

/**
 * CP5.1 — Breadcrumb "Trang chủ / … / <chuyên mục đang xem>".
 * CP5.3 — Nhánh THẺ: "Trang chủ / Tin tức / Từ khoá: <thẻ>". Thẻ KHÔNG có tổ tiên nên phải rẽ nhánh
 *         sớm: gọi `get_ancestors( $term_id, 'category' )` / `get_cat_name()` bằng ID thẻ sẽ ra chuỗi RỖNG
 *         (crumb trắng). Mục "Tin tức" chỉ chèn khi trang danh sách tin KHÁC trang chủ (nếu không sẽ có
 *         2 crumb cùng trỏ về `/`).
 *
 * Tự viết thay vì `woocommerce_breadcrumb()`: trang tin tức không thuộc WooCommerce (hàm đó còn
 * phụ thuộc plugin đang bật).
 */
function cp_news_breadcrumb(): void {
	$cp_term = get_queried_object();
	if ( ! $cp_term instanceof WP_Term ) {
		return;
	}

	$cp_sep = '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';

	if ( 'post_tag' === $cp_term->taxonomy ) {
		echo '<nav class="cp-breadcrumb" aria-label="' . esc_attr__( 'Đường dẫn', 'tungleads-theme' ) . '">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Trang chủ', 'tungleads-theme' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hằng chuỗi.
		echo $cp_sep; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup cố định.

		$cp_hub = cp_news_archive_url();
		if ( untrailingslashit( $cp_hub ) !== untrailingslashit( home_url( '/' ) ) ) {
			echo '<a href="' . esc_url( $cp_hub ) . '">' . esc_html__( 'Tin tức', 'tungleads-theme' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hằng chuỗi.
			echo $cp_sep; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup cố định.
		}

		echo '<span class="cp-breadcrumb__here">'
			. esc_html(
				sprintf(
					/* translators: %s: tên thẻ (tag). */
					__( 'Từ khoá: %s', 'tungleads-theme' ),
					$cp_term->name
				)
			) . '</span>';
		echo '</nav>';
		return;
	}

	// Chuỗi tổ tiên đang ở dạng gần → xa, đảo lại cho đúng thứ tự hiển thị rồi nối chính nó.
	$cp_chain   = array_reverse( get_ancestors( $cp_term->term_id, 'category' ) );
	$cp_chain[] = (int) $cp_term->term_id;

	echo '<nav class="cp-breadcrumb" aria-label="' . esc_attr__( 'Đường dẫn', 'tungleads-theme' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Trang chủ', 'tungleads-theme' ) . '</a>';
	foreach ( $cp_chain as $cp_ancestor_id ) {
		echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
		if ( (int) $cp_term->term_id === (int) $cp_ancestor_id ) {
			echo '<span class="cp-breadcrumb__here">' . esc_html( get_cat_name( (int) $cp_ancestor_id ) ) . '</span>';
			continue;
		}
		echo '<a href="' . esc_url( (string) get_category_link( (int) $cp_ancestor_id ) ) . '">'
			. esc_html( get_cat_name( (int) $cp_ancestor_id ) ) . '</a>';
	}
	echo '</nav>';
}

/**
 * CP5.1 — Một thẻ bài viết trong danh sách (`.cp-news-card`).
 *
 * @param bool $featured Bài ĐẦU của trang 1: ảnh trên / chữ dưới, ảnh size `large`.
 */
function cp_news_card( bool $featured = false ): void {
	$cp_cats    = get_the_category();
	$cp_cat     = ! empty( $cp_cats ) ? $cp_cats[0] : null;
	$cp_classes = 'cp-news-card' . ( $featured ? ' cp-news-card--featured' : '' );

	// Bài không có mô tả → lấy 32 từ đầu của nội dung, tránh thẻ trắng chữ.
	$cp_excerpt = trim( wp_strip_all_tags( (string) get_the_excerpt() ) );
	if ( '' === $cp_excerpt ) {
		$cp_excerpt = wp_strip_all_tags( (string) get_the_content() );
	}
	$cp_excerpt = wp_trim_words( $cp_excerpt, 32, '…' );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( $cp_classes ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<?php /* Ảnh là link phụ của cùng bài (link chính là tiêu đề) → ẩn khỏi screen reader,
			         `alt=""` để không đọc tên file ảnh.
			         `sizes` khai ĐÚNG bề ngang thẻ (300px, thẻ nổi bật ~1040px) thay vì để WordPress
			         dùng fallback `500px`: fallback làm Chrome chọn ứng viên ~533w — vừa nặng gấp đôi
			         vừa có thể trỏ vào file không tồn tại (ảnh vỡ, đo được `naturalWidth = 0`). */ ?>
			<a class="cp-news-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php
				the_post_thumbnail(
					$featured ? 'large' : 'medium',
					array(
						'alt'      => '',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => $featured
							? '(max-width: 768px) 100vw, (max-width: 1200px) 900px, 1040px'
							: '(max-width: 900px) 100vw, (max-width: 1200px) 260px, 300px',
					)
				);
				?>
			</a>
		<?php endif; ?>

		<div class="cp-news-card__body">
			<div class="cp-news-card__meta">
				<?php if ( $cp_cat instanceof WP_Term ) : ?>
					<a class="cp-news-card__cat" href="<?php echo esc_url( (string) get_category_link( $cp_cat ) ); ?>"><?php echo esc_html( $cp_cat->name ); ?></a>
				<?php endif; ?>
				<time class="cp-news-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<svg class="cp-news-card__clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</div>

			<h2 class="cp-news-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<?php if ( '' !== $cp_excerpt ) : ?>
				<p class="cp-news-card__excerpt"><?php echo esc_html( $cp_excerpt ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/** CP5.1 — Cột phải: chuyên mục + tin mới nhất + hộp hỗ trợ (hộp của CP3.2). */
function cp_news_sidebar(): void {
	cp_news_cats_box();
	cp_news_latest_box();
	if ( function_exists( 'cp_single_support_box' ) ) {
		cp_single_support_box();
	}
}

/** CP5.1 — Hộp "Chuyên mục": mục đang xem được WordPress tự gắn class `current-cat`. */
function cp_news_cats_box(): void {
	$cp_items = wp_list_categories(
		array(
			'taxonomy'   => 'category',
			'title_li'   => '',
			'show_count' => true,
			'hide_empty' => true,
			'echo'       => false,
		)
	);
	if ( ! is_string( $cp_items ) || false === strpos( $cp_items, '<li' ) ) {
		return;
	}

	echo '<div class="cp-side-box cp-news-cats-box"><h4>' . esc_html__( 'Chuyên mục', 'tungleads-theme' ) . '</h4>';
	echo '<ul class="cp-side-cats cp-news-cats">' . $cp_items . '</ul></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup do `wp_list_categories()` sinh.
}

/**
 * CP5.3 — Hộp "Từ khoá phổ biến" cho cột phải TRANG THẺ: các thẻ nhiều bài nhất, bỏ chính thẻ đang xem.
 *
 * Vì sao KHÔNG dùng `wp_tag_cloud()`: site có **483 thẻ**, trong đó **354 thẻ chỉ gắn 1 bài** (đo
 * 2026-09-16) → đổ hết ra là rác và loãng link nội bộ. Ở đây lấy theo SỐ BÀI giảm dần và chỉ nhận
 * thẻ có `count >= 2` ⇒ 12 thẻ "nặng" nhất (mỗi thẻ 5–25 bài).
 * Chip tái dùng `.cp-article__tag` của CP5.2 (không khai CSS trùng); số bài in kèm để biết thẻ nào
 * nhiều nội dung. Đổi số lượng bằng filter `cp_news_tags_box_number`.
 */
function cp_news_tags_box(): void {
	$cp_current = get_queried_object();
	$cp_exclude = $cp_current instanceof WP_Term && 'post_tag' === $cp_current->taxonomy ? array( (int) $cp_current->term_id ) : array();

	$cp_tags = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			// Lấy dư 30 rồi mới lọc `count >= 2`: `number` của `get_terms()` đếm cả thẻ 1 bài nằm xen
			// giữa bảng xếp hạng nên không thể đặt đúng 12 ngay từ truy vấn.
			'number'     => 30,
			'exclude'    => $cp_exclude,
		)
	);
	if ( is_wp_error( $cp_tags ) || ! $cp_tags ) {
		return;
	}

	$cp_limit = max( 1, (int) apply_filters( 'cp_news_tags_box_number', 12 ) );
	$cp_tags  = array_slice(
		array_values( array_filter( $cp_tags, static fn ( WP_Term $cp_item ): bool => (int) $cp_item->count >= 2 ) ),
		0,
		$cp_limit
	);
	if ( ! $cp_tags ) {
		return;
	}

	echo '<div class="cp-side-box cp-news-tags-box"><h4>' . esc_html__( 'Từ khoá phổ biến', 'tungleads-theme' ) . '</h4>';
	echo '<div class="cp-side-tags">';
	foreach ( $cp_tags as $cp_tag ) {
		echo '<a class="cp-article__tag" href="' . esc_url( (string) get_tag_link( (int) $cp_tag->term_id ) ) . '">'
			. esc_html( $cp_tag->name )
			// Dấu cách đầu chuỗi: `.textContent` không có khoảng trắng nên tên đọc liền số bài
			// ("cửa gỗ công nghiệp25") — thêm space để tên đọc/trình đọc màn hình tách đúng.
			. '<span class="cp-side-tags__count"> ' . esc_html( number_format_i18n( (int) $cp_tag->count ) ) . '</span></a>';
	}
	echo '</div></div>';
}

/** CP5.1 — Hộp "Tin mới nhất": 5 bài mới nhất toàn site, tái dùng list `.cp-side-news` của CP3.2. */
function cp_news_latest_box(): void {
	$cp_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 5,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $cp_query->have_posts() ) {
		return;
	}
	?>
	<div class="cp-side-box cp-news-latest-box">
		<h4><?php esc_html_e( 'Tin mới nhất', 'tungleads-theme' ); ?></h4>
		<ul class="cp-side-news cp-news-latest">
			<?php
			while ( $cp_query->have_posts() ) :
				$cp_query->the_post();
				?>
				<li>
					<a href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<span class="cp-side-news__thumb"><?php the_post_thumbnail( 'thumbnail', array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '64px' ) ); ?></span>
						<?php endif; ?>
						<span class="cp-side-news__text">
							<span class="cp-side-news__name"><?php the_title(); ?></span>
							<span class="cp-side-news__date"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></span>
						</span>
					</a>
				</li>
				<?php
			endwhile;
			?>
		</ul>
	</div>
	<?php
	wp_reset_postdata();
}

/** CP5.1 — Phân trang: bọc `the_posts_pagination()` (markup `.page-numbers` của WP) trong `.cp-pagination`. */
function cp_news_pagination(): void {
	if ( (int) $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	echo '<div class="cp-pagination">';
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'screen_reader_text' => __( 'Phân trang bài viết', 'tungleads-theme' ),
			'prev_text'          => '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html__( 'Trang trước', 'tungleads-theme' ) . '</span>',
			'next_text'          => '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html__( 'Trang sau', 'tungleads-theme' ) . '</span>',
		)
	);
	echo '</div>';
}

/**
 * CP5.1 — Mô tả chuyên mục: render DƯỚI danh sách bài viết, thu gọn sẵn.
 *
 * Giống CP3.1 (`.cp-shop-desc`): nội dung SEO có thể rất dài nên mặc định `max-height: 12em`
 * + gradient mờ dần, bấm "Xem thêm" mở tức thì (không animate `max-height` → không jank).
 * JS inline bám theo `document.currentScript.previousElementSibling` nên không cần file riêng.
 *
 * @param string $desc Mô tả chuyên mục (plain text, có thể xuống dòng).
 */
function cp_news_desc( string $desc ): void {
	$desc = trim( $desc );
	if ( '' === $desc ) {
		return;
	}
	?>
	<div class="cp-shop-desc cp-news-desc">
		<div class="cp-shop-desc__body"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
		<button type="button" class="cp-shop-desc__toggle" aria-expanded="false">
			<span class="cp-shop-desc__more"><?php esc_html_e( 'Xem thêm', 'tungleads-theme' ); ?></span>
			<span class="cp-shop-desc__less"><?php esc_html_e( 'Thu gọn', 'tungleads-theme' ); ?></span>
		</button>
	</div>
	<script>
	(function () {
		var box = document.currentScript.previousElementSibling;
		var btn = box && box.querySelector('.cp-shop-desc__toggle');
		if ( ! btn ) { return; }
		btn.addEventListener('click', function () {
			var open = box.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	})();
	</script>
	<?php
}

/* ============================ CP5.2 — CHI TIẾT BÀI VIẾT ============================ */

/**
 * CP5.2 — Breadcrumb trang chi tiết: "Trang chủ / <chuyên mục> / <tiêu đề rút gọn>".
 *
 * Dùng thanh mảnh `.cp-breadcrumb--bar` (như trang chi tiết SP) thay vì `.cp-pagehero`:
 * tiêu đề bài viết dài nên đưa vào hero sẽ chiếm quá nhiều chiều cao.
 */
function cp_news_single_breadcrumb(): void {
	$cp_cats = get_the_category();
	$cp_cat  = ! empty( $cp_cats ) ? $cp_cats[0] : null;

	echo '<nav class="cp-breadcrumb cp-breadcrumb--bar" aria-label="' . esc_attr__( 'Đường dẫn', 'tungleads-theme' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Trang chủ', 'tungleads-theme' ) . '</a>';
	if ( $cp_cat instanceof WP_Term ) {
		echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
		echo '<a href="' . esc_url( (string) get_category_link( $cp_cat ) ) . '">' . esc_html( $cp_cat->name ) . '</a>';
	}
	echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
	echo '<span class="cp-breadcrumb__here">' . esc_html( wp_trim_words( get_the_title(), 8, '…' ) ) . '</span>';
	echo '</nav>';
}

/**
 * CP5.2 — Thời lượng đọc ước tính (200 từ/phút).
 *
 * KHÔNG dùng `str_word_count()`: hàm đó chỉ đếm ký tự A–Z (tiếng Việt có dấu bị bỏ qua) →
 * tách bằng `preg_split( '/\s+/u' )` cho đúng.
 */
function cp_news_reading_time( int $post_id = 0 ): string {
	$post_id = $post_id > 0 ? $post_id : (int) get_the_ID();
	$text    = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	$words   = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$minutes = max( 1, (int) ceil( count( is_array( $words ) ? $words : array() ) / 200 ) );

	return sprintf(
		/* translators: %d: số phút đọc ước tính. */
		__( '%d phút đọc', 'tungleads-theme' ),
		$minutes
	);
}

/** CP5.2 — Dòng meta bài viết: chuyên mục (link) · ngày · tác giả · phút đọc. */
function cp_news_article_meta(): void {
	$cp_cats = get_the_category();
	$cp_cat  = ! empty( $cp_cats ) ? $cp_cats[0] : null;
	?>
	<div class="cp-article__meta">
		<?php if ( $cp_cat instanceof WP_Term ) : ?>
			<a class="cp-article__cat" href="<?php echo esc_url( (string) get_category_link( $cp_cat ) ); ?>"><?php echo esc_html( $cp_cat->name ); ?></a>
		<?php endif; ?>
		<time class="cp-article__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
			<svg class="cp-article__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
			<?php echo esc_html( get_the_date() ); ?>
		</time>
		<span class="cp-article__author">
			<svg class="cp-article__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			<?php echo esc_html( get_the_author() ); ?>
		</span>
		<span class="cp-article__read"><?php echo esc_html( cp_news_reading_time() ); ?></span>
	</div>
	<?php
}

/** CP5.2 — Danh sách tag của bài dạng chip (bài SEO có thể có rất nhiều tag → tự xuống hàng). */
function cp_news_article_tags(): void {
	$cp_tags = get_the_tags();
	if ( empty( $cp_tags ) || is_wp_error( $cp_tags ) ) {
		return;
	}

	echo '<div class="cp-article__tags"><span class="cp-article__tags-label">'
		. esc_html__( 'Từ khoá:', 'tungleads-theme' ) . '</span>';
	foreach ( $cp_tags as $cp_tag ) {
		echo '<a class="cp-article__tag" href="' . esc_url( (string) get_tag_link( $cp_tag ) ) . '">'
			. esc_html( $cp_tag->name ) . '</a>';
	}
	echo '</div>';
}

/** CP5.2 — Điều hướng bài trước / bài sau (chỉ in mục có bài thật). */
function cp_news_article_nav(): void {
	$cp_items = array(
		array( get_previous_post(), 'prev', __( 'Bài trước', 'tungleads-theme' ), '‹' ),
		array( get_next_post(), 'next', __( 'Bài sau', 'tungleads-theme' ), '›' ),
	);
	$cp_items = array_filter( $cp_items, static fn ( array $item ): bool => $item[0] instanceof WP_Post );
	if ( ! $cp_items ) {
		return;
	}

	echo '<nav class="cp-article-nav" aria-label="' . esc_attr__( 'Điều hướng bài viết', 'tungleads-theme' ) . '">';
	foreach ( $cp_items as $cp_item ) {
		printf(
			'<a class="cp-article-nav__item cp-article-nav__item--%1$s" href="%2$s">'
			. '<span class="cp-article-nav__dir"><span aria-hidden="true">%3$s</span> %4$s</span>'
			. '<span class="cp-article-nav__title">%5$s</span></a>',
			esc_attr( $cp_item[1] ),
			esc_url( (string) get_permalink( $cp_item[0] ) ),
			esc_html( $cp_item[3] ),
			esc_html( $cp_item[2] ),
			esc_html( get_the_title( $cp_item[0] ) )
		);
	}
	echo '</nav>';
}

/**
 * CP5.2 — Dải "Bài viết liên quan" (cùng chuyên mục, 4 bài) — nền xám, full chiều rộng.
 *
 * @param int $post_id Bài đang xem (loại khỏi kết quả).
 */
function cp_news_related( int $post_id = 0 ): void {
	$post_id  = $post_id > 0 ? $post_id : (int) get_the_ID();
	$cp_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 4,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'category__in'        => wp_get_post_categories( $post_id ),
		)
	);
	if ( ! $cp_query->have_posts() ) {
		return;
	}
	?>
	<section class="cp-news-related">
		<div class="cp-container">
			<h2 class="cp-news-related__title"><?php esc_html_e( 'Bài viết liên quan', 'tungleads-theme' ); ?></h2>
			<div class="cp-news-related__grid">
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
										'sizes'   => '(max-width: 600px) 100vw, (max-width: 1024px) 45vw, 300px',
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
				?>
			</div>
		</div>
	</section>
	<?php
	wp_reset_postdata();
}
