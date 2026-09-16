/**
 * Button call/zalo - TungLeAds — repeater “Nút liên hệ” ở Settings → Button Call/Zalo.
 *
 * - “+ Thêm nút”: clone `<template id="tlcz-row-tpl">` rồi thay `__i__` bằng số tăng dần.
 *   Index chỉ dùng để đặt `name` — SERVER tự đánh lại index khi lưu (`array_values`), nên xoá
 *   dòng giữa bảng cũng không làm lỗi dữ liệu.
 * - “✕”: xoá dòng. Nếu chỉ còn 1 dòng thì XOÁ NỘI DUNG dòng đó (giữ 1 dòng trống để nhập).
 * - Lưu mà không còn dòng nào có số ⇒ server tự quay về 4 số mặc định (xem `tlcz_sanitize()`).
 */
(function () {
	'use strict';

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

	addBtn.addEventListener('click', function () {
		if (rows().length >= max) {
			return;
		}

		var holder = document.createElement('tbody');
		holder.innerHTML = tpl.innerHTML.replace(/__i__/g, String(next++));

		var row = holder.querySelector('tr');

		if (row) {
			tbody.appendChild(row);
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
			// Chỉ còn 1 dòng: xoá nội dung để vẫn còn chỗ nhập.
			row.querySelectorAll('input[type="text"]').forEach(function (input) {
				input.value = '';
			});

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
