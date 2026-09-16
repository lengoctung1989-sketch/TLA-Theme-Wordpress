/**
 * Button call/zalo - TungLeAds — JS trang Settings → Button Call/Zalo.
 *
 * - Repeater: “+ Thêm nút” (clone `<template>`, thay `__i__` bằng số tăng dần) · “✕” xoá dòng
 *   (chỉ còn 1 dòng thì XOÁ NỘI DUNG để vẫn còn chỗ nhập). Index chỉ để đặt `name` — server tự đánh
 *   lại index khi lưu (`array_values`) nên xoá dòng giữa bảng không làm lỗi dữ liệu.
 * - Color picker: dùng Iris có sẵn của WordPress (`wp-color-picker`). ĐỂ TRỐNG = dùng màu mặc định
 *   của kiểu ⇒ `defaultColor: false` để có nút “Xoá”.
 * - Ảnh icon: mở thư viện Media (`wp.media`), lưu attachment ID vào input ẩn + hiện ảnh xem trước.
 * - Đổi “Kiểu” ⇒ cập nhật placeholder ô Giá trị (số điện thoại vs username/link).
 */
(function () {
	'use strict';

	var $ = window.jQuery;
	var table = document.getElementById('tlcz-rows');
	var tpl = document.getElementById('tlcz-row-tpl');
	var addBtn = document.querySelector('.tlcz-add');

	if (!table || !tpl || !addBtn) {
		return;
	}

	var tbody = table.querySelector('tbody');
	var max = parseInt(table.getAttribute('data-max') || '8', 10);
	var next = parseInt(table.getAttribute('data-next') || '1', 10);

	function rows() {
		return tbody.querySelectorAll('tr.tlcz-row');
	}

	function syncAdd() {
		addBtn.disabled = rows().length >= max;
	}

	/** Color picker cho 2 ô màu của 1 dòng. */
	function initColors(row) {
		if (!$ || !$.fn.wpColorPicker) {
			return;
		}

		$(row).find('.tlcz-color').each(function () {
			var $input = $(this);

			if ($input.data('tlczIris')) {
				return;
			}

			$input.wpColorPicker({ defaultColor: false, palettes: false });
			$input.data('tlczIris', 1);
		});
	}

	/** Chọn / xoá ảnh icon qua thư viện Media của WordPress. */
	function initMedia(row) {
		var pick = row.querySelector('.tlcz-pick');
		var clear = row.querySelector('.tlcz-clear-icon');
		var input = row.querySelector('.tlcz-icon-id');
		var preview = row.querySelector('.tlcz-icon-preview');

		if (!pick || !input || !preview || !window.wp || !window.wp.media) {
			return;
		}

		pick.addEventListener('click', function () {
			var frame = window.wp.media({
				title: 'Chọn ảnh icon cho nút',
				button: { text: 'Dùng ảnh này' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				var size = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;

				input.value = att.id;
				preview.innerHTML = '<img src="' + size + '" alt="" width="40" height="40">';

				if (clear) {
					clear.hidden = false;
				}
			});

			frame.open();
		});

		if (clear) {
			clear.addEventListener('click', function () {
				input.value = '';
				preview.innerHTML = '';
				clear.hidden = true;
			});
		}
	}

	/** Đổi Kiểu ⇒ đổi placeholder ô Giá trị. */
	function initType(row) {
		var select = row.querySelector('.tlcz-type');
		var value = row.querySelector('.tlcz-value');

		if (!select || !value) {
			return;
		}

		select.addEventListener('change', function () {
			var opt = select.options[select.selectedIndex];
			value.placeholder = opt && opt.getAttribute('data-ph') ? opt.getAttribute('data-ph') : '';
		});
	}

	function enhance(row) {
		initColors(row);
		initMedia(row);
		initType(row);
	}

	tbody.querySelectorAll('tr.tlcz-row').forEach(enhance);

	addBtn.addEventListener('click', function () {
		if (rows().length >= max) {
			return;
		}

		var holder = document.createElement('tbody');
		holder.innerHTML = tpl.innerHTML.replace(/__i__/g, String(next++));

		var row = holder.querySelector('tr');

		if (row) {
			tbody.appendChild(row);
			enhance(row);
		}

		syncAdd();
	});

	table.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.tlcz-remove') : null;

		if (!btn) {
			return;
		}

		e.preventDefault();

		var row = btn.closest('tr');

		if (!row) {
			return;
		}

		if (rows().length > 1) {
			row.parentNode.removeChild(row);
		} else {
			// Chỉ còn 1 dòng: xoá nội dung để vẫn còn chỗ nhập (giá trị + màu + ảnh icon).
			row.querySelectorAll('input[type="text"]').forEach(function (input) {
				input.value = '';
			});

			var iconId = row.querySelector('.tlcz-icon-id');
			var iconBox = row.querySelector('.tlcz-icon-preview');
			var iconClear = row.querySelector('.tlcz-clear-icon');

			if (iconId) {
				iconId.value = '';
			}
			if (iconBox) {
				iconBox.innerHTML = '';
			}
			if (iconClear) {
				iconClear.hidden = true;
			}

			var check = row.querySelector('input[type="checkbox"]');
			var select = row.querySelector('select');

			if (check) {
				check.checked = true;
			}
			if (select) {
				select.selectedIndex = 0;
			}
		}

		syncAdd();
	});

	syncAdd();
})();
