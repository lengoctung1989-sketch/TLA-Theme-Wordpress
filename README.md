# TLA Theme Wordpress — caophat.vn

Monorepo gộp 3 thành phần triển khai cho site **caophat.vn** (WordPress/WooCommerce, dựa trên Flatsome).
Cả 3 phải cài đủ thì site mới chạy đúng — child theme không tự hoạt động nếu thiếu parent theme.

```
TLA-Theme-Wordpress/
├── parent-theme/        # Parent theme tuỳ biến (tungleads-theme) — nền tảng dùng chung nhiều site
├── child-theme-cp/       # Child theme riêng cho caophat.vn (tungleads-theme-cp) — style/tính năng riêng site
└── plugin-tien-ich/       # Plugin tiện ích riêng site (pl-tien-ich-tungleads) — 2FA, term editor, v.v.
```

## Cài đặt lên WordPress

Copy/deploy đúng thư mục đích:

| Thư mục trong repo | Đích trên WordPress |
|---|---|
| `parent-theme/` | `wp-content/themes/tungleads-theme/` |
| `child-theme-cp/` | `wp-content/themes/tungleads-theme-cp/` |
| `plugin-tien-ich/` | `wp-content/plugins/pl-tien-ich-tungleads/` |

Kích hoạt theo thứ tự: **parent theme trước** (không kích hoạt trực tiếp, chỉ cần có mặt) → **kích hoạt child theme** (Giao diện > Themes) → **kích hoạt plugin**.

## Tài liệu chi tiết từng phần

Mỗi thư mục con là 1 dự án con độc lập, có tài liệu riêng:

- `child-theme-cp/CLAUDE.md`, `child-theme-cp/AGENTS.md`, `child-theme-cp/.ai/` — quy ước code, P-index (CPx.y), nhật ký làm việc của child theme.
- `parent-theme/` — xem README/CLAUDE.md riêng trong thư mục (nếu có).
- `plugin-tien-ich/` — xem README riêng trong thư mục.

## Lịch sử gộp repo

3 thư mục trên trước đây là 3 git repo riêng (đúng với cách chúng được version độc lập trong máy dev), được gộp vào monorepo này bằng `git subtree` (giữ nguyên lịch sử commit từng phần) ngày 2026-09-18, để dễ quản lý trên GitHub và phản ánh đúng quan hệ phụ thuộc (child theme không chạy được nếu thiếu parent theme).
