# Button call/zalo - TungLeAds (`button-call-zalo-tungleads`)

Widget liên hệ nổi cho **caophat.vn**: cụm nút **Gọi điện + Zalo** neo sát **lề phải**, hover để mở rộng; in ở cuối **mọi trang** (hook `wp_footer`, prio 20).

- **Giao diện**: giữ **nguyên** thiết kế “TÙNG LÊ ADS — CONTACT FLOATING WIDGET v1.3” (class `wd-contact-*`) — kể cả mobile giữ đúng layout desktop.
- **Dữ liệu**: số điện thoại / Zalo nhập ở **Settings → Button Call/Zalo**, không cần sửa code.

## Cài đặt

**Settings → Button Call/Zalo**

| Mục | Việc |
|---|---|
| Hiện widget | Bỏ tick là ẩn cả cụm nút (CSS/JS cũng không nạp) |
| Nút liên hệ (repeater) | Mỗi dòng: **Kiểu** (Gọi điện / Zalo) · **Nhãn nhỏ** · **Số** · **Bật**; thêm/xoá dòng ngay trong trang |

> v1.0.1 (2026-09-16): bỏ tuỳ chọn “Ẩn nút gọi nổi của theme (`.cp-fab`)” — theme Cao Phát đã **xoá hẳn** nút gọi nổi `.cp-fab` (CP1.3) nên tuỳ chọn đó không còn gì để ẩn.

Khi nhập cần biết:

- Số có dấu chấm/khoảng trắng vẫn dùng được: `0834.021.021` → `tel:0834021021` và `https://zalo.me/0834021021` (mọi ký tự không phải số bị bỏ).
- **Nút ĐẦU TIÊN** nếu là “Gọi điện” mới có hiệu ứng **pulse** (CSS dùng `:first-child` — đúng thiết kế v1.3).
- Bỏ tick **Bật** = tạm ẩn 1 nút mà không xoá số.
- Tất cả nút bị tắt = widget tự ẩn. **Xoá hết dòng rồi lưu = quay về 4 số mặc định** (Gọi 0834.021.021 · Gọi 0834.484.484 · Zalo 2 số đó).
- Tối đa **8** nút (hằng `TLCZ_MAX_ROWS`).

## Filter cho lập trình viên

| Filter | Kiểu | Việc |
|---|---|---|
| `tlcz_should_render` | `bool` | Tắt widget theo điều kiện trang (ví dụ chỉ ở trang chi tiết SP) |
| `tlcz_button_url` | `string` (nhận `$url`, `$btn`) | Đổi link của 1 nút — ví dụ trỏ về Zalo OA |

```php
// Chỉ hiện widget ở trang chủ + trang chi tiết sản phẩm
add_filter( 'tlcz_should_render', fn( $on ) => $on && ( is_front_page() || is_product() ) );

// Nút Zalo trỏ về Zalo OA
add_filter( 'tlcz_button_url', function ( $url, $btn ) {
	return 'zalo' === $btn['type'] ? 'https://zalo.me/oa-cao-phat' : $url;
}, 10, 2 );
```

## Tracking khi khách bấm

JS bắn sự kiện `wd-contact:click` (bubbles) với `detail = { type: 'phone'|'zalo', phone: '0834021021' }`. Bản trong thiết kế chỉ `console.log` — đã bỏ log ở production:

```js
document.addEventListener('wd-contact:click', function (e) {
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({ event: 'contact_click', contact_type: e.detail.type, phone: e.detail.phone });
});
```

## File

| File | Việc |
|---|---|
| `button-call-zalo-tungleads.php` | Option `tlcz_settings`, in widget, trang Settings, sanitize |
| `assets/contact-widget.css` | CSS **nguyên bản** thiết kế v1.3 (chỉ thêm ghi chú nguồn ở đầu file) |
| `assets/contact-widget.js` | Bắn `wd-contact:click` |
| `assets/admin.js` | Thêm/xoá dòng nút (chỉ nạp ở trang cài đặt) |

CSS/JS **chỉ nạp khi widget thật sự in** (có ≥ 1 nút đang bật) và version theo `filemtime()` — không cần build step.

## Deploy

Plugin nằm trong `wordpress/wp-content/plugins/` (đã gitignore ở repo gốc) → **git repo riêng**, ship qua `deploy-caophat.sh` ở gốc. **Chỉ đẩy production khi Tùng yêu cầu rõ ràng.** Requires **PHP 8.2**.
