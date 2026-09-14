# TL Site — Cao Phát (`tl-site-caophat`)

Plugin site-specific cho **caophat.vn**. Giữ **tầng dữ liệu / hành vi** riêng của site, tách khỏi theme (`tungleads-theme-cp`) để đổi giao diện không làm mất data.

Ranh giới: theme = trình bày · plugin = *dữ liệu gì tồn tại* + *hành vi*.

## Hiện có

| Phần | Việc |
|---|---|
| Tracking (`wp_head` prio 1 + `wp_body_open`) | GTM `GTM-KCVHR8P`, GA4 `G-L37N4Q06LP`, Google Ads `AW-10871632223`, Meta Pixel `5267684856622253` — chuyển từ Flatsome → Advanced → Global HTML |
| Thông số kỹ thuật SP | Tab riêng trong "Dữ liệu sản phẩm" (admin) — 8 trường lưu meta `_tlcp_spec_*` (`size`, `door_type`, `leaf`, `frame`, `features`, `origin`, `warranty`, `note`). Sửa danh sách: `tlcp_spec_fields()`. Frontend hiển thị ở trang chi tiết SP (child theme đọc meta). |
| Hotline chi nhánh | Option `tlcp_support_branches`, sửa ở **Settings → Cao Phát** (mỗi dòng: `Tên \| Số`). Theme đọc qua `tlcp_support_branches()`. |
| Đặt hàng nhanh (CP3.3) | Handler AJAX `cp_quick_order` — nhận form từ popup ở trang chi tiết SP và tạo **đơn WooCommerce thật** (COD, trạng thái "Đang xử lý"). Chống spam: nonce + honeypot + 5 đơn/IP/10 phút. |

### Đặt hàng nhanh (CP3.3)

- Popup + JS nằm ở child theme (`tungleads-theme-cp`); **tạo đơn nằm ở plugin này** (`tlcp_quick_order_handle()`).
- Đơn tạo ra: `created_via = cp-quick-order`, thanh toán COD (nếu gateway bật), trạng thái **Đang xử lý** → tự trừ tồn kho + gửi email "Đơn hàng mới" cho admin. Lọc đơn nhanh về sau: `wc_get_orders( array( 'created_via' => 'cp-quick-order' ) )`.
- Khách không cần tài khoản — đơn guest, billing/shipping lấy từ thông tin khách nhập.
- Chặn: nonce `tlcp_quick_order` · honeypot `cp_hp` · rate-limit **5 đơn/IP/10 phút** (hằng `TL_CP_QO_MAX` / `TL_CP_QO_WINDOW`, đếm bằng transient `tlcp_qo_<md5 ip>`).
- Giá luôn lấy từ server (`$product->get_price()`), không tin dữ liệu client gửi lên.

### Hotline chi nhánh

- Nhập ở **Settings → Cao Phát**: mỗi dòng một chi nhánh, dạng `CN Quận 7 | 0834.484.484`. Thứ tự dòng = thứ tự hiển thị.
- Để trống rồi lưu = quay về danh sách mặc định (`tlcp_support_branches_default()` trong `tl-site-caophat.php`).
- Dòng thiếu dấu `|` hoặc thiếu vế nào sẽ bị bỏ qua (không gây lỗi hiển thị).
- Theme đọc bằng `function_exists('tlcp_support_branches')` → nếu plugin tắt, theme tự dùng danh sách dự phòng; nếu muốn ghi đè bằng code: filter `cp_support_branches`.

### Kích hoạt tracking — đúng trình tự

1. Xác minh lại 4 ID (so với tài khoản GA/Ads/Meta hiện tại).
2. Cân nhắc: GTM thường đã chứa GA + Ads + Pixel → nếu đúng, xoá 3 khối gtag/pixel trong file, chỉ giữ GTM. Mặc định file sao y production (cả 4).
3. **Cùng lúc**: bật plugin này **và** xoá đoạn scripts tương ứng khỏi Flatsome Global HTML. Không để cả hai cùng chạy (double pageview/conversion).
4. Test: GA DebugView, Meta Pixel Helper, Tag Assistant.

## Về sau (chưa làm)

CPT / taxonomy / form báo giá / webhook → thêm vào `tl-site-caophat.php` ở khối stub cuối file. Không cho vào theme.

## Deploy

Plugin này nằm trong `wordpress/` (gitignore của repo gốc) → cần **git repo riêng** như 2 theme, hoặc đưa vào script deploy. Xem `deploy-caophat.sh` ở gốc repo.

**Chỉ đẩy production (`deploy-caophat.sh --go`) khi Tùng yêu cầu rõ ràng.** Không tự ý chạy.

- Requires PHP 8.2 (giống theme). Nâng MultiPHP production **trước** khi activate.
