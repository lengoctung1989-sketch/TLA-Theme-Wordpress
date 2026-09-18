# AGENTS.md — luật làm việc cho MONOREPO `TLA-Theme-Wordpress` (site caophat.vn)

**Chuẩn áp dụng:** `~/.claude/docs/project-structure-standard.md` — **v1.0 (2026-09-18)**.
Repo này là **1 monorepo gộp 4 phần triển khai** (trước 2026-09-18 là các git repo riêng, gộp bằng `git subtree` giữ lịch sử):

| Thư mục | Là gì | Đích trên WordPress | Tài liệu riêng |
| :--- | :--- | :--- | :--- |
| `parent-theme/` | Parent theme `tungleads-theme` (nền chung nhiều site) | `wp-content/themes/tungleads-theme/` | `parent-theme/AGENTS.md` + `CLAUDE.md` + `.ai/` |
| `child-theme-cp/` | Child theme `tungleads-theme-cp` (skin riêng caophat.vn) | `wp-content/themes/tungleads-theme-cp/` | `child-theme-cp/AGENTS.md` + `CLAUDE.md` + `.ai/` |
| `plugin-tien-ich/` | Plugin `pl-tien-ich-tungleads` (tracking · bảo trì · mục lục · 2FA · editor danh mục · đặt hàng nhanh) | `wp-content/plugins/pl-tien-ich-tungleads/` | `plugin-tien-ich/README.md` |
| `plugin-zalo/` | Plugin `button-call-zalo-tungleads` (widget nút liên hệ nổi) | `wp-content/plugins/button-call-zalo-tungleads/` | `plugin-zalo/README.md` |

## TRƯỚC khi code (mỗi phiên)

1. **Đọc luật của phần mình sửa**: `<phần>/AGENTS.md` (+ `CLAUDE.md`) và **§1** trong `.ai/WORKLOG.md` của phần đó.
2. `git status` + `git --no-pager log --oneline -5` — **cả 4 phần nằm trong MỘT repo**.
3. Sửa khối đã có số (`CPx.y` ở child · `Pn.m` ở parent) ⇒ **`grep` mục tương ứng trong `.ai/FEATURE_MAP.md` của phần đó TRƯỚC khi code** — nhiều quyết định đã chốt SAU KHI ĐO (kích thước, màu, hành vi) ⇒ **không được revert**.

## SAU khi xong

1. Kiểm chứng phần vừa sửa theo luật của phần đó (đo bằng trình duyệt với UI — không suy luận từ code).
2. Commit kèm **nhãn model + số**: `feat(CP3.2)[claude]: …` · `fix(P2.3)[deepseek]: …`.
3. Ghi nhật ký vào **`.ai/WORKLOG.md` của phần vừa sửa** (§1 ghi đè · §2 chỉ ghi thêm, ≤20 dòng × ≤300 ký tự).
4. Kết thúc **SẠCH**: `git status` không còn thay đổi chưa commit.

## Luật bất biến

- **Chỉ MỘT model làm việc tại một thời điểm**; model sau đọc §1 của phần đó trước khi tiếp tục.
- **Không tự deploy.** Chỉ chạy `deploy-caophat.sh` (dry-run) — `--go` **CHỈ khi Tùng yêu cầu rõ ràng**.
  ⚠️ Script rsync **từ thư mục con của monorepo** vào đúng đích WordPress (dấu `/` ở cuối đường dẫn nguồn là **bắt buộc**; thiếu ⇒ đẩy sai tên thư mục ⇒ **vỡ theme/plugin trên production**).
- **Sửa đúng phần:** skin/UI của caophat.vn ⇒ `child-theme-cp/` · nền tảng dùng chung nhiều site ⇒ `parent-theme/` · nghiệp vụ/tiện ích ⇒ `plugin-tien-ich/` · widget liên hệ ⇒ `plugin-zalo/`.
- Không commit `.env`/file nhạy cảm · **không revert quyết định đã chốt sau khi đo**.
- **Gốc monorepo KHÔNG phải theme** (không có `style.css`) ⇒ WordPress phải trỏ vào `child-theme-cp/`:
  local = 4 bind mount trong `docker-compose.yml`; production = rsync thư mục con (xem script deploy).
- **`parent-theme/` là bản chuẩn TẠM THỜI, chỉ đúng khi caophat.vn là site DUY NHẤT dùng nó.** Khi có site thứ 2 dùng `tungleads-theme` ⇒ **tách `parent-theme/` thành repo riêng** (`git subtree split --prefix=parent-theme`), mỗi monorepo site `git subtree pull` bản chuẩn về — không copy tay, tránh phân kỳ âm thầm.
- **Chỉ deploy bằng `deploy-caophat.sh` có guard mapping** (kiểm 4 file mốc trước khi rsync, thêm 2026-09-18 sau sự cố rsync sai tầng). Không tự viết/dùng script deploy khác hoặc rsync tay.
- Hạn mức tài liệu: xem bảng trong `child-theme-cp/AGENTS.md` (mỗi phần tự pin phiên bản chuẩn của mình).
