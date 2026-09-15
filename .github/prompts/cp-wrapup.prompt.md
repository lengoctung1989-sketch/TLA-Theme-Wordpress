---
description: "Chốt phiên làm việc cho repo tungleads-theme-cp: cập nhật .ai/WORKLOG.md (§1 + §2), đồng bộ 3 lớp tài liệu (bảng CP trong AGENTS.md/CLAUDE.md · tag trong code · .ai/FEATURE_MAP.md), kiểm cú pháp rồi commit LOCAL kèm nhãn model."
---

Chốt phiên làm việc cho child theme `tungleads-theme-cp` (caophat.vn).

## 1. `.ai/WORKLOG.md`

- `§1 ĐANG LÀM` — **ghi đè** bằng đúng 1 khối mới: model · việc đang làm · file đã chạm · trạng thái (xong / dang dở + việc tiếp theo) · việc cần Tùng duyệt.
- `§2 NHẬT KÝ` — **chỉ thêm** 1 dòng vào cuối bảng: `| <giờ thật> | <model> | <việc đã làm> | <file chính> | <trạng thái> |`. Không sửa/xoá dòng cũ.
- Giờ lấy bằng `date "+%Y-%m-%d %H:%M"` — **không nối tiếp** dãy giờ của các dòng cũ (đã từng lệch hàng giờ).
- Khi thêm/sửa dòng bảng markdown: anchor vào **heading kế tiếp**, không anchor vào ô `| ✅ |` (ô này lặp ở cuối mọi dòng → dễ làm vỡ bảng). Sau khi sửa, kiểm lại: `grep -c '^| 2026-' .ai/WORKLOG.md` và không có dòng trống nằm giữa bảng.

## 2. Đồng bộ 3 lớp cho mọi số `CP` vừa chạm

| Lớp | Ở đâu | Kiểm gì |
|---|---|---|
| Nghĩa | bảng số trong `AGENTS.md` **và** `CLAUDE.md` | 2 file phải khớp nhau |
| File thuộc số nào | tag `// CPx.y` ở entry point trong code | `grep -rn "CPx.y" .` |
| File + chi tiết kỹ thuật | `.ai/FEATURE_MAP.md` | có dòng cho số đó |

Số mới thì định nghĩa ở bảng TRƯỚC, rồi mới gắn tag. Số là ID bất biến — không renumber, gap thoải mái.

## 3. Kiểm tra trước khi commit

- `php -l` mọi file PHP vừa sửa · `node --check` file JS vừa sửa.
- CSS: số `{` phải bằng số `}`.
- `git status --short` — không để lọt `.DS_Store`, log tạm, file debug.

## 4. Commit LOCAL

- `feat(CPx.y)[<model>]: …` hoặc `fix(CPx.y)[<model>]: …`.
- Child theme chỉ chấp nhận nhãn `claude` / `deepseek`. Nếu phiên này do model khác chạy → **hỏi Tùng** trước khi commit.
- Repo **không có remote** → không push, không đề xuất deploy. Deploy production là việc của Tùng + Claude Code.

## 5. In bảng tóm tắt

File đã sửa · số CP · số đo/kiểm chứng · việc Tùng cần test tay (thứ máy không tự kiểm được) · việc còn treo.
