---
description: "Use when editing the Cao Phát Door child theme front-end assets — assets/caophat.css (the whole skin) and assets/*.js (nav menu, quick order, single gallery). Covers CSS specificity battles with WooCommerce, design tokens, the no-build-step flow, and the measure-don't-guess rule."
applyTo: "assets/**"
---

# CSS / JS — skin Cao Phát (`assets/`)

`assets/caophat.css` là **toàn bộ** giao diện (`.cp-*`). `style.css` chỉ là header khai báo theme — **không viết CSS vào đó**. Không có build step: sửa file → hard-reload (Cmd+Shift+R) là thấy.

## CSS

- Đặt tên theo phong cách có sẵn: `.cp-<khối>`, `.cp-<khối>__<phần>`, `.cp-<khối>--<biến thể>` (VD `.cp-side-box`, `.cp-nav__toggle`, `.cp-shop-toolbar`).
- Màu/khoảng cách lấy từ token `--cp-*` ở đầu file; không hard-code hex mới khi token đã có.
- Mở khối bằng comment `/* CPx.y <slug> */` — cùng số với bảng `CPx.y` trong `CLAUDE.md`.
- Đè link/nút của theme parent: theme parent có `body.cp …` và nhiều `!important`; link mang class nút cần `.cp a.<class>` để thắng `.cp a { color: inherit }`.
- Tôn trọng `prefers-reduced-motion` khi thêm transition/animation.

### Đè CSS của WooCommerce = phải ĐẾM độ ưu tiên

Đây là bẫy lặp lại nhiều nhất của repo (đã ghi từng lần trong `.ai/FEATURE_MAP.md`). Quy tắc:

1. **Đếm trước khi viết.** WooCommerce dùng selector rất mạnh:
   - `.woocommerce:where(body:not(.woocommerce-uses-block-theme)) ul.products li.product .price` = **(0,4,2)** (đã ép giá card về olive sai 1 lần)
   - `.woocommerce ul.order_details li` = **(0,2,2)**
   - `.woocommerce form .form-row-first` / `... textarea` = **(0,2,2)**
   - `.woocommerce #payment #place_order` = **(1,2,0)** (nền tím mặc định)
2. **Thêm 1 class để thắng — hạn chế `!important`** (`!important` chỉ dùng để chặn nền autofill của Chrome/UA stylesheet).
3. Rule NGOÀI `@media` ở **cuối file** cùng specificity sẽ đè rule trong `@media` → override mobile phải nâng specificity.
4. Sửa xong **đo lại** `getComputedStyle` đúng thuộc tính vừa đổi, rồi dán số vào báo cáo.
5. Đừng quên `ins { text-decoration: none }` khi reskin giá (trình duyệt gạch chân `<ins>` mặc định).

## JS

- Vanilla, không thêm dependency. Bọc `(function () { 'use strict'; … })()`; thoát sớm nếu thiếu phần tử (`if (!el) { return; }`).
- JS là lớp tăng cường: quản lý `aria-expanded` / `aria-controls`, khoá cuộn khi mở panel, trả focus về nút đã mở khi đóng bằng Esc.
- Vùng bấm nhỏ hơn 44px → thêm lớp phủ trong suốt `::before` (44×44, `position:absolute`, `translate(-50%,-50%)`), không phình kích thước hiển thị.
- Kiểm cú pháp: `node --check assets/<file>.js`; sau đó xem console trang local phải **0 lỗi JS**.

## Trước khi báo xong

Đo bằng trình duyệt thật, không suy luận từ code — quy trình + snippet ở skill **`cp-ui-verify`** (`.github/skills/cp-ui-verify/`). Tối thiểu: số đo trước/sau · dải bề rộng 1440/1280/1100/1024/768/390 · không tràn ngang · `elementFromPoint` cho mọi nút vừa đổi.
