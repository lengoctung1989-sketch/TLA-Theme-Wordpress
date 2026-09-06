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
$cp_disp = cp_hotline_display();
$cp_img  = get_stylesheet_directory_uri() . '/assets/images';
$cp_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

// Chữ + ảnh sửa được trong Appearance → Customize → "Trang chủ Cao Phát"; rỗng thì dùng mặc định.
$cp_hero_badge = get_theme_mod( 'cp_hero_badge' ) ?: __( 'Thương hiệu cửa uy tín tại TP.HCM', 'tungleads-theme' );
$cp_hero_title = get_theme_mod( 'cp_hero_title' ) ?: __( 'Thế giới <em>Cửa gỗ công nghiệp</em>, cửa nhựa giả gỗ &amp; cửa chống cháy', 'tungleads-theme' );
$cp_hero_desc  = get_theme_mod( 'cp_hero_desc' ) ?: __( 'Cao Phát Door mang đến sản phẩm cửa chất lượng cao, đa dạng mẫu mã và kiểu dáng — tiện nghi, an toàn và thẩm mỹ cho mọi không gian sống.', 'tungleads-theme' );
$cp_hero_img   = get_theme_mod( 'cp_hero_image' ) ?: $cp_img . '/hero.png';
$cp_cta_title  = get_theme_mod( 'cp_cta_title' ) ?: __( 'Cần tư vấn chọn cửa phù hợp?', 'tungleads-theme' );
$cp_cta_desc   = get_theme_mod( 'cp_cta_desc' ) ?: __( 'Đội ngũ Cao Phát Door hỗ trợ đo đạc, báo giá và thi công tận nơi tại TP.HCM.', 'tungleads-theme' );
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
					<a class="cp-btn cp-btn-primary" href="<?php echo esc_url( $cp_shop ); ?>"><?php esc_html_e( 'Xem sản phẩm', 'tungleads-theme' ); ?></a>
					<a class="cp-btn cp-btn-ghost" href="tel:<?php echo esc_attr( $cp_tel ); ?>"><?php esc_html_e( 'Nhận báo giá 24/7', 'tungleads-theme' ); ?></a>
				</div>
			</div>
			<div class="cp-hero-media">
				<img src="<?php echo esc_url( $cp_hero_img ); ?>" alt="<?php esc_attr_e( 'Showroom cửa gỗ công nghiệp Cao Phát Door', 'tungleads-theme' ); ?>">
				<div class="cp-hero-float">
					<div><b>10+ <?php esc_html_e( 'năm', 'tungleads-theme' ); ?></b><small><?php esc_html_e( 'Kinh nghiệm thi công', 'tungleads-theme' ); ?></small></div>
					<div><b>5.000+</b><small><?php esc_html_e( 'Công trình hoàn thiện', 'tungleads-theme' ); ?></small></div>
				</div>
			</div>
		</div>
	</section>

	<!-- FEATURES -->
	<div class="cp-container">
		<div class="cp-features">
			<?php
			$cp_features = array(
				array( 'M1 3h15v13H1zM16 8h4l3 3v5h-7z', __( 'Miễn phí vận chuyển', 'tungleads-theme' ), __( 'Giao lắp nội thành TP.HCM', 'tungleads-theme' ) ),
				array( 'M2 5h20v14H2zM2 10h20', __( 'Thanh toán linh hoạt', 'tungleads-theme' ), __( 'Nhiều hình thức tiện lợi', 'tungleads-theme' ) ),
				array( 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', __( 'Bảo hành 24 tháng', 'tungleads-theme' ), __( 'Cam kết chính hãng', 'tungleads-theme' ) ),
				array( 'M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72', __( 'Báo giá 24/7', 'tungleads-theme' ), $cp_disp ),
			);
			foreach ( $cp_features as $cp_f ) :
				?>
				<div class="cp-feature">
					<span class="ic" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo esc_attr( $cp_f[0] ); ?>"/></svg></span>
					<div><b><?php echo esc_html( $cp_f[1] ); ?></b><span><?php echo esc_html( $cp_f[2] ); ?></span></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- DANH MỤC -->
	<section class="cp-section">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php esc_html_e( 'Danh mục nổi bật', 'tungleads-theme' ); ?></span>
					<h2><?php esc_html_e( 'Khám phá theo dòng sản phẩm', 'tungleads-theme' ); ?></h2>
					<p><?php esc_html_e( 'Đầy đủ các dòng cửa cho phòng ngủ, nhà vệ sinh, chung cư và công trình.', 'tungleads-theme' ); ?></p>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( $cp_shop ); ?>"><?php esc_html_e( 'Xem tất cả →', 'tungleads-theme' ); ?></a>
			</div>
			<?php get_template_part( 'template-parts/home-categories' ); ?>
		</div>
	</section>

	<!-- BÁN CHẠY -->
	<section class="cp-section alt">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php esc_html_e( 'Bán chạy nhất', 'tungleads-theme' ); ?></span>
					<h2><?php esc_html_e( 'Sản phẩm được ưa chuộng', 'tungleads-theme' ); ?></h2>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( $cp_shop ); ?>"><?php esc_html_e( 'Xem thêm →', 'tungleads-theme' ); ?></a>
			</div>
			<?php get_template_part( 'template-parts/home-products', null, array( 'type' => 'best' ) ); ?>
		</div>
	</section>

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

	<!-- KHUYẾN MÃI -->
	<section class="cp-section alt">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php esc_html_e( 'Khuyến mãi', 'tungleads-theme' ); ?></span>
					<h2><?php esc_html_e( 'Sản phẩm đang giảm giá', 'tungleads-theme' ); ?></h2>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( $cp_shop ); ?>"><?php esc_html_e( 'Xem thêm →', 'tungleads-theme' ); ?></a>
			</div>
			<?php get_template_part( 'template-parts/home-products', null, array( 'type' => 'sale' ) ); ?>
		</div>
	</section>

	<!-- BLOG -->
	<section class="cp-section">
		<div class="cp-container">
			<div class="cp-section-head">
				<div>
					<span class="cp-eyebrow"><?php esc_html_e( 'Kiến thức & báo giá', 'tungleads-theme' ); ?></span>
					<h2><?php esc_html_e( 'Bài viết mới nhất', 'tungleads-theme' ); ?></h2>
				</div>
				<a class="cp-link-more" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Xem tất cả →', 'tungleads-theme' ); ?></a>
			</div>
			<?php get_template_part( 'template-parts/home-blog' ); ?>
		</div>
	</section>

</main>
<?php
get_footer();
