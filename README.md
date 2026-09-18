# PL Tiện Ích - TungLeAds

**Tiện ích dùng chung cho các website WordPress** — phát triển bởi **Tùng Lê Ads**. Nhiều tính năng, mỗi tính năng bật/tắt độc lập; cấu hình ở **Settings → PL Tiện Ích**.

**Từ v1.0.0 đã đổi tên SẠCH để dùng chung:** thư mục + file chính `pl-tien-ich-tungleads`, text-domain `pl-tien-ich-tungleads`, tiền tố hàm/option/CSS `tlpi_` / `TLPI_` / `tlpi-`, trang Settings slug `tlpi-support`.

⚠️ Site nào đã dùng bản CŨ (tiền tố `tlcp_`, thư mục `tl-site-caophat`) thì **không mất gì**: plugin tự đọc option tên cũ qua cầu nối `tlpi_option()`, vẫn nhận AJAX action `cp_quick_order` + honeypot `cp_hp` + nonce cũ, và theme đọc cả meta `_tlcp_spec_*`. Muốn dọn hẳn thì chạy `docs/prod-migrate-plugin-prefix.php` (repo gốc) — xem mục “Chuyển từ bản cũ”.

Ranh giới: theme = trình bày · plugin = *dữ liệu gì tồn tại* + *hành vi*.

## Hiện có

| Phần | Việc |
|---|---|
| Tracking (`wp_head` prio 1 + `wp_body_open`) | **4 ID nay SỬA ĐƯỢC ở Settings → PL Tiện Ích → “ID tracking”** (v0.6.0): GTM · GA4 · Google Ads · Meta Pixel + công tắc bật/tắt chung. Ô để trống = không in khối đó; bỏ tick = không in khối nào. Giá trị mặc định (chưa lưu gì) = 4 hằng số `TLPI_GTM_ID`/`TLPI_GA4_ID`/`TLPI_GADS_ID`/`TLPI_PIXEL_ID` = 4 ID đang chạy ở dự án hiện tại (`GTM-KCVHR8P` · `G-L37N4Q06LP` · `AW-10871632223` · `5267684856622253`), sao y bản cũ ở Flatsome → Advanced → Global HTML. |
| Thông số kỹ thuật SP | Tab riêng trong "Dữ liệu sản phẩm" (admin) — 8 trường lưu meta `_tlpi_spec_*` (`size`, `door_type`, `leaf`, `frame`, `features`, `origin`, `warranty`, `note`). Sửa danh sách: `tlpi_spec_fields()`. Frontend hiển thị ở trang chi tiết SP (child theme đọc meta). |
| ~~Hotline chi nhánh~~ | **ĐÃ CHUYỂN VỀ THEME (v0.5.0, Tùng chốt 2026-09-17)** — nay sửa ở **Customizer của theme đang dùng** (theme_mod `cp_branches`, kéo thả từng dòng). Xem ghi chú ở khối `CP1.9` trong `pl-tien-ich-tungleads.php` + README của child theme. |
| Ghi công | `tlpi_credit_line()` in `Phiên bản <version> \| Bởi Tùng Lê Ads` ở **cuối trang Settings → PL Tiện Ích**; **và** ở **/wp-admin/plugins.php** dòng `Phiên bản <version> \| Bởi Tùng Lê Ads` có **“Tùng Lê Ads” là link** — do header `Author URI: https://tungleads.com/` (WP core tự bọc `<a>`). Version lấy động từ header plugin. |
| Đặt hàng nhanh (CP3.3) | Handler AJAX `tlpi_quick_order` — nhận form từ popup ở trang chi tiết SP và tạo **đơn WooCommerce thật** (COD, trạng thái "Đang xử lý"). Chống spam: nonce + honeypot + 5 đơn/IP/10 phút. |
| **Xác minh 2 lớp — 2FA (v1.2.0)** | Option `tlpi_2fa` + user meta `_tlpi_totp_secret` / `_tlpi_totp_codes` / `_tlpi_totp_want`. **TOTP (RFC 6238)** app xác thực: chặn đăng nhập sau khi đúng mật khẩu, màn nhập mã riêng (`wp-login.php?action=tlpi_totp`), tự ghi danh + 10 mã dự phòng in 1 lần, cookie “nhớ thiết bị” (HMAC buộc với mã bí mật), tối đa 5 lần nhập sai, đặt lại ở trang Hồ sơ. Mã bí mật lưu dạng **mã hoá sodium**. Cấu hình ở **Settings → PL Tiện Ích** (vai trò bắt buộc + số ngày nhớ thiết bị). |
| **Đường dẫn đăng nhập (v1.1.0)** | Option `tlpi_login` — đổi trang đăng nhập sang đường dẫn riêng + ẩn `wp-admin`/`wp-login.php` với khách (404 hoặc chuyển về trang chủ), người đã đăng nhập vẫn dùng `wp-admin` bình thường. Cần permalink đẹp. Cấu hình ở **Settings → PL Tiện Ích**. |
| **Mục lục nội dung (CP8)** | Option `tlpi_toc` — **nút dọc cố định + drawer** VÀ/HOẶC **khối mục lục trong nội dung bài** (`<details>`, đặt đầu bài hoặc sau đoạn mở đầu; mở/thu được cả khi tắt JS), chỉnh ở **Settings → PL Tiện Ích** (bật/tắt chung · post type · số cấp H2–H4 · ngưỡng heading tối thiểu · đánh số `1 · 2 · 2.1` · nhãn · mép trái/phải · màu · mobile ≤768 · scroll-spy · ID loại trừ). Quét heading ở `the_content` prio 12 (thêm `id` còn thiếu), in nút/drawer ở `wp_footer` prio 5. |

### Đặt hàng nhanh (CP3.3)

- Popup + JS nằm ở child theme (`tungleads-theme-cp`); **tạo đơn nằm ở plugin này** (`tlpi_quick_order_handle()`).
- Đơn tạo ra: `created_via = tlpi-quick-order`, thanh toán COD (nếu gateway bật), trạng thái **Đang xử lý** → tự trừ tồn kho + gửi email "Đơn hàng mới" cho admin. Lọc đơn nhanh về sau: `wc_get_orders( array( 'created_via' => 'tlpi-quick-order' ) )`.
- Khách không cần tài khoản — đơn guest, billing/shipping lấy từ thông tin khách nhập.
- Chặn: nonce `tlpi_quick_order` · honeypot `tlpi_hp` · rate-limit **5 đơn/IP/10 phút** (hằng `TLPI_QO_MAX` / `TLPI_QO_WINDOW`, đếm bằng transient `tlpi_qo_<md5 ip>`).
- Giá luôn lấy từ server (`$product->get_price()`), không tin dữ liệu client gửi lên.

### Hotline chi nhánh — ĐÃ CHUYỂN VỀ THEME (v0.5.0)

Trước đây mục này nằm ở **Settings → PL Tiện Ích** (option `tlpi_support_branches`). Từ **v0.5.0** plugin **gỡ hẳn** (option, textarea, `tlpi_support_branches_default()/()/ _text()`, `register_setting`).

- Nay sửa ở **Customizer của theme đang dùng** (theme_mod `cp_branches`, repeater kéo thả) — dùng **`cp_support_branches()`** trong theme đang dùng.
- Lý do (Tùng chốt 2026-09-17): đây là **nội dung hiển thị**, không phải business logic — mà hotline CHÍNH `cp_hotline_tel` vốn đã ở Customizer ⇒ **1 chỗ sửa mọi số điện thoại**.
- Trang Settings của plugin giờ có **3 mục**: Chèn mã tracking · Chế độ bảo trì · Mục lục nội dung (+ dòng link sang Customizer cho phần hotline).
- **Dữ liệu cũ trên production:** chạy `wp eval-file docs/prod-migrate-branches.php` (dry-run) rồi `… go` để ghi sang theme_mod + xoá option. Theme có **cầu nối** đọc thẳng option cũ nếu theme_mod còn rỗng ⇒ deploy lệch thứ tự không mất số.

### ID tracking — nhập ở Settings, không sửa file (v0.6.0, Tùng hỏi 2026-09-17)

**Settings → PL Tiện Ích → mục đầu “ID tracking”**: công tắc **“In 4 khối tracking ở trên”** + 4 ô:
**Google Tag Manager** (`GTM-…`) · **GA4** (`G-…`) · **Google Ads** (`AW-…`) · **Meta Pixel** (số).

- Option `tlpi_tracking_ids`; **chưa lưu bao giờ ⇒ dùng 4 hằng số mặc định** trong `pl-tien-ich-tungleads.php`
  (`TLPI_GTM_ID`…) — nên nâng cấp plugin **không đổi gì** trên site đang chạy (đã đo: front-end y hệt trước/sau).
- **Đã lưu rồi thì option là chuẩn**: ô **để trống = KHÔNG in khối đó** (không tự quay về mặc định — nếu
  quay về thì không tắt được khối nào). Bỏ tick công tắc = **không in khối nào** (chỉ còn mã dán ở mục dưới).
- Nhập gì cũng được: sanitize tự bỏ dấu cách/ngoặc, chỉ giữ `A–Z 0–9 - _` và viết hoa.
- Filter `tlpi_tracking_ids` để ghi đè bằng code nếu cần.
- **Vì sao cần:** mang plugin sang website khác chỉ cần **đổi 4 ID trong admin** (trước đây phải sửa code).
  Để nguyên ID của dự án cũ trên site khác = dữ liệu site đó chảy vào tài khoản GA/Ads/Pixel của dự án đó.
- ⚠️ **Khi deploy lên site đang dùng:** nếu 4 khối tracking đang nằm ở chỗ khác (ví dụ **Flatsome → Advanced → Global HTML**) thì phải **GỠ Ở CHỖ CŨ CÙNG LÚC**, không để cả hai chạy (đếm đôi).

### Kích hoạt tracking — đúng trình tự

1. Xác minh lại 4 ID (so với tài khoản GA/Ads/Meta hiện tại).
2. Cân nhắc: GTM thường đã chứa GA + Ads + Pixel → nếu đúng, xoá 3 khối gtag/pixel trong file, chỉ giữ GTM. Mặc định file sao y production (cả 4).
3. **Cùng lúc**: bật plugin này **và** xoá đoạn scripts tương ứng khỏi Flatsome Global HTML. Không để cả hai cùng chạy (double pageview/conversion).
4. Test: GA DebugView, Meta Pixel Helper, Tag Assistant.

### Chèn mã tracking vào 3 vị trí (v0.2.0 — Tùng yêu cầu 2026-09-16)

**Settings → PL Tiện Ích → mục “Chèn mã tracking”**: 3 ô dán mã — dán **NGUYÊN** mã nhà cung cấp cấp (kèm cả thẻ `<script>` nếu có).

| Ô nhập | In ra ở đâu | Hook (độ ưu tiên) | Thường dùng cho |
|---|---|---|---|
| **Sau thẻ `<head>`** | Sớm nhất trong `<head>`, ngay sau 4 khối tracking sẵn có của plugin | `wp_head` prio **1** | GTM, GA4, Google Ads, mã xác minh site |
| **Sau thẻ mở `<body>`** | Ngay sau `<body …>` | `wp_body_open` prio **1** | `<noscript>` của GTM / Meta Pixel |
| **Cuối trang (footer)** | Sau mọi script khác, trước `</body>` | `wp_footer` prio **99** | chat widget, script tải chậm |

- Ô trống ⇒ không in gì. **Không in trong `wp-admin`** (guard `is_admin()`), nên số liệu admin không bị lẫn.
- **Không lọc mã khi lưu** (lọc là hỏng mã) nhưng chỉ giữ nguyên văn khi người lưu có quyền `unfiltered_html`; thiếu quyền đó thì mã đi qua `wp_kses_post` (thẻ `<script>` bị bỏ).
- ⚠️ **Cảnh báo đếm đôi:** 4 khối tracking đầu file (GTM `GTM-KCVHR8P` · GA4 · Google Ads · Meta Pixel) **vẫn in sẵn** trong `<head>` + `<noscript>` ngay `<body>`. Dán mã TRÙNG ở 3 ô này ⇒ **đếm 2 lần** ⇒ gỡ một trong hai chỗ.
- Có filter `tlpi_tracking_code` (mảng 3 khoá `head`/`body`/`footer`) nếu muốn chèn theo điều kiện.
- **Đã kiểm chứng (E2E Playwright + admin tạm, 2026-09-16):** trang Settings có 4 `textarea` (1 hotline + 3 tracking) ✓ · lưu và đọc lại đúng giá trị ✓ · front-end: mã head nằm trong `<head>` **trước mọi `<link rel=stylesheet>`** ✓, mã body **ngay sau `<body …>`** ✓, mã footer **sau `tabs.js` và trước `</body>`** ✓, cả 3 **chạy thật** (`window.__TLCP_HEAD/BODY/FOOT = [1,1,1]`) ✓ · trong `wp-admin` **không chạy** (`[null,null,null]`, 0 `<script>` chứa mã) ✓. Script đo: `docs/measure/tracking-e2e.mjs` + `tracking-diag.mjs`.

### Chế độ bảo trì (v0.3.0 — Tùng yêu cầu 2026-09-17)

**Settings → PL Tiện Ích → mục “Chế độ bảo trì”**: 1 **checkbox bật/tắt** + 1 **ô nội dung thông báo**.

- Khách **CHƯA đăng nhập** thấy trang bảo trì: **HTTP 503 + `Retry-After: 3600`** (đúng chuẩn bảo trì *tạm thời* — Google **giữ** trang trong index, không đánh rớt) · `<meta name="robots" content="noindex, nofollow">` · `nocache_headers()` + `DONOTCACHEPAGE` (LiteSpeed không cache trang bảo trì).
- Người có quyền **`manage_options`** (đổi được qua filter `tlpi_maintenance_capability`) **vẫn xem web BÌNH THƯỜNG** ⇒ bật bảo trì rồi vẫn sửa nội dung / cài plugin.
- **KHÔNG chặn**: `wp-admin`, AJAX, cron, WP-CLI, REST/JSON (`/wp-json/`) ⇒ trình soạn thảo và tác vụ nền không bị hỏng.
- Trang bảo trì là **HTML + CSS nội tuyến**, không dùng CSS/JS của theme ⇒ vẫn hiện đúng kể cả khi theme đang lỗi/đang nâng cấp; có link “Quản trị viên đăng nhập”.
- Ô thông báo để trống ⇒ dùng câu mặc định. Cho phép HTML cơ bản (`p, strong, br, a, ul/li`…), **không** cho `<script>` (lọc bằng `wp_kses_post`). Có filter `tlpi_maintenance`.
- **Đã kiểm chứng (E2E Playwright, 2026-09-17):** bật qua UI → khách `HTTP 503` + `Retry-After: 3600` + tiêu đề “Bảo trì — <tên site>” + thông báo tự nhập (giữ `<strong>`) + **không** có CSS/JS theme, không có `.cp-header` ✓ · `/wp-json/` (khách) = **200** ✓ · admin = **200** + `.cp-header` bình thường ✓ · tắt → khách **200** ✓. Script: `docs/measure/maint-e2e.mjs`.

### Mục lục nội dung (CP8 — v0.4.0 thêm khối trong bài ở v0.4.1, Tùng yêu cầu 2026-09-17)

**Settings → PL Tiện Ích → “Mục lục nội dung”** — 2 cách hiển thị, **bật/tắt độc lập** (bật cả 2 cũng được):

1. **Nút DỌC cố định ở mép màn hình** → bấm mở **drawer** danh sách heading (layout theo demo
   `tungleads.com/khoa-hoc/khoa-hoc-quang-cao-google-ads-thuc-chien`).
2. **Khối mục lục NGAY TRONG NỘI DUNG BÀI** (`<details>` + `<summary>`): đặt **đầu bài** hoặc
   **sau đoạn mở đầu**; mở sẵn hoặc thu gọn (bấm tiêu đề mới mở). Dùng thẻ HTML gốc nên **mở/thu
   chạy được cả khi tắt JS**; JS chỉ thêm cuộn mượt + bù header sticky cho các link mục.

- Tuỳ chọn: **bật/tắt chung** · **post type** (mặc định `post` + `page`) · **số cấp heading** (chỉ H2 · H2+H3 · H2+H3+H4) · **số heading tối thiểu** (bài ngắn không hiện) · **đánh số** `1 · 2 · 2.1` · **hiện nút dọc** · **hiện khối trong nội dung** + **vị trí** + **mở sẵn** · **nhãn nút/tiêu đề khối** · **mép trái/phải** · **màu** (để nguyên = `--cp-accent` của theme) · **mobile ≤768px** · **tô nền mục đang xem (scroll-spy)** · **danh sách ID loại trừ**.
- **Cách chạy:** filter `the_content` **prio 12** (sau `do_shortcode` prio 11 nên heading do shortcode sinh ra cũng được quét) → regex quét `<h2>/<h3>/<h4>`, **thêm `id`** cho heading còn thiếu (slug theo tiêu đề; `sanitize_title()` bỏ dấu tiếng Việt; trùng thì `-2`, `-3`) và lưu danh sách; **nút + drawer in ở `wp_footer` prio 5**; **khối trong nội dung chèn ngay vào `$content`** (sau khi quét nên khối không tự lọt vào danh sách). Đủ `min` heading mới lưu ⇒ không đủ thì **trả nội dung NGUYÊN BẢN** (không thêm id nào).
- ⚠️ **Vì sao `wp_footer` prio 5 (không phải 20):** `wp_print_footer_scripts` của core gắn vào `wp_footer` prio **20** ⇒ nếu in markup ở prio 20 thì thẻ `<script>` của `assets/toc.js` ra **TRƯỚC** markup, JS chạy lúc chưa có nút ⇒ bấm không mở (`aria-expanded` đứng nguyên `false`). Đã dính và sửa 2026-09-17.
- ⚠️ **Cuộn phải DÒ LẠI + nhảy thẳng khi xa:** ảnh `loading="lazy"` tải xong làm trang CAO THÊM. Quãng **ngắn** (≤ 1,5 màn hình) thì cuộn mượt + dò lại ~3s là khớp (đo `/bao-gia-cua-nhua-gia-re-tphcm/`: hụt 1565px → bù xong). Quãng **dài** mà cuộn mượt thì đích “chạy xa mãi” (đo: 36.567px → 78.599px, 12 lần dò trong 3s không đuổi kịp) ⇒ **nhảy thẳng (`behavior:'auto'`) khi > 1,5 màn hình**, chỉ còn sai số nhỏ quanh đích ⇒ khớp sau 1–2 lần chỉnh. Vòng dò **tự huỷ** khi người dùng tự cuộn (`wheel`/`touchstart`/`keydown`).
- **Deep link `…#ten-muc`** (Google “jump to”, link khách dán) cũng dừng dưới header sticky: JS đặt `--tlpi-scroll-pad`, CSS có `html { scroll-padding-top: var(--tlpi-scroll-pad, 90px) }`.
- ⚠️ **Màu chọn ở Settings phải truyền vào khối trong nội dung** bằng `style="--tlpi-toc-color:…"` — khối nằm trong `the_content`, KHÔNG nằm trong wrapper `.tlpi-toc` (nơi biến này được gắn) ⇒ thiếu là khối lệch màu so với nút dọc.
- **Asset đi kèm plugin**: `assets/toc.css` + `assets/toc.js`, chỉ nạp khi trang thuộc post type đã bật **và** còn ít nhất 1 cách hiển thị (tắt cả 2 ⇒ **không nạp gì**). `z-index` nút **45** (dưới header sticky 50, mega panel 60), drawer **90**; khi drawer mở thì `body.tlpi-toc-open` ẩn widget nổi `button-call-zalo-tungleads` (cùng mép phải).
- **A11y:** nút `aria-expanded` + `aria-controls`, drawer `inert` khi đóng (không tab được vào), mục đang xem `aria-current`, đóng bằng ✕ / `Esc` / bấm ra ngoài, tôn trọng `prefers-reduced-motion`, ẩn khi in.
- **Kiểm chứng** (`docs/measure/toc-*.mjs` ở repo gốc): khối trong bài **990×226 @top** (nằm trong `.cp-article__content`) / **990×616 @p1** (dưới đoạn mở đầu), thu gọn còn **60px**; bấm mục (cả khối lẫn drawer) → heading dừng **14px** dưới header ở 1440 và 390; 7 biến thể đúng (`float_off` · `inline_off` · `both_off` ⇒ **không nạp CSS/JS** · `collapse` · `p1` · `top` · `depth3`); E2E Settings (đổi mép/màu/tắt đánh số → front-end ăn theo); **tràn ngang 0**; smoke test 24/24.

### Đường dẫn đăng nhập — ẩn wp-admin / wp-login.php (v1.1.0)

**Settings → PL Tiện Ích → mục “Đường dẫn đăng nhập”**: đổi trang đăng nhập sang một đường dẫn riêng
(`/dang-nhap-caophat/`) và trả **404** (hoặc chuyển về trang chủ) cho KHÁCH chưa đăng nhập khi họ vào
`wp-admin/…` / `wp-login.php` — đây là 2 đường dẫn bị bot dò liên tục.

- **Người ĐÃ đăng nhập vẫn dùng `/wp-admin/` bình thường** — `admin_url()` KHÔNG bị đổi nên dashboard,
  AJAX trong admin, link menu… chạy y như trước. Chỉ khách bị chặn.
- Đường dẫn mới phục vụ **chính `wp-login.php` của core** ⇒ mọi action hoạt động: đăng nhập · đăng xuất ·
  **quên/đặt lại mật khẩu** · postpass · register…
- Link do WP sinh ra tự trỏ đường dẫn mới: `wp_login_url()` · `wp_logout_url()` · `wp_lostpassword_url()` ·
  `wp_registration_url()` (qua filter `site_url` / `network_site_url` / `wp_redirect`).
- **KHÔNG chặn**: `admin-ajax.php` · `admin-post.php` · `load-styles.php` · `load-scripts.php` ·
  `/wp-admin/css|js|images|fonts/…` (trang đăng nhập tải CSS/JS từ đó — chặn là trang login trắng bệch) ·
  REST `/wp-json/` · cron · XML-RPC · wp-cli.
- **Cần “Permalink đẹp”**: nếu Settings → Permalinks đang để “Mặc định” thì tính năng **tự TẮT**
  (đường dẫn mới là URL đẹp, permalink mặc định sẽ bị Apache trả 404 trước khi tới WordPress). Trang Settings
  hiện cảnh báo đỏ khi ở tình trạng này.
- Vài tinh chỉnh kèm theo: URL bị chặn trả 404 **có `X-Robots-Tag: noindex`** + trang 404 tự chứa (không nạp
  theme ⇒ bot không dò được gì), và **trang đăng nhập KHÔNG bị tính vào GA/Meta Pixel** (tracking tự bỏ qua).
- **CỨU HỘ khi quên đường dẫn** (bắt buộc nhớ): `wp option delete tlpi_login` — hoặc `wp option update
  tlpi_login '{"on":""}' --format=json`. Không có wp-cli thì xoá/đổi tên thư mục plugin qua FTP.
- ⚠️ Khi tính năng BẬT, các script đo trong `docs/measure/` phải đăng nhập qua đường dẫn mới
  (`/dang-nhap-quan-tri/`) chứ không phải `/wp-login.php`. Kiểm chứng ở `docs/measure/login-path-e2e.mjs`
  + `login-assets-check.mjs`.

### Xác minh 2 lớp (2FA) bằng app xác thực — TOTP (v1.2.0)

**Settings → PL Tiện Ích → mục “Xác minh 2 lớp (2FA)”**: bật/tắt, chọn **vai trò bắt buộc** (mặc định chỉ
*Quản trị viên*) và số ngày **“nhớ thiết bị”** (mặc định 30, nhập 0 để hỏi mỗi lần).

- Chuẩn **TOTP (RFC 6238)** như Google Authenticator / Authy / 1Password — **không phụ thuộc email**, không
  gọi dịch vụ ngoài. Mã 6 số đổi mỗi 30 giây, chấp nhận cả mã của bước trước/sau (±30s) cho đồng hồ lệch.
- **Luồng 2 bước**: nhập mật khẩu → màn `wp-login.php?action=tlpi_totp…` nhập mã 6 số (hoặc **mã dự phòng**)
  → mới thật sự đăng nhập. Mật khẩu đúng nhưng chưa xác minh thì **không có phiên** nào được tạo.
- **Tài khoản chưa có mã bí mật** ⇒ màn đó tự hiện mã bí mật (32 ký tự Base32) + link “Mở app xác thực”
  (`otpauth://`, bấm được trên điện thoại) để ghi danh, rồi in **10 mã dự phòng MỘT LẦN** (đã hash, mỗi mã
  dùng 1 lần — cứu khi mất điện thoại).
- **Nhớ thiết bị**: sau khi xác minh, đặt cookie HMAC **buộc với tài khoản + mã bí mật** ⇒ **đặt lại 2FA là
  mọi thiết bị cũ hết hiệu lực**. Nhập sai quá 5 lần ⇒ huỷ phiên xác minh (phải nhập lại mật khẩu).
- **Trang Hồ sơ** (Users → Hồ sơ của bạn): xem trạng thái · **Tạo lại 10 mã dự phòng** · **Đặt lại 2FA**
  (khi đổi/mất điện thoại) · ô **tự nguyện bật** cho tài khoản ngoài danh sách bắt buộc.
- **CỨU HỘ**: `wp user meta delete <ID> _tlpi_totp_secret` — hoặc người có quyền `edit_user` bấm “Đặt lại 2FA”.
- ⚠️ 2FA áp cho **luồng đăng nhập bằng form**; API dùng Application Password / XML-RPC **KHÔNG** bị chặn
  (muốn siết thì tắt Application Passwords). Khi 2FA BẬT, mọi script đo tự động có đăng nhập
  (`branches-e2e`, `toc-admin`, `rebrand-check`, `emoji-check`, `reviews-*`…) sẽ **không vào được** — dùng
  `docs/measure/2fa-e2e.mjs` / `2fa-api-check.sh` hoặc tạm tắt 2FA.
- Mã bí mật được **mã hoá (sodium secretbox)** bằng khoá dẫn xuất từ `AUTH_KEY` + `SECURE_AUTH_SALT` trước
  khi lưu user meta.

## Về sau (chưa làm)

CPT / taxonomy / form báo giá / webhook → thêm vào `pl-tien-ich-tungleads.php` ở khối stub cuối file. Không cho vào theme.

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
1. **Settings → PL Tiện Ích → “ID tracking”**: nhập 4 ID của site mới (hoặc bỏ tick công tắc nếu không dùng tracking).
2. Cài **WooCommerce** nếu muốn “Thông số kỹ thuật” + “Đặt hàng nhanh”; plugin chạy tốt cả khi không có WC.
3. 8 trường “Thông số kỹ thuật” là chuyên ngành **cửa** — sửa `tlpi_spec_fields()` nếu dùng cho ngành khác.
4. Mục lục bù header sticky dựa vào class `.cp-header` nếu theme có (theme Cao Phát dùng) ⇒ theme khác thì offset = **0** (vẫn dùng được, chỉ là mục tiêu nằm sát mép trên).
5. Plugin **không phụ thuộc theme/site nào** (không gọi hàm của theme) ⇒ gỡ theme đang dùng vẫn chạy.

## Chuyển từ bản cũ (tiền tố `tlcp_`, thư mục `tl-site-caophat`) — v1.0.0

**Không bắt buộc chạy** — plugin tự tương thích ngược (đọc option cũ, nhận action/honeypot/nonce/meta cũ),
nên chỉ cần cập nhật file là site chạy y như trước, KHÔNG mất cấu hình. Muốn dọn cho sạch:

| Việc | Lệnh / cách làm |
| :--- | :--- |
| Xem trước | `wp eval-file docs/prod-migrate-plugin-prefix.php` |
| Chuyển option `tlcp_*` → `tlpi_*` + meta `_tlcp_spec_*` → `_tlpi_spec_*` | `wp eval-file docs/prod-migrate-plugin-prefix.php go` |
| Thứ tự an toàn khi deploy | Bật **Chế độ bảo trì** → cập nhật theme + plugin → chạy script (dry-run rồi `go`) → tắt bảo trì |
| Xoá thư mục plugin cũ | Xoá `wp-content/plugins/tl-site-caophat/` sau khi đã activate bản mới |

**⚠️ 5 điều đã dính khi đổi tên + nhập liệu (đừng lặp lại):**
1. **Option ĐÃ LƯU nhưng RỖNG** (ví dụ `tlpi_tracking_ids` = `{"on":"","gtm":"",…}`) nghĩa là *“không in khối tracking nào”* —
   nếu vô tình lưu form khi 4 ô ID trống thì site **tắt tracking**. Kiểm nhanh: `wp option get tlpi_tracking_ids`.
   Không muốn giới hạn ⇒ xoá option đó để plugin dùng lại 4 ID mặc định.
   ⚠️ **Ca nguy hiểm hơn (đã dính 2026-09-17):** `on:""` **nhưng 4 ID vẫn còn** ⇒ vẫn KHÔNG in khối nào (công tắc ở dòng đầu của mục “ID tracking”). Muốn bật lại: xoá option hoặc tick lại công tắc rồi Lưu.
2. **`wp option get active_plugins`** vẫn giữ đường dẫn plugin CŨ sau khi đổi tên thư mục ⇒ phải xoá entry cũ
   (hoặc tắt/bật lại plugin) nếu không WP báo plugin không tồn tại.
3. **`wp eval-file … -- --go` KHÔNG chạy** — WP-CLI báo `unknown --go parameter`; các script trong `docs/` dùng tham số **vị trí** `go`.
4. **Ghi option bằng CHUỖI JSON thay vì MẢNG** ⇒ plugin đọc bằng `get_option()` mong nhận **array** (Settings API lưu array); nhận chuỗi sẽ coi như “không có dữ liệu” và **quay về mặc định** (đã dính ở `docs/prod-import-plugin-options.php` bản đầu — nay ghi `tlpi_toc_sanitize(...)` dạng mảng, chuỗi JSON chỉ để IN RA).
5. **Ký tự emoji trong chuỗi hiển thị** ⇒ lõi WP đổi thành `<img src="…">` lấy từ CDN — **link vỡ** (Tùng báo 2026-09-18). Cơ chế đo được: lõi WP nạp `wp-includes/js/wp-emoji-release.min.js` (thư viện **twemoji**) rồi thay mọi emoji trong DOM thành `<img>`; site này lấy base từ `_wpemojiSettings.svgUrl` = **`false`** (theme cha `tungleads-theme` đặt `emoji_svg_url` = `false` trong `src/Features/Performance.php`) ⇒ twemoji rơi về **base mặc định của thư viện** = `https://cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/` — mà đường dẫn đó **404** (đúng phải là `…/assets/svg/…svg` hoặc `…/assets/72x72/…png`) ⇒ **ảnh vỡ**.
   - **Đã dính lần 2 (2026-09-18):** tôi (agent) thêm `⚠️` vào 2 chuỗi Settings ở `includes/login-path.php` (đúng lúc cảnh báo “Permalink đẹp” + “LƯU LẠI đường dẫn”) ⇒ trang Settings hiện ảnh vỡ. **Đã bỏ emoji, giữ nguyên câu chữ** (phần nhấn mạnh đã có màu `#b32d2e` + `font-weight:600`).
   - ⚠️ **Nguy hiểm nhất là trong `wp-admin`:** theme cha gỡ `print_emoji_detection_script` ở `wp_head` + `admin_print_scripts` khi `init`, **nhưng** `wp-admin/includes/admin-filters.php:59` gắn lại hook đó **sau** `init` ⇒ admin **vẫn chạy** twemoji (front-end thì đã sạch). Vậy: **tuyệt đối không dùng emoji trong câu chữ nào của plugin** (kể cả trong `wp-admin`). Ký tự KHÔNG bị đổi (an toàn): `→ ⇒ ✓ ✕ ★ • – …` (★ U+2605 không nằm trong bộ twemoji).
   - **Cách kiểm sau mỗi lần sửa chuỗi hiển thị:** `node docs/measure/emoji-check.mjs` (phải `soImgCDN: 0`, `soImgEmoji: 0`) + `node docs/measure/emoji-where.mjs` (phải `trongKhoiPlugin: []`).

## Deploy

Plugin có **git repo riêng** (như 2 theme); mỗi dự án đưa lên production bằng script deploy của dự án đó.

**Chỉ đẩy production khi Tùng yêu cầu rõ ràng.** Không tự ý chạy.

- Requires PHP 8.2 (giống theme). Nâng MultiPHP production **trước** khi activate.
