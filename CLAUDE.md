# tungleads-theme-cp — hướng dẫn dự án cho Claude

**Child theme** của `tungleads-theme` (parent, `Template:` trong `style.css`). Skin site-specific cho **caophat.vn** — cửa gỗ công nghiệp / cửa nhựa / cửa chống cháy.

- Không business logic, không build step (CSS tĩnh `assets/caophat.css`).
- Parent lo: boot, FeatureRegistry, SiteMode, Setup, Enqueue, Performance, Security, SEO, WooCommerce integration, templates blog/archive/page.
- Child override: `header.php`, `footer.php`, `front-page.php`, `woocommerce.php`/hook shop, `assets/caophat.css`, template-parts trang chủ.
- Font: Be Vietnam Pro. Palette: nâu gỗ `#8a5a2b` · cam đất `#c8471f` · kem `#f6f4f1` (biến `--cp-*` trong `caophat.css`).
- Hotline mặc định `0834.021.021` — filter `cp_hotline_display` / `cp_hotline_tel`.

## P-index — quy ước riêng của child: tiền tố **`CP`**

`CP<nhóm>.<số>`. Tách khỏi P-index của parent (P1–P7) để `grep` không lẫn.
3 lớp đồng bộ: file này (nghĩa) · tag `// CPx.y` ở entry point · `.ai/FEATURE_MAP.md` (file).

### Nhóm

| Nhóm | Phạm vi |
| :--- | :--- |
| **CP1** | Khung/chrome — bootstrap child, header, footer, hotline, logo |
| **CP2** | Trang chủ — `front-page.php`, template-part, Customizer |
| **CP3** | WooCommerce skin — trang danh mục/cửa hàng, chi tiết sản phẩm |
| **CP4** | Assets — `caophat.css`, ảnh |

### Số đã đặt

| Số | Nghĩa | Trạng thái |
| :--- | :--- | :--- |
| `CP1.1` | Bootstrap child — enqueue font + `caophat.css` (sau bundle parent), `add_theme_support('custom-logo')`, body class `cp`, helper `cp_hotline_*()`, `cp_product_card()` | ✅ |
| `CP1.2` | Header — topbar, header sticky (logo `the_custom_logo`/fallback, `wp_nav_menu` primary → `.cp-nav`, hotline) | ✅ |
| `CP1.3` | Footer — footer tối 4 cột (menu `footer`, contact), footer-bottom, nút hotline nổi (FAB) | ✅ |
| `CP2.1` | Trang chủ — `front-page.php`: hero + 4 feature + CTA (chuỗi i18n / theme_mod) + gọi 3 template-part động | ✅ |
| `CP2.2` | Khối động trang chủ — `home-categories` (6 product_cat), `home-products` (best=total_sales \| sale), `home-blog` (3 bài mới) | ✅ |
| `CP2.3` | Customizer — section `cp_home`: hero (nhãn/tiêu đề `<em>`/mô tả/ảnh) + CTA (tiêu đề/mô tả). `front-page.php` đọc `get_theme_mod` fallback default i18n | ✅ |
| `CP3.1` | Trang danh mục / cửa hàng — hook `woocommerce_before/after_main_content` dựng pagehero + layout 2 cột (sidebar `product_cat` + lưới). Loop item bọc `.cp-card` qua hook. Nút loop → "Xem chi tiết". KHÔNG copy template WooCommerce | ✅ |
| `CP3.2` | Chi tiết sản phẩm — gallery + tabs + related theo `product-detail.html` | ⏳ chưa làm |

## Quy trình / DoD

- Thêm số: định nghĩa ở bảng trên → tag `// CPx.y` → thêm dòng `.ai/FEATURE_MAP.md`.
- DoD: `php -l` sạch · không PHP notice khi `WP_DEBUG` · chuỗi bọc i18n (text domain `tungleads-theme`) · giữ phong cách CSS `.cp-*` · commit message `feat(CP3.1): ...`.
