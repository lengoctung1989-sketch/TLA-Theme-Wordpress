# AGENTS.md — Hướng dẫn bắt buộc cho mọi AI agent làm việc trên repo này

**Repo:** child theme `tungleads-theme-cp` (site **caophat.vn** — cửa gỗ công nghiệp / cửa nhựa / cửa chống cháy). Child theme chỉ **trình bày** (skin CSS + template override + hook); **business logic → plugin `tl-site-caophat`**; nền tảng → parent `tungleads-theme`.
**Deploy:** `deploy-caophat.sh` (parent + child + plugin) — **chỉ chạy khi Tùng yêu cầu rõ ràng**, không tự ý.

Repo dùng **2 model chạy TUẦN TỰ: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **không bao giờ chạy song song**. Model sau chỉ bắt đầu khi model trước đã dừng hẳn và để lại nhật ký trong `.ai/WORKLOG.md`. Nhãn commit chỉ được là 1 trong 2 tên này — phiên chạy model khác (vd `cline`) → **hỏi Tùng trước khi commit**.

**Quy tắc chi tiết nằm ở `CLAUDE.md`** (bảng số `CPx.y` + quyết định đã chốt · quy ước code · môi trường · kiểm chứng). File này là **bản tóm tắt bắt buộc** — KHÔNG thêm chi tiết/số đo vào đây để khỏi tốn token mỗi phiên.

## TRƯỚC khi code (mỗi phiên)

1. **Đọc `.ai/WORKLOG.md`**: khối **§1 TRẠNG THÁI HIỆN TẠI** (việc đang làm · file đã chạm · việc tiếp theo) + **10 dòng cuối §2**. Trạng thái repo lấy từ §1 — KHÔNG lấy từ ghi chú của phiên cũ (có thể đã lỗi thời).
2. **Sửa khối đã có số CP → mở `CLAUDE.md`, đọc đúng mục `CPx.y`** — nhiều quyết định đã chốt SAU KHI ĐO (nhãn nút, icon 35px, `pointer-events` panel menu…) ⇒ **không được revert**.
3. Tra `.ai/FEATURE_MAP.md` — danh sách file thật của số CP đang sửa.
4. `git status` + `git --no-pager log --oneline -5` — xác nhận trạng thái repo.
5. Sửa file khớp `**/*.php` / `assets/**` → `.github/instructions/*.instructions.md` (tương đương `.ai/rules/` ở dự án khác) **tự gắn** — theo luật ở đó.

## Làn thay đổi — CHỌN LÀN TRƯỚC KHI LÀM

| Làn | Khi nào | Đo | Tài liệu | Báo cáo |
| :--- | :--- | :--- | :--- | :--- |
| **A — 1 thuộc tính** | ≤3 dòng CSS/JS/HTML, **không** đổi `@media`/DOM/z-index | tái dùng script đo có sẵn (`/tmp/cp-pw/`), **2–3 mức**: 1440 · 390 · breakpoint liên quan | **chỉ 1 dòng §2 ≤ ~300 ký tự**; không đụng `CLAUDE.md` / FEATURE_MAP | **1–2 dòng** |
| **B — 1 khối** | 1 khối CSS hoặc 1 template-part, có responsive/hành vi | 4–6 mức + `elementFromPoint` nếu có phần tử tương tác | 1 dòng §2 + cập nhật mục `CPx.y` trong `CLAUDE.md` (+ FEATURE_MAP nếu đổi file) | 3–5 dòng |
| **C — tính năng / đổi mô hình** | nhiều file, đổi cấu trúc, thêm UI | đầy đủ + E2E (Customizer/Playwright) | 3 lớp: `CLAUDE.md` + FEATURE_MAP + §1/§2 | báo cáo có cấu trúc |

**Luôn giữ ở MỌI làn (không cắt):** `scrollWidth <= innerWidth` ở mobile khi đụng kích thước/nội dung · `php -l` file PHP có sửa · `node --check` file JS có sửa · **1 dòng §2 ngay khi xong**.
**Cắt ở làn A/B:** sweep 6–13 mức · ảnh chụp (chỉ khi Tùng yêu cầu) · quét nhiều URL chỉ để tìm PHP notice · viết script đo MỚI · đọc file dài (dùng `grep -n` + `sed -n` đúng cửa sổ).
**Yêu cầu mơ hồ → hỏi 1 câu ngắn có lựa chọn TRƯỚC khi làm** — làm sai rồi đo lại + viết lại tài liệu là khoản lãng phí lớn nhất.
**Đo bằng trình duyệt rồi mới kết luận** (không suy luận từ code) — quy trình + snippet: `.github/skills/cp-ui-verify/SKILL.md`.

## SAU khi xong (trước khi dừng phiên)

1. Kiểm chứng phần vừa sửa cho XANH (theo làn ở trên) — không để lại lỗi đã biết mà không ghi.
2. Đồng bộ **3 lớp**: `CLAUDE.md` (định nghĩa + quyết định của số CP) → tag `// CPx.y` trong code → dòng tương ứng trong `.ai/FEATURE_MAP.md`.
3. Commit kèm nhãn model: `feat(CP3.1)[deepseek]: ...` · `fix(CP2.5)[claude]: ...`.
4. Ghi `.ai/WORKLOG.md`: **1 dòng vào cuối §2** (chỉ ghi thêm, không sửa dòng cũ) + **ghi đè §1** (trạng thái + việc tiếp theo).
5. Kết thúc phiên ở trạng thái SẠCH: working tree không còn thay đổi chưa commit.
6. Việc dở dang **phải** nằm trong §1 — không có việc nào "bỏ lửng" mà không ghi.

## Quy trình 1 khung chát = 1 chức năng (Tùng dùng)

Tùng làm **tuần tự**: mỗi chức năng (1 số CP) nằm trong 1 khung chát riêng, **không mở 2 khung cùng lúc**; khung mới chỉ mở khi khung trước đã SẠCH.

| Tùng gõ | Agent phải làm |
| :--- | :--- |
| **“Chốt phiên CPx.y”** | Đồng bộ 3 lớp → `php -l` / `node --check` file có sửa → commit `feat(CPx.y)[model]: …` → 1 dòng §2 + ghi đè §1 → xác nhận `git status` sạch. (Tương đương lệnh `/cp-wrapup`.) |
| **“Tạm dừng CPx.y, commit dở dang”** | Ghi §1: đang ở đâu · còn file nào phải sửa · lưu ý → commit `wip(CPx.y)[model]: <mô tả>` → xác nhận `git status` sạch. Không cần xanh 100% nhưng phải ghi rõ chỗ đang dở/đỏ. |

## Luật bất biến

- **Chỉ MỘT model làm việc tại một thời điểm** — model sau đọc §1 WORKLOG trước, tiếp tục mục dở dang nếu có.
- **Không tự deploy / push production**, không tự chạy `deploy-caophat.sh --go` (chỉ khi Tùng yêu cầu rõ ràng). Việc chỉ chạy được trên prod (regenerate thumbnail, ảnh `.webp` do LiteSpeed sinh, sửa dữ liệu host) là phần bàn giao của Tùng.
- Child theme **không chứa business logic**, không thêm build step; không sửa plugin `tl-site-caophat` / parent nếu không được yêu cầu.
- Không commit file nhạy cảm; **không revert quyết định đã chốt sau khi đo**.
- **Không thêm chi tiết vào file này** — chi tiết thuộc `CLAUDE.md`, `.ai/FEATURE_MAP.md`, `.ai/WORKLOG.md`.

## Môi trường (local)

- Bật: `cd "/Volumes/DATA/Claude-Code/TLA Theme" && docker compose up -d` → http://localhost:8888 (MariaDB `127.0.0.1:3307`).
- wp-cli: `docker compose run --rm -T wpcli <lệnh>` — **luôn có `-T`**. VD: `wpcli theme mod list`.
- **Không có build step**: CSS/JS enqueue theo `filemtime()` → sửa file rồi hard-reload (Cmd+Shift+R).
