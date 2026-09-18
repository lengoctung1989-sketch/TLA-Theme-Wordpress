<?php
/**
 * Trang danh sách tin tức (blog index).
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<h1 class="tl-archive-title"><?php single_post_title(); ?></h1>

	<?php if ( have_posts() ) : ?>
		<div class="tl-post-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'post' );
			endwhile;
			?>
		</div>

		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Chưa có bài viết nào.', 'tungleads-theme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
