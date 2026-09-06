# A11y pass — V1 (WCAG 2.1 AA baseline)

## Contrast (palette theme.json trên nền `base` #ffffff)

| Token | Hex | Tỉ lệ vs #fff | Kết luận |
| :--- | :--- | :--- | :--- |
| `contrast` (body text) | #16181d | ~17.7:1 | AAA |
| `muted` (meta text) | #5b616e | ~6.2:1 | AA (text thường) |
| `primary` (link) | #1d4ed8 | ~7.7:1 | AAA |
| `primary-hover` | #1739a8 | ~9:1 | AAA |
| `accent` (sale/CTA) | #0f766e | ~4.9:1 | AA (text thường) |
| `base` text trên `primary` (nút) | #fff/#1d4ed8 | ~7.7:1 | AAA |
| `base` text trên `accent` (badge) | #fff/#0f766e | ~4.9:1 | AA |

`border` #d9dce1 chỉ dùng cho đường viền trang trí (không mang nghĩa) nên không tính contrast text.

## Keyboard / focus

- Skip-link (`header.php`): ẩn ngoài màn hình, hiện rõ khi focus, trỏ `#main`.
- `:focus-visible` toàn cục → viền `--tl-focus-ring` (2px primary) + offset 2px (`style.css`).
- Menu chính: `wp_nav_menu` xuất `<nav aria-label> > ul > li > a` semantic, không JS bắt buộc.
- Cart / Mini-cart / Checkout / bộ lọc: dùng block chính thức của WooCommerce (a11y do WooCommerce đảm nhiệm); theme chỉ thêm focus ring cho control WC Blocks (`woocommerce.css`).
- `prefers-reduced-motion: reduce` → mọi `--tl-dur-*` về 0ms (`tokens.css`).

## ARIA

Chỉ dùng khi HTML semantic không đủ: `aria-label` cho `<nav>` (menu chính, điều hướng bài viết), `aria-hidden`/`tabindex="-1"` cho link ảnh trùng link tiêu đề trong card, `screen-reader-text` cho nhãn ngữ cảnh nút "Xem chi tiết".

## Còn treo (ngoài phạm vi V1)

- Chưa test với trình đọc màn hình thực tế (NVDA/VoiceOver) — cần làm trước khi phát hành theme công khai.
- Chưa có e2e/axe tự động trong CI (spec: không bắt buộc ở V1).
