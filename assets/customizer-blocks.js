/* CP2.4 + CP2.5 — Customizer: repeater "Thêm danh mục" (thêm/xoá/kéo thả khối) */
(function ($) {
	'use strict';

	function sync($wrap) {
		var $input = $wrap.find('.cp-blocks__value');
		if (!$input.length) { return; }

		var rows = $wrap.find('.cp-blocks__list > .cp-block-row').map(function () {
			var $r = $(this);
			return {
				cat: String($r.find('[data-field="cat"]').val() || ''),
				title: String($r.find('[data-field="title"]').val() || ''),
				order: String($r.find('[data-field="order"]').val() || 'date-desc'),
				count: String($r.find('[data-field="count"]').val() || ''),
				layout: String($r.find('[data-field="layout"]').val() || ''),
				products: String($r.find('[data-field="products"]').val() || '')
			};
		}).get();

		// Đánh số lại "Khối N" theo thứ tự hiện tại
		$wrap.find('.cp-blocks__list > .cp-block-row').each(function (i) {
			$(this).find('.cp-block-row__n').text(i + 1);
		});

		var json = JSON.stringify(rows);
		if ($input.val() === json) { return; }
		$input.val(json);

		var settingId = $input.attr('data-customize-setting-link');
		if (settingId && window.wp && wp.customize) {
			wp.customize(settingId).set(json);
		}
	}

	function addRow($wrap) {
		var $tpl = $wrap.find('.cp-blocks__tpl .cp-block-row').first();
		if (!$tpl.length) { return; }

		var $row = $tpl.clone()
			.removeClass('cp-block-row--tpl')
			.removeAttr('hidden')
			.removeAttr('aria-hidden');

		$row.find('[data-field="title"]').val('');
		$wrap.find('.cp-blocks__list').append($row);

		sync($wrap);
		$row.find('[data-field="cat"]').trigger('focus');
		$row.get(0).scrollIntoView({ block: 'nearest' });
	}

	/* ---------- CP2.7 — picker "Sản phẩm ưu tiên" (tải qua AJAX theo danh mục của khối) ---------- */

	function updateCount($row) {
		var ids = String($row.find('[data-field="products"]').val() || '').split(',').filter(Boolean);
		$row.find('.cp-block-products__count').text('(' + ids.length + ')');
	}

	// Tick / kéo thả xong → gom ID ĐANG TICK theo thứ tự hiển thị vào input ẩn, rồi mới sync JSON
	function syncProducts($wrap, $row) {
		var ids = [];
		$row.find('.cp-block-products__list > li').each(function () {
			var $li = $(this);
			if ($li.find('input[type=checkbox]').prop('checked')) { ids.push(String($li.data('id'))); }
		});
		$row.find('[data-field="products"]').val(ids.join(','));
		updateCount($row);
		sync($wrap);
	}

	function renderProducts($wrap, $row, items, chosen) {
		var $list = $row.find('.cp-block-products__list').empty();
		var $status = $row.find('.cp-block-products__status');

		if (!items.length) {
			$status.text((window.cpBlocksData && cpBlocksData.i18n.empty) || '').show();
			return;
		}
		$status.hide();

		items.forEach(function (item) {
			var $li = $('<li class="cp-block-products__item"></li>').attr('data-id', item.id);
			var $label = $('<label class="cp-block-products__label"><input type="checkbox"> <span></span></label>');
			$label.find('span').text(item.title + (item.outside ? ' (' + cpBlocksData.i18n.outside + ')' : ''));
			if (chosen.indexOf(String(item.id)) !== -1) { $label.find('input[type=checkbox]').prop('checked', true); }
			$li.append($label).append('<span class="cp-block-products__handle" aria-hidden="true">⋮⋮</span>');
			$list.append($li);
		});

		if ($.fn.sortable) {
			$list.sortable({
				items: '> li',
				handle: '.cp-block-products__handle',
				axis: 'y',
				tolerance: 'pointer',
				update: function () { syncProducts($wrap, $row); }
			});
		}
		updateCount($row);
	}

	function loadProducts($wrap, $row) {
		var cat = String($row.find('[data-field="cat"]').val() || '');
		var $status = $row.find('.cp-block-products__status');

		if (!cat || !window.cpBlocksData) {
			$row.find('.cp-block-products__list').empty();
			$status.text((window.cpBlocksData && cpBlocksData.i18n.pickCat) || '').show();
			return;
		}

		$status.text(cpBlocksData.i18n.loading).show();
		$.post(cpBlocksData.ajaxUrl, {
			action: 'cp_block_products',
			nonce: cpBlocksData.nonce,
			cat: cat,
			order: String($row.find('[data-field="order"]').val() || 'date-desc'),
			chosen: String($row.find('[data-field="products"]').val() || '')
		}).done(function (res) {
			if (res && res.success && res.data && res.data.items) {
				$row.data('cp-products-loaded', true);
				renderProducts($wrap, $row, res.data.items, res.data.chosen || []);
			} else {
				$status.text(cpBlocksData.i18n.error).show();
			}
		}).fail(function () {
			$status.text(cpBlocksData.i18n.error).show();
		});
	}

	$(function () {
		$('.cp-blocks').each(function () {
			var $wrap = $(this);
			if ($wrap.data('cp-blocks-ready')) { return; }
			$wrap.data('cp-blocks-ready', true);

			var $list = $wrap.find('.cp-blocks__list');
			if ($.fn.sortable) {
				$list.sortable({
					items: '> .cp-block-row',
					handle: '.cp-block-row__handle',
					axis: 'y',
					tolerance: 'pointer',
					cursor: 'grabbing',
					update: function () { sync($wrap); }
				});
			}

			$wrap.on('change', '[data-field]', function () { sync($wrap); });

			/* Đổi danh mục / sắp xếp → danh sách sản phẩm phải tải lại */
			$wrap.on('change', '[data-field="cat"], [data-field="order"]', function () {
				var $row = $(this).closest('.cp-block-row');
				$row.removeData('cp-products-loaded');
				$row.find('.cp-block-products__list').empty();
				$row.find('.cp-block-products__status')
					.text((window.cpBlocksData && cpBlocksData.i18n.pickCat) || '')
					.show();
				if (!$row.find('.cp-block-products__panel').prop('hidden')) { loadProducts($wrap, $row); }
			});

			$wrap.on('click', '.cp-block-products__toggle', function (e) {
				e.preventDefault();
				var $row = $(this).closest('.cp-block-row');
				var $panel = $row.find('.cp-block-products__panel');
				var willOpen = $panel.prop('hidden');
				$panel.prop('hidden', !willOpen);
				$(this).attr('aria-expanded', willOpen ? 'true' : 'false');
				if (willOpen && !$row.data('cp-products-loaded')) { loadProducts($wrap, $row); }
			});

			$wrap.on('change', '.cp-block-products__list input[type=checkbox]', function () {
				syncProducts($wrap, $(this).closest('.cp-block-row'));
			});

			$wrap.on('click', '.cp-blocks__add', function (e) { e.preventDefault(); addRow($wrap); });
			$wrap.on('click', '.cp-block-row__remove', function (e) {
				e.preventDefault();
				$(this).closest('.cp-block-row').remove();
				sync($wrap);
			});

			// Đánh số "Khối N" + số sản phẩm đã chọn đúng ngay khi mở
			$wrap.find('.cp-block-row').each(function () { updateCount($(this)); });
			sync($wrap);
		});
	});
})(jQuery);
