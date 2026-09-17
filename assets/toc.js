/**
 * CP8 — Mục lục nội dung (plugin tl-site-caophat).
 *
 * 1. Nút dọc mở/đóng DRAWER (class `is-open` + `aria-expanded` + thuộc tính `inert` cho a11y).
 * 2. Bấm 1 mục: cuộn MƯỢT tới heading, CÓ BÙ CHIỀU CAO header sticky (theme cao thay đổi theo khổ:
 *    77/161/186px — đo lúc bấm chứ không hard-code) + thanh admin (nếu đang đăng nhập).
 * 3. Scroll-spy: mục đang xem được gắn `is-active` (CSS tô nền) + tự cuộn trong danh sách cho thấy.
 * 4. Đóng: nút ✕, phím Esc, hoặc bấm ra ngoài drawer. Không dùng thư viện ngoài.
 *
 * Không JS thì nút/drawer ẩn (`visibility: hidden` trong CSS) ⇒ trang vẫn bình thường, không lỗi.
 */
(function () {
	'use strict';

	// Markup do PHP in ở `wp_footer` prio 5 ⇒ LUÔN có trước thẻ script này (prio 20).
	var root = document.querySelector('.tlcp-toc');
	if (!root) {
		return;
	}

	var toggle = root.querySelector('.tlcp-toc__toggle');
	var panel = root.querySelector('.tlcp-toc__panel');
	var closeBtn = root.querySelector('.tlcp-toc__close');
	var list = root.querySelector('.tlcp-toc__list');
	if (!toggle || !panel || !list) {
		return;
	}

	var links = Array.prototype.slice.call(list.querySelectorAll('.tlcp-toc__link'));
	if (!links.length) {
		return;
	}

	var spyOn = !root.classList.contains('tlcp-toc--no-spy');
	var items = links.map(function (a) {
		var id = (a.getAttribute('href') || '').replace(/^#/, '');
		return { a: a, li: a.parentElement, el: id ? document.getElementById(id) : null };
	}).filter(function (it) { return !!it.el; });

	/* ---------- Bù chiều cao header sticky + admin bar ---------- */
	function offsetTop() {
		var h = 0;
		var header = document.querySelector('.cp-header');
		if (header) {
			h += header.getBoundingClientRect().height;
		}
		var bar = document.getElementById('wpadminbar');
		if (bar && bar.getBoundingClientRect().height > 0) {
			h += bar.getBoundingClientRect().height;
		}
		return Math.round(h + 14);
	}

	/* ---------- 1. Mở / đóng ---------- */
	function setOpen(open) {
		root.classList.toggle('is-open', open);
		document.body.classList.toggle('tlcp-toc-open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (open) {
			panel.removeAttribute('inert');
			window.setTimeout(function () {
				var act = list.querySelector('.tlcp-toc__item.is-active .tlcp-toc__link') || links[0];
				if (act) { act.focus({ preventScroll: true }); }
			}, 260);
		} else {
			panel.setAttribute('inert', '');
		}
	}

	toggle.addEventListener('click', function () {
		setOpen(!root.classList.contains('is-open'));
	});

	if (closeBtn) {
		closeBtn.addEventListener('click', function () {
			setOpen(false);
			toggle.focus({ preventScroll: true });
		});
	}

	document.addEventListener('keydown', function (e) {
		if ('Escape' === e.key && root.classList.contains('is-open')) {
			setOpen(false);
			toggle.focus({ preventScroll: true });
		}
	});

	document.addEventListener('mousedown', function (e) {
		if (!root.classList.contains('is-open')) { return; }
		if (panel.contains(e.target) || toggle.contains(e.target)) { return; }
		setOpen(false);
	});

	/* ---------- 2. Bấm mục → cuộn tới heading ---------- */

	/**
	 * Cuộn tới 1 heading rồi DÒ LẠI vài lần: ảnh `loading="lazy"` phía trên tải xong làm trang CAO
	 * THÊM (đo 2026-09-17 ở bài `/bao-gia-cua-nhua-gia-re-tphcm/`: cuộn đúng 5493px nhưng
	 * `body.scrollHeight` tăng 43469 → 46010 ⇒ heading bị đẩy xuống 1565px khỏi khung nhìn).
	 * Vòng dò chạy tối đa ~3s và TỰ HUỶ ngay khi người dùng tự cuộn/ bấm phím.
	 */
	function goTo(el) {
		var tries = 0;
		var stopped = false;
		var stop = function () { stopped = true; };

		['wheel', 'touchstart', 'keydown'].forEach(function (ev) {
			window.addEventListener(ev, stop, { passive: true, once: true });
		});

		var step = function () {
			if (stopped) { return; }
			var want = Math.max(0, el.getBoundingClientRect().top + window.pageYOffset - offsetTop());
			if (Math.abs(window.pageYOffset - want) > 8) {
				window.scrollTo({ top: want, behavior: 'smooth' });
			}
			tries++;
			if (tries < 12) {
				window.setTimeout(step, 250);
			}
		};

		step();
	}

	links.forEach(function (a) {
		a.addEventListener('click', function (e) {
			var id = (a.getAttribute('href') || '').replace(/^#/, '');
			var el = id ? document.getElementById(id) : null;
			if (!el) { return; }

			e.preventDefault();
			goTo(el);
			if (window.history && window.history.pushState) {
				window.history.pushState(null, '', '#' + id);
			}
			if (window.matchMedia('(max-width: 768px)').matches) {
				setOpen(false);
			}
		});
	});

	/* ---------- 3. Scroll-spy ---------- */
	if (spyOn && items.length) {
		var ticking = false;
		var mark = function () {
			var off = offsetTop() + 4;
			var current = null;
			items.forEach(function (it) {
				if (it.el.getBoundingClientRect().top - off <= 0) { current = it; }
			});
			// Ở đáy trang thì luôn chọn mục cuối (mục cuối có thể không bao giờ chạm đỉnh).
			if (window.innerHeight + window.pageYOffset >= document.body.scrollHeight - 4) {
				current = items[items.length - 1];
			}
			items.forEach(function (it) {
				var on = current === it;
				it.li.classList.toggle('is-active', on);
				if (on) { it.a.setAttribute('aria-current', 'true'); } else { it.a.removeAttribute('aria-current'); }
			});
			if (current && root.classList.contains('is-open')) {
				var box = current.li.getBoundingClientRect();
				var listBox = list.getBoundingClientRect();
				if (box.top < listBox.top || box.bottom > listBox.bottom) {
					current.li.scrollIntoView({ block: 'nearest' });
				}
			}
			ticking = false;
		};

		window.addEventListener('scroll', function () {
			if (ticking) { return; }
			ticking = true;
			window.requestAnimationFrame(mark);
		}, { passive: true });
		window.addEventListener('resize', mark);
		mark();
	}
})();
