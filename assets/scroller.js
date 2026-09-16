/**
 * CP2.8 — Nút cuộn ‹ › cho dải "Cuộn ngang" (khối sản phẩm / tin tức trên trang chủ).
 *
 * Thanh trượt đã ẩn bằng CSS; người dùng cuộn bằng kéo/trackpad hoặc 2 nút này (mỗi lần 1 thẻ).
 * Nút tự ẩn khi dải đã ở đầu/cuối (`.is-start` / `.is-end`).
 */
(function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// CP2.8 / CP3.7 — list nằm trong `.cp-scroller`: khối trang chủ dùng `.cp-products` / `.cp-cat-news` /
	// `.cp-cats`; dải "Sản phẩm tương tự" (CP3.7) là `ul.products` của WooCommerce. KHÔNG gắn thêm class
	// `.cp-products` cho `ul.products`: `.cp-products` là GRID 4 cột, gắn vào sẽ phải đấu specificity với
	// `.woocommerce .cp-related-band ul.products` (đang set flex + cuộn ngang).
	var LIST_SEL = '.cp-products, .cp-cat-news, .cp-cats, ul.products';

	function step(list) {
		var first = list.firstElementChild;
		if (!first) { return Math.round(list.clientWidth * 0.8); }

		var cs = window.getComputedStyle(list);
		var gap = parseFloat(cs.columnGap || cs.gap || '0') || 0;

		return Math.round(first.getBoundingClientRect().width + gap);
	}

	function update(scroller) {
		var list = scroller.querySelector(LIST_SEL);
		if (!list) { return; }

		var max = list.scrollWidth - list.clientWidth;
		var left = list.scrollLeft;

		scroller.classList.toggle('is-start', left <= 1);
		scroller.classList.toggle('is-end', left >= max - 1);
		scroller.classList.toggle('cp-scroller--static', max <= 1);
	}

	function move(scroller, dir) {
		var list = scroller.querySelector(LIST_SEL);
		if (!list) { return; }

		list.scrollBy({
			left: dir * step(list),
			behavior: reduce ? 'auto' : 'smooth'
		});
	}

	function init(scroller) {
		if (scroller.dataset.cpScroller) { return; }
		scroller.dataset.cpScroller = '1';

		var list = scroller.querySelector(LIST_SEL);
		if (!list) { return; }

		scroller.addEventListener('click', function (e) {
			var btn = e.target.closest('.cp-scroller__btn');
			if (!btn) { return; }
			move(scroller, btn.classList.contains('cp-scroller__btn--next') ? 1 : -1);
		});

		var raf = 0;
		list.addEventListener('scroll', function () {
			if (raf) { return; }
			raf = window.requestAnimationFrame(function () {
				raf = 0;
				update(scroller);
			});
		}, { passive: true });

		window.addEventListener('resize', function () { update(scroller); });
		window.addEventListener('load', function () { update(scroller); });

		// Ảnh lazy làm bề rộng thẻ đổi sau khi tải → cập nhật lại trạng thái
		list.querySelectorAll('img').forEach(function (img) {
			if (!img.complete) { img.addEventListener('load', function () { update(scroller); }); }
		});

		update(scroller);
	}

	function boot() {
		document.querySelectorAll('.cp-scroller').forEach(init);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
