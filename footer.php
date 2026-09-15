<?php
/**
 * Footer Cao Phát Door: footer 3–4 CỘT (mỗi cột = tiêu đề + ô soạn thảo văn bản) + nút hotline nổi (FAB).
 *
 * CP1.3 footer — nền (màu + ảnh + độ đậm lớp màu) và nội dung từng cột đọc từ Customizer
 * "Footer Cao Phát": `cp_footer_columns()` / `cp_footer_get()` / `cp_footer_flag()` /
 * `cp_footer_col_get()` (`functions.php`, 1 nguồn sự thật cho default); dòng bản quyền + ghi công qua
 * `cp_footer_text()`; hotline qua `cp_hotline_display()` / `cp_hotline_tel()`. Ô để trống trong
 * Customizer → dùng lại mặc định nên file này không giữ chuỗi nào.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_tel  = cp_hotline_tel();
$cp_disp = cp_hotline_display();
$cp_cols = cp_footer_columns();
$cp_logo = cp_footer_flag( 'logo' );
?>
<footer class="cp-footer">
	<div class="cp-container">
		<div class="cp-footer-grid cp-footer-grid--cols-<?php echo esc_attr( (string) count( $cp_cols ) ); ?>">
			<?php
			/* CP1.3 — mỗi cột = TIÊU ĐỀ + 1 ô soạn thảo văn bản (Customizer "Footer Cao Phát").
			   Cột chọn nguồn "Menu" thì in menu "Footer" (Appearance → Menus) thay cho ô soạn thảo.
			   Logo (Site Identity) chỉ in ở CỘT 1 và chỉ khi bật "Hiện logo ở Cột 1". */
			foreach ( $cp_cols as $cp_i => $cp_col ) :
				?>
				<div class="cp-foot-col">
					<?php if ( 0 === $cp_i && $cp_logo ) : ?>
						<a class="cp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php if ( has_custom_logo() ) : ?>
								<?php the_custom_logo(); ?>
							<?php else : ?>
								<span class="cp-logo-mark">CP</span>
								<span class="cp-logo-text"><b><?php bloginfo( 'name' ); ?></b><span><?php esc_html_e( 'Thế giới cửa gỗ', 'tungleads-theme' ); ?></span></span>
							<?php endif; ?>
						</a>
					<?php endif; ?>

					<?php if ( '' !== $cp_col['title'] ) : ?>
						<h4><?php echo esc_html( $cp_col['title'] ); ?></h4>
					<?php endif; ?>

					<?php if ( 'menu' === $cp_col['source'] ) : ?>
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
					<?php elseif ( '' !== $cp_col['content'] ) : ?>
						<div class="cp-col__body"><?php echo cp_footer_kses_content( $cp_col['content'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- đã lọc bằng KSES ở `cp_footer_kses_content()`. ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="cp-footer-bottom">
			<?php $cp_copy = cp_footer_text( 'copyright' ); ?>
			<?php if ( '' !== $cp_copy ) : ?>
				<span><?php echo esc_html( $cp_copy ); ?></span>
			<?php endif; ?>
			<span class="cp-footer-credit">
				<?php
				/*
				 * CP1.3 — Dòng ghi công CỐ ĐỊNH trong code (yêu cầu Tùng 2026-09-16): luôn in kèm link về
				 * tungleads.com, KHÔNG còn ô nhập trong Customizer (đã bỏ setting/control `cp_footer_credit`).
				 */
				printf(
					/* translators: %s: thẻ liên kết tới trang tungleads.com */
					esc_html__( 'Thiết kế bởi: %s', 'tungleads-theme' ), // phpcs:ignore WordPress.Security.EscapeOutput -- markup <a> cố định bên dưới.
					'<a href="https://tungleads.com/" target="_blank" rel="noopener">tungleads.com</a>'
				);
				?>
			</span>
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
