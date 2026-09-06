# TLA Theme — Cao Phát Door (`tungleads-theme-cp`)

Child theme của [`tungleads-theme`](../tungleads-theme). Chỉ chứa **skin riêng của site caophat.vn** — không có business logic, không có build step.

- Parent: `tungleads-theme` (khai báo `Template:` trong `style.css`)
- Font: Be Vietnam Pro (Google Fonts) · Palette: nâu gỗ `#8a5a2b`, cam đất `#c8471f`
- CSS: `assets/caophat.css` (tĩnh, enqueue trong `functions.php` sau bundle theme cha)

## Cấu trúc

| File | Vai trò |
|---|---|
| `functions.php` | Enqueue font + CSS; `add_theme_support('custom-logo')`; body class `cp`; helper `cp_product_card()`, `cp_hotline_*()` |
| `header.php` / `footer.php` | Override: topbar, header sticky, footer tối, nút hotline nổi (FAB) |
| `front-page.php` | Trang chủ — khối tĩnh (hero/feature/CTA) inline i18n; khối động gọi template-part |
| `template-parts/home-categories.php` | Lưới `product_cat` (6 danh mục nhiều sản phẩm nhất) |
| `template-parts/home-products.php` | Lưới sản phẩm — `type=best` (theo `total_sales`) \| `type=sale` (đang giảm) |
| `template-parts/home-blog.php` | 3 bài viết mới nhất |
| `assets/caophat.css` | Toàn bộ CSS giao diện (`.cp-*`) + phần thích ứng WordPress |
| `assets/images/` | 6 ảnh cửa mockup (hero + 5 loại cửa) |

## Cấu hình sau khi kích hoạt

1. **Appearance → Customize → Site Identity**: upload logo, đổi Site Title thành "Cao Phát Door".
2. Gán menu vào vị trí **Primary**.
3. **Settings → Reading**: front page = trang tĩnh "Trang chủ".
4. Hotline mặc định `0834.021.021` — đổi bằng filter `cp_hotline_display` / `cp_hotline_tel`.

## Ghi chú

- Hero/feature/CTA để chuỗi trong `front-page.php`. Chuyển sang block pattern nếu client cần tự sửa nội dung.
- Card danh mục dùng ảnh cửa mặc định khi `product_cat` chưa set thumbnail.
