<?php
/**
 * Chi tiết bài viết.
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'tl-single' ); ?>>
			<header class="tl-single__header">
				<h1 class="tl-single__title"><?php the_title(); ?></h1>
				<p class="tl-single__meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="tl-single__media"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>

			<div class="tl-single__content">
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
		?>
		<nav class="tl-single__nav" aria-label="<?php esc_attr_e( 'Điều hướng bài viết', 'tungleads-theme' ); ?>">
			<?php the_post_navigation( array( 'screen_reader_text' => __( 'Điều hướng bài viết', 'tungleads-theme' ) ) ); ?>
		</nav>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
