/**
 * CP3.9 — Tab "Đánh giá" ở trang chi tiết sản phẩm.
 *
 * 1. Thanh lọc theo số sao (`.cp-rv__chip`): mỗi `<li>` đánh giá được PHP gắn class `cp-rv-star-N`
 *    (filter `comment_class` trong `inc/woocommerce.php`). Bấm chip ⇒ ẩn/hiện `<li>` bằng class
 *    `.is-hidden` (KHÔNG dùng thuộc tính `hidden` vì `display` của `li.review` có thể bị CSS theme
 *    cha đè, còn class của mình thì mình kiểm soát).
 * 2. Nút "Đánh giá ngay" (`.cp-rv__open`): cuộn mượt tới form rồi focus ô chọn sao — chỉ can thiệp
 *    khi form CÓ trên trang; JS tắt thì nút vẫn là anchor `#review_form` bình thường.
 *
 * Chỉ chạy ở trang chi tiết SP (script được enqueue có điều kiện `is_product()`), không phụ thuộc
 * thư viện ngoài.
 */
(function () {
	'use strict';

	var box = document.querySelector('.cp-rv');
	if (!box) {
		return;
	}

	/* ---------- 1. Lọc theo số sao ---------- */
	var list = box.querySelector('.commentlist');
	var chips = box.querySelectorAll('.cp-rv__chip');

	if (list && chips.length) {
		var items = list.children;
		var noResult = document.createElement('p');

		noResult.className = 'cp-rv__noresult';
		noResult.hidden = true;
		list.parentNode.insertBefore(noResult, list.nextSibling);

		var apply = function (star) {
			var shown = 0;

			Array.prototype.forEach.call(items, function (li) {
				var ok = 'all' === star || li.classList.contains('cp-rv-star-' + star);

				li.classList.toggle('is-hidden', !ok);
				if (ok) {
					shown++;
				}
			});

			noResult.hidden = shown > 0;
			if (0 === shown) {
				noResult.textContent = 'Không có đánh giá nào ở mức ' + star + ' sao.';
			}
		};

		Array.prototype.forEach.call(chips, function (chip) {
			chip.addEventListener('click', function () {
				var star = chip.getAttribute('data-cp-star') || 'all';

				Array.prototype.forEach.call(chips, function (other) {
					var on = other === chip;

					other.classList.toggle('is-active', on);
					other.setAttribute('aria-pressed', on ? 'true' : 'false');
				});

				apply(star);
			});
		});
	}

	/* ---------- 2. Nút "Đánh giá ngay" → form ---------- */
	var openBtn = box.querySelector('.cp-rv__open');
	var form = box.querySelector('#review_form');

	if (openBtn && form) {
		openBtn.addEventListener('click', function (e) {
			// WooCommerce PHAO `select#rating` rồi dựng widget sao `p.stars` (JS `single-product.js`)
			// ⇒ focus vào thứ ĐANG HIỆN, đừng focus vào select đã bị ẩn (focus không đi đâu cả).
			var rating = form.querySelector('#rating');
			var target = rating && rating.offsetParent ? rating : form.querySelector('.stars a');
			if (!target) {
				target = form.querySelector('#comment');
			}

			// Cho trình duyệt tự nhảy anchor nếu không hỗ trợ cuộn mượt.
			if (typeof form.scrollIntoView !== 'function' || !target) {
				return;
			}

			e.preventDefault();
			form.scrollIntoView({ behavior: 'smooth', block: 'center' });
			window.setTimeout(function () {
				target.focus({ preventScroll: true });
			}, 350);
		});
	}
})();
