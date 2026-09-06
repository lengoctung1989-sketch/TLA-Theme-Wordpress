<?php
/**
 * Template fallback.
 *
 * @package TL\Theme
 *
 * P3.1 base-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;

		the_posts_pagination();
	else :
		?>
		<p><?php esc_html_e( 'Không tìm thấy nội dung.', 'tungleads-theme' ); ?></p>
		<?php
	endif;
	?>
</main>
<?php
get_footer();
