/* CP1.9 — Customizer: repeater “Chi nhánh & Hotline” (thêm / xoá / kéo thả từng dòng)
 *
 * Giá trị setting = JSON `[{"name":"CN Quận 7","tel":"0834.484.484"}, …]`. Mẫu JS này dựa trên
 * `customizer-blocks.js` (CP2.4) — cùng cách: gom dữ liệu từ DOM → ghi input ẩn → `wp.customize.set()`
 * để Customizer biết là “có thay đổi” và lưu đúng giá trị đã sắp thứ tự.
 */
(function ($) {
	'use strict';

	/** Gom các dòng trong DOM → JSON → input ẩn + setting của Customizer. */
	function sync($wrap) {
		var $input = $wrap.find('.cp-branches__value');
		if (!$input.length) { return; }

		var rows = $wrap.find('.cp-branches__list > .cp-branch-row').map(function () {
			var $r = $(this);
			return {
				name: String($r.find('[data-field="name"]').val() || '').trim(),
				tel: String($r.find('[data-field="tel"]').val() || '').trim()
			};
		}).get();

		var json = JSON.stringify(rows);
		if ($input.val() === json) { return; }

		$input.val(json);

		var settingId = $input.attr('data-customize-setting-link');
		if (settingId && window.wp && wp.customize) {
			wp.customize(settingId).set(json);
		}
	}

	/** Thêm 1 dòng trống từ hàng mẫu ẩn. */
	function addRow($wrap) {
		var $tpl = $wrap.find('.cp-branches__tpl .cp-branch-row').first();
		if (!$tpl.length) { return; }

		var $row = $tpl.clone()
			.removeClass('cp-branch-row--tpl')
			.removeAttr('hidden')
			.removeAttr('aria-hidden');

		$row.find('[data-field="name"], [data-field="tel"]').val('');
		$wrap.find('.cp-branches__list').append($row);

		sync($wrap);
		$row.find('[data-field="name"]').trigger('focus');
		$row.get(0).scrollIntoView({ block: 'nearest' });
	}

	$(function () {
		$('.cp-branches').each(function () {
			var $wrap = $(this);
			if ($wrap.data('cp-branches-ready')) { return; }
			$wrap.data('cp-branches-ready', true);

			var $list = $wrap.find('.cp-branches__list');
			if ($.fn.sortable) {
				$list.sortable({
					items: '> .cp-branch-row',
					handle: '.cp-branch-row__handle',
					axis: 'y',
					tolerance: 'pointer',
					cursor: 'grabbing',
					update: function () { sync($wrap); }
				});
			}

			// `input` = đồng bộ ngay khi gõ (không cần rời ô mới ăn), `change` phòng trình duyệt cũ.
			$wrap.on('input change', '[data-field]', function () { sync($wrap); });

			$wrap.on('click', '.cp-branches__add', function (e) {
				e.preventDefault();
				addRow($wrap);
			});

			$wrap.on('click', '.cp-branch-row__remove', function (e) {
				e.preventDefault();
				$(this).closest('.cp-branch-row').remove();
				sync($wrap);
			});

			sync($wrap);
		});
	});
})(jQuery);
