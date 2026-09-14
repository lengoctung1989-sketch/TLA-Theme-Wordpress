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
- **Việc:** CP3.2 — `.cp-side-branches`: canh đều trái/phải (tên trái — số phải), bỏ icon đầu dòng
- **File đang chạm:** `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md`
- **Trạng thái:** XONG
- **Đã xong:** Bỏ markup icon + biến `$ic_branch` + rule `.cp-side-branch__ic`; `.cp-side-branches a` về `justify-content: space-between; align-items: baseline; gap: 10px`; bỏ dấu `":"` sau tên (không còn cần khi tên/số tách 2 mép); rút lại `$path`/`$icon` về 1 dòng như cũ vì không còn nơi dùng thứ 2. Kiểm chứng `localhost:8888`: 0 phần tử icon; 4/4 dòng tên sát mép trái, số sát mép phải, cùng 1 hàng
- **Việc tiếp theo:** (không) — chờ việc tiếp theo
- **Cập nhật lúc:** 2026-09-14 09:35

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
| 2026-09-14 09:10 | deepseek | CP3.2 mobile: padding thẻ SP + ô tabs 28px → 10px; sửa rule chết `.cp-single-layout > div.product` | `assets/caophat.css`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-14 09:25 | deepseek | CP3.2 mobile: padding thẻ SP + ô tabs 10px → 15px | `assets/caophat.css`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-14 09:35 | deepseek | CP3.2: `.cp-side-branches` canh đều trái/phải, bỏ icon đầu dòng + bỏ dấu ":" | `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
