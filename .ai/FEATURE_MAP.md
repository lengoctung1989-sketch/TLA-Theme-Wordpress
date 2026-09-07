# FEATURE_MAP — tungleads-theme-cp

Nghĩa từng số ở `CLAUDE.md`. Bảng này liệt kê file + chi tiết kỹ thuật.
Tiền tố `CP` để tách khỏi P-index của theme cha.

| CP-index | Chức năng / nhiệm vụ | File | Chi tiết kỹ thuật |
| :--- | :--- | :--- | :--- |
| **CP1.1** bootstrap | Nạp font + CSS skin sau bundle theme cha, khai báo custom-logo, gắn body class `cp`, cung cấp helper dùng chung. | `functions.php` | `wp_enqueue_scripts` prio 20: `cp-fonts` (Be Vietnam Pro) + `cp-style` (`assets/caophat.css`). `after_setup_theme`: `add_theme_support('custom-logo')`. `body_class` += `cp`. `cp_hotline_display()`/`cp_hotline_tel()` (filterable), `cp_product_card(WC_Product)` |
| **CP1.2** header | Topbar (3 ưu điểm + hotline) + header sticky (logo, menu chính, khối hotline). | `header.php` | `the_custom_logo()` / fallback text. `wp_nav_menu('primary', items_wrap ul)`. `<button.cp-burger>` (chưa gắn JS). Không mở `<main>` (template tự mở) |
| **CP1.3** footer | Footer tối 4 cột + dòng bản quyền + nút gọi nổi. | `footer.php` | Cột 2 = `wp_nav_menu('footer')`. `admin_email` cho email. `.cp-fab > a.call` (tel). `wp_footer()` |
| **CP2.1** front-page | Layout trang chủ: khối tĩnh + gọi khối động. | `front-page.php` | Hero (badge/h1 `<em>`/desc/ảnh/2 nút) · strip 4 feature (mảng PHP) · CTA band. Đọc `get_theme_mod('cp_hero_*','cp_cta_*')` với fallback `__()`. `get_template_part` cho 3 khối động |
| **CP2.2** home-blocks | 3 khối động lấy dữ liệu thật. | `template-parts/home-categories.php` | `get_terms('product_cat', number 6, orderby count, exclude default)` → `.cp-cats`. Ảnh: `thumbnail_id` term meta / fallback `door-hdf.png` |
| | | `template-parts/home-products.php` | `$args['type']`: `best` = `wc_get_products(orderby meta_value_num, meta_key total_sales)` · `sale` = `wc_get_product_ids_on_sale()` rand. Limit 4 → `cp_product_card()` |
| | | `template-parts/home-blog.php` | `WP_Query(post, 3, no_found_rows)` → `.cp-posts`, `<time>` = tên category / ngày |
| **CP2.3** customizer | Sửa chữ + ảnh hero/CTA không cần code. | `functions.php` (`customize_register`) | Section `cp_home` prio 30. Setting: `cp_hero_badge/title/desc` (title = `wp_kses_post`, còn lại `sanitize_text/textarea_field`), `cp_hero_image` (`WP_Customize_Image_Control`, `esc_url_raw`), `cp_cta_title/desc`. `transport refresh` |
| **CP3.1** shop-danh-mục | Skin trang cửa hàng + lưu trữ product_cat/tag: pagehero + 2 cột (sidebar + lưới), card `.cp-card`. | `inc/woocommerce.php` | Xem "CP3.1 — chi tiết" dưới |
| **CP3.2** single-product | Layout chi tiết sản phẩm theo demo moderndoor.vn: lưới 9/3, thẻ trắng, ô tabs riêng, sidebar (danh mục + hỗ trợ), hộp giá + badge %, nút MUA HÀNG, related carousel nền xám. | `inc/woocommerce.php`, `assets/caophat.css` (khối CP3.2), `assets/single-gallery.js` | Guard `cp_is_single_product()`. `cp_single_open` mở `.cp-single-section > .cp-container > breadcrumb + .cp-single-layout > .cp-single-main`. `cp_single_close` @ `woocommerce_after_main_content`: render `.cp-single-tabs.cp-card` + `woocommerce_output_product_data_tabs()`, đóng `.cp-single-main`, in `<aside.cp-single-side>` (`cp_product_cat_box` + `cp_single_support_box`), đóng layout, rồi `.cp-related-band` + `woocommerce_output_related_products()` + dải CTA. `remove_action` mặc định của `woocommerce_output_product_data_tabs`/`woocommerce_output_related_products` @ `woocommerce_after_single_product_summary`. `cp_single_cat_label` prio 4. `cp_single_price_box_open/close` prio 9/11: `_open` mở `.cp-price-box`, `_close` in `.cp-price-off` "Tiết kiệm &lt;wc_price(diff)&gt;" SAU `.price` rồi đóng div. Helper `cp_regular_sale_price()` (xử lý biến thể), `cp_sale_percent()`. Filter `woocommerce_sale_flash` (prio 10, 3 args) đổi mọi `.onsale` → `-N%`. `cp_single_buynow_btn` @ `woocommerce_before_add_to_cart_button` prio 5. `cp_single_hotline_btn` @ `woocommerce_after_add_to_cart_button` prio 20. `cp_gallery_carousel_options` filter `woocommerce_single_product_carousel_options` → `directionNav`. Ô số lượng ẩn bằng CSS. `single-gallery.js` (enqueue @ prio 20, guard `is_product()`): bọc `ol.flex-control-thumbs` vào `.cp-thumbs` (ô riêng dưới ảnh chính) + 2 nút `.cp-thumbs-nav` cuộn strip; `MutationObserver` chờ flexslider dựng xong control nav. |
| | | `assets/caophat.css` | `.woocommerce div.product` grid 2 cột (gallery \| summary) — `.cp.woocommerce div.product > .woocommerce-product-gallery\|.summary { width:100%; float:none }` ghi đè float/width 48% của `woocommerce-layout.css` để 2 cột đều nhau; tabs + related full-width; reskin `.summary`, `p.price`, `form.cart`, `.product_meta`, `ul.tabs`, `.woocommerce-Tabs-panel`, `table.shop_attributes`, `.related ul.products` → `.cp-card` |
| **CP4.1** css | Toàn bộ giao diện `.cp-*` + phần thích ứng WordPress. | `assets/caophat.css` | Tokens `--cp-*`, buttons, topbar/header/hero/features/section/cats/products/cta/posts/footer/fab, responsive. Cuối file: adapt `wp_nav_menu`, `.screen-reader-text`, WooCommerce price, `main#main` padding |
| **CP4.2** images | Ảnh mockup. | `assets/images/*.png` | `hero.png` + `door-{hdf,mdf,composite,korea,fire}.png` (1024²) |

## CP3.1 — chi tiết trang danh mục

Không copy `woocommerce/archive-product.php`. Dùng hook (theme cha đã gỡ `woocommerce_get_sidebar` ở P4.1; cha cũng set `loop_shop_columns=3`, `loop_shop_per_page=12` ở P4.2).

| Hook / filter | Việc |
| :--- | :--- |
| `woocommerce_before_main_content` (bỏ `woocommerce_output_content_wrapper`, thêm mới) | In `.cp-pagehero` (breadcrumb + `woocommerce_page_title`) + mở `.cp-section > .cp-container > .cp-shop`; in `.cp-sidebar` (`wp_list_categories('product_cat')`); mở `.cp-shop-main` |
| `woocommerce_after_main_content` (bỏ wrapper cũ, thêm mới) | Đóng `.cp-shop-main` + `.cp-shop` + `.cp-container` + `.cp-section` |
| `woocommerce_before_shop_loop_item_title` prio 5 / 20 | Mở `.cp-card-media` (bọc sale flash + thumbnail) → đóng, mở `.cp-card-body` + in tên `product_cat` (`.cp-card-cat`) |
| `woocommerce_after_shop_loop_item` prio 20 | Đóng `.cp-card-body` |
| `woocommerce_loop_add_to_cart_link` | Thay nút giỏ hàng bằng link "Xem chi tiết" class `cp-card-btn` |
| Guard | Chỉ chạy khi `is_shop() \|\| is_product_taxonomy()` (không đụng single product) |

CSS thêm ở `caophat.css`: `.cp-pagehero`, `.cp-shop`, `.cp-sidebar` (WP markup `wp_list_categories`), `.woocommerce ul.products` → lưới 3 cột, `li.product` → card.
