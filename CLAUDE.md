# tungleads-theme-cp — hướng dẫn dự án cho Claude

**Child theme** của `tungleads-theme` (parent, `Template:` trong `style.css`). Skin site-specific cho **caophat.vn** — cửa gỗ công nghiệp / cửa nhựa / cửa chống cháy.

- **Base:** `tungleads-theme@v0.1.0` (git tag trên repo parent). Nâng parent là hành động có chủ đích: đổi số ở đây + `README.md` → test lại toàn bộ child.
- **Ranh giới:** child chỉ trình bày (skin CSS + template override + hook). Business logic (CPT, taxonomy, API riêng) → plugin `tl-site-plugin`, KHÔNG cho vào child theme.
- **Deploy:** ship cả parent + child + plugin `tl-site-caophat` + `assets/dist/` của parent qua `deploy-caophat.sh` (gốc repo). **Chỉ đẩy production (`--go`) khi Tùng yêu cầu rõ ràng** — không tự ý chạy.

- Không business logic, không build step (CSS tĩnh `assets/caophat.css`).
- Parent lo: boot, FeatureRegistry, SiteMode, Setup, Enqueue, Performance, Security, SEO, WooCommerce integration, templates blog/archive/page.
- Child override: `header.php`, `footer.php`, `front-page.php`, `woocommerce.php`/hook shop, `assets/caophat.css`, template-parts trang chủ.
- Font: Be Vietnam Pro. Palette: **primary vàng nghệ `#fbaf02`** (tông chủ đạo, chữ trên nền primary dùng `--cp-on-primary` `#241d05`) · accent cam đất `#c8471f` (CTA/hotline, tương phản) · kem `#f6f4f1` (biến `--cp-*` trong `caophat.css`). Link có class nút (`.cp-btn-*`, `.cp-buynow`, `.cp-single-hotline`, `.cp-side-support-tel`) cần selector `.cp a.<class>` để thắng `.cp a{color:inherit}`.
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
| `CP3.2` | Chi tiết sản phẩm (layout theo demo moderndoor.vn) — breadcrumb mảnh + lưới 9/3 `.cp-single-layout`: `.cp-single-main` (thẻ trắng `div.product` gallery\|summary + ô tabs riêng `.cp-single-tabs.cp-card`) \| `.cp-single-side` (box "Danh mục sản phẩm" + box "Hỗ trợ trực tuyến"). Hộp giá `.cp-price-box` (cột dọc): giá + tag `.cp-price-off` "Tiết kiệm &lt;số tiền&gt;" (= giá gốc − giá bán). Badge sale trên ảnh (shop / trang chủ / liên quan / chi tiết) đổi qua filter `woocommerce_sale_flash` + helper `cp_sale_percent()` → **tag chữ nhật `-N%`** đỏ tươi (`--cp-sale` `#e30613`), dính sát góc trái-trên ảnh, chỉ bo góc dưới-phải; fallback `SALE` khi không tính được %. Trang chủ: `cp_product_card()` in `.cp-badge` cũng dùng `cp_sale_percent()`. Nút `.cp-buynow` (tel:) cạnh "Thêm vào giỏ", ẩn ô số lượng, nút hotline phụ full-width. Gallery bật mũi tên flexslider (`woocommerce_single_product_carousel_options`); `single-gallery.js` tách dải thumbnail ra ô riêng `.cp-thumbs` dưới ảnh chính + 2 nút mũi tên cuộn. Related tách khỏi `div.product` → dải `.cp-related-band` nền xám, carousel cuộn ngang (scroll-snap). Dải CTA cuối. KHÔNG copy template | ✅ |

## Quy trình / DoD

- Thêm số: định nghĩa ở bảng trên → tag `// CPx.y` → thêm dòng `.ai/FEATURE_MAP.md`.
- DoD: `php -l` sạch · không PHP notice khi `WP_DEBUG` · chuỗi bọc i18n (text domain `tungleads-theme`) · giữ phong cách CSS `.cp-*` · commit message `feat(CP3.1): ...`.
