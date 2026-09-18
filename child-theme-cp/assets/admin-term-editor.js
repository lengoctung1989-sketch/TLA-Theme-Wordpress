/**
 * CP3.12 — Đồng bộ trình soạn thảo “Miêu tả” (danh mục sản phẩm) về textarea trước khi lưu.
 *
 * Vì sao cần: nội dung người dùng gõ nằm trong iframe TinyMCE, không tự chảy vào `<textarea>`.
 * Ở màn SỬA (form POST thường) thì `submit` là đủ, nhưng màn THÊM của WordPress lại dùng
 * `wp-admin/js/tags.js`: nó **bắt click `#submit`** rồi `$('#addtag').serialize()` (KHÔNG submit form).
 * Nên mọi lắng nghe ở đây đặt ở **pha capture trên `document`** — chạy TRƯỚC handler của core.
 * Thiếu bước này: thêm danh mục xong, mô tả bị rỗng.
 */
( function () {
	'use strict';

	/** Textarea core (`#tag-description` ở màn thêm, `#description` ở màn sửa) — chặn không cho gửi. */
	function disableCoreField() {
		var core = document.getElementById( 'tag-description' ) ||
			document.querySelector( '.term-description-wrap textarea' );

		if ( core ) {
			core.disabled = true;
			core.setAttribute( 'aria-hidden', 'true' );
		}
	}

	/** Editor → textarea (mọi editor trên trang, `triggerSave()` của TinyMCE làm việc này). */
	function syncEditor() {
		if ( window.tinymce && 'function' === typeof window.tinymce.triggerSave ) {
			window.tinymce.triggerSave();
		}
	}

	/** Phần tử được bấm có phải nút gửi form không? */
	function isSubmitter( el ) {
		if ( ! el || ! el.getAttribute ) {
			return false;
		}

		return 'submit' === el.getAttribute( 'type' ) || 'submit' === el.id;
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', disableCoreField );
	} else {
		disableCoreField();
	}

	// 1. Form POST thường (màn SỬA, và màn THÊM khi theme/plugin khác submit thật).
	document.addEventListener( 'submit', syncEditor, true );

	// 2. AJAX của core (màn THÊM): click `#submit` ⇒ serialize() ngay sau đó.
	document.addEventListener(
		'click',
		function ( event ) {
			var el = event.target;

			if ( isSubmitter( el ) || ( el && el.closest && el.closest( '#addtag .submit, form .submit' ) ) ) {
				syncEditor();
			}
		},
		true
	);
}() );
