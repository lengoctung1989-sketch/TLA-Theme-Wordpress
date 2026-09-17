# TL Site — Cao Phát (`tl-site-caophat`)

Plugin site-specific cho **caophat.vn**. Giữ **tầng dữ liệu / hành vi** riêng của site, tách khỏi theme (`tungleads-theme-cp`) để đổi giao diện không làm mất data.

Ranh giới: theme = trình bày · plugin = *dữ liệu gì tồn tại* + *hành vi*.

## Hiện có

| Phần | Việc |
|---|---|
| Tracking (`wp_head` prio 1 + `wp_body_open`) | GTM `GTM-KCVHR8P`, GA4 `G-L37N4Q06LP`, Google Ads `AW-10871632223`, Meta Pixel `5267684856622253` — chuyển từ Flatsome → Advanced → Global HTML |
| Thông số kỹ thuật SP | Tab riêng trong "Dữ liệu sản phẩm" (admin) — 8 trường lưu meta `_tlcp_spec_*` (`size`, `door_type`, `leaf`, `frame`, `features`, `origin`, `warranty`, `note`). Sửa danh sách: `tlcp_spec_fields()`. Frontend hiển thị ở trang chi tiết SP (child theme đọc meta). |
| Hotline chi nhánh | Option `tlcp_support_branches`, sửa ở **Settings → Cao Phát** (mỗi dòng: `Tên \| Số`). Theme đọc qua `tlcp_support_branches()`. |
| Ghi công | `tlcp_credit_line()` in `Phiên bản <version> \| Bởi Tung Le Ads` ở **cuối trang Settings → Cao Phát**; **và** ở **/wp-admin/plugins.php** dòng `Phiên bản 0.1.2 \| Bởi Tung Le Ads` có **“Tung Le Ads” là link** — do header `Author URI: https://tungleads.com/` (WP core tự bọc `<a>`). Version lấy động từ header plugin. |
| Đặt hàng nhanh (CP3.3) | Handler AJAX `cp_quick_order` — nhận form từ popup ở trang chi tiết SP và tạo **đơn WooCommerce thật** (COD, trạng thái "Đang xử lý"). Chống spam: nonce + honeypot + 5 đơn/IP/10 phút. |
| **Mục lục nội dung (CP8)** | Option `tlcp_toc` — **nút dọc cố định + drawer danh sách heading**, chỉnh ở **Settings → Cao Phát** (bật/tắt · post type · số cấp H2–H4 · ngưỡng heading tối thiểu · nhãn nút · mép trái/phải · màu nút · mobile ≤768 · scroll-spy · đánh số `1 · 2 · 2.1` · ID loại trừ). Quét heading ở `the_content` prio 12 (thêm `id` còn thiếu), in nút/drawer ở `wp_footer` prio 5. |

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

### Chèn mã tracking vào 3 vị trí (v0.2.0 — Tùng yêu cầu 2026-09-16)

**Settings → Cao Phát → mục “Chèn mã tracking”**: 3 ô dán mã — dán **NGUYÊN** mã nhà cung cấp cấp (kèm cả thẻ `<script>` nếu có).

| Ô nhập | In ra ở đâu | Hook (độ ưu tiên) | Thường dùng cho |
|---|---|---|---|
| **Sau thẻ `<head>`** | Sớm nhất trong `<head>`, ngay sau 4 khối tracking sẵn có của plugin | `wp_head` prio **1** | GTM, GA4, Google Ads, mã xác minh site |
| **Sau thẻ mở `<body>`** | Ngay sau `<body …>` | `wp_body_open` prio **1** | `<noscript>` của GTM / Meta Pixel |
| **Cuối trang (footer)** | Sau mọi script khác, trước `</body>` | `wp_footer` prio **99** | chat widget, script tải chậm |

- Ô trống ⇒ không in gì. **Không in trong `wp-admin`** (guard `is_admin()`), nên số liệu admin không bị lẫn.
- **Không lọc mã khi lưu** (lọc là hỏng mã) nhưng chỉ giữ nguyên văn khi người lưu có quyền `unfiltered_html`; thiếu quyền đó thì mã đi qua `wp_kses_post` (thẻ `<script>` bị bỏ).
- ⚠️ **Cảnh báo đếm đôi:** 4 khối tracking đầu file (GTM `GTM-KCVHR8P` · GA4 · Google Ads · Meta Pixel) **vẫn in sẵn** trong `<head>` + `<noscript>` ngay `<body>`. Dán mã TRÙNG ở 3 ô này ⇒ **đếm 2 lần** ⇒ gỡ một trong hai chỗ.
- Có filter `tlcp_tracking_code` (mảng 3 khoá `head`/`body`/`footer`) nếu muốn chèn theo điều kiện.
- **Đã kiểm chứng (E2E Playwright + admin tạm, 2026-09-16):** trang Settings có 4 `textarea` (1 hotline + 3 tracking) ✓ · lưu và đọc lại đúng giá trị ✓ · front-end: mã head nằm trong `<head>` **trước mọi `<link rel=stylesheet>`** ✓, mã body **ngay sau `<body …>`** ✓, mã footer **sau `tabs.js` và trước `</body>`** ✓, cả 3 **chạy thật** (`window.__TLCP_HEAD/BODY/FOOT = [1,1,1]`) ✓ · trong `wp-admin` **không chạy** (`[null,null,null]`, 0 `<script>` chứa mã) ✓. Script đo: `docs/measure/tracking-e2e.mjs` + `tracking-diag.mjs`.

### Chế độ bảo trì (v0.3.0 — Tùng yêu cầu 2026-09-17)

**Settings → Cao Phát → mục “Chế độ bảo trì”**: 1 **checkbox bật/tắt** + 1 **ô nội dung thông báo**.

- Khách **CHƯA đăng nhập** thấy trang bảo trì: **HTTP 503 + `Retry-After: 3600`** (đúng chuẩn bảo trì *tạm thời* — Google **giữ** trang trong index, không đánh rớt) · `<meta name="robots" content="noindex, nofollow">` · `nocache_headers()` + `DONOTCACHEPAGE` (LiteSpeed không cache trang bảo trì).
- Người có quyền **`manage_options`** (đổi được qua filter `tlcp_maintenance_capability`) **vẫn xem web BÌNH THƯỜNG** ⇒ bật bảo trì rồi vẫn sửa nội dung / cài plugin.
- **KHÔNG chặn**: `wp-admin`, AJAX, cron, WP-CLI, REST/JSON (`/wp-json/`) ⇒ trình soạn thảo và tác vụ nền không bị hỏng.
- Trang bảo trì là **HTML + CSS nội tuyến**, không dùng CSS/JS của theme ⇒ vẫn hiện đúng kể cả khi theme đang lỗi/đang nâng cấp; có link “Quản trị viên đăng nhập”.
- Ô thông báo để trống ⇒ dùng câu mặc định. Cho phép HTML cơ bản (`p, strong, br, a, ul/li`…), **không** cho `<script>` (lọc bằng `wp_kses_post`). Có filter `tlcp_maintenance`.
- **Đã kiểm chứng (E2E Playwright, 2026-09-17):** bật qua UI → khách `HTTP 503` + `Retry-After: 3600` + tiêu đề “Bảo trì — <tên site>” + thông báo tự nhập (giữ `<strong>`) + **không** có CSS/JS theme, không có `.cp-header` ✓ · `/wp-json/` (khách) = **200** ✓ · admin = **200** + `.cp-header` bình thường ✓ · tắt → khách **200** ✓. Script: `docs/measure/maint-e2e.mjs`.

### Mục lục nội dung (CP8 — v0.4.0, Tùng yêu cầu 2026-09-17)

**Settings → Cao Phát → “Mục lục nội dung”**: nút **DỌC** cố định ở mép màn hình, bấm mở **drawer**
danh sách heading của bài; bấm 1 mục thì cuộn tới heading đó, cuộn trang thì mục đang xem được tô nền.
Layout tham khảo demo `tungleads.com/khoa-hoc/khoa-hoc-quang-cao-google-ads-thuc-chien`.

- Tuỳ chọn: **bật/tắt** · **post type** áp dụng (mặc định `post` + `page`) · **số cấp heading** (chỉ H2 · H2+H3 · H2+H3+H4) · **số heading tối thiểu** (bài ngắn không hiện) · **nhãn nút** · **mép trái/phải** · **màu nút** (để nguyên = dùng `--cp-accent` của theme) · **hiện trên mobile ≤768px** · **tô nền mục đang xem (scroll-spy)** · **đánh số** `1 · 2 · 2.1` · **danh sách ID loại trừ**.
- **Cách chạy:** filter `the_content` **prio 12** (sau `do_shortcode` prio 11 nên heading do shortcode sinh ra cũng được quét) → regex quét `<h2>/<h3>/<h4>`, **thêm `id`** cho heading còn thiếu (slug theo tiêu đề; `sanitize_title()` bỏ dấu tiếng Việt; trùng thì `-2`, `-3`) và lưu danh sách; **nút + drawer in ở `wp_footer` prio 5**. Đủ `min` heading mới lưu ⇒ không đủ thì **trả nội dung NGUYÊN BẢN** (không thêm id nào).
- ⚠️ **Vì sao `wp_footer` prio 5 (không phải 20):** `wp_print_footer_scripts` của core gắn vào `wp_footer` prio **20** ⇒ nếu in markup ở prio 20 thì thẻ `<script>` của `assets/toc.js` ra **TRƯỚC** markup, JS chạy lúc chưa có nút ⇒ bấm không mở (`aria-expanded` đứng nguyên `false`). Đã dính và sửa 2026-09-17.
- ⚠️ **Cuộn tới heading phải DÒ LẠI:** ảnh `loading="lazy"` phía trên tải xong làm trang CAO THÊM (đo ở `/bao-gia-cua-nhua-gia-re-tphcm/`: cuộn đúng 5493px nhưng `scrollHeight` 43469 → 46010 ⇒ heading bị đẩy xuống 1565px). JS bám đích tối đa ~3s, **tự huỷ ngay khi người dùng tự cuộn** (`wheel`/`touchstart`/`keydown`).
- **Asset đi kèm plugin**: `assets/toc.css` + `assets/toc.js`, chỉ nạp khi trang thuộc post type đã bật (SP không bật ⇒ **không nạp cả CSS/JS**). Màu lấy token theme ⇒ vẫn khớp skin; `z-index` nút **45** (dưới header sticky 50, mega panel 60) và drawer **90**; khi drawer mở thì `body.tlcp-toc-open` ẩn widget nổi `button-call-zalo-tungleads` (cùng mép phải).
- **A11y:** nút `aria-expanded` + `aria-controls`, drawer `inert` khi đóng (không tab được vào), mục đang xem `aria-current`, đóng bằng ✕ / `Esc` / bấm ra ngoài, tôn trọng `prefers-reduced-motion`, ẩn khi in.
- **Kiểm chứng** (`docs/measure/toc-*.mjs` ở repo gốc): E2E mở/đóng + cuộn (heading dừng **14px** dưới header ở cả 1440 và 390) + scroll-spy; E2E Settings (đổi mép/màu/tắt đánh số → front-end ăn theo); SP không bật / ngưỡng `min` / `exclude` / `mobile=off` đều đúng; **tràn ngang 0**.

## Về sau (chưa làm)

CPT / taxonomy / form báo giá / webhook → thêm vào `tl-site-caophat.php` ở khối stub cuối file. Không cho vào theme.

## Deploy

Plugin này nằm trong `wordpress/` (gitignore của repo gốc) → cần **git repo riêng** như 2 theme, hoặc đưa vào script deploy. Xem `deploy-caophat.sh` ở gốc repo.

**Chỉ đẩy production (`deploy-caophat.sh --go`) khi Tùng yêu cầu rõ ràng.** Không tự ý chạy.

- Requires PHP 8.2 (giống theme). Nâng MultiPHP production **trước** khi activate.
