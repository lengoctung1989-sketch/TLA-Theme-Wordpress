<?php
/**
 * @package TL\Theme
 *
 * P3.1 base-templates
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="tl-site-footer">
	<div class="tl-container">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
