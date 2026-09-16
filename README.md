# Button call/zalo - TungLeAds (`button-call-zalo-tungleads`)

Widget liên hệ nổi cho **caophat.vn**: cụm nút **Gọi điện + Zalo** neo sát **lề phải**, hover để mở rộng; in ở cuối **mọi trang** (hook `wp_footer`, prio 20).

- **Giao diện**: giữ **nguyên** thiết kế “TÙNG LÊ ADS — CONTACT FLOATING WIDGET v1.3” (class `wd-contact-*`) — kể cả mobile giữ đúng layout desktop.
- **Dữ liệu**: số điện thoại / Zalo nhập ở **Settings → Button Call/Zalo**, không cần sửa code.

## Cài đặt

**Settings → Button Call/Zalo**

| Mục | Việc |
|---|---|
| Hiện widget | Bỏ tick là ẩn cả cụm nút (CSS/JS cũng không nạp) |
| Nút liên hệ (repeater) | Mỗi dòng: **Kiểu** (Gọi điện / Zalo / Facebook / Link tuỳ chỉnh) · **Nhãn nhỏ** · **Giá trị** (số hoặc link) · **Màu sắc** (màu nút + màu chữ) · **Ảnh icon** · **Bật**; thêm/xoá dòng ngay trong trang |
| Custom CSS / JS | 2 ô nhập riêng cho plugin này; chỉ in ra khi widget đang hiển thị |

**Kiểu & giá trị tương ứng**

| Kiểu | Giá trị cần nhập | Link sinh ra |
|---|---|---|
| Gọi điện | số điện thoại (`0834.021.021`) | `tel:0834021021` |
| Zalo | số điện thoại | `https://zalo.me/0834021021` |
| Facebook | username/ID trang (`caophatdoor`) **hoặc** dán link fanpage | `https://m.me/caophatdoor` (giữ nguyên nếu bạn dán link `http…`) |
| Link tuỳ chỉnh | link bất kỳ: `https://…`, `mailto:a@b.com`, `tel:…`, `sms:…` (thiếu scheme ⇒ tự thêm `https://`) | dùng đúng giá trị đã nhập |

**Màu sắc & icon**

- Để **trống cả 2 ô màu** = dùng màu mặc định của kiểu (đỏ cho Gọi · xanh cho Zalo · xanh dương cho Facebook · xám cho Link).
- **Icon mặc định (v1.1.2):** nút **Gọi** = điện thoại + **2 gợn sóng** (Feather `phone-call`, `tlcz_icon_default()` nhánh `phone`, 3 `path`, `stroke: currentColor` nét 2px/22px); **Zalo** / **Facebook** = ô tròn chữ `Z` / `f`; **Link tuỳ chỉnh** = quả cầu. Muốn xem lại các phương án khác: xem lịch sử phiên 2026-09-16 trong `.ai/WORKLOG.md` của child theme.
- Nhập màu = đè màu/gradient mặc định; màu chữ cũng đổi luôn ô tròn chữ `Z`/`f` và icon SVG.
- **Ảnh icon** tải lên (nút *Chọn ảnh* → thư viện Media) sẽ **thay thế** icon mặc định; nên dùng ảnh vuông ~100×100px, nền trong suốt.

> v1.1.3 (2026-09-16): **hạ cụm nút xuống thấp hơn** — `.wd-contact-widget { top: 50% → 75% }` (yêu cầu Tùng). Cụm 210×256 nằm vừa màn hình ở 1440×900 / 1280×800 / 1024×600 / 390×844 / 390×667; **khung nhìn thấp hơn ~512px thì đáy cụm bị cắt** (cần 0,25×H ≥ 128px).
>
> v1.1.2 (2026-09-16): **đổi icon nút GỌI** sang “điện thoại + 2 gợn sóng” (Feather `phone-call`) — Tùng chốt qua trang xem thử 6 phương án (chọn phương án C). Icon cũ là ống nghe nét trơn 1 path.
>
> v1.1.1 (2026-09-16): thêm header `Author URI: https://tungleads.com/` ⇒ ở **/wp-admin/plugins.php**, dòng `Phiên bản 1.1.1 | Bởi Tung Le Ads` có **“Tung Le Ads” là link** (WP core tự bọc `<a>` khi có Author URI — xem `class-wp-plugins-list-table.php`).
>
> v1.1.0 (2026-09-16): thêm kiểu **Facebook** + **Link tuỳ chỉnh**, **màu nút/màu chữ riêng cho từng nút**, **upload ảnh icon**, **Custom CSS/JS**, và 2 dòng ghi công (xem cuối trang Settings).
>
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
| `assets/admin.js` | Thêm/xoá dòng nút · color picker (Iris của WP) cho 2 ô màu · chọn ảnh icon qua thư viện Media · đổi placeholder theo kiểu (chỉ nạp ở trang cài đặt) |

CSS/JS **chỉ nạp khi widget thật sự in** (có ≥ 1 nút đang bật) và version theo `filemtime()` — không cần build step.

## Deploy

Plugin nằm trong `wordpress/wp-content/plugins/` (đã gitignore ở repo gốc) → **git repo riêng**, ship qua `deploy-caophat.sh` ở gốc. **Chỉ đẩy production khi Tùng yêu cầu rõ ràng.** Requires **PHP 8.2**.
