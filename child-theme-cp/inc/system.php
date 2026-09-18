<?php
/**
 * CP7 — TRANG HỆ THỐNG: 404 (CP7.1) · kết quả tìm kiếm (CP7.2) · vệ sinh shortcode cũ (CP7.3).
 *
 * Vì sao có file này: theme con chỉ override 8 template (`header/footer/front-page/page/single/
 * category/tag`), nên các view KHÁC rơi vào template của theme CHA — đo 2026-09-16: trang 404 và
 * tìm kiếm chung ra `main.tl-main.tl-container` với **0** phần tử `.cp-*` ⇒ mất hẳn skin Cao Phát
 * (khách bấm từ ô search / gõ sai URL là thấy trang "lạ").
 *
 * Khối hiển thị đặt ở đây (không nhét vào `inc/news.php`/`inc/page.php`) vì 404 và tìm kiếm không
 * thuộc nghiệp vụ tin tức lẫn trang tĩnh. CSS tái dùng NGUYÊN của CP1.6 (`.cp-search`), CP3.1/CP3.2
 * (`.cp-pagehero`, `.cp-side-box`, `.cp-side-cats`), CP5.1 (`.cp-news-layout`, `.cp-news-card`,
 * `.cp-news-empty`, `.cp-news-aside`) — chỉ thêm khối `CP7` nhỏ ở cuối `caophat.css`.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

/* ======================================================================
   CP7.1 + CP7.2 — phần dùng chung cho 404 và kết quả tìm kiếm
   ====================================================================== */

/**
 * CP7.1/CP7.2 — Breadcrumb 2 cấp "Trang chủ / &lt;mục&gt;" cho trang hệ thống.
 *
 * KHÔNG dùng `cp_page_breadcrumb()` (cần `get_the_ID()` của một trang) hay `cp_news_breadcrumb()`
 * (cần `get_queried_object()` là WP_Term) — 404 không có đối tượng truy vấn, tìm kiếm không có cây
 * cha/con. Ở đây chỉ 1 cấp cố định nên truyền chữ vào là đủ.
 *
 * @param string $here Nhãn mục đang xem (đã là chuỗi dịch).
 */
function cp_system_breadcrumb( string $here ): void {
	echo '<nav class="cp-breadcrumb" aria-label="' . esc_attr__( 'Đường dẫn', 'tungleads-theme' ) . '">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Trang chủ', 'tungleads-theme' ) . '</a>';
	echo '<span class="cp-breadcrumb__sep" aria-hidden="true">/</span>';
	echo '<span class="cp-breadcrumb__here">' . esc_html( $here ) . '</span>';
	echo '</nav>';
}

/**
 * CP7.1/CP7.2 — Ô tìm kiếm đặt TRONG nội dung (404 + trang kết quả rỗng).
 *
 * Tái dùng NGUYÊN skin `.cp-search` của CP1.6 (pill, nút kính lúp, chặn nền autofill xanh của
 * Chrome) — chỉ thêm class `.cp-inline-search` để giãn rộng ra giữa cột nội dung. Mặc định giới hạn
 * vào sản phẩm (`post_type=product`) giống ô trên header: site bán cửa nên khách gõ vào đây thường
 * là tìm sản phẩm; truyền `''` nếu muốn tìm mọi loại nội dung.
 *
 * @param string $post_type `product` (mặc định) hoặc `''` để không lọc.
 */
function cp_inline_search_form( string $post_type = 'product' ): void {
	$cp_uid = wp_unique_id( 'cp-search-inline-' );
	?>
	<form class="cp-search cp-inline-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="<?php echo esc_attr( $cp_uid ); ?>"><?php esc_html_e( 'Tìm sản phẩm', 'tungleads-theme' ); ?></label>
		<input id="<?php echo esc_attr( $cp_uid ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Nhập tên cửa, mã sản phẩm…', 'tungleads-theme' ); ?>" autocomplete="off" autocapitalize="off" spellcheck="false">
		<?php if ( '' !== $post_type ) : ?>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
		<?php endif; ?>
		<button type="submit" aria-label="<?php echo esc_attr__( 'Tìm kiếm', 'tungleads-theme' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
		</button>
	</form>
	<?php
}

/**
 * CP7.1 — Hộp cột phải "Danh mục sản phẩm" (10 danh mục nhiều sản phẩm nhất).
 *
 * Markup + class y hệt `cp_news_cats_box()` (CP5.1) để dùng lại CSS `.cp-side-box` / `.cp-side-cats`
 * của CP3.2 — KHÔNG khai CSS trùng. Guard `taxonomy_exists()` để site chưa có WooCommerce vẫn chạy.
 */
function cp_product_cats_box(): void {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}

	$cp_items = wp_list_categories(
		array(
			'taxonomy'   => 'product_cat',
			'title_li'   => '',
			'show_count' => true,
			'hide_empty' => true,
			'number'     => 10,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'echo'       => false,
		)
	);
	if ( ! is_string( $cp_items ) || false === strpos( $cp_items, '<li' ) ) {
		return;
	}

	echo '<div class="cp-side-box"><h4>' . esc_html__( 'Danh mục sản phẩm', 'tungleads-theme' ) . '</h4>';
	echo '<ul class="cp-side-cats">' . $cp_items . '</ul></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup do `wp_list_categories()` sinh.
}

/* ======================================================================
   CP7.2 — một kết quả tìm kiếm
   ====================================================================== */

/**
 * CP7.2 — In 1 kết quả tìm kiếm: SẢN PHẨM có thẻ riêng (kèm giá), các loại khác dùng thẻ tin CP5.1.
 */
function cp_search_card(): void {
	if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
		$cp_product = wc_get_product( get_the_ID() );
		if ( $cp_product instanceof WC_Product ) {
			cp_search_product_card( $cp_product );
			return;
		}
	}

	// Bài viết / trang / CPT khác → thẻ `.cp-news-card` của CP5.1 (ảnh + nhãn + trích đoạn).
	cp_news_card();
}

/**
 * CP7.2 — Thẻ kết quả là SẢN PHẨM.
 *
 * Tái dùng khung `.cp-news-card` của CP5.1 (ảnh 300px + chữ) nhưng thay NGÀY ĐĂNG bằng **GIÁ**: ngày
 * đăng sản phẩm là thông tin vô nghĩa với khách, còn giá là thứ khách tìm. Kèm link danh mục SP ở vị
 * trí nhãn chuyên mục (phải là thẻ `<a>` vì CSS dùng `.cp a.cp-news-card__cat`).
 *
 * @param WC_Product $product Sản phẩm của vòng lặp (đã kiểm `instanceof` ở `cp_search_card()`).
 */
function cp_search_product_card( WC_Product $product ): void {
	$cp_terms = get_the_terms( get_the_ID(), 'product_cat' );
	$cp_cat   = is_array( $cp_terms ) && ! empty( $cp_terms ) ? $cp_terms[0] : null;
	$cp_link  = $cp_cat instanceof WP_Term ? get_term_link( $cp_cat ) : '';
	$cp_link  = is_string( $cp_link ) ? $cp_link : '';

	$cp_excerpt = trim( wp_strip_all_tags( (string) $product->get_short_description() ) );
	if ( '' === $cp_excerpt ) {
		$cp_excerpt = trim( wp_strip_all_tags( (string) get_the_excerpt() ) );
	}
	$cp_excerpt = wp_trim_words( $cp_excerpt, 32, '…' );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cp-news-card cp-search-card--product' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<a class="cp-news-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php
				the_post_thumbnail(
					'medium',
					array(
						'alt'      => '',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 900px) 100vw, (max-width: 1200px) 260px, 300px',
					)
				);
				?>
			</a>
		<?php endif; ?>

		<div class="cp-news-card__body">
			<div class="cp-news-card__meta">
				<?php if ( $cp_cat instanceof WP_Term && '' !== $cp_link ) : ?>
					<a class="cp-news-card__cat" href="<?php echo esc_url( $cp_link ); ?>"><?php echo esc_html( $cp_cat->name ); ?></a>
				<?php endif; ?>
				<span class="cp-search-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>

			<h2 class="cp-news-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<?php if ( '' !== $cp_excerpt ) : ?>
				<p class="cp-news-card__excerpt"><?php echo esc_html( $cp_excerpt ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/* ======================================================================
   CP7.3 — vệ sinh shortcode Flatsome còn sót trong nội dung cũ
   ====================================================================== */

/**
 * CP7.3 — Danh sách tag shortcode của Flatsome / UX Builder (DANH SÁCH ỨNG VIÊN, không phải "bóc hết").
 *
 * Luật bóc là **chỉ tag KHÔNG còn ai đăng ký** (`! shortcode_exists()`) nên tag trùng tên với plugin
 * khác đang bật (`products` của WooCommerce, `contact-form-7`, `search` của plugin tìm kiếm…) tự
 * động được giữ nguyên. Thêm/bớt bằng filter `cp_flatsome_shortcodes`.
 *
 * @return string[]
 */
function cp_flatsome_shortcodes(): array {
	return (array) apply_filters(
		'cp_flatsome_shortcodes',
		array(
			'ux_banner', 'ux_text', 'ux_image', 'ux_image_box', 'ux_slider', 'ux_slide', 'ux_products',
			'ux_product_categories', 'ux_bestseller_products', 'ux_featured_products', 'ux_sale_products',
			'ux_pages', 'ux_team_member', 'ux_price_table', 'ux_countdown', 'ux_video', 'ux_gallery',
			'ux_hotspot', 'ux_stack', 'ux_menu', 'ux_title', 'ux_link', 'ux_go_to', 'ux_instagram_feed',
			'ux_sidebar', 'ux_portfolio', 'ux_facebook', 'ux_pinterest',
			'section', 'row', 'row_inner', 'col', 'col_inner', 'gap', 'button', 'title', 'featured_box',
			'text_box', 'lightbox', 'accordion', 'tabgroup', 'block', 'divider', 'message_box',
			'testimonial', 'testimonial_group', 'price_table', 'blog_posts', 'blog_posts_grid',
			'blog_posts_masonry', 'blog_posts_slider', 'contact', 'scroll_to', 'search', 'map', 'video',
			'follow', 'share', 'zoom', 'page_header', 'page_header_nav', 'page_header_title',
		)
	);
}

/**
 * CP7.3 — Callback bóc 1 tag: giữ phần CHỮ bên trong thẻ bao, bỏ hẳn thẻ tự đóng.
 *
 * @param array<int,string> $m Kết quả khớp của `get_shortcode_regex()`.
 * @return string
 */
function cp_strip_shortcode_keep_inner( array $m ): string {
	// `[[tag]]` = cách WordPress escape shortcode ⇒ trả về dạng chữ, chỉ bỏ 1 cặp ngoặc.
	if ( '[' === ( $m[1] ?? '' ) && ']' === ( $m[6] ?? '' ) ) {
		return substr( (string) $m[0], 1, -1 );
	}

	return isset( $m[5] ) && is_string( $m[5] ) ? $m[5] : '';
}

/**
 * CP7.3 — Filter `the_content`: bóc shortcode Flatsome/UX Builder KHÔNG còn ai đăng ký.
 *
 * Vì sao cần: nội dung cũ của site dựng bằng UX Builder; sau khi bỏ Flatsome thì `[section]`,
 * `[row]`, `[col]`, `[featured_box]`, `[contact]`… in ra **chữ thô** giữa trang (đo 2026-09-16 ở
 * `/lien-he/` và `/zalo-cao-phat/`). Bóc thẻ nhưng GIỮ phần chữ ⇒ trang vẫn đọc được thay vì một
 * đống ngoặc vuông.
 *
 * Ba lớp bảo vệ:
 *  1. `is_admin()` — trình soạn thảo LUÔN thấy nội dung GỐC. Thiếu lớp này thì mở trang ra sửa rồi
 *     bấm Cập nhật là phần nội dung đã lọc bị ghi đè ⇒ mất, không lấy lại được.
 *  2. Flatsome đang chạy (`shortcode_exists('ux_banner')` / `'ux_text'`) ⇒ không đụng gì.
 *  3. Từng tag phải "mồ côi" (`! shortcode_exists($tag)`) mới bóc ⇒ không phá shortcode của plugin
 *     đang bật.
 * Chạy priority **9** (trước `wpautop` 10 và `do_shortcode` 11) để đoạn văn còn được xuống dòng đúng.
 *
 * @param string $content Nội dung bài viết/trang.
 * @return string
 */
function cp_strip_orphan_flatsome_shortcodes( string $content ): string {
	if ( is_admin() || '' === $content || false === strpos( $content, '[' ) ) {
		return $content;
	}
	if ( ! apply_filters( 'cp_strip_flatsome_shortcodes', true ) ) {
		return $content;
	}
	if ( shortcode_exists( 'ux_banner' ) || shortcode_exists( 'ux_text' ) ) {
		return $content;
	}

	$tags = array_values(
		array_filter(
			cp_flatsome_shortcodes(),
			static fn( string $tag ): bool => ! shortcode_exists( $tag )
		)
	);
	if ( ! $tags ) {
		return $content;
	}

	$pattern = get_shortcode_regex( $tags );

	// Thẻ LỒNG nhau (`[section]…[row]…[/row]…[/section]`) mỗi lượt chỉ bóc được lớp ngoài ⇒ lặp tới
	// khi nội dung không đổi. Nội dung thật sâu 3–4 cấp nên 5 lượt là thừa an toàn.
	for ( $i = 0; $i < 5; $i++ ) {
		$new = preg_replace_callback( "/$pattern/s", 'cp_strip_shortcode_keep_inner', $content );
		if ( ! is_string( $new ) || $new === $content ) {
			break;
		}
		$content = $new;
	}

	return $content;
}
add_filter( 'the_content', 'cp_strip_orphan_flatsome_shortcodes', 9 );


