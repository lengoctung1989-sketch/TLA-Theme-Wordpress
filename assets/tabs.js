/**
 * CP2.9 — Khối "tab sản phẩm" trên trang chủ: đổi tab.
 *
 * Panel không active đã có sẵn thuộc tính `hidden` từ PHP; JS chỉ đổi `hidden` + `aria-selected`
 * + `tabindex` (a11y theo mẫu tablist/tab/tabpanel) và điều khiển bằng mũi tên ← → Home End.
 * Sau mỗi lần đổi tab có bắn `resize` để `scroller.js` (CP2.8) đo lại dải "Cuộn ngang" vừa hiện
 * (panel ẩn có `clientWidth = 0` → trạng thái nút ‹ › tính sai nếu không đo lại).
 */
(function () {
	'use strict';

	function activate(block, idx) {
		var tabs = block.querySelectorAll('.cp-tablist__btn');
		var panels = block.querySelectorAll('.cp-tabpane');
		if (!tabs.length || idx < 0 || idx >= tabs.length) { return; }

		for (var i = 0; i < tabs.length; i++) {
			var on = (i === idx);
			tabs[i].classList.toggle('is-active', on);
			tabs[i].setAttribute('aria-selected', on ? 'true' : 'false');
			tabs[i].tabIndex = on ? 0 : -1;
			if (panels[i]) { panels[i].hidden = !on; }
		}

		window.dispatchEvent(new Event('resize'));
	}

	function init(block) {
		if (block.dataset.cpTabs) { return; }
		block.dataset.cpTabs = '1';

		var tablist = block.querySelector('.cp-tablist');
		if (!tablist) { return; }

		tablist.addEventListener('click', function (e) {
			var btn = e.target.closest ? e.target.closest('.cp-tablist__btn') : null;
			if (!btn) { return; }
			activate(block, Array.prototype.indexOf.call(tablist.querySelectorAll('.cp-tablist__btn'), btn));
		});

		tablist.addEventListener('keydown', function (e) {
			if (['ArrowRight', 'ArrowLeft', 'Home', 'End'].indexOf(e.key) === -1) { return; }

			var tabs = tablist.querySelectorAll('.cp-tablist__btn');
			var cur = Array.prototype.indexOf.call(tabs, document.activeElement);
			if (cur === -1) { return; }

			e.preventDefault();

			var next = cur;
			if ('ArrowRight' === e.key) { next = (cur + 1) % tabs.length; }
			if ('ArrowLeft' === e.key) { next = (cur - 1 + tabs.length) % tabs.length; }
			if ('Home' === e.key) { next = 0; }
			if ('End' === e.key) { next = tabs.length - 1; }

			activate(block, next);
			tabs[next].focus();
		});
	}

	function boot() {
		document.querySelectorAll('.cp-tabs-block').forEach(init);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
