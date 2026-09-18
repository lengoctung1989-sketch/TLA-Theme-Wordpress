<?php
/**
 * Danh sách dịch vụ — CPT `dichvu` (đăng ký ở tl-site-plugin).
 * Chỉ có tác dụng khi site mode ∈ {service, hybrid} và plugin đã đăng ký CPT.
 *
 * @package TL\Theme
 *
 * P3.3 dichvu-templates
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="tl-main tl-container">
	<header class="tl-archive-header">
		<h1 class="tl-archive-title"><?php post_type_archive_title(); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="tl-service-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'dichvu' );
			endwhile;
			?>
		</div>

		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Chưa có dịch vụ nào.', 'tungleads-theme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
