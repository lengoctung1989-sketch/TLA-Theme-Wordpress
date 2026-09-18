# WORKLOG — bàn giao công việc giữa các model

**Repo:** `tungleads-theme` (PARENT theme TLA Theme) · nhánh `main` · không có remote
**Lưu ý:** đây là repo parent dùng chung cho nhiều site khách — sửa ở đây ảnh hưởng mọi child theme.

> Chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**.
> **Không cần script, không cần cài gì** — chỉ đọc và sửa markdown.
> Luật chi tiết ở `CLAUDE.md` (Claude Code) và `AGENTS.md` (DeepSeek), mục "Phối hợp giữa các model".
> P-index của repo này: `P1`–`P7` — bảng nghĩa ở `CLAUDE.md`.
>
> 1. **Đầu phiên:** đọc `§1 ĐANG LÀM` + 10 dòng cuối của `§2`.
> 2. **Bắt đầu việc:** cập nhật `§1` (model, việc, file sẽ chạm, trạng thái).
> 3. **Hết việc/phiên:** cập nhật lại `§1` (xong / dang dở + việc tiếp theo) rồi ghi 1 dòng vào `§2`.
> 4. `§1` **ghi đè** (chỉ giữ khối mới nhất) · `§2` **chỉ ghi thêm**, không sửa dòng cũ.

---

## §1 ĐANG LÀM (bàn giao — ghi đè mỗi phiên, chỉ giữ 1 khối)

- **Model:** deepseek (phiên chạy **Cline**/VS Code — nhãn commit do Tùng chốt: `[deepseek]`).
- **Việc ĐÃ XONG (2026-09-18):** (a) `fix(P2.3)` gỡ twemoji cả trong **wp-admin**; (b) rút `AGENTS.md` 9,9 KB → 1,96 KB;
  (c) thêm luật **nguồn sự thật** vào `CLAUDE.md`: SỰ THẬT = §1 + FEATURE_MAP (sửa tại chỗ) · LỊCH SỬ = §2 + archive
  (chỉ ghi thêm, phải dán nhãn đính chính) — theo lo ngại của Tùng về “dòng cũ sai còn nằm lại”.
- **Đang dở:** (không). Việc của Tùng khi deploy: **ship cả parent** (fix P2.3 áp cho mọi child theme).
---

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|
| 2026-09-13 19:45 | deepseek | Tạo worklog bàn giao + luật phối hợp model | `.ai/WORKLOG.md`, `CLAUDE.md`, `AGENTS.md` | xong |
| 2026-09-13 19:50 | deepseek | Chốt nhãn model chỉ còn `claude`/`deepseek` (bỏ codex/copilot) | như trên + 3 file child | xong |
| 2026-09-18 10:58 | deepseek | `fix(P2.3)` emoji: gỡ twemoji ở **wp-admin** (thêm `admin_init` vì `admin-filters.php` nạp sau `init` gắn lại hook) + bỏ `emoji_svg_url`=`false` (làm twemoji rơi về base jsdelivr **404** ⇒ ảnh vỡ). Đo: 4 màn admin + front-end `_wpemojiSettings` không còn, tiêm emoji vào DOM ⇒ 0 `<img>` | `src/Features/Performance.php`, `CHANGELOG.md`, `.ai/FEATURE_MAP.md` | xong |


| 2026-09-18 12:32 | deepseek | Rút `AGENTS.md` 9,9 KB → 1,6 KB (trước trùng **97,6%** với `CLAUDE.md`) + thêm hạn mức tài liệu & luật “chỉ TRỎ, không chép lại”. Không mất luật nào. | `AGENTS.md`, `.ai/WORKLOG.md` | xong |
| 2026-09-18 13:28 (giờ thật) | deepseek | ❌ **ĐÍNH CHÍNH luật §2** — thêm luật “SỰ THẬT = §1 + FEATURE_MAP (sửa tại chỗ) · LỊCH SỬ = §2 + archive (chỉ thêm, phải có nhãn `❌ đã …`)”. | `CLAUDE.md`, `.ai/WORKLOG.md` | xong |