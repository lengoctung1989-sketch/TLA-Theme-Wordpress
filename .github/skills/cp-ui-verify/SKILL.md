---
name: cp-ui-verify
description: "Verify a front-end change in the tungleads-theme-cp child theme (caophat.vn) before reporting it done. Use when editing assets/caophat.css, assets/*.js, header/menu/mobile layout, or the WooCommerce shop / single product / cart / checkout skins; also use whenever you must prove that a UI change works instead of guessing. Covers starting the local WordPress Docker site (localhost:8888), measuring with Playwright (getBoundingClientRect, getComputedStyle, elementFromPoint hit tests, hover path simulation, recursive stylesheet scans), the responsive width matrix, and how to report measured numbers."
argument-hint: "[mô tả thay đổi UI vừa làm]"
---

# Kiểm chứng UI — caophat.vn (child theme `tungleads-theme-cp`)

## Khi nào dùng

Bất cứ khi nào vừa sửa `assets/caophat.css`, `assets/*.js`, `header.php`, `footer.php`, `front-page.php`, `template-parts/*`, hoặc hook WooCommerce trong `inc/woocommerce.php` — và **luôn dùng trước khi nói "đã xong"**.

Vì sao skill này tồn tại: repo đã nhiều lần kết luận **SAI** vì chỉ đọc code — panel menu tự mở dù CSS "nhìn đúng", logo đè lên nút search, rule mobile bị rule cuối file đè, giá card bị WooCommerce ép sang olive. Những lỗi đó **chỉ lộ ra khi đo**.

## Nguyên tắc: không được đoán

- Kết luận kiểu "CSS này chắc ăn rồi" là lỗi. Phải có **số đo thật**.
- Số đo phải so **trước/sau**, cùng môi trường, cùng bề rộng, và được dán vào báo cáo cho Tùng + §2 `.ai/WORKLOG.md`.
- **Không tin ảnh chụp**: tab nền của VS Code có thể render theo bề rộng thật của panel, không theo `setViewportSize`. Tin số đo.
- Không suy luận hành vi bảo mật/hiển thị từ mô tả setting hay từ DOM attribute — phải test thật (VD `required` trên input không phản ánh validate phía server).

## Quy trình

### 1. Dựng môi trường

```bash
cd "/Volumes/DATA/Claude-Code/TLA Theme" && docker compose up -d
```

- Site: http://localhost:8888 (MariaDB ở `127.0.0.1:3307`).
- `wp-cli`: `docker compose run --rm -T wpcli <lệnh>` (luôn có `-T`).
- `WP_DEBUG` + `WP_DEBUG_DISPLAY` = 1 → PHP notice hiện ngay trên trang.
- Không có build step (`filemtime()` cache-bust) → sửa file + hard-reload là thấy.

Trang hay dùng: chi tiết SP `/san-pham/<slug>/` · danh mục `/danh-muc/<slug>/` · `/cart/` · `/checkout/`. Thêm `?nc=<timestamp>` khi nghi cache.

### 2. Kiểm tra tĩnh trước khi đo

```bash
php -l <file.php>            # mọi file PHP vừa sửa
node --check assets/x.js     # file JS vừa sửa
```

CSS: số `{` phải bằng số `}` — không build step nên sai 1 ngoặc là hỏng toàn bộ skin phía sau.

### 3. Đo bằng Playwright

Chạy checklist ở [references/playwright-checks.md](./references/playwright-checks.md). Bắt buộc tối thiểu:

1. **Số đo trước/sau** của đúng phần tử vừa sửa (`getBoundingClientRect` + `getComputedStyle`).
2. **`elementFromPoint`** tại tâm và 4 điểm quanh mọi nút vừa đổi / nằm gần phần tử rộng. Nút nhỏ bị đè là lỗi thường gặp mà ảnh chụp không thấy; Playwright `click` timeout với "intercepts pointer events" cũng là dấu hiệu này.
3. **Dải bề rộng**: 1440 · 1280 · 1100 · 1024 · 768 · 390 — kiểm `document.documentElement.scrollWidth <= innerWidth` ở **từng** mức.
4. **Console = 0 lỗi JS**, HTML không có PHP notice mới.
5. Hover/focus: mô phỏng **đường di chuyển** nhiều bước nhỏ (~4–8px) sau `scrollIntoView` — KHÔNG teleport (teleport bỏ qua vùng panel → test PASS GIẢ).
6. Ảnh: kiểm request >= 400 qua `performance.getEntriesByType('resource')`.

### 4. Báo cáo

Ghi vào câu trả lời **và** §2 `.ai/WORKLOG.md`: file đã sửa · số đo trước/sau · dải bề rộng đã kiểm · lỗi còn lại · việc Tùng phải test tay (thứ máy không tự kiểm được: autofill Chrome, máy thật iOS/macOS…).

## Cạm bẫy phải nhớ (đã trả giá)

| Dấu hiệu | Nguyên nhân thật |
|---|---|
| Đo rect thấy "không chồng nhau" mà nút không bấm được | Đo nhầm phần tử — hit-test bằng `elementFromPoint` |
| Rule trong `@media` "không chạy" | Rule NGOÀI media ở CUỐI file cùng specificity đè. Quét `document.styleSheets` **phải đệ quy vào `CSSMediaRule`** |
| `justify-content: center` không căn giữa | Con là `<a>` bị kéo giãn hết khung → `margin: 0 auto` cho chính con đó |
| Ảnh méo tỉ lệ | Clamp cả `max-width` + `max-height` → chỉ clamp 1 chiều (+ `object-fit: contain`) |
| `textContent` vẫn thấy chữ dù đã `display: none` | Dùng `innerText` (hoặc `offsetParent`/rect height) để biết phần tử có hiện |
| Hover "không hoạt động" | Phần tử ngoài viewport → `scrollIntoView` rồi kiểm `rect.y >= 0 && rect.bottom <= innerHeight` trước |
| Nút hiển thị 30px nhưng cần vùng bấm 44px | Lớp phủ `::before` trong suốt 44×44 (`content:''`, `position:absolute`, `translate(-50%,-50%)`) — pseudo vẫn nhận chuột |
| Panel ẩn vẫn bắt hover | `opacity: 0` / `visibility: hidden` ở cha bị con đè → luôn kèm `pointer-events: none` ở trạng thái đóng, `auto` ở trạng thái mở (kể cả đường mở không qua hover, VD `.is-open` trên mobile) |
| CSS đè plugin "không ăn" | Chưa đếm độ ưu tiên — WooCommerce dùng tới (0,4,2) / (1,2,0). Xem `.github/instructions/theme-assets.instructions.md` |
| Nút trong `<li>` nhảy vị trí khi mở submenu | `top: 50%` tính lại theo chiều cao mới → dùng `top` cố định |

Bản án đã chốt + danh sách đầy đủ: bảng `CP…` trong `CLAUDE.md` và `.ai/FEATURE_MAP.md`.
