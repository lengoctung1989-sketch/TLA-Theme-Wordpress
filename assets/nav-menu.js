/**
 * CP1.5 — Menu mobile trên header.
 *
 * - Nút burger (`.cp-burger`) mở/đóng panel dọc `.cp-nav.is-open` (chỉ hiện ≤768px).
 * - Mục có menu con được gắn thêm nút `.cp-nav__toggle` để mở/đóng dạng accordion.
 *   Nút được chèn là ANH EM của `<a>` (KHÔNG lồng `<button>` trong `<a>` — HTML không hợp lệ).
 * - Đóng panel: Esc, click ra ngoài, hoặc resize lên desktop.
 * - JS tắt → burger không làm gì, menu desktop vẫn hoạt động bình thường.
 */
(function () {
	'use strict';

	function init() {
		var header = document.querySelector('.cp-header');
		if (!header) { return; }

		var nav = header.querySelector('.cp-nav');
		var burger = header.querySelector('.cp-burger');
		if (!nav || !burger) { return; }

		function isMobile() {
			return window.matchMedia('(max-width: 768px)').matches;
		}

		function setOpen(open) {
			nav.classList.toggle('is-open', open);
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
			// Khoá cuộn trang khi panel đang mở (chỉ có tác dụng ở mobile).
			document.body.classList.toggle('cp-nav-open', open && isMobile());
		}

		/* 0. CP1.4b — đánh dấu mục MEGA: mục CẤP 1 mà bên trong có menu cấp 3.
		      CSS sẽ xếp menu cấp 2 của mục này thành các CỘT (tiêu đề cột = cấp 2, danh sách = cấp 3).
		      Chỉ xét mục cấp 1 — cấp sâu hơn vẫn là dropdown/accordion bình thường. */
		Array.prototype.forEach.call(nav.querySelectorAll(':scope > ul > li.menu-item-has-children'), function (li) {
			var sub = li.querySelector(':scope > .sub-menu');
			if (!sub || !sub.querySelector(':scope > li > .sub-menu')) { return; }
			li.classList.add('cp-mega');
			sub.classList.add('sub-menu--mega');

			/* CP1.4b — các mục cấp 2 KHÔNG có danh mục con (cấp 3) gom chung vào 1 cột,
			   để chúng không mỗi cái chiếm 1 cột với danh sách rỗng. */
			var misc = null;
			Array.prototype.slice.call(sub.children).forEach(function (item) {
				if (item.querySelector(':scope > .sub-menu')) { return; } // có cấp 3 → giữ 1 cột riêng
				if (!misc) {
					misc = document.createElement('li');
					misc.className = 'cp-mega__misc';
					var list = document.createElement('ul');
					list.className = 'sub-menu';
					misc.appendChild(list);
					sub.appendChild(misc);
				}
				misc.firstChild.appendChild(item);
			});
		});

		/* 0b. CP1.4b — cột nào là cột ĐẦU TIÊN của một HÀNG mới (panel xuống dòng)?
		      Panel dùng grid `auto-fit` nên CSS không biết cột nào xuống hàng → đo `offsetTop`
		      rồi gắn `.cp-row-start` để CSS bỏ gạch dọc + thụt lề trái cho cột đầu hàng đó. */
		function markRowStarts() {
			Array.prototype.forEach.call(nav.querySelectorAll('.sub-menu--mega'), function (sub) {
				var rowTop = null;
				Array.prototype.forEach.call(sub.children, function (li) {
					li.classList.remove('cp-row-start');
					var top = li.offsetTop;
					if (null === rowTop || top > rowTop + 1) {
						li.classList.add('cp-row-start');
						rowTop = top;
					}
				});
			});
		}

		markRowStarts();
		// Font load xong có thể đổi số cột mỗi hàng → đánh dấu lại.
		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(markRowStarts);
		}

		/* 1. Gắn nút mở/đóng cho mọi mục có menu con (mọi cấp). */
		Array.prototype.forEach.call(nav.querySelectorAll('li.menu-item-has-children'), function (li) {
			var link = li.querySelector(':scope > a');
			if (!link) { return; }

			var toggle = document.createElement('button');
			toggle.type = 'button';
			toggle.className = 'cp-nav__toggle';
			toggle.setAttribute('aria-expanded', 'false');
			toggle.setAttribute('aria-label', link.textContent.trim() + ' — mở menu con');
			toggle.appendChild(document.createElement('span'));
			li.insertBefore(toggle, link.nextSibling);

			toggle.addEventListener('click', function (e) {
				e.preventDefault();
				var open = !li.classList.contains('is-open');

				// Accordion: đóng các mục cùng cấp trước khi mở mục này.
				Array.prototype.forEach.call(li.parentNode.children, function (sibling) {
					if (sibling === li || !sibling.classList.contains('is-open')) { return; }
					sibling.classList.remove('is-open');
					var btn = sibling.querySelector(':scope > .cp-nav__toggle');
					if (btn) { btn.setAttribute('aria-expanded', 'false'); }
				});

				li.classList.toggle('is-open', open);
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
		});

		/* 2. Burger. */
		burger.addEventListener('click', function () {
			setOpen(!nav.classList.contains('is-open'));
		});

		/* 3. Đóng panel: Esc / click ra ngoài / lên desktop. */
		document.addEventListener('keydown', function (e) {
			if ('Escape' === e.key && nav.classList.contains('is-open')) {
				setOpen(false);
				burger.focus();
			}
		});

		document.addEventListener('click', function (e) {
			if (!nav.classList.contains('is-open')) { return; }
			if (nav.contains(e.target) || burger.contains(e.target)) { return; }
			setOpen(false);
		});

		window.addEventListener('resize', function () {
			if (!isMobile() && nav.classList.contains('is-open')) { setOpen(false); }
			markRowStarts(); // số cột mỗi hàng đổi theo bề rộng → đánh dấu lại cột đầu hàng
		});
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
