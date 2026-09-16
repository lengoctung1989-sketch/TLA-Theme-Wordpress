<?php
/**
 * CP5.1 — Trang danh mục tin tức (lưu trữ chuyên mục bài viết).
 *
 * Override của child CHỈ áp cho chuyên mục (category). Các lưu trữ khác: thẻ → `tag.php` (CP5.3),
 * tác giả / ngày / định dạng → `archive.php` của theme cha.
 *
 * Thân trang nằm ở `template-parts/news-archive.php` (**dùng chung với trang thẻ — CP5.3**);
 * khối hiển thị nằm ở `inc/news.php` (tag `// CP5.1`).
 * Trước CP5.1 trang này dùng `archive.php` của theme cha: không có `.cp-container`, không hero,
 * danh sách là `.tl-post-card` (không ảnh) → lệch hẳn skin Cao Phát.
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/news-archive' );
get_footer();
