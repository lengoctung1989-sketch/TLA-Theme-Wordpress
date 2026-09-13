# WORKLOG — bàn giao công việc giữa các model

**Repo:** `tungleads-theme-cp` (child theme caophat.vn) · nhánh `main` · không có remote
**Parent:** `tungleads-theme` (pin version bằng git tag, xem dòng `Base:` trong `README.md`)

> Chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**.
> **Không cần script, không cần cài gì** — chỉ đọc và sửa markdown.
> Luật chi tiết ở `CLAUDE.md` (Claude Code) và `AGENTS.md` (DeepSeek), mục "Phối hợp giữa các model".
> P-index của repo này dùng tiền tố **`CP`** (`CP1`–`CP4`) — bảng nghĩa ở `CLAUDE.md`.
>
> 1. **Đầu phiên:** đọc `§1 ĐANG LÀM` + 10 dòng cuối của `§2`.
> 2. **Bắt đầu việc:** cập nhật `§1` (model, việc, file sẽ chạm, trạng thái).
> 3. **Hết việc/phiên:** cập nhật lại `§1` (xong / dang dở + việc tiếp theo) rồi ghi 1 dòng vào `§2`.
> 4. `§1` **ghi đè** (chỉ giữ khối mới nhất) · `§2` **chỉ ghi thêm**, không sửa dòng cũ.

---

## §1 ĐANG LÀM (bàn giao — ghi đè mỗi phiên, chỉ giữ 1 khối)

- **Model:** deepseek
- **Việc:** CP3.2 — hotline chi nhánh: thêm icon + canh trái, và làm **sửa được trong admin** (Settings → Cao Phát ở plugin)
- **File đang chạm:** `inc/woocommerce.php` + `assets/caophat.css` + 3 file tài liệu (repo child) · `plugins/tl-site-caophat/tl-site-caophat.php` + `README.md` (repo ngoài, thư mục `/wordpress/` không được git track)
- **Trạng thái:** XONG
- **Đã xong:** (1) Icon nhỏ đầu mỗi dòng, cả dòng canh trái. (2) Plugin thêm trang **Settings → Cao Phát** (option `tlcp_support_branches`, mỗi dòng `Tên | Số`, có nonce + `manage_options`), hàm `tlcp_support_branches()` parse dữ liệu; theme đọc qua `function_exists()` và có mảng dự phòng nếu plugin tắt. Backup plugin: `.scratch/backup-tl-site-caophat/tl-site-caophat.php.bak-20260913-2130` (hash khớp bản gốc). Kiểm chứng: menu admin hiện `options-general.php => Cao Phát | slug=tlcp-support | cap=manage_options`; render ra textarea 4 dòng mặc định + nonce + nút Lưu; đổi option → frontend đổi theo, xoá option → về 4 số mặc định; `php -l` cả 2 file sạch
- **Việc tiếp theo:** (không) — LƯU Ý chưa test bằng mắt trong wp-admin vì phiên browser không có cookie đăng nhập (Tùng tự mở `Settings → Cao Phát` kiểm tra)
- **Cập nhật lúc:** 2026-09-13 20:45

---

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|
| 2026-09-13 19:45 | deepseek | Tạo worklog bàn giao + luật phối hợp model | `.ai/WORKLOG.md`, `CLAUDE.md`, `AGENTS.md` | xong |
| 2026-09-13 19:50 | deepseek | Chốt nhãn model chỉ còn `claude`/`deepseek` (bỏ codex/copilot) | như trên + 3 file parent | xong |
| 2026-09-13 20:05 | deepseek | CP3.2: chiều cao thu gọn mô tả ngắn `7.5em` → `15em` + đồng bộ 3 lớp tài liệu | `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:15 | deepseek | CP3.2: nhãn nút mua → "THÊM GIỎ HÀNG"; ẩn số hotline trên mobile (chỉ còn "Gọi ngay") | `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:25 | deepseek | CP3.2: thông số kỹ thuật — nhãn + giá trị cùng 1 dòng ("Kích thước: 1m x 3m") | `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:30 | deepseek | CP3.2: box "Hỗ trợ trực tuyến" thêm 4 hotline chi nhánh (filter `cp_support_branches`) | `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:40 | deepseek | CP3.2: box "Hỗ trợ trực tuyến" — icon đầu mỗi dòng + canh trái toàn bộ | `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:45 | deepseek | CP3.2: hotline chi nhánh sửa được trong admin (plugin thêm Settings → Cao Phát, option `tlcp_support_branches`) | `plugins/tl-site-caophat/tl-site-caophat.php` (+README), `themes/tungleads-theme-cp/inc/woocommerce.php` | xong |
