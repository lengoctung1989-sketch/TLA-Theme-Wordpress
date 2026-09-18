# TLA-Theme-Wordpress — CHỈ MỤC monorepo (caophat.vn)

**1 repo = 4 phần** (trước 2026-09-18 là các repo riêng ⇒ đã gộp bằng `git subtree`). **Luật làm việc: `AGENTS.md` cùng thư mục** (đọc trước khi code).

| Cần sửa gì | Mở thư mục | Luật của phần | Chi tiết tính năng |
| :--- | :--- | :--- | :--- |
| Skin/UI/trang của caophat.vn | `child-theme-cp/` | `child-theme-cp/AGENTS.md` | `child-theme-cp/.ai/FEATURE_MAP.md` → `### CPx.y` |
| Nền tảng dùng chung nhiều site | `parent-theme/` | `parent-theme/AGENTS.md` + `CLAUDE.md` | `parent-theme/.ai/FEATURE_MAP.md` → `Pn.m` |
| Tracking · bảo trì · mục lục · 2FA · editor danh mục · đặt hàng nhanh | `plugin-tien-ich/` | `plugin-tien-ich/README.md` | README plugin (mục “Hiện có”) |
| Widget nút liên hệ nổi | `plugin-zalo/` | `plugin-zalo/README.md` | README plugin |
| Đẩy production | (ngoài repo) | — | `/Volumes/DATA/Claude-Code/TLA Theme/deploy-caophat.sh` (16 bước A–D, mặc định dry-run) |

**⚠️ 2 điều dễ vỡ (đã dính):**
1. Child theme **không chạy** nếu thiếu parent theme (lỗi `parent theme missing`).
2. **Gốc monorepo không phải theme** (không có `style.css`) ⇒ phải trỏ WordPress vào `child-theme-cp/`:
   *local* = 4 bind mount trong `docker-compose.yml` (thiếu ⇒ WP báo `theme_no_stylesheet`) · *production* = rsync thư mục con trong script deploy.

**Chuẩn chung:** `~/.claude/docs/project-structure-standard.md` **v1.0** — CHANGELOG cùng thư mục.
