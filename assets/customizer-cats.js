/* CP2.6 — Customizer: kéo thả sắp thứ tự danh mục (chỉ nạp trong wp-admin/customize.php) */
(function ($) {
	'use strict';

	function sync($list) {
		var $value = $list.closest('.customize-control').find('.cp-term-order__value');
		if (!$value.length) { return; }

		var ids = $list.children('.cp-term-order__item').map(function () {
			var $cb = $(this).find('input[type=checkbox]');
			return $cb.prop('checked') ? String($cb.val()) : null;
		}).get().filter(Boolean);

		var csv = ids.join(',');
		if ($value.val() === csv) { return; }
		$value.val(csv);

		var settingId = $value.attr('data-customize-setting-link');
		if (settingId && window.wp && wp.customize) {
			wp.customize(settingId).set(csv);
		}
	}

	$(function () {
		$('.cp-term-order').each(function () {
			var $list = $(this);
			if ($list.data('cp-sortable')) { return; }
			$list.data('cp-sortable', true);

			if ($.fn.sortable) {
				$list.sortable({
					items: '> li',
					handle: '.cp-term-order__handle',
					axis: 'y',
					tolerance: 'pointer',
					cursor: 'grabbing',
					update: function () { sync($list); }
				});
			}

			$list.on('change', 'input[type=checkbox]', function () { sync($list); });
		});
	});
})(jQuery);
