# TL Site — Cao Phát (`tl-site-caophat`)

Plugin site-specific cho **caophat.vn**. Giữ **tầng dữ liệu / hành vi** riêng của site, tách khỏi theme (`tungleads-theme-cp`) để đổi giao diện không làm mất data.

Ranh giới: theme = trình bày · plugin = *dữ liệu gì tồn tại* + *hành vi*.

## Hiện có

| Phần | Việc |
|---|---|
| Tracking (`wp_head` prio 1 + `wp_body_open`) | **4 ID nay SỬA ĐƯỢC ở Settings → Cao Phát → “ID tracking”** (v0.6.0): GTM · GA4 · Google Ads · Meta Pixel + công tắc bật/tắt chung. Ô để trống = không in khối đó; bỏ tick = không in khối nào. Giá trị mặc định (chưa lưu gì) = 4 hằng số `TL_CP_GTM_ID`/`TL_CP_GA4_ID`/`TL_CP_GADS_ID`/`TL_CP_PIXEL_ID` = ID của caophat.vn (`GTM-KCVHR8P` · `G-L37N4Q06LP` · `AW-10871632223` · `5267684856622253`), sao y bản cũ ở Flatsome → Advanced → Global HTML. |
| Thông số kỹ thuật SP | Tab riêng trong "Dữ liệu sản phẩm" (admin) — 8 trường lưu meta `_tlcp_spec_*` (`size`, `door_type`, `leaf`, `frame`, `features`, `origin`, `warranty`, `note`). Sửa danh sách: `tlcp_spec_fields()`. Frontend hiển thị ở trang chi tiết SP (child theme đọc meta). |
| ~~Hotline chi nhánh~~ | **ĐÃ CHUYỂN VỀ THEME (v0.5.0, Tùng chốt 2026-09-17)** — nay sửa ở **Customizer → “Chi nhánh & Hotline Cao Phát”** (theme_mod `cp_branches`, kéo thả từng dòng). Xem ghi chú ở khối `CP1.9` trong `tl-site-caophat.php` + README của child theme. |
| Ghi công | `tlcp_credit_line()` in `Phiên bản <version> \| Bởi Tung Le Ads` ở **cuối trang Settings → Cao Phát**; **và** ở **/wp-admin/plugins.php** dòng `Phiên bản 0.1.2 \| Bởi Tung Le Ads` có **“Tung Le Ads” là link** — do header `Author URI: https://tungleads.com/` (WP core tự bọc `<a>`). Version lấy động từ header plugin. |
| Đặt hàng nhanh (CP3.3) | Handler AJAX `cp_quick_order` — nhận form từ popup ở trang chi tiết SP và tạo **đơn WooCommerce thật** (COD, trạng thái "Đang xử lý"). Chống spam: nonce + honeypot + 5 đơn/IP/10 phút. |
| **Mục lục nội dung (CP8)** | Option `tlcp_toc` — **nút dọc cố định + drawer** VÀ/HOẶC **khối mục lục trong nội dung bài** (`<details>`, đặt đầu bài hoặc sau đoạn mở đầu; mở/thu được cả khi tắt JS), chỉnh ở **Settings → Cao Phát** (bật/tắt chung · post type · số cấp H2–H4 · ngưỡng heading tối thiểu · đánh số `1 · 2 · 2.1` · nhãn · mép trái/phải · màu · mobile ≤768 · scroll-spy · ID loại trừ). Quét heading ở `the_content` prio 12 (thêm `id` còn thiếu), in nút/drawer ở `wp_footer` prio 5. |

### Đặt hàng nhanh (CP3.3)

- Popup + JS nằm ở child theme (`tungleads-theme-cp`); **tạo đơn nằm ở plugin này** (`tlcp_quick_order_handle()`).
- Đơn tạo ra: `created_via = cp-quick-order`, thanh toán COD (nếu gateway bật), trạng thái **Đang xử lý** → tự trừ tồn kho + gửi email "Đơn hàng mới" cho admin. Lọc đơn nhanh về sau: `wc_get_orders( array( 'created_via' => 'cp-quick-order' ) )`.
- Khách không cần tài khoản — đơn guest, billing/shipping lấy từ thông tin khách nhập.
- Chặn: nonce `tlcp_quick_order` · honeypot `cp_hp` · rate-limit **5 đơn/IP/10 phút** (hằng `TL_CP_QO_MAX` / `TL_CP_QO_WINDOW`, đếm bằng transient `tlcp_qo_<md5 ip>`).
- Giá luôn lấy từ server (`$product->get_price()`), không tin dữ liệu client gửi lên.

### Hotline chi nhánh — ĐÃ CHUYỂN VỀ THEME (v0.5.0)

Trước đây mục này nằm ở **Settings → Cao Phát** (option `tlcp_support_branches`). Từ **v0.5.0** plugin **gỡ hẳn** (option, textarea, `tlcp_support_branches_default()/()/ _text()`, `register_setting`).

- Nay sửa ở **Giao diện → Tuỳ biến → “Chi nhánh & Hotline Cao Phát”** (theme_mod `cp_branches`, repeater kéo thả) — dùng **`cp_support_branches()`** trong child theme `tungleads-theme-cp`.
- Lý do (Tùng chốt 2026-09-17): đây là **nội dung hiển thị**, không phải business logic — mà hotline CHÍNH `cp_hotline_tel` vốn đã ở Customizer ⇒ **1 chỗ sửa mọi số điện thoại**.
- Trang Settings của plugin giờ có **3 mục**: Chèn mã tracking · Chế độ bảo trì · Mục lục nội dung (+ dòng link sang Customizer cho phần hotline).
- **Dữ liệu cũ trên production:** chạy `wp eval-file docs/prod-migrate-branches.php` (dry-run) rồi `… go` để ghi sang theme_mod + xoá option. Theme có **cầu nối** đọc thẳng option cũ nếu theme_mod còn rỗng ⇒ deploy lệch thứ tự không mất số.

### ID tracking — nhập ở Settings, không sửa file (v0.6.0, Tùng hỏi 2026-09-17)

**Settings → Cao Phát → mục đầu “ID tracking”**: công tắc **“In 4 khối tracking ở trên”** + 4 ô:
**Google Tag Manager** (`GTM-…`) · **GA4** (`G-…`) · **Google Ads** (`AW-…`) · **Meta Pixel** (số).

- Option `tlcp_tracking_ids`; **chưa lưu bao giờ ⇒ dùng 4 hằng số mặc định** trong `tl-site-caophat.php`
  (`TL_CP_GTM_ID`…) — nên nâng cấp plugin **không đổi gì** trên site đang chạy (đã đo: front-end y hệt trước/sau).
- **Đã lưu rồi thì option là chuẩn**: ô **để trống = KHÔNG in khối đó** (không tự quay về mặc định — nếu
  quay về thì không tắt được khối nào). Bỏ tick công tắc = **không in khối nào** (chỉ còn mã dán ở mục dưới).
- Nhập gì cũng được: sanitize tự bỏ dấu cách/ngoặc, chỉ giữ `A–Z 0–9 - _` và viết hoa.
- Filter `tlcp_tracking_ids` để ghi đè bằng code nếu cần.
- **Vì sao cần:** mang plugin sang website khác chỉ cần **đổi 4 ID trong admin** (trước đây phải sửa code).
  Để nguyên ID caophat.vn trên site khác = dữ liệu site đó chảy vào tài khoản GA/Ads/Pixel của caophat.vn.
- ⚠️ **Deploy caophat.vn:** 4 khối này **sao y bản đang chạy ở Flatsome → Advanced → Global HTML** ⇒ khi
  deploy phải **GỠ 4 khối đó khỏi Flatsome cùng lúc**, không để cả hai chạy (đếm đôi).

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

### Mục lục nội dung (CP8 — v0.4.0 thêm khối trong bài ở v0.4.1, Tùng yêu cầu 2026-09-17)

**Settings → Cao Phát → “Mục lục nội dung”** — 2 cách hiển thị, **bật/tắt độc lập** (bật cả 2 cũng được):

1. **Nút DỌC cố định ở mép màn hình** → bấm mở **drawer** danh sách heading (layout theo demo
   `tungleads.com/khoa-hoc/khoa-hoc-quang-cao-google-ads-thuc-chien`).
2. **Khối mục lục NGAY TRONG NỘI DUNG BÀI** (`<details>` + `<summary>`): đặt **đầu bài** hoặc
   **sau đoạn mở đầu**; mở sẵn hoặc thu gọn (bấm tiêu đề mới mở). Dùng thẻ HTML gốc nên **mở/thu
   chạy được cả khi tắt JS**; JS chỉ thêm cuộn mượt + bù header sticky cho các link mục.

- Tuỳ chọn: **bật/tắt chung** · **post type** (mặc định `post` + `page`) · **số cấp heading** (chỉ H2 · H2+H3 · H2+H3+H4) · **số heading tối thiểu** (bài ngắn không hiện) · **đánh số** `1 · 2 · 2.1` · **hiện nút dọc** · **hiện khối trong nội dung** + **vị trí** + **mở sẵn** · **nhãn nút/tiêu đề khối** · **mép trái/phải** · **màu** (để nguyên = `--cp-accent` của theme) · **mobile ≤768px** · **tô nền mục đang xem (scroll-spy)** · **danh sách ID loại trừ**.
- **Cách chạy:** filter `the_content` **prio 12** (sau `do_shortcode` prio 11 nên heading do shortcode sinh ra cũng được quét) → regex quét `<h2>/<h3>/<h4>`, **thêm `id`** cho heading còn thiếu (slug theo tiêu đề; `sanitize_title()` bỏ dấu tiếng Việt; trùng thì `-2`, `-3`) và lưu danh sách; **nút + drawer in ở `wp_footer` prio 5**; **khối trong nội dung chèn ngay vào `$content`** (sau khi quét nên khối không tự lọt vào danh sách). Đủ `min` heading mới lưu ⇒ không đủ thì **trả nội dung NGUYÊN BẢN** (không thêm id nào).
- ⚠️ **Vì sao `wp_footer` prio 5 (không phải 20):** `wp_print_footer_scripts` của core gắn vào `wp_footer` prio **20** ⇒ nếu in markup ở prio 20 thì thẻ `<script>` của `assets/toc.js` ra **TRƯỚC** markup, JS chạy lúc chưa có nút ⇒ bấm không mở (`aria-expanded` đứng nguyên `false`). Đã dính và sửa 2026-09-17.
- ⚠️ **Cuộn phải DÒ LẠI + nhảy thẳng khi xa:** ảnh `loading="lazy"` tải xong làm trang CAO THÊM. Quãng **ngắn** (≤ 1,5 màn hình) thì cuộn mượt + dò lại ~3s là khớp (đo `/bao-gia-cua-nhua-gia-re-tphcm/`: hụt 1565px → bù xong). Quãng **dài** mà cuộn mượt thì đích “chạy xa mãi” (đo: 36.567px → 78.599px, 12 lần dò trong 3s không đuổi kịp) ⇒ **nhảy thẳng (`behavior:'auto'`) khi > 1,5 màn hình**, chỉ còn sai số nhỏ quanh đích ⇒ khớp sau 1–2 lần chỉnh. Vòng dò **tự huỷ** khi người dùng tự cuộn (`wheel`/`touchstart`/`keydown`).
- **Deep link `…#ten-muc`** (Google “jump to”, link khách dán) cũng dừng dưới header sticky: JS đặt `--tlcp-scroll-pad`, CSS có `html { scroll-padding-top: var(--tlcp-scroll-pad, 90px) }`.
- ⚠️ **Màu chọn ở Settings phải truyền vào khối trong nội dung** bằng `style="--tlcp-toc-color:…"` — khối nằm trong `the_content`, KHÔNG nằm trong wrapper `.tlcp-toc` (nơi biến này được gắn) ⇒ thiếu là khối lệch màu so với nút dọc.
- **Asset đi kèm plugin**: `assets/toc.css` + `assets/toc.js`, chỉ nạp khi trang thuộc post type đã bật **và** còn ít nhất 1 cách hiển thị (tắt cả 2 ⇒ **không nạp gì**). `z-index` nút **45** (dưới header sticky 50, mega panel 60), drawer **90**; khi drawer mở thì `body.tlcp-toc-open` ẩn widget nổi `button-call-zalo-tungleads` (cùng mép phải).
- **A11y:** nút `aria-expanded` + `aria-controls`, drawer `inert` khi đóng (không tab được vào), mục đang xem `aria-current`, đóng bằng ✕ / `Esc` / bấm ra ngoài, tôn trọng `prefers-reduced-motion`, ẩn khi in.
- **Kiểm chứng** (`docs/measure/toc-*.mjs` ở repo gốc): khối trong bài **990×226 @top** (nằm trong `.cp-article__content`) / **990×616 @p1** (dưới đoạn mở đầu), thu gọn còn **60px**; bấm mục (cả khối lẫn drawer) → heading dừng **14px** dưới header ở 1440 và 390; 7 biến thể đúng (`float_off` · `inline_off` · `both_off` ⇒ **không nạp CSS/JS** · `collapse` · `p1` · `top` · `depth3`); E2E Settings (đổi mép/màu/tắt đánh số → front-end ăn theo); **tràn ngang 0**; smoke test 24/24.

## Về sau (chưa làm)

CPT / taxonomy / form báo giá / webhook → thêm vào `tl-site-caophat.php` ở khối stub cuối file. Không cho vào theme.

## Mang sang website WordPress khác (đã ĐO THẬT 2026-09-17)

Đã cài thử plugin vào **một WordPress MỚI** (WP 7.1 + theme mặc định `twentytwentyfive` + **KHÔNG có WooCommerce**):

| Hạng mục | Kết quả trên site lạ |
|---|---|
| Kích hoạt plugin · front-end · trang Settings | ✅ chạy, **0 warning/fatal** (log container sạch) |
| **Mục lục** (nút dọc + khối trong bài) | ✅ đủ chức năng trên theme lạ: nút 35×157, khối 4 mục, bấm nút mở drawer, không tràn ngang — màu tự dùng fallback nội bộ vì theme không có token `--cp-*` |
| **Chế độ bảo trì** | ✅ khách nhận **HTTP 503** |
| **Đặt hàng nhanh** (AJAX) | ✅ **không fatal** khi thiếu WooCommerce (nonce chặn trước, có guard `function_exists('wc_get_product')`) |
| **Thông số kỹ thuật SP** | ⚠️ cần WooCommerce — thiếu WC thì hook không chạy, im lặng (vô hại) |
| **4 ID tracking** | ✅ từ **v0.6.0** sửa được ở Settings ⇒ sang site khác chỉ cần nhập 4 ID mới, không sửa code |

**Checklist khi mang đi:**
1. **Settings → Cao Phát → “ID tracking”**: nhập 4 ID của site mới (hoặc bỏ tick công tắc nếu không dùng tracking).
2. Cài **WooCommerce** nếu muốn “Thông số kỹ thuật” + “Đặt hàng nhanh”; plugin chạy tốt cả khi không có WC.
3. 8 trường “Thông số kỹ thuật” là chuyên ngành **cửa** — sửa `tlcp_spec_fields()` nếu dùng cho ngành khác.
4. Mục lục bù header sticky dựa vào class `.cp-header` của theme Cao Phát ⇒ theme khác thì offset = **0** (vẫn dùng được, chỉ là mục tiêu nằm sát mép trên).
5. Plugin **không phụ thuộc theme/site nào** (không gọi hàm của theme) ⇒ gỡ theme Cao Phát vẫn chạy.

## Deploy

Plugin này nằm trong `wordpress/` (gitignore của repo gốc) → cần **git repo riêng** như 2 theme, hoặc đưa vào script deploy. Xem `deploy-caophat.sh` ở gốc repo.

**Chỉ đẩy production (`deploy-caophat.sh --go`) khi Tùng yêu cầu rõ ràng.** Không tự ý chạy.

- Requires PHP 8.2 (giống theme). Nâng MultiPHP production **trước** khi activate.
