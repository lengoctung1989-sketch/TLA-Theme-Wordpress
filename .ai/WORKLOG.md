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

- **Model:** deepseek (phiên này chạy **Cline**/VS Code — nhãn commit do Tùng chốt: `[deepseek]`)
- **Việc:** `fix(P2.3)` — emoji/twemoji: gỡ nốt trong **wp-admin** + bỏ `emoji_svg_url` = `false` (nguyên nhân emoji trong admin thành **ảnh vỡ** trỏ `cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/<code>.svg` — **404**). Tùng chọn phương án **A: sửa ở theme cha** (2026-09-18).
- **File đang chạm:** `src/Features/Performance.php`, `CHANGELOG.md`, `.ai/FEATURE_MAP.md`, `.ai/WORKLOG.md` (parent) + `.ai/WORKLOG.md` của child `tungleads-theme-cp`
- **Đã xong:** `disableEmoji()` nay chạy ở **cả `init` lẫn `admin_init`** (vì `wp-admin/includes/admin-filters.php` nạp **sau** `init` rồi gắn lại hook) + gỡ thêm `embed_head` và `admin_enqueue_scripts` → `wp_enqueue_emoji_styles`. Đo: 4 màn admin + front-end đều `_wpemojiSettings` **không còn**, `window.twemoji` = `undefined`; tiêm `⚠️ ✅ 🔴 ★` vào DOM ⇒ **0 `<img>`** (vẫn là ký tự, không còn ảnh vỡ). `php -l` + `vendor/bin/phpcs` sạch.
- **Việc tiếp theo:** (không) — fix này áp cho **MỌI child theme** khi deploy parent. Lưu ý cho child: không cần/không nên dùng emoji trong chuỗi hiển thị nữa (nay hiện đúng ký tự, nhưng để nhất quán thì tránh — xem bẫy #5 trong README plugin `pl-tien-ich-tungleads`).
- **Cập nhật lúc:** 2026-09-18 10:58


---

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|
| 2026-09-13 19:45 | deepseek | Tạo worklog bàn giao + luật phối hợp model | `.ai/WORKLOG.md`, `CLAUDE.md`, `AGENTS.md` | xong |
| 2026-09-13 19:50 | deepseek | Chốt nhãn model chỉ còn `claude`/`deepseek` (bỏ codex/copilot) | như trên + 3 file child | xong |
| 2026-09-18 10:58 | deepseek | `fix(P2.3)` emoji: gỡ twemoji ở **wp-admin** (thêm `admin_init` vì `admin-filters.php` nạp sau `init` gắn lại hook) + bỏ `emoji_svg_url`=`false` (làm twemoji rơi về base jsdelivr **404** ⇒ ảnh vỡ). Đo: 4 màn admin + front-end `_wpemojiSettings` không còn, tiêm emoji vào DOM ⇒ 0 `<img>` | `src/Features/Performance.php`, `CHANGELOG.md`, `.ai/FEATURE_MAP.md` | xong |

