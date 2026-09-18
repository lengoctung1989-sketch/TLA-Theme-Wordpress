<?php
/**
 * Khung hiển thị 1 mục dịch vụ (CPT `dichvu`) trong vòng lặp.
 * Dùng chung: archive-dichvu.php và khối "dịch vụ liên quan".
 * CPT `dichvu` đăng ký ở tl-site-plugin — theme chỉ trình bày.
 *
 * @package TL\Theme
 *
 * P3.3 dichvu-templates
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'tl-service-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="tl-service-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>

	<h2 class="tl-service-card__title">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<div class="tl-service-card__excerpt"><?php the_excerpt(); ?></div>

	<p>
		<a class="tl-service-card__more" href="<?php the_permalink(); ?>">
			<?php esc_html_e( 'Xem chi tiết', 'tungleads-theme' ); ?>
			<span class="screen-reader-text"><?php the_title(); ?></span>
		</a>
	</p>
</article>
