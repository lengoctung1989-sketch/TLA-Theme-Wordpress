<?php
/**
 * Header Cao Phát Door: topbar + header sticky (logo + menu + hotline).
 *
 * CP1.2 header
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_tel   = cp_hotline_tel();
$cp_disp  = cp_hotline_display();
$cp_items = cp_header_items();

/* CP1.8 — class `cp-hide-<key>` cho mục bị TẮT trong Customizer (rule tĩnh ở `caophat.css`;
   áp cho MỌI khổ màn hình — Tùng chốt 2026-09-16). */
$cp_hide = '';
foreach ( array_keys( cp_header_items_all() ) as $cp_key ) {
	if ( ! in_array( $cp_key, $cp_items, true ) ) {
		$cp_hide .= ' cp-hide-' . $cp_key;
	}
}
$cp_html = cp_header_html();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Bỏ qua tới nội dung', 'tungleads-theme' ); ?></a>

<?php // CP1.8 — Topbar: bật/tắt + màu + 3 dòng chữ đầu + nhãn bên phải đều sửa trong Customizer. ?>
<?php if ( cp_header_flag( 'topbar_show' ) ) : ?>
<div class="cp-topbar">
	<div class="cp-container">
		<ul>
			<?php foreach ( cp_topbar_lines() as $cp_line ) : ?>
				<li><?php echo esc_html( $cp_line ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="cp-topbar-right">
			<span><?php echo esc_html( cp_header_get( 'topbar_label' ) ); ?></span>
			<a href="tel:<?php echo esc_attr( $cp_tel ); ?>"><strong><?php echo esc_html( $cp_disp ); ?></strong></a>
		</div>
	</div>
</div>
<?php endif; ?>

<header class="cp-header<?php echo esc_attr( $cp_hide ); ?>">
	<div class="cp-container">
		<?php /* CP1.2b — Nút burger đặt là CON ĐẦU TIÊN của `.cp-container` để ở mobile nó nằm
		           bên TRÁI (desktop `display: none` nên không ảnh hưởng). */ ?>
		<button class="cp-burger" type="button" aria-label="<?php esc_attr_e( 'Mở menu', 'tungleads-theme' ); ?>" aria-expanded="false" aria-controls="cp-nav"><span></span><span></span><span></span></button>

		<?php if ( has_custom_logo() ) : ?>
			<?php /* CP1.2 — KHÔNG bọc logo trong <a> nữa: `the_custom_logo()` đã in <a class="custom-logo-link">;
			           lồng <a> trong <a> là HTML không hợp lệ → trình duyệt tự tách thẻ và `.cp-logo` bị rỗng 0×0. */ ?>
			<div class="cp-logo cp-hitem cp-hitem--logo"><?php the_custom_logo(); ?></div>
		<?php else : ?>
			<a class="cp-logo cp-hitem cp-hitem--logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<span class="cp-logo-mark">CP</span>
				<span class="cp-logo-text">
					<b><?php bloginfo( 'name' ); ?></b>
					<span><?php esc_html_e( 'Thế giới cửa gỗ', 'tungleads-theme' ); ?></span>
				</span>
			</a>
		<?php endif; ?>

		<nav id="cp-nav" class="cp-nav cp-hitem cp-hitem--menu" aria-label="<?php esc_attr_e( 'Menu chính', 'tungleads-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => '',
					'items_wrap'     => '<ul>%3$s</ul>',
					'fallback_cb'    => false,
					// CP1.4b — 3 cấp: cấp 1 = mục nav, cấp 2 = tiêu đề cột của mega menu, cấp 3 = các mục trong cột.
					'depth'          => 3,
				)
			);
			?>
		</nav>

		<?php /* CP1.6 — Ô tìm kiếm sản phẩm nằm TRONG `.cp-header-cta` (đứng ĐẦU khối → desktop vẫn ở
		           bên trái hotline). Ở ≤1024px khối cta chiếm hết hàng và tự `flex-wrap` để ô search
		           xuống hàng riêng full chiều ngang — xem CSS khối CP1.6. */ ?>
		<div class="cp-header-cta">
			<form id="cp-search" class="cp-search cp-hitem cp-hitem--search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="cp-search-input"><?php esc_html_e( 'Tìm sản phẩm', 'tungleads-theme' ); ?></label>
				<input id="cp-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Tìm cửa theo tên, mã…', 'tungleads-theme' ); ?>" autocomplete="off" autocapitalize="off" spellcheck="false">
				<?php /* Chỉ tìm trong sản phẩm — site bán cửa, khách gõ vào đây là muốn tìm sản phẩm */ ?>
				<input type="hidden" name="post_type" value="product">
				<button type="submit" aria-label="<?php esc_attr_e( 'Tìm kiếm', 'tungleads-theme' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
				</button>
			</form>

			<a class="cp-hotline cp-hitem cp-hitem--hotline" href="tel:<?php echo esc_attr( $cp_tel ); ?>">
				<span class="ic" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				</span>
				<span>
					<small><?php esc_html_e( 'Tư vấn miễn phí', 'tungleads-theme' ); ?></small>
					<b><?php echo esc_html( $cp_disp ); ?></b>
				</span>
			</a>
			<?php /* CP1.2b — Icon tìm kiếm (chỉ hiện ở mobile): bấm để mở ô search thành 1 hàng riêng. */ ?>
			<button class="cp-search-toggle" type="button" aria-label="<?php esc_attr_e( 'Tìm sản phẩm', 'tungleads-theme' ); ?>" aria-expanded="false" aria-controls="cp-search">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
			</button>
			<?php cp_header_cart_link(); /* CP1.7 — icon giỏ hàng, nằm bên phải khối hotline (@see cp_header_cart_link() — có class cp-hitem--cart của CP1.8) */ ?>
		</div>

		<?php
		/* CP1.8 — Khối HTML tự do, cùng HÀNG với logo/menu/search/tư vấn/giỏ.
		   Mặc định chỉ hiện ở desktop (>1024px, class `cp-hitem--html-desk`) để KHÔNG phá bố cục
		   mobile đã đo (CP1.2b); tắt tuỳ chọn "chỉ desktop" nếu muốn hiện mọi khổ.
		   Vị trí trong hàng do Customizer quyết định (`order`); trong DOM nó đứng sau cùng. */
		?>
		<?php if ( '' !== $cp_html ) : ?>
			<div class="cp-hitem cp-hitem--html<?php echo cp_header_flag( 'html_desktop_only' ) ? ' cp-hitem--html-desk' : ''; ?>">
				<?php echo $cp_html; // phpcs:ignore WordPress.Security.EscapeOutput -- đã qua wp_kses_post() trong cp_header_html(). ?>
			</div>
		<?php endif; ?>
	</div>
</header>
