---
description: "Use when creating or editing PHP in the tungleads-theme-cp child theme (functions.php, header.php, footer.php, front-page.php, inc/woocommerce.php, template-parts/*). Covers the child-theme boundary, the CP-index tagging rule, i18n/escaping, and hook-over-template-copy."
applyTo: "**/*.php"
---

# PHP — child theme Cao Phát Door

Luật chung ở `AGENTS.md` (bản tóm tắt); bảng số + quy ước chi tiết ở `CLAUDE.md`; danh sách file của từng số nằm ở `.ai/FEATURE_MAP.md`. Đây chỉ là luật ngắn khi sửa `.php`.

## Ranh giới (không thương lượng)

- Child theme **chỉ trình bày**: skin CSS, template override, hook. Không CPT/taxonomy/API/đơn hàng riêng.
- Business logic site-specific → plugin `wordpress/wp-content/themes/tungleads-theme-cp/plugin-tien-ich/` (monorepo `TLA-Theme-Wordpress`).
- **Không copy template WooCommerce** (không tạo `woocommerce/*.php` trong child). Luôn dùng hook/filter — xem cách `inc/woocommerce.php` đang làm.
- Đổi version parent (`Template:` / dòng `Base:` trong `README.md`) là hành động có chủ đích: đổi số + test lại toàn bộ child.

## Trước khi viết code

1. Số `CP<nhóm>.<số>` phải **có sẵn** trong bảng của `CLAUDE.md`. Chưa có → định nghĩa số trước, rồi mới code.
2. Tìm chỗ đã chạm để không làm trùng/lạc: `grep -rn "CP3.1" .`
3. Đọc mục `CPx.y` tương ứng trong bảng `CLAUDE.md` trước khi sửa khối đã có — nhiều thứ trông "thừa" nhưng là **quyết định đã chốt sau khi đo** (nhãn nút mua, icon 35px, `max-width` 1 chiều của logo, `pointer-events` của panel menu…). Đừng revert.

## Quy ước code

- Đầu file: `defined( 'ABSPATH' ) || exit;`
- Tag `// CPx.y` (đúng cú pháp comment của ngôn ngữ) ở **entry point** của tính năng: hook đăng ký, hàm chính, page/component gốc.
- Hàm helper prefix `cp_`; docblock `@package TL\Theme\CP`; dùng type hint + return type, ưu tiên `static fn` cho closure ngắn.
- Mọi chuỗi hiển thị bọc i18n, **text domain `tungleads-theme`**: `esc_html__( 'Giỏ hàng', 'tungleads-theme' )`. Escape khi in (`esc_html`, `esc_attr`, `wp_kses_post`).
- Sanitize input/URL: `sanitize_text_field`, `esc_url_raw`, `absint`. Form/AJAX phải có nonce.
- Điểm khách tự chỉnh trong admin → `get_theme_mod()` + Customizer (xem CP2.3). Điểm lập trình viên ghi đè → `apply_filters()` (VD `cp_hotline_display`, `cp_support_branches`, `cp_zalo_url`).
- JS tắt / plugin tắt → trang vẫn phải dùng được (progressive enhancement: nút đặt nhanh vẫn giữ `href="tel:…"`).

## Trước khi báo xong

- `php -l <file>` sạch cho **mọi** file PHP vừa sửa (`php` có sẵn trên máy).
- Tải lại trang local với `WP_DEBUG` bật → không có PHP notice/warning mới.
- Đồng bộ **3 lớp**: bảng số trong `CLAUDE.md` · tag trong code · dòng trong `.ai/FEATURE_MAP.md`.
- Cập nhật `.ai/WORKLOG.md` (§1 + 1 dòng §2). Có thể dùng lệnh `/cp-wrapup`.
- Gợi ý Tùng chạy `/ponytail-review` sau khi xong 1 page/module.
