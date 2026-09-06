<?php
/**
 * 3 bài viết mới nhất trên trang chủ.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

$cp_posts = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $cp_posts->have_posts() ) {
	return;
}
?>
<div class="cp-posts">
	<?php
	while ( $cp_posts->have_posts() ) :
		$cp_posts->the_post();
		$cp_cats = get_the_category();
		?>
		<article class="cp-post">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
				<?php echo esc_html( ! empty( $cp_cats ) ? $cp_cats[0]->name : get_the_date() ); ?>
			</time>
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		</article>
		<?php
	endwhile;
	wp_reset_postdata();
	?>
</div>
