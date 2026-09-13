# tungleads-theme-cp — hướng dẫn dự án cho DeepSeek

**Child theme** của `tungleads-theme` (parent, `Template:` trong `style.css`). Skin site-specific cho **caophat.vn** — cửa gỗ công nghiệp / cửa nhựa / cửa chống cháy.

- **Base:** `tungleads-theme@v0.1.0` (git tag trên repo parent). Nâng parent là hành động có chủ đích: đổi số ở đây + `README.md` → test lại toàn bộ child.
- **Ranh giới:** child chỉ trình bày (skin CSS + template override + hook). Business logic (CPT, taxonomy, API riêng) → plugin `tl-site-plugin`, KHÔNG cho vào child theme.
- **Deploy:** ship cả parent + child + plugin `tl-site-caophat` + `assets/dist/` của parent qua `deploy-caophat.sh` (gốc repo). **Chỉ đẩy production (`--go`) khi Tùng yêu cầu rõ ràng** — không tự ý chạy.

- Không business logic, không build step (CSS tĩnh `assets/caophat.css`).
- Parent lo: boot, FeatureRegistry, SiteMode, Setup, Enqueue, Performance, Security, SEO, WooCommerce integration, templates blog/archive/page.
- Child override: `header.php`, `footer.php`, `front-page.php`, `woocommerce.php`/hook shop, `assets/caophat.css`, template-parts trang chủ.
- Font: Be Vietnam Pro. Palette: **primary vàng nghệ `#fbaf02`** (tông chủ đạo, chữ trên nền primary dùng `--cp-on-primary` `#241d05`) · accent cam đất `#c8471f` (CTA/hotline, tương phản) · kem `#f6f4f1` (biến `--cp-*` trong `caophat.css`). Link có class nút (`.cp-btn-*`, `.cp-buynow`, `.cp-single-hotline`, `.cp-side-support-tel`) cần selector `.cp a.<class>` để thắng `.cp a{color:inherit}`.
- Hotline mặc định `0834.021.021` — filter `cp_hotline_display` / `cp_hotline_tel`.

## Phối hợp giữa các model (BẮT BUỘC)

Repo này chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**. Nhãn model chỉ được là 1 trong 2 tên này.
Trạng thái bàn giao nằm ở `.ai/WORKLOG.md` — không script, không cài thêm gì.

1. **Đầu phiên:** đọc `.ai/WORKLOG.md` (§1 ĐANG LÀM + 10 dòng cuối §2) trước khi làm bất cứ việc gì.
2. **Bắt đầu việc:** cập nhật §1 — model, việc đang làm, file sẽ chạm, trạng thái.
3. **Hết việc / hết phiên:** cập nhật lại §1 (xong / dang dở + việc tiếp theo) và ghi 1 dòng vào §2 (bảng nhật ký).
4. §1 **ghi đè** (chỉ giữ khối mới nhất) · §2 **chỉ ghi thêm**, không sửa/xoá dòng cũ.
5. §1 đang ghi việc **dang dở của model khác** → không tự sửa tiếp file đó, hỏi người dùng trước.
6. Commit kèm nhãn model — chỉ `[claude]` hoặc `[deepseek]`: `feat(CP3.2)[deepseek]: ...`

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
| `CP3.1` | Trang danh mục / cửa hàng — hook `woocommerce_before/after_main_content` dựng pagehero + layout 2 cột (sidebar `product_cat` tái dùng `.cp-side-box`/`.cp-side-cats` của CP3.2 + lưới **4 cột × 5 dòng = 20 sp/trang** qua `loop_shop_per_page`). Loop item bọc `.cp-card` qua hook. Nút loop → "Xem chi tiết". Mô tả danh mục (`woocommerce_archive_description`) gỡ khỏi header mặc định → render DƯỚI lưới trong `.cp-shop-desc` (thu gọn sẵn + nút "Xem thêm/Thu gọn", ~8 dòng JS inline). `woocommerce_show_page_title` = false (pagehero đã có H1). Thanh công cụ `.cp-shop-toolbar` (bọc `woocommerce_before_shop_loop` @19/@35): `[số kết quả]` … `.cp-price-filter` (nút Tất cả / 1–3tr / 3–5tr / >5tr qua query var `min_price`/`max_price` sẵn có, bấm nút đang chọn = bỏ lọc, giữ `orderby`; cũng in ở `woocommerce_no_products_found` để bỏ lọc khi 0 kết quả) `[sắp xếp]` (`select` reskin, mũi tên SVG data-URI). `.cp-shop-main` = thẻ trắng shadow như `.cp-side-box`. Phân trang reskin căn giữa, nút bo tròn 42px, mũi tên ‹ ›. KHÔNG copy template WooCommerce | ✅ |
| `CP3.2` | Chi tiết sản phẩm (layout theo demo moderndoor.vn) — breadcrumb mảnh + lưới 9/3 `.cp-single-layout`: `.cp-single-main` (thẻ trắng `div.product` gallery\|summary + `.cp-spec` thông số + ô tabs riêng `.cp-single-tabs.cp-card`) \| `.cp-single-side` (box "Hỗ trợ trực tuyến" `cp_single_support_box()` — số chính + `.cp-side-branches` danh sách hotline chi nhánh (mỗi dòng icon + "tên: số", cả dòng canh trái, `tel:`, filter `cp_support_branches`) + box "Cam kết Cao Phát" `cp_trust_box()` — icon + chữ hoa, filter `cp_trust_items` + box "Sản phẩm mới" `cp_single_new_products_box()` (5 SP mới nhất)). Hộp giá `.cp-price-box` (cột dọc): giá + tag `.cp-price-off` "Tiết kiệm &lt;số tiền&gt;" (= giá gốc − giá bán). Badge sale trên ảnh (shop / trang chủ / liên quan / chi tiết) đổi qua filter `woocommerce_sale_flash` + helper `cp_sale_percent()` → **tag chữ nhật `-N%`** đỏ tươi (`--cp-sale` `#e30613`), dính sát góc trái-trên ảnh, chỉ bo góc dưới-phải; fallback `SALE` khi không tính được %. Trang chủ: `cp_product_card()` in `.cp-badge` cũng dùng `cp_sale_percent()`. Mô tả ngắn → hộp `.cp-short-desc` nổi bật + thu gọn `max-height:15em` (nút Xem thêm/Thu gọn). Dòng "Thẻ:" (`.tagged_as`) ẩn bằng CSS. Nút `.cp-buynow` (tel:, icon túi) cạnh nút mua — nhãn đổi qua filter `woocommerce_product_single_add_to_cart_text` thành "Thêm giỏ hàng", CSS `text-transform: uppercase` sẵn có hiển thị "THÊM GIỎ HÀNG" (icon giỏ qua `::before`); dưới là hàng `.cp-contact-row` = "Gọi ngay" (cam, số hotline bọc `.cp-tel-num` ẩn ở `≤768px` — mobile và nút CTA cuối trang chỉ còn "Gọi ngay") + "Chat Zalo" (xanh `#0068ff`, `zalo.me/<số>`, filter `cp_zalo_url`), mỗi nút có icon. Ẩn ô số lượng, nút hotline phụ full-width. Gallery bật mũi tên flexslider (`woocommerce_single_product_carousel_options`); `single-gallery.js` tách dải thumbnail ra ô riêng `.cp-thumbs` dưới ảnh chính + 2 nút mũi tên cuộn. Related tách khỏi `div.product` → dải `.cp-related-band` nền xám, carousel cuộn ngang (scroll-snap). Dải CTA cuối. Ngay dưới ô tabs: thẻ `.cp-spec` "Thông số kỹ thuật" (`cp_single_spec_table()`) — đọc 8 meta `_tlcp_spec_*` do plugin `tl-site-caophat` lưu, lưới 2 cột, mỗi dòng 1 hàng ngang (nhãn + giá trị cùng dòng qua `dt`/`dd` `display:inline`, nhãn thêm `": "` bằng `::after`) + icon SVG inline. KHÔNG copy template | ✅ |

## Quy trình / DoD

- Thêm số: định nghĩa ở bảng trên → tag `// CPx.y` → thêm dòng `.ai/FEATURE_MAP.md`.
- DoD: `php -l` sạch · không PHP notice khi `WP_DEBUG` · chuỗi bọc i18n (text domain `tungleads-theme`) · giữ phong cách CSS `.cp-*` · commit message `feat(CP3.1): ...`.
