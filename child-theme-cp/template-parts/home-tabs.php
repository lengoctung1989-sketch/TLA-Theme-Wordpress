<?php
/**
 * CP2.9 — Khối "tab sản phẩm" trên trang chủ: 4 tab CỐ ĐỊNH.
 *
 * Tab: Sản phẩm mới · Bán chạy · Hot (cờ "Nổi bật" WooCommerce) · Khuyến mãi.
 * Mỗi tab cấu hình trong Customize → "Trang chủ — Khối tab sản phẩm": bật/tắt · tiêu đề ·
 * số lượng (1–20) · bố cục (Lưới 4/3/2 cột · Cuộn ngang).
 *
 * TÁI DÙNG: `cp_product_tabs()` / `cp_tab_layouts()` / `cp_tab_products()` (`functions.php`),
 * `cp_product_card()` (CP1.1), `.cp-cat-block--<layout>` + `cp_scroller_open()/close()` (CP2.4/CP2.8)
 * → lưới, dải cuộn, nút ‹ › hoạt động y như các khối sản phẩm theo danh mục.
 * Tab bị tắt trong Customizer HOẶC không có sản phẩm nào → tự ẩn (không in tab rỗng).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) || ! function_exists( 'cp_product_tabs' ) ) {
	return;
}

$cp_layouts = cp_tab_layouts();
$cp_panels  = array();

foreach ( cp_product_tabs() as $cp_source => $cp_tab ) {
	if ( ! get_theme_mod( 'cp_tab_' . $cp_source . '_show', true ) ) {
		continue;
	}

	$cp_title  = trim( (string) get_theme_mod( 'cp_tab_' . $cp_source . '_title', $cp_tab['title'] ) );
	$cp_count  = max( 1, min( 20, absint( get_theme_mod( 'cp_tab_' . $cp_source . '_count', 8 ) ) ) );
	$cp_layout = (string) get_theme_mod( 'cp_tab_' . $cp_source . '_layout', 'cols-4' );
	$cp_layout = array_key_exists( $cp_layout, $cp_layouts ) ? $cp_layout : 'cols-4';

	$cp_products = cp_tab_products( $cp_source, $cp_count );
	if ( ! $cp_products ) {
		continue;
	}

	$cp_panels[ $cp_source ] = array(
		'title'    => '' !== $cp_title ? $cp_title : $cp_tab['title'],
		'layout'   => $cp_layout,
		'products' => $cp_products,
	);
}

if ( ! $cp_panels ) {
	return;
}

$cp_first = (string) array_key_first( $cp_panels );
?>
<section class="cp-section cp-tabs-block">
	<div class="cp-container">
		<div class="cp-tablist" role="tablist" aria-label="<?php esc_attr_e( 'Sản phẩm theo nhóm', 'tungleads-theme' ); ?>">
			<?php foreach ( $cp_panels as $cp_source => $cp_panel ) : ?>
				<?php $cp_on = ( $cp_source === $cp_first ); ?>
				<button type="button" role="tab" class="cp-tablist__btn<?php echo $cp_on ? ' is-active' : ''; ?>"
					id="cp-tab-<?php echo esc_attr( $cp_source ); ?>"
					aria-controls="cp-tab-panel-<?php echo esc_attr( $cp_source ); ?>"
					aria-selected="<?php echo $cp_on ? 'true' : 'false'; ?>"
					tabindex="<?php echo $cp_on ? '0' : '-1'; ?>">
					<span class="cp-tablist__icon"><?php echo cp_tab_icon( $cp_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG tĩnh, không có dữ liệu người dùng ?></span>
					<?php echo esc_html( $cp_panel['title'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $cp_panels as $cp_source => $cp_panel ) : ?>
			<?php $cp_on = ( $cp_source === $cp_first ); ?>
			<div class="cp-tabpane cp-cat-block cp-cat-block--<?php echo esc_attr( $cp_panel['layout'] ); ?>"
				id="cp-tab-panel-<?php echo esc_attr( $cp_source ); ?>"
				role="tabpanel"
				aria-labelledby="cp-tab-<?php echo esc_attr( $cp_source ); ?>"
				tabindex="0"<?php echo $cp_on ? '' : ' hidden'; ?>>
				<?php if ( 'scroll' === $cp_panel['layout'] ) : ?>
					<?php cp_scroller_open(); // CP2.8 — nút cuộn ‹ (chỉ bố cục "Cuộn ngang") ?>
				<?php endif; ?>
				<div class="cp-products">
					<?php foreach ( $cp_panel['products'] as $cp_product ) : ?>
						<?php cp_product_card( $cp_product ); ?>
					<?php endforeach; ?>
				</div>
				<?php if ( 'scroll' === $cp_panel['layout'] ) : ?>
					<?php cp_scroller_close(); // CP2.8 — nút cuộn › + đóng `.cp-scroller` ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
