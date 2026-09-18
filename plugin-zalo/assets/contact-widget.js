/**
 * Button call/zalo - TungLeAds — JS cho widget liên hệ nổi.
 *
 * v1.2.0 (Tùng 2026-09-16) — “NHẤP 2 LẦN”:
 *   • Lần nhấp 1 → CHẶN điều hướng, gắn class `.is-armed` cho nút ⇒ nút DI CHUYỂN (trượt sang trái +
 *     mở rộng ra hiện nhãn) y như trạng thái `:hover` trên desktop. Máy cảm ứng không có hover nên
 *     đây là cách duy nhất để khách THẤY nút trước khi gọi.
 *   • Lần nhấp 2 (cùng nút đó, trong vòng `ARM_MS`) → đi link như bình thường + bắn `wd-contact:click`.
 *   • Quá `ARM_MS` không nhấp lại, hoặc nhấp ra ngoài, hoặc bấm Esc → huỷ trạng thái đã chạm.
 *   • Click do BÀN PHÍM (Enter/Space có `event.detail === 0`) ⇒ đi link NGAY, không bắt bấm 2 lần.
 *
 * TRACKING — giữ nguyên sự kiện cũ, thêm 1 sự kiện cho lần nhấp đầu:
 *     `wd-contact:arm`   → lần nhấp ĐẦU (khách mới “chạm” nút, CHƯA mở liên hệ)
 *     `wd-contact:click` → lần nhấp THỨ HAI (khách THẬT SỰ mở liên hệ) — dùng cái này để đếm chuyển đổi
 * Cả hai đều `bubbles: true`, `detail = { type, value, phone }`:
 *
 *     document.addEventListener('wd-contact:click', function (e) {
 *         window.dataLayer = window.dataLayer || [];
 *         window.dataLayer.push({ event: 'contact_click', contact_type: e.detail.type, phone: e.detail.phone });
 *     });
 *
 * UỶ NHIỆM từ `document` (không gắn trực tiếp vào `.wd-contact-widget`): script nằm ở
 * `wp_print_footer_scripts` (wp_footer prio 20) nên có thể chạy TRƯỚC markup widget ⇒ gắn trực tiếp
 * bằng `querySelector` sẽ ra null và widget “câm”. Uỷ nhiệm còn chạy được nếu markup được chèn muộn.
 * Không log ra console ở production.
 */
(function () {
	'use strict';

	// Bao lâu thì “quên” lần nhấp đầu (ms). Đổi 1 chỗ này là đổi cho cả widget.
	var ARM_MS = 3000;

	var armed = null;   // nút đang ở trạng thái “đã nhấp lần đầu”
	var timer = 0;      // hẹn giờ huỷ trạng thái đó
	var live = null;    // vùng aria-live cho screen reader (tạo khi cần)

	function clearArm() {
		if (armed) {
			armed.classList.remove('is-armed');
			armed = null;
		}
		if (timer) {
			window.clearTimeout(timer);
			timer = 0;
		}
	}

	// Screen reader không thấy nút “mở rộng” ⇒ đọc hộ câu xác nhận.
	function announce(text) {
		if (!live) {
			live = document.createElement('span');
			live.setAttribute('aria-live', 'polite');
			live.setAttribute('role', 'status');
			live.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap;';
			document.body.appendChild(live);
		}
		live.textContent = text;
	}

	document.addEventListener('click', function (e) {
		var item = e.target.closest ? e.target.closest('.wd-contact-item') : null;

		if (!item) {
			return;
		}

		var label = item.querySelector('strong');
		var type = item.getAttribute('data-tlcz-type')
			|| (item.classList.contains('wd-contact-zalo') ? 'zalo' : 'phone');
		var value = item.getAttribute('data-tlcz-value')
			|| (label ? label.textContent.replace(/\D/g, '') : '');
		var detail = { type: type, value: value, phone: value };

		// ---- Lần nhấp ĐẦU: chỉ “di chuyển” nút, KHÔNG mở link. ----
		// `e.detail > 0` = chuột/cảm ứng. Bàn phím (Enter/Space) có `detail === 0` ⇒ đi link luôn.
		if (item !== armed && e.detail > 0) {
			e.preventDefault();

			clearArm();
			armed = item;
			item.classList.add('is-armed');
			timer = window.setTimeout(clearArm, ARM_MS);

			item.dispatchEvent(new CustomEvent('wd-contact:arm', { bubbles: true, detail: detail }));

			var small = item.querySelector('small');
			announce(
				(small ? small.textContent.trim() + ': ' : '')
				+ (label ? label.textContent.trim() : '')
				+ ' — bấm lần nữa để mở'
			);
			return;
		}

		// ---- Lần nhấp THỨ HAI (hoặc click bàn phím): mở link + bắn tracking. ----
		e.target.dispatchEvent(new CustomEvent('wd-contact:click', {
			bubbles: true,
			detail: detail
		}));

		clearArm();
	});

	// Nhấp ra NGOÀI nút ⇒ huỷ trạng thái đã chạm (để nút không mở rộng “dính” mãi).
	document.addEventListener('pointerdown', function (e) {
		if (armed && !(e.target.closest && e.target.closest('.wd-contact-item'))) {
			clearArm();
		}
	}, { passive: true });

	// Esc ⇒ huỷ trạng thái đã chạm.
	document.addEventListener('keydown', function (e) {
		if ('Escape' === e.key) {
			clearArm();
		}
	});
})();

