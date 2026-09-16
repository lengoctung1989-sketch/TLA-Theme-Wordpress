/**
 * Button call/zalo - TungLeAds — JS cho widget liên hệ nổi.
 *
 * Bản gốc trong thiết kế v1.3 chỉ `console.log` khi bấm; ở đây thay bằng cách BẮN SỰ KIỆN
 * `wd-contact:click` (bubbles) để site tự gắn tracking (GTM/GA4/Meta) mà không cần sửa plugin:
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

	document.addEventListener('click', function (e) {
		var item = e.target.closest ? e.target.closest('.wd-contact-item') : null;

		if (!item) {
			return;
		}

		var label = item.querySelector('strong');
		var type = item.classList.contains('wd-contact-zalo') ? 'zalo' : 'phone';
		var phone = item.getAttribute('data-tlcz-value')
			|| (label ? label.textContent.replace(/\D/g, '') : '');

		e.target.dispatchEvent(new CustomEvent('wd-contact:click', {
			bubbles: true,
			detail: {
				type: type,
				phone: phone
			}
		}));
	});
})();
