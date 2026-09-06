<?php
/**
 * Footer Cao Phát Door: footer tối 4 cột + nút hotline nổi (FAB).
 *
 * CP1.3 footer
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_tel  = cp_hotline_tel();
$cp_disp = cp_hotline_display();
?>
<footer class="cp-footer">
	<div class="cp-container">
		<div class="cp-footer-grid">
			<div>
				<a class="cp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<span class="cp-logo-mark">CP</span>
						<span class="cp-logo-text"><b><?php bloginfo( 'name' ); ?></b><span><?php esc_html_e( 'Thế giới cửa gỗ', 'tungleads-theme' ); ?></span></span>
					<?php endif; ?>
				</a>
				<p style="margin-top:16px;"><?php esc_html_e( 'Thương hiệu cửa gỗ công nghiệp, cửa nhựa giả gỗ và cửa chống cháy tại TP. Hồ Chí Minh. Chất lượng cao, đa dạng mẫu mã, bảo hành 24 tháng.', 'tungleads-theme' ); ?></p>
			</div>

			<div>
				<h4><?php esc_html_e( 'Sản phẩm', 'tungleads-theme' ); ?></h4>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => '',
							'items_wrap'     => '<ul>%3$s</ul>',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
				}
				?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Hỗ trợ', 'tungleads-theme' ); ?></h4>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Chính sách bảo hành', 'tungleads-theme' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Hướng dẫn mua hàng', 'tungleads-theme' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Chính sách vận chuyển', 'tungleads-theme' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Liên hệ', 'tungleads-theme' ); ?></h4>
				<ul class="cp-foot-contact">
					<li>
						<span class="ic" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
						<a href="tel:<?php echo esc_attr( $cp_tel ); ?>"><?php echo esc_html( $cp_disp ); ?></a>
					</li>
					<li>
						<span class="ic" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span>
						<?php echo esc_html( get_option( 'admin_email' ) ); ?>
					</li>
					<li>
						<span class="ic" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
						<?php esc_html_e( 'TP. Hồ Chí Minh', 'tungleads-theme' ); ?>
					</li>
				</ul>
			</div>
		</div>

		<div class="cp-footer-bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span><?php esc_html_e( 'Thiết kế bởi tungleads.com', 'tungleads-theme' ); ?></span>
		</div>
	</div>
</footer>

<div class="cp-fab">
	<a class="call" href="tel:<?php echo esc_attr( $cp_tel ); ?>" aria-label="<?php esc_attr_e( 'Gọi hotline', 'tungleads-theme' ); ?>">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
	</a>
</div>

<?php wp_footer(); ?>
</body>
</html>
