# AGENTS.md — điểm vào cho agent (DeepSeek / Cline) · repo `tungleads-theme`

**⚠️ Luật làm việc ĐẦY ĐỦ nằm ở `CLAUDE.md` — ĐỌC FILE ĐÓ TRƯỚC KHI LÀM.** File này chỉ giữ phần không được quên
(trước đây file này chép lại gần như toàn bộ `CLAUDE.md` ⇒ trùng lặp, tốn token mỗi phiên — bỏ 2026-09-18).

- **Đây là PARENT theme dùng chung cho nhiều site khách** ⇒ sửa ở đây **ảnh hưởng MỌI child theme**. Nâng parent =
  commit có chủ đích + ghi `CHANGELOG.md` → cập nhật dòng `Base: tungleads-theme@vX.Y.Z` ở từng child → test lại.
- **Chỉ MỘT model làm việc tại một thời điểm**: `claude` / `deepseek` chạy **tuần tự**. Nhãn commit chỉ được là
  `claude` hoặc `deepseek` — phiên model khác (vd `cline`) → **hỏi Tùng trước khi commit**.
- **`.ai/WORKLOG.md`: §1 GHI ĐÈ mỗi phiên · §2 CHỈ GHI THÊM.** Trạng thái lấy từ §1, không lấy từ ghi chú phiên cũ.
- **P-index (`Pn.m`)**: nghĩa + nhóm + số đã đặt ở `CLAUDE.md` §“P-index”; chi tiết file/test ở `.ai/FEATURE_MAP.md`.
- **Môi trường:** WordPress ≥ 6.5 · PHP ≥ 8.2 · text domain `tungleads-theme` · namespace `TL\Theme\` → `src/` (PSR-4) ·
  site mode `hybrid`. Local: `docker-compose.yml` ở gốc repo `TLA Theme`, WP core ở `../../../wordpress/`.
- **Không tự deploy / push production.** `vendor/` + `assets/dist/` đã commit sẵn ⇒ deploy **không cần** Composer/Node.
- **Hạn mức tài liệu (chống phình token) — kiểm `wc -c` trước khi commit:** `CLAUDE.md` ≤ 12 KB · `AGENTS.md` ≤ 4 KB ·
  1 dòng §2 ≤ 300 ký tự (≈350 byte) · §1 ≤ 10 dòng. **CẤM chép lại nội dung giữa các file — chỉ được TRỎ** (luật → CLAUDE.md ·
  chi tiết tính năng → `.ai/FEATURE_MAP.md` · việc theo phiên → `.ai/WORKLOG.md`).
