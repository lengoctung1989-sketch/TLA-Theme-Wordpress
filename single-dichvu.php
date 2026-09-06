<?php
/**
 * Chi tiết dịch vụ — CPT `dichvu` (đăng ký ở tl-site-plugin).
 *
 * @package TL\Theme
 *
 * P3.3 dichvu-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'tl-service-single' ); ?>>
			<header class="tl-service-single__header">
				<h1 class="tl-service-single__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="tl-service-single__media"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>

			<div class="tl-service-single__content">
				<?php
				the_content();
				wp_link_pages();
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
