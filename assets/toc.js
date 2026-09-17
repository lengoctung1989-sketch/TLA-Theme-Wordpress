/**
 * CP8 — Mục lục nội dung (plugin PL Tiện Ích - TungLeAds). Hai phần chạy ĐỘC LẬP nhau:
 *
 * A) KHỐI MỤC LỤC TRONG NỘI DUNG (`.tlcp-toc-inline`) — thẻ `<details>` do PHP in nên mở/thu được
 *    **không cần JS**; JS chỉ thêm cuộn mượt + bù chiều cao header sticky cho các link mục.
 * B) NÚT DỌC + DRAWER (`.tlcp-toc`) — mở/đóng drawer (`is-open` + `aria-expanded` + `inert`),
 *    cuộn tới heading, scroll-spy tô nền mục đang xem, đóng bằng ✕ / `Esc` / bấm ra ngoài.
 *
 * Hai phần dùng chung `offsetTop()` + `goTo()`. Bật/tắt từng phần ở **Settings → PL Tiện Ích**
 * (tắt phần nào thì markup phần đó không có ⇒ hàm tự bỏ qua, không lỗi).
 *
 * Lưu ý: Cuộn phải **DÒ LẠI**: ảnh `loading="lazy"` tải xong làm trang CAO THÊM (đo 2026-09-17:
 * `scrollHeight` 43469 → 46010 ⇒ cuộn đúng 5493px vẫn hụt 1565px). `goTo()` bám đích tối đa ~3s và
 * **tự huỷ ngay khi người dùng tự cuộn** (`wheel` / `touchstart` / `keydown`).
 *
 * Không dùng thư viện ngoài.
 */
(function () {
	'use strict';

	/* ============================== DÙNG CHUNG ============================== */

	/** Bù chiều cao header sticky (`.cp-header`) + thanh admin khi đang đăng nhập. */
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

	/**
	 * Ghi độ bù vào biến CSS `--tlcp-scroll-pad` (CSS đặt `html { scroll-padding-top: … }`):
	 * nhờ vậy DEEP LINK `…#ten-muc` (Google “jump to”, link dán cho khách) cũng dừng dưới header
	 * sticky, không cần JS can thiệp vào cú nhảy của trình duyệt.
	 */
	function syncScrollPad() {
		document.documentElement.style.setProperty('--tlcp-scroll-pad', offsetTop() + 'px');
	}

	syncScrollPad();
	window.addEventListener('resize', syncScrollPad);
	window.addEventListener('load', syncScrollPad);

	/**
	 * Cuộn tới 1 phần tử rồi DÒ LẠI cho tới khi đúng đích.
	 *
	 * Lưu ý: Quãng đường DÀI thì nhảy THẲNG (`auto`), không cuộn mượt: ảnh `loading="lazy"` phía trên
	 * nạp dần trong lúc cuộn mượt ⇒ trang cao thêm liên tục và đích “chạy xa mãi”. Đo 2026-09-17 ở
	 * `/bao-gia-cua-nhua-gia-re-tphcm/`: đích 36.567px bị đẩy tới 78.599px, 12 lần dò trong 3s vẫn
	 * không đuổi kịp; trong khi nhảy thẳng thì ảnh phía trên KHÔNG kịp vào khung nhìn ⇒ hầu như
	 * không đổi vị trí, chỉ còn sai số nhỏ do ảnh quanh đích vừa tải ⇒ dò 2–3 lần là khớp.
	 * Quãng ngắn (≤ 1,5 màn hình) vẫn cuộn mượt cho êm.
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
			var delta = Math.abs(window.pageYOffset - want);

			if (delta > 8) {
				var instant = delta > window.innerHeight * 1.5;
				window.scrollTo({ top: want, behavior: instant ? 'auto' : 'smooth' });
			}

			tries++;
			if (tries < 12) {
				window.setTimeout(step, 250);
			}
		};

		step();
	}

	/**
	 * Gắn hành vi cho các link mục: chặn nhảy mặc định → cuộn mượt có bù header → cập nhật `#id`
	 * trên thanh địa chỉ (không làm trình duyệt nhảy lần 2). `after()` chạy sau khi cuộn (dùng để
	 * đóng drawer trên mobile).
	 */
	function bindAnchors(links, after) {
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
				if ('function' === typeof after) { after(); }
			});
		});
	}

	/* ==================== A. KHỐI MỤC LỤC TRONG NỘI DUNG ==================== */
	(function () {
		var box = document.querySelector('.tlcp-toc-inline');
		if (!box) { return; }

		bindAnchors(Array.prototype.slice.call(box.querySelectorAll('.tlcp-toc-inline__link')));
	})();

	/* ========================= B. NÚT DỌC + DRAWER ========================= */
	(function () {
		var root = document.querySelector('.tlcp-toc');
		if (!root) { return; }

		var toggle = root.querySelector('.tlcp-toc__toggle');
		var panel = root.querySelector('.tlcp-toc__panel');
		var closeBtn = root.querySelector('.tlcp-toc__close');
		var list = root.querySelector('.tlcp-toc__list');

		if (!toggle || !panel || !list) { return; }

		var links = Array.prototype.slice.call(list.querySelectorAll('.tlcp-toc__link'));
		if (!links.length) { return; }

		var spyOn = !root.classList.contains('tlcp-toc--no-spy');
		var items = links.map(function (a) {
			var id = (a.getAttribute('href') || '').replace(/^#/, '');
			return { a: a, li: a.parentElement, el: id ? document.getElementById(id) : null };
		}).filter(function (it) { return !!it.el; });

		/** Mở/đóng drawer: class `is-open` (CSS trượt panel) + `aria-expanded` + `inert` cho a11y. */
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

		// Trên mobile drawer che gần hết màn hình ⇒ bấm mục thì đóng luôn cho thấy nội dung.
		bindAnchors(links, function () {
			if (window.matchMedia('(max-width: 768px)').matches) {
				setOpen(false);
			}
		});

		/* Scroll-spy: mục đang xem được gắn `is-active` (CSS tô nền) + tự cuộn trong danh sách. */
		if (spyOn && items.length) {
			var ticking = false;

			var mark = function () {
				var off = offsetTop() + 4;
				var current = null;

				items.forEach(function (it) {
					if (it.el.getBoundingClientRect().top - off <= 0) { current = it; }
				});

				// Ở đáy trang thì chọn mục cuối (mục cuối có thể không bao giờ chạm đỉnh).
				if (window.innerHeight + window.pageYOffset >= document.body.scrollHeight - 4) {
					current = items[items.length - 1];
				}

				items.forEach(function (it) {
					var on = current === it;
					it.li.classList.toggle('is-active', on);
					if (on) {
						it.a.setAttribute('aria-current', 'true');
					} else {
						it.a.removeAttribute('aria-current');
					}
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
})();
