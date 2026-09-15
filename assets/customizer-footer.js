/**
 * CP1.3 — Customizer "Footer Cao Phát": nâng 4 ô nội dung cột thành TinyMCE + đồng bộ vào setting.
 *
 * Vì sao không init ngay từ đầu: control trong Customizer được render sẵn cả vào sidebar rồi mới ẩn/hiện
 * theo section — init TinyMCE lúc khung còn `display: none` sẽ cho iframe rộng 0px. Nên chỉ init khi
 * section "cp_footer" được mở.
 *
 * Nếu `wp.editor` không có (script editor chưa nạp) thì bỏ qua — `<textarea>` vẫn hoạt động bình thường
 * nhờ `data-customize-setting-link` của WP.
 */
( function ( $, api ) {
	'use strict';

	if ( ! api || ! api.section ) {
		return;
	}

	var SECTION_ID = 'cp_footer';
	var SELECTOR = '.cp-editor';

	/** Lấy id setting từ `data-customize-setting-link` của textarea. */
	function settingId( $ta ) {
		return $ta.attr( 'data-customize-setting-link' );
	}

	/** Đối tượng TinyMCE của 1 textarea (WP không có `wp.editor.getEditor` → dùng `tinymce.get`). */
	function getEd( id ) {
		return window.tinymce && id ? window.tinymce.get( id ) : null;
	}

	/**
	 * Đăng ký plugin TinyMCE 4 tối giản `cpcode`: nút **"Mã HTML"** (icon `<>`) mở hộp thoại soạn mã nguồn.
	 *
	 * Vì sao phải tự làm: WP **KHÔNG** kèm plugin `code` của TinyMCE (WP dùng tab Văn bản/HTML của trình
	 * soạn thảo cổ điển), mà ô soạn thảo ở Customizer chỉ có 1 TinyMCE nên không có chỗ xem/sửa mã HTML.
	 * Skin `lightgray` của WP có sẵn icon `code` (`.mce-i-code`) nên nút hiện đúng icon `<>`.
	 *
	 * Nút này CHỈ đọc/ghi nội dung qua `editor.getContent({source_view:true})` → `editor.setContent()`,
	 * sau đó `fire('change')` để `sync()` bên dưới đẩy vào setting Customizer (setContent không tự fire).
	 */
	function registerCodeButton() {
		if ( ! window.tinymce || ! tinymce.PluginManager || tinymce.PluginManager.get( 'cpcode' ) ) {
			return;
		}

		tinymce.PluginManager.add( 'cpcode', function ( editor ) {
			function apply( e ) {
				editor.setContent( e.data.cp_html || '' );
				editor.undoManager.add();
				editor.fire( 'change' );
				editor.focus();
			}

			editor.addButton( 'cpcode', {
				icon: 'code',
				tooltip: 'Mã HTML',
				onclick: function () {
					editor.windowManager.open( {
						title: 'Mã HTML',
						body: [
							{
								type: 'textbox',
								name: 'cp_html',
								multiline: true,
								minWidth: 620,
								minHeight: 380,
								value: editor.getContent( { source_view: true } ),
								style: 'font-family: Menlo, Consolas, monospace; font-size: 12.5px; line-height: 1.5'
							}
						],
						onsubmit: apply,
						onSubmit: apply
					} );
				}
			} );
		} );
	}

	/** Khởi tạo TinyMCE cho 1 textarea (idempotent). */
	function initEditor( $ta ) {
		var id = $ta.attr( 'id' );
		var sId = settingId( $ta );
		var ed;

		if ( ! id || ! sId || ! window.wp || ! wp.editor || ! window.tinymce ) {
			return;
		}
		if ( getEd( id ) ) {
			return;
		}

		registerCodeButton();

		wp.editor.initialize( id, {
			tinymce: {
				wpautop: true,
				menubar: false,
				statusbar: false,
				resize: false,
				height: 200,
				plugins: 'lists,link,paste,wordpress,wplink,textcolor,cpcode',
				toolbar1: ( $ta.attr( 'data-cp-toolbar' ) || 'bold,italic,bullist,numlist,link,unlink,undo,redo' ) + ',fontsizeselect,forecolor,cpcode',
				/* CỠ CHỮ + MÀU CHỮ (yêu cầu Tùng 2026-09-15) PHẢI nằm ở HÀNG 1: WP **ẩn hàng toolbar thứ 2**
				   (chỉ hiện khi bấm nút "Toolbar Toggle") nên để ở `toolbar2` thì nút CÓ trong DOM mà KHÔNG
				   nhìn thấy (đo 2026-09-15: `rect = 0×0`, `offsetParent = null`). Sidebar Customizer hẹp
				   (~300px) nên TinyMCE tự xuống dòng — cứ để chung hàng 1.
				   `forecolor` cần plugin `textcolor` (WP có sẵn); cỡ chữ để dạng px cho khớp CSS theme. */
				toolbar2: '',
				fontsize_formats: '12px 13px 14px 15px 16px 18px 20px 22px 24px 28px 32px',
				block_formats: 'Đoạn văn=p;Tiêu đề nhỏ=h5;Danh sách=ul'
			},
			quicktags: false,
			mediaButtons: false
		} );

		ed = getEd( id );
		if ( ! ed ) {
			return;
		}

		/* Nội dung GỐC trong textarea (server render). Chỉ đồng bộ khi nội dung THỰC SỰ khác gốc —
		   nếu không, mỗi lần mở section rồi Publish sẽ ghi theme mod cho cả 4 cột (giá trị bằng mặc định
		   nhưng làm "đóng băng" mặc định: sau này sửa default trong code sẽ không còn tác dụng). */
		var banDau = String( $ta.val() || '' );

		function sync() {
			var value = ed.getContent();
			if ( value === banDau ) {
				return;
			}
			if ( api( sId ).get() !== value ) {
				api( sId ).set( value );
			}
		}

		ed.on( 'change keyup undo redo', sync );
	}

	/** Init mọi ô soạn thảo bên trong 1 phần tử (section container). */
	function initEditorsIn( $root ) {
		$root.find( SELECTOR ).each( function () {
			initEditor( $( this ) );
		} );
	}

	api.bind( 'ready', function () {
		var section = api.section( SECTION_ID );

		if ( ! section ) {
			return;
		}

		if ( section.expanded() ) {
			initEditorsIn( section.container );
		}

		section.expanded.bind( function ( expanded ) {
			if ( ! expanded ) {
				return;
			}
			initEditorsIn( section.container );
			// Section vừa hiện → TinyMCE cần đo lại chiều rộng cho khớp sidebar.
			window.setTimeout( function () {
				section.container.find( SELECTOR ).each( function () {
					var ed = getEd( $( this ).attr( 'id' ) );
					if ( ed ) {
						ed.fire( 'resize' );
					}
				} );
			}, 120 );
		} );

		// Dự phòng: nếu TinyMCE không init được, textarea vẫn sync vào setting khi rời ô.
		$( document ).on( 'change', SELECTOR, function () {
			var sId = settingId( $( this ) );
			if ( sId && api( sId ).get() !== this.value ) {
				api( sId ).set( this.value );
			}
		} );
	} );
}( jQuery, wp.customize ) );
