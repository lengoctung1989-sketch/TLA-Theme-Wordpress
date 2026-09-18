/* CP1.8 — Customizer: bật/tắt + kéo thả thứ tự 6 mục của header (chỉ nạp ở wp-admin/customize.php).
   Cùng cách làm với CP2.6 (`customizer-cats.js`): giá trị setting là CSV key theo đúng thứ tự
   hiển thị và CHỈ gồm mục đang bật. */
(function ($) {
	'use strict';

	function sync($list) {
		var $value = $list.closest('.customize-control').find('.cp-horder__value');
		if (!$value.length) { return; }

		var keys = $list.children('.cp-horder__item').map(function () {
			var $cb = $(this).find('input[type=checkbox]');
			return $cb.prop('checked') ? String($cb.val()) : null;
		}).get().filter(Boolean);

		var csv = keys.join(',');
		if ($value.val() === csv) { return; }
		$value.val(csv);

		var settingId = $value.attr('data-customize-setting-link');
		if (settingId && window.wp && wp.customize) {
			wp.customize(settingId).set(csv);
		}
	}

	function mark($list) {
		$list.children('.cp-horder__item').each(function () {
			var on = $(this).find('input[type=checkbox]').prop('checked');
			$(this).toggleClass('cp-horder_off', !on);
		});
	}

	$(function () {
		$('.cp-horder').each(function () {
			var $list = $(this);
			if ($list.data('cp-sortable')) { return; }
			$list.data('cp-sortable', true);

			if ($.fn.sortable) {
				$list.sortable({
					items: '> li',
					handle: '.cp-horder__handle',
					axis: 'y',
					tolerance: 'pointer',
					cursor: 'grabbing',
					update: function () { mark($list); sync($list); }
				});
			}

			$list.on('change', 'input[type=checkbox]', function () { mark($list); sync($list); });
		});
	});
})(jQuery);
