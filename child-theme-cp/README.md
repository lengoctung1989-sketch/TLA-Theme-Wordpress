# TLA Theme — Cao Phát Door (`tungleads-theme-cp`)

Child theme của [`tungleads-theme`](../tungleads-theme). Chỉ chứa **skin riêng của site caophat.vn** — không có business logic, không có build step.

- Parent: `tungleads-theme` (khai báo `Template:` trong `style.css`) · Base: `tungleads-theme@v0.1.0` (git tag)
- Parent lo: bootstrap, FeatureRegistry, SiteMode, Setup, Enqueue, Performance, Security, SEO, WooCommerce integration, template blog/archive/page
- Font: Be Vietnam Pro (Google Fonts) · Palette: primary vàng nghệ `#fbaf02` (chữ trên nền primary `#241d05`), accent cam đất `#c8471f`, badge sale đỏ `#e30613`, kem `#f6f4f1` (biến `--cp-*` trong `caophat.css`)
- CSS: `assets/caophat.css` (tĩnh, enqueue trong `functions.php` sau bundle theme cha)
- Business logic site-specific (CPT/taxonomy/API) → plugin `tl-site-plugin`, KHÔNG nằm ở child theme này

## Cấu trúc

| File | Vai trò |
|---|---|
| `functions.php` | Enqueue font + CSS; `add_theme_support('custom-logo')`; body class `cp`; helper `cp_product_card()`, `cp_hotline_*()` |
| `header.php` / `footer.php` | Override: topbar, header sticky, footer (3–4 cột, mỗi cột = tiêu đề + ô soạn thảo, nền màu/ảnh tuỳ biến), nút hotline nổi (FAB). **Sửa trong Customizer → “Footer Cao Phát”** |
| `front-page.php` | Trang chủ — khối tĩnh (hero/feature/CTA) inline i18n; khối động gọi template-part |
| `template-parts/home-categories.php` | Lưới `product_cat` (6 danh mục nhiều sản phẩm nhất) |
| `template-parts/home-tabs.php` | Khối tab sản phẩm — 4 tab cố định (Mới · Bán chạy · Hot · Khuyến mãi), tuỳ chọn trong Customizer |
| `template-parts/home-cat-products.php` | Khối "Sản phẩm theo danh mục" (repeater trong Customizer) |
| `template-parts/home-cat-news.php` | Khối "Tin tức theo chuyên mục" (repeater trong Customizer) — 4 bố cục: Lưới 3 cột · Lưới 2 cột · Cuộn ngang · **Nổi bật (1 bài lớn bên trái + 5 bài bên phải)** |
| `assets/caophat.css` | Toàn bộ CSS giao diện (`.cp-*`) + phần thích ứng WordPress |
| `assets/images/` | 6 ảnh cửa mockup (hero + 5 loại cửa) |
| `screenshot.jpg` | **Ảnh thumbnail cho `/wp-admin/themes.php`** (yêu cầu Tùng 2026-09-16): ảnh do Tùng gửi, nén còn **1200×675 JPEG ~273KB** (bản gốc 1600×900 PNG 1.6MB ở `/tmp/cp-pw/screenshot-original-backup.png`). ⚠️ Card theme trong WP là khung **3:2** (`padding-top: 66.67%`, `img{position:absolute;top:0;width:100%}`) nên ảnh 16:9 **hụt đáy 41px (~16%)** — muốn đầy khít thì dùng ảnh tỉ lệ **3:2** (VD 1200×800). WordPress ưu tiên `screenshot.png` trước `.jpg` ⇒ **không đặt cả 2 file**. |

## Cấu hình sau khi kích hoạt

1. **Appearance → Customize → Site Identity**: upload logo, đổi Site Title thành "Cao Phát Door".
2. Gán menu vào vị trí **Primary**.
3. **Settings → Reading**: front page = trang tĩnh "Trang chủ".
4. Hotline mặc định `0834.021.021` — đổi bằng filter `cp_hotline_display` / `cp_hotline_tel`.

## Ghi chú

- Hero/feature/CTA để chuỗi trong `front-page.php`. Chuyển sang block pattern nếu client cần tự sửa nội dung.
- Card danh mục dùng ảnh cửa mặc định khi `product_cat` chưa set thumbnail.
