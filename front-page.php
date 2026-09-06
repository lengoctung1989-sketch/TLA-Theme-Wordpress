<?php
/**
 * Trang chủ. Nội dung tĩnh do editor dựng bằng block pattern (không hardcode trong PHP).
 * Nếu front page là "bài viết mới nhất" thì WordPress dùng home.php thay vì file này.
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
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
