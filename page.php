<?php
/**
 * Trang tĩnh. Nội dung do editor dựng bằng block/pattern.
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'tl-page' ); ?>>
			<div class="tl-page__content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
