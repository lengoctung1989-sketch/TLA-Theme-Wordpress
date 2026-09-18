<?php
/**
 * Khu vực bình luận cho single.php / page.php.
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="tl-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="tl-comments__title">
			<?php
			printf(
				/* translators: %s: số lượng bình luận. */
				esc_html( _n( '%s bình luận', '%s bình luận', get_comments_number(), 'tungleads-theme' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>

		<ol class="tl-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'screen_reader_text' => __( 'Điều hướng bình luận', 'tungleads-theme' ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="tl-comments__closed"><?php esc_html_e( 'Đã đóng bình luận.', 'tungleads-theme' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
