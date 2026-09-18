<?php
/**
 * Trang lưu trữ mặc định (category, tag, tác giả, ngày...).
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<header class="tl-archive-header">
		<h1 class="tl-archive-title"><?php the_archive_title(); ?></h1>
		<?php the_archive_description( '<div class="tl-archive-desc">', '</div>' ); ?>
	</header>

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
		<p><?php esc_html_e( 'Không tìm thấy nội dung.', 'tungleads-theme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
