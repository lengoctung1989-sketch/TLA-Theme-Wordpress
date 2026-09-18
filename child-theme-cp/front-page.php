<?php
/**
 * Trang chủ Cao Phát Door.
 *
 * CP2.1 front-page
 *
 * Khối tĩnh (hero / feature / CTA) đang để chuỗi trong PHP cho nhanh + đúng bản mockup.
 * ponytail: chuyển sang block pattern khi client cần tự sửa nội dung.
 * Khối động (danh mục / sản phẩm / blog) lấy từ dữ liệu WordPress/WooCommerce.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cp_tel  = cp_hotline_tel();
$cp_disp = cp_hotline_display(); // dùng ở nút CTA "Gọi ngay %s" (dòng ~127) — KHÔNG xoá.
$cp_img  = get_stylesheet_directory_uri() . '/assets/images';
$cp_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

// Chữ + ảnh sửa được trong Appearance → Customize → "Trang chủ Cao Phát"; rỗng thì dùng mặc định.
$cp_hero_badge = get_theme_mod( 'cp_hero_badge' ) ?: __( 'Thương hiệu cửa uy tín tại TP.HCM', 'tungleads-theme' );
$cp_hero_title = get_theme_mod( 'cp_hero_title' ) ?: __( 'Thế giới <em>Cửa gỗ công nghiệp</em>, cửa nhựa giả gỗ &amp; cửa chống cháy', 'tungleads-theme' );
$cp_hero_desc  = get_theme_mod( 'cp_hero_desc' ) ?: __( 'Cao Phát Door mang đến sản phẩm cửa chất lượng cao, đa dạng mẫu mã và kiểu dáng — tiện nghi, an toàn và thẩm mỹ cho mọi không gian sống.', 'tungleads-theme' );
/* Ảnh hero: bản mặc định dùng WebP (**986KB PNG → 37KB**) + bản 652px cho màn hình nhỏ.
   Ảnh tuỳ chỉnh trong Customizer dùng nguyên file người dùng tải lên (không có srcset). */
$cp_hero_img    = get_theme_mod( 'cp_hero_image' );
$cp_hero_src    = $cp_hero_img ? $cp_hero_img : $cp_img . '/hero.webp';
$cp_hero_srcset = $cp_hero_img ? '' : $cp_img . '/hero-652.webp 652w, ' . $cp_img . '/hero.webp 1024w';
$cp_cta_title  = get_theme_mod( 'cp_cta_title' ) ?: __( 'Cần tư vấn chọn cửa phù hợp?', 'tungleads-theme' );
$cp_cta_desc   = get_theme_mod( 'cp_cta_desc' ) ?: __( 'Đội ngũ Cao Phát Door hỗ trợ đo đạc, báo giá và thi công tận nơi tại TP.HCM.', 'tungleads-theme' );
/* 2026-09-15 (yêu cầu Tùng) — 2 nút hero + 2 ô số liệu: sửa trong Customize → "Trang chủ Cao Phát".
   Để trống → dùng lại mặc định dưới đây. */
$cp_hero_btn1  = get_theme_mod( 'cp_hero_btn1' ) ?: __( 'Xem sản phẩm', 'tungleads-theme' );
$cp_hero_btn2  = get_theme_mod( 'cp_hero_btn2' ) ?: __( 'Nhận báo giá 24/7', 'tungleads-theme' );
/* Link của 2 nút — để trống thì nút 1 về trang cửa hàng, nút 2 gọi hotline (tel:). */
$cp_hero_btn1_url = get_theme_mod( 'cp_hero_btn1_url' ) ?: $cp_shop;
$cp_hero_btn2_url = get_theme_mod( 'cp_hero_btn2_url' ) ?: 'tel:' . $cp_tel;
$cp_stat1_val  = get_theme_mod( 'cp_hero_stat1_value' ) ?: __( '10+ năm', 'tungleads-theme' );
$cp_stat1_lbl  = get_theme_mod( 'cp_hero_stat1_label' ) ?: __( 'Kinh nghiệm thi công', 'tungleads-theme' );
$cp_stat2_val  = get_theme_mod( 'cp_hero_stat2_value' ) ?: __( '5.000+', 'tungleads-theme' );
$cp_stat2_lbl  = get_theme_mod( 'cp_hero_stat2_label' ) ?: __( 'Công trình hoàn thiện', 'tungleads-theme' );
/* CP2.6 — 2 dòng chữ khối "Danh mục nổi bật": Appearance → Customize → "Trang chủ — Danh mục
   nổi bật" (setting `cp_cats_label` / `cp_cats_title`); để trống → dùng lại mặc định dưới đây.
   Khối này đã BỎ dòng mô tả phụ + link "Xem tất cả →" theo yêu cầu Tùng 2026-09-15. */
$cp_cats_label = get_theme_mod( 'cp_cats_label' ) ?: __( 'Danh mục nổi bật', 'tungleads-theme' );
$cp_cats_title = get_theme_mod( 'cp_cats_title' ) ?: __( 'Khám phá theo dòng sản phẩm', 'tungleads-theme' );
?>
<main id="main">

	<!-- HERO -->
	<section class="cp-hero">
		<div class="cp-container">
			<div class="cp-hero-copy">
				<span class="cp-hero-badge">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 15 8l6 .9-4.5 4.3 1 6.1L12 16.5 6.5 19.3l1-6.1L3 8.9 9 8z"/></svg>
					<?php echo esc_html( $cp_hero_badge ); ?>
				</span>
				<h1><?php echo wp_kses_post( $cp_hero_title ); ?></h1>
				<p><?php echo esc_html( $cp_hero_desc ); ?></p>
				<div class="cp-hero-actions">
					<a class="cp-btn cp-btn-primary" href="<?php echo esc_url( $cp_hero_btn1_url ); ?>"><?php echo esc_html( $cp_hero_btn1 ); ?></a>
					<a class="cp-btn cp-btn-ghost" href="<?php echo esc_url( $cp_hero_btn2_url ); ?>"><?php echo esc_html( $cp_hero_btn2 ); ?></a>
				</div>
			</div>
			<div class="cp-hero-media">
				<img src="<?php echo esc_url( $cp_hero_src ); ?>"
					<?php if ( $cp_hero_srcset ) : ?>srcset="<?php echo esc_attr( $cp_hero_srcset ); ?>" sizes="(max-width: 900px) 100vw, 652px"<?php endif; ?>
					alt="<?php esc_attr_e( 'Showroom cửa gỗ công nghiệp Cao Phát Door', 'tungleads-theme' ); ?>" fetchpriority="high" decoding="async">
				<div class="cp-hero-float">
					<div><b><?php echo esc_html( $cp_stat1_val ); ?></b><small><?php echo esc_html( $cp_stat1_lbl ); ?></small></div>
					<div><b><?php echo esc_html( $cp_stat2_val ); ?></b><small><?php echo esc_html( $cp_stat2_lbl ); ?></small></div>
				</div>
			</div>
		</div>
	</section>

	<!-- FEATURES -->
	<div class="cp-container">
		<div class="cp-features">
			<?php
			/* CP2.10 — 4 cụm nổi bật: TIÊU ĐỀ + MÔ TẢ + ICON đều quản trị ở Customizer →
			   “4 cụm nổi bật Cao Phát”; mặc định nằm ở `cp_features_defaults()` (`functions.php`).
			   Icon mặc định = SVG inline; upload ảnh ở Customizer thì ảnh THAY thế (xem `cp_feature_icon()`). */
			for ( $cp_f = 1; $cp_f <= 4; $cp_f++ ) :
				?>
				<div class="cp-feature">
					<span class="ic" aria-hidden="true"><?php echo cp_feature_icon( $cp_f ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG hằng hoặc ảnh do `wp_get_attachment_image()` sinh. ?></span>
					<div><b><?php echo esc_html( cp_feature_get( $cp_f, 'title' ) ); ?></b><span><?php echo esc_html( cp_feature_get( $cp_f, 'text' ) ); ?></span></div>
				</div>
			<?php endfor; ?>
		</div>
	</div>

	<!-- DANH MỤC -->
	<section class="cp-section">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php echo esc_html( $cp_cats_label ); ?></span>
					<h2><?php echo esc_html( $cp_cats_title ); ?></h2>
				</div>
			</div>
			<?php get_template_part( 'template-parts/home-categories' ); ?>
		</div>
	</section>

	<!-- CP2.9 — KHỐI TAB SẢN PHẨM (Customize → "Trang chủ — Khối tab sản phẩm") -->
	<?php get_template_part( 'template-parts/home-tabs' ); ?>

	<!-- CP2.4 — KHỐI SẢN PHẨM THEO DANH MỤC (Customize → "Trang chủ — Khối sản phẩm theo danh mục") -->
	<?php get_template_part( 'template-parts/home-cat-products' ); ?>

	<!-- 2026-09-15 (yêu cầu Tùng): đã XOÁ 2 khối "BÁN CHẠY" + "KHUYẾN MÃI" ở đây —
	     nội dung 2 nhóm này nay nằm trong khối tab sản phẩm CP2.9 phía trên.
	     `template-parts/home-products.php` (chỉ 2 khối này dùng) cũng đã xoá theo. -->

	<!-- CTA -->
	<section class="cp-section">
		<div class="cp-container">
			<div class="cp-cta">
				<div>
					<h3><?php echo esc_html( $cp_cta_title ); ?></h3>
					<p><?php echo esc_html( $cp_cta_desc ); ?></p>
				</div>
				<a class="cp-btn cp-btn-accent" href="tel:<?php echo esc_attr( $cp_tel ); ?>"><?php echo esc_html( sprintf( /* translators: %s: số hotline */ __( 'Gọi ngay %s', 'tungleads-theme' ), $cp_disp ) ); ?></a>
			</div>
		</div>
	</section>

	<!-- CP2.5 — KHỐI TIN TỨC THEO CHUYÊN MỤC (Customize → "Trang chủ — Khối tin tức theo chuyên mục") -->
	<?php get_template_part( 'template-parts/home-cat-news' ); ?>

	<!-- 2026-09-15 (yêu cầu Tùng): đã XOÁ khối "BLOG" ("Kiến thức & báo giá" / "Bài viết mới nhất")
	     — tin tức trên trang chủ nay do khối CP2.5 ("Khối tin tức theo chuyên mục") đảm nhiệm.
	     `template-parts/home-blog.php` (chỉ khối này dùng) cũng đã xoá theo. -->

</main>
<?php
get_footer();
