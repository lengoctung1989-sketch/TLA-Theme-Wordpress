<?php
/**
 * CP5.3 — Trang THẺ (lưu trữ theo tag) — `/the/<slug>/` (tag base = `the`, option `tag_base`).
 *
 * Trước CP5.3 trang thẻ rơi vào `archive.php` của theme cha: `main.tl-main.tl-container` (không
 * `.cp-container`), **không hero, không breadcrumb, không cột phải**, danh sách là `.tl-post-card`
 * (không ảnh) — đo 2026-09-16: 0 thẻ `.cp-news-card` / 10 `.tl-post-card` → lệch hẳn skin Cao Phát.
 *
 * Nay dùng CHUNG thân trang với chuyên mục (`template-parts/news-archive.php`): hero `.cp-pagehero`
 * + lưới 2 cột `.cp-news-layout` + `.cp-news-card` + sidebar. Khác biệt của trang thẻ nằm trong
 * partial (`$cp_is_tag`): breadcrumb "Trang chủ / Tin tức / Từ khoá: …", câu rỗng "Thẻ này chưa có
 * bài viết nào." và hộp cột phải thêm "Từ khoá phổ biến" (`cp_news_tags_box()`, `inc/news.php`).
 *
 * @package TL\Theme\CP
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/news-archive' );
get_footer();
