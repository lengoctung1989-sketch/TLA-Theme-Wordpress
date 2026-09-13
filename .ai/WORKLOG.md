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
- **Việc:** Chỉnh `.cp-short-desc__body` (CP3.2) — chiều cao thu gọn `7.5em` → `15em`
- **File đang chạm:** `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md`
- **Trạng thái:** XONG
- **Đã xong:** Đổi `max-height` hộp mô tả ngắn trang chi tiết SP; đồng bộ lại 3 lớp tài liệu (nghĩa ở `CLAUDE.md`/`AGENTS.md` + bảng `.ai/FEATURE_MAP.md`). Kiểm chứng trên `localhost:8888`: computed `max-height: 225px` (= 15em × 15px), nút "Xem thêm" vẫn hiện vì nội dung 1532px > 225px; CSS tự cache-bust qua `filemtime`
- **Việc tiếp theo:** (không) — chờ việc tiếp theo
- **Cập nhật lúc:** 2026-09-13 20:05

---

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|
| 2026-09-13 19:45 | deepseek | Tạo worklog bàn giao + luật phối hợp model | `.ai/WORKLOG.md`, `CLAUDE.md`, `AGENTS.md` | xong |
| 2026-09-13 19:50 | deepseek | Chốt nhãn model chỉ còn `claude`/`deepseek` (bỏ codex/copilot) | như trên + 3 file parent | xong |
| 2026-09-13 20:05 | deepseek | CP3.2: chiều cao thu gọn mô tả ngắn `7.5em` → `15em` + đồng bộ 3 lớp tài liệu | `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
