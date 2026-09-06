<?php
/**
 * Khung hiển thị 1 bài viết trong vòng lặp (dùng chung: home.php, archive.php).
 *
 * @package TL\Theme
 *
 * P3.4 blog-templates
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'tl-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="tl-post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<h2 class="tl-post-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<p class="tl-post-card__meta">
		<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
	</p>

	<div class="tl-post-card__excerpt"><?php the_excerpt(); ?></div>
</article>
