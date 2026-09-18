<?php
/**
 * CP3.12 — Ô “Miêu tả” của DANH MỤC SẢN PHẨM dùng TRÌNH SOẠN THẢO ĐẦY ĐỦ (như trang thêm bài viết).
 *
 * Yêu cầu Tùng 2026-09-17: *“Ô nhập liệu -> Miêu tả trong phần thêm danh mục sản phẩm mới -> đổi ô nhập
 * liệu thành ô nhập liệu trình soạn thảo đầy đủ tính năng như phần thêm bài viết”*.
 *
 * Vì sao phải làm: WordPress vẽ ô “Miêu tả” của taxonomy bằng `<textarea>` THUẦN — màn THÊM ở
 * `wp-admin/edit-tags.php:521` (`#tag-description`), màn SỬA ở `wp-admin/edit-tag-form.php:205`
 * (`#description`) — và **không nạp TinyMCE** ở 2 màn này. Đo 2026-09-17 (Playwright, tài khoản admin):
 * `typeof tinymce === 'undefined'`, `typeof wp.editor === 'undefined'`, `tinyMCEPreInit.mceInit = []`
 * ⇒ phải tự nạp editor. Trang thêm bài viết & thêm sản phẩm ở site này đều chạy **editor CỔ ĐIỂN**
 * (`#postdivrich` + TinyMCE, KHÔNG có block editor) ⇒ dùng `wp_editor()` là ra đúng bộ nút như bài viết,
 * kể cả các nút do plugin **TinyMCE Advanced** thêm.
 *
 * CÁCH LÀM (và các điểm dễ vỡ — ĐỪNG bỏ):
 *  1. In THÊM 1 field `wp_editor()` giữ NGUYÊN `name="description"`: hook `{tax}_add_form_fields` chạy sau
 *     field core (`edit-tags.php:553` sau dòng 521) và `{tax}_edit_form_fields` cũng chạy sau field core
 *     ⇒ field của mình nằm SAU trong DOM ⇒ khi 2 field trùng tên, giá trị của mình thắng (PHP lấy bản cuối).
 *  2. Field core bị ẩn bằng CSS (`.term-description-wrap{display:none}`) + `disabled` bằng JS ⇒ chỉ 1 field
 *     thật sự được gửi. ⚠️ Field của mình PHẢI dùng class khác (`cp-term-editor-wrap`), nếu không thì CSS
 *     ẩn luôn editor của mình.
 *  3. `wp-admin/js/tags.js` ở màn THÊM **không** submit form: nó bắt **click `#submit`** rồi
 *     `$('#addtag').serialize()` ⇒ chỉ bắt `submit` là KHÔNG kịp. `assets/admin-term-editor.js` vì vậy gọi
 *     `tinymce.triggerSave()` ở pha **capture trên `document`** (chạy trước handler của core) cho cả
 *     `click` lẫn `submit`. Thiếu bước này thì lưu xong mô tả bị rỗng.
 *
 * Nội dung lưu vào `term.description` và được `wp_filter_post_kses()` lọc khi ghi DB (cho phép thẻ như bài
 * viết) ⇒ in ra front-end bằng `term_description()`. Theme in mô tả này trong `.cp-shop-desc__body`
 * (`inc/woocommerce.php` → `cp_shop_desc_below()`, dưới lưới sản phẩm, có nút “Xem thêm”).
 *
 * @package CaoPhat\Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy áp dụng — mặc định chỉ “Danh mục sản phẩm”.
 *
 * @return string[]
 */
function cp_term_editor_taxonomies(): array {
	return (array) apply_filters( 'cp_term_editor_taxonomies', array( 'product_cat' ) );
}

/**
 * Đang ở màn THÊM/SỬA term của taxonomy áp dụng? Trả `''` | `'add'` | `'edit'`.
 */
function cp_term_editor_screen(): string {
	global $pagenow;

	if ( ! in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return '';
	}

	// Chỉ để chọn giao diện, không đổi dữ liệu ⇒ không cần nonce.
	$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $taxonomy, cp_term_editor_taxonomies(), true ) ) {
		return '';
	}

	return 'term.php' === $pagenow ? 'edit' : 'add';
}

/** Dòng hướng dẫn dưới ô soạn thảo (lấy `desc_field_description` của taxonomy, fallback câu của theme). */
function cp_term_editor_help(): string {
	$taxonomy = isset( $_REQUEST['taxonomy'] ) ? sanitize_key( wp_unslash( (string) $_REQUEST['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tax      = $taxonomy ? get_taxonomy( $taxonomy ) : false;

	if ( $tax && ! empty( $tax->labels->desc_field_description ) ) {
		return (string) $tax->labels->desc_field_description;
	}

	return __( 'Định dạng được giữ nguyên như khi viết bài (đậm/nghiêng, danh sách, link, ảnh…).', 'tungleads-theme-cp' );
}


/**
 * In field “Miêu tả” bằng trình soạn thảo đầy đủ.
 *
 * @param string $content Nội dung hiện có (màn SỬA) hoặc `''` (màn THÊM).
 */
function cp_term_editor_field( string $content = '' ): void {
	$screen = cp_term_editor_screen();
	if ( '' === $screen ) {
		return;
	}

	// Ẩn field textarea của core (xem ghi chú 2 ở đầu file).
	echo '<style id="cp-term-editor-css">.term-description-wrap{display:none!important}</style>';

	// JS phải nằm SAU script của editor ⇒ enqueue ngay tại đây (lúc này `wp_editor()` sắp nạp tinymce).
	$js_path = get_stylesheet_directory() . '/assets/admin-term-editor.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'cp-term-editor',
			get_stylesheet_directory_uri() . '/assets/admin-term-editor.js',
			array( 'jquery', 'editor', 'quicktags' ),
			(int) filemtime( $js_path ),
			true
		);
	}

	$editor_args = array(
		'textarea_name' => 'description',
		'textarea_rows' => 14,
		'media_buttons' => true,
		'teeny'         => false,
		'quicktags'     => true,
		'tinymce'       => true,
		'editor_class'  => 'cp-term-editor',
	);

	// ⚠️ ID RIÊNG, không dùng 'description': màn SỬA, core cũng đặt `id="description"` cho textarea của
	// nó ⇒ trùng id làm TinyMCE init nhầm vào ô core (đang bị ẩn/disabled) ⇒ editor hiện ra rỗng và
	// không gõ được. Gửi dữ liệu là do `name` quyết định ⇒ `name="description"` vẫn giữ nguyên.
	ob_start();
	wp_editor( $content, 'cp-description', $editor_args );
	$editor = (string) ob_get_clean();

	$help = '<p class="description">' . esc_html( cp_term_editor_help() ) . '</p>';

	if ( 'add' === $screen ) {
		echo '<div class="form-field cp-term-editor-wrap">';
		echo '<label for="cp-description">' . esc_html__( 'Miêu tả', 'tungleads-theme-cp' ) . '</label>';
		echo $editor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_editor() tự escape.
		echo $help;   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- đã esc_html() ở trên.
		echo '</div>';

		return;
	}

	echo '<tr class="form-field cp-term-editor-wrap">';
	echo '<th scope="row"><label for="cp-description">' . esc_html__( 'Miêu tả', 'tungleads-theme-cp' ) . '</label></th>';
	echo '<td>';
	echo $editor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $help;   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</td>';
	echo '</tr>';
}

/** Màn THÊM: hook chạy sau field core (`wp-admin/edit-tags.php`). */
function cp_term_editor_add_field(): void {
	cp_term_editor_field( '' );
}

/**
 * Màn SỬA: hook chạy sau field core (`wp-admin/edit-tag-form.php`).
 *
 * @param WP_Term $term     Term đang sửa.
 * @param string  $taxonomy Slug taxonomy.
 */
function cp_term_editor_edit_field( $term, $taxonomy = '' ): void {
	cp_term_editor_field( isset( $term->description ) ? (string) $term->description : '' );
}

foreach ( cp_term_editor_taxonomies() as $cp_te_tax ) {
	add_action( "{$cp_te_tax}_add_form_fields", 'cp_term_editor_add_field' );
	add_action( "{$cp_te_tax}_edit_form_fields", 'cp_term_editor_edit_field', 10, 2 );
}
unset( $cp_te_tax );

/*
 * Cho phép ĐỊNH DẠNG KHỐI trong mô tả danh mục — nếu không thì trình soạn thảo thành vô nghĩa.
 *
 * WP core gắn `wp_filter_kses` vào `pre_term_description` (`wp-includes/default-filters.php:49`);
 * `wp_filter_kses()` = `wp_kses_data()` chỉ cho thẻ INLINE ⇒ `<p>`, `<ul>`, `<h1..h6>`… bị BÓC khi lưu.
 * Đo 2026-09-17 (wp-cli round-trip): `<p>Doan 1</p><ul><li>Muc A</li></ul><strong>Dam</strong>` ⇒ lưu
 * thành `Doan 1Muc A<strong>Dam</strong>` — mất sạch đoạn/danh sách.
 *
 * Đổi sang `wp_kses_post()` = ĐÚNG bộ thẻ của bài viết (`p ul ol li h1..h6 img a table blockquote…`),
 * vẫn chặn `<script>` như core. Người sửa được mô tả danh mục cũng chính là người viết được bài
 * (`manage_terms` ⇔ `edit_posts`) ⇒ cùng mức tin cậy. Áp cho MỌI taxonomy (không chỉ product_cat) vì
 * đây là hành vi của core, không phải của riêng màn nào.
 */
remove_filter( 'pre_term_description', 'wp_filter_kses' );
add_filter( 'pre_term_description', 'wp_kses_post' );

