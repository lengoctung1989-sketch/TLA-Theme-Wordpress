# WORKLOG — bàn giao công việc giữa các model

**Repo:** `tungleads-theme-cp` (child theme caophat.vn) · nhánh `main` · **không có remote** (Tùng chốt 2026-09-14: dự án không dùng remote — đừng đề xuất push)
**Parent:** `tungleads-theme` (pin version bằng git tag, xem dòng `Base:` trong `README.md`)

> Chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**.
> **Không cần script, không cần cài gì** — chỉ đọc và sửa markdown.
> Luật chi tiết + bảng số CP ở **`CLAUDE.md`** (nguồn duy nhất); `AGENTS.md` chỉ là bản tóm tắt bắt buộc (cấu trúc Tùng chốt 2026-09-15).
> P-index của repo này dùng tiền tố **`CP`** (`CP1`–`CP5`) — bảng nghĩa ở `CLAUDE.md`.
>
> 1. **Đầu phiên:** đọc `§1 ĐANG LÀM` + 10 dòng cuối của `§2`.
> 2. **Bắt đầu việc:** cập nhật `§1` (model, việc, file sẽ chạm, trạng thái).
> 3. **Hết việc/phiên:** cập nhật lại `§1` (xong / dang dở + việc tiếp theo) rồi ghi 1 dòng vào `§2`.

> 4. `§1` **ghi đè** (chỉ giữ khối mới nhất) · `§2` **chỉ ghi thêm**, không sửa dòng cũ.

---

## §1 ĐANG LÀM (bàn giao — ghi đè mỗi phiên, chỉ giữ 1 khối)

- **Model:** phiên này chạy **Cline** (VS Code) — **nhãn commit do Tùng chốt: `[deepseek]`**.
- **Việc ĐÃ XONG phiên 2026-09-18:** (1) `.cp-spec__ic` 40→30px; (2) `.cp-contact-btn` thêm `padding: 10px`;
  (3) plugin: bỏ emoji khỏi 2 chuỗi Settings (ảnh vỡ Twemoji); (4) **theme cha** fix P2.3: gỡ twemoji cả trong wp-admin;
  (5) **tối ưu tài liệu**: `CLAUDE.md` thành **chỉ mục CP** (131,6 KB → **6,9 KB**, -95%), chi tiết dồn về `.ai/FEATURE_MAP.md`,
  thêm **hạn mức chống phình** + luật “một sự thật một nhà” vào `AGENTS.md`. (6) **ngoài repo**: chốt + áp 6 chỉnh sửa vào chuẩn cấu trúc dự án `~/.claude/docs/project-structure-standard.md` (bản 18b) — Tùng tự xử 2 việc còn lại. Chi tiết: §2 các dòng `08:06` · `10:48` · `10:58` · `11:56` · `12:20` · `12:50` · `13:25` · `14:10`.
- **Đang dở:** (không) — cả 5 repo đã commit SẠCH.
- **Chờ TÙNG (việc duy nhất còn lại):** chạy `./deploy-caophat.sh --go` để đẩy production — checklist 16 bước nằm ngay trong
  file đó (nhóm A/B/C/D). Khi deploy: plugin đã **v1.2.0** + **theme cha có fix P2.3** ⇒ phải ship **cả parent**.
  Muốn dùng 2FA / đổi đường dẫn admin thì **bật SAU** khi deploy xong.
- **Còn tồn trên production (phần Tùng):** regenerate thumbnail (96 ảnh lỗi) · ảnh `.webp` do LiteSpeed sinh · sửa dữ liệu host.

---
## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|

> 📦 **Rotate 2026-09-18:** 144 dòng cũ (trước 2026-09-13 19:45) đã chuyển **nguyên văn** sang `.ai/WORKLOG-archive-2026-Q3.md`. §2 chỉ giữ **≤20 dòng gần nhất** (luật ở `AGENTS.md`). Tra việc cũ: `grep -n "<khoá>" .ai/WORKLOG-archive-2026-Q3.md` **Rút gọn 2026-09-18:** 20 dòng đang giữ đã viết lại ≤ 300 ký tự/dòng; bản ĐẦY ĐỦ ở `.ai/FEATURE_MAP.md` §“§2 GỐC”.

| 2026-09-17 14:35 (giờ thật) | cline | **Đo lại mục lục theo CẤU HÌNH TÙNG TỰ CHỈNH** (Tùng vào Settings → Cao Phát đổi: nhãn **“Mục lục”**, mép **TRÁI**, màu **`#ffa305`**): xác nhận từng… *(xem FEATURE_MAP §2 GỐC)* | `docs/measure/toc-left.mjs`, `docs/measure/README.md` | ✅ |
| 2026-09-17 14:12 (giờ thật) | cline | **CP8 v0.4.1 — THÊM KHỐI MỤC LỤC TRONG NỘI DUNG BÀI VIẾT** (Tùng: *“thêm tuỳ chọn mục lục hiển thị trong phần nội dung bài viết”*, ngay sau khi tự tay… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/toc.php`, `assets/toc.css`, `assets/t | ✅ |
| 2026-09-17 14:38 (giờ thật) | cline | **CP1.9 — “CHI NHÁNH & HOTLINE CAO PHÁT” CHUYỂN TỪ PLUGIN VỀ THEME** (Tùng hỏi *“chuyển phần (Hotline chi nhánh) trong plugin ra ngoài phần cài đặt …… *(xem FEATURE_MAP §2 GỐC)* | theme: `inc/customizer.php`, `functions.php`, `inc/wooc | ✅ |
| 2026-09-17 14:57 (giờ thật) | cline | **1) TRẢ LỜI CÂU HỎI “mang plugin sang WordPress khác có chạy không?” — ĐÃ ĐO THẬT:** dựng **một WordPress MỚI** trong Docker (WP 7.1 + t | plugin: `tl-site-caophat.php` (**v0.6.0* | ✅ — ❌ đã đổi tên 2026-09-17 → plugin `pl-tien-ich-tungleads` |
| 2026-09-17 15:10 (giờ thật) | cline | **ĐỔI TÊN + GHI CHÚ PLUGIN THEO HƯỚNG DÙNG CHUNG (v0.7.0)** (Tùng: *“tận dụng Plugin này cho các dự án website WordPress khác → các thông | plugin: `tl-site-caophat.php` (**v0.7.0* | ✅ — ❌ đã đổi tên 2026-09-17 → plugin `pl-tien-ich-tungleads` |
| 2026-09-17 15:21 (giờ thật) | cline | **XOÁ ẢNH EMOJI LỖI TRONG PLUGIN** (Tùng: *“Xoá link ảnh lỗi trong plugin `https://cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/26a0 | plugin: `tl-site-caophat.php`, `assets/t | ✅ — ❌ đã đổi tên 2026-09-17 → plugin `pl-tien-ich-tungleads` |
| 2026-09-17 15:38 (giờ thật) | cline | **PLUGIN v1.0.0 — ĐỔI TÊN “SẠCH HẲN” ĐỂ DÙNG CHUNG** (Tùng: *“Muốn đổi luôn cho ‘sạch’ hẳn không? -> đổi luôn cho mình -> test lại kỹ đảm bảo không lỗ… *(xem FEATURE_MAP §2 GỐC)* | plugin: **đổi tên thư mục + `pl-tien-ich-tungleads.php` | ✅ |
| 2026-09-17 15:58 (giờ thật) | cline | **CP3.9 — SỬA “CÓ SP HIỆN, CÓ SP KHÔNG HIỆN TAB ĐÁNH GIÁ”** (Tùng: *“kiểm tra phần đánh giá sản phẩm làm sao để hiện đánh giá lên các sản phẩm → hiện… *(xem FEATURE_MAP §2 GỐC)* | `CLAUDE.md` (CP3.9), `.ai/FEATURE_MAP.md` (CP3.9), `.ai | ✅ |
| 2026-09-17 16:13 (giờ thật) | cline | **BÀN GIAO DEPLOY CHO PHIÊN SAU (Claude) + 2 bug bắt được khi test** (Tùng: *“Claude hiện không biết các thay đổi mới nhất của bạn để push lên product… *(xem FEATURE_MAP §2 GỐC)* | `deploy-caophat.sh`, `docs/prod-import-plugin-options.p | ✅ |
| 2026-09-17 21:59 (giờ thật) | cline | **PLUGIN v1.1.0 — TÍNH NĂNG 1/2: ĐỔI ĐƯỜNG DẪN ĐĂNG NHẬP (ẩn wp-admin)** (Tùng: *“Tiếp tục thêm cho tôi chức năng thay đổi đường dẫn mặc định của admi… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/login-path.php` (**mới**), `pl-tien-i | ✅ |
| 2026-09-17 23:31 (giờ thật) | cline | **PLUGIN v1.2.0 — TÍNH NĂNG 2/2: XÁC MINH 2 LỚP (2FA) BẰNG APP XÁC THỰC (TOTP)** (Tùng chốt phương án **B — app xác thực**, không dùng email). **Code:… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/two-factor.php` (**mới**), `pl-tien-i | ✅ |
| 2026-09-18 07:59 (giờ thật) | cline | **CHILD THEME CP3.12 — Ô “MIÊU TẢ” CỦA DANH MỤC SẢN PHẨM THÀNH TRÌNH SOẠN THẢO ĐẦY ĐỦ (như trang thêm bài viết)** (Tùng yêu cầu). **Code:** `inc/admin… *(xem FEATURE_MAP §2 GỐC)* | child: `inc/admin-editor.php` (**mới**), `assets/admin- | ✅ |
| 2026-09-18 08:06 (giờ thật) | cline | **CSS (làn A) — icon khối “Thông số kỹ thuật” `.cp-spec__ic` 40×40 → 30×30** (Tùng yêu cầu, chỉ đổi kích thước). `assets/caophat.css` dòng 1651. **Đo… *(xem FEATURE_MAP §2 GỐC)* | child: `assets/caophat.css` | ✅ |
| 2026-09-18 10:48 (giờ thật) | cline | **PLUGIN — XOÁ EMOJI `⚠️` KHỎI 2 CHUỖI SETTINGS (ảnh vỡ Twemoji jsdelivr)** (Tùng: *“Xoá đường dẫn icon https://cdn.jsdelivr.net/gh/jdecked/twemoji@17… *(xem FEATURE_MAP §2 GỐC)* | plugin: `includes/login-path.php`, `README.md`; child:  | ✅ |
| 2026-09-18 10:58 (giờ thật) | cline | **THEME CHA `tungleads-theme` — FIX GỐC RỄ ẢNH VỠ EMOJI TWEMOJI (P2.3)** (Tùng chốt phương án **A: sửa ở theme cha**). `src/Features/Performance.php`:… *(xem FEATURE_MAP §2 GỐC)* | parent: `src/Features/Performance.php`, `CHANGELOG.md`, | ✅ |
| 2026-09-18 11:56 (giờ thật) | cline | **CSS (làn A) — `.cp-contact-btn` thêm `padding: 10px`** (Tùng yêu cầu; rule này TRƯỚC ĐÓ không có padding, dùng `height: 48px` + flex center). `asset… *(xem FEATURE_MAP §2 GỐC)* | child: `assets/caophat.css` | ✅ |
































| 2026-09-18 12:20 (giờ thật) | cline | **Tối ưu tài liệu: `CLAUDE.md` → CHỈ MỤC CP** (113,9 KB → 6,9 KB, -94%); chi tiết dồn về FEATURE_MAP; thêm hạn mức chống phình ở AGENTS.md. Chi tiết: FEATURE_MAP `### Tối ưu tài liệu 2026-09-18`. | `CLAUDE.md`, `AGENTS.md`, FEATURE_MAP, WORKLOG | ✅ |
| 2026-09-18 12:50 (giờ thật) | cline | **Rotate §2** (144 dòng cũ → `.ai/WORKLOG-archive-2026-Q3.md`, nguyên văn; §2 còn 20 dòng) + **tách 4 hàng CP > 3 KB** trong FEATURE_MAP xuống mục `### CPx.y`. | WORKLOG · archive · FEATURE_MAP · AGENTS | ✅ |
| 2026-09-18 13:25 (giờ thật) | cline | ❌ **ĐÍNH CHÍNH luật §2**: SỰ THẬT = §1 + FEATURE_MAP (sửa tại chỗ) · LỊCH SỬ = §2 + archive (chỉ thêm + phải dán nhãn đính chính); đã dán nhãn 13 dòng cũ. | AGENTS, WORKLOG, archive, plugin README | ✅ |

| 2026-09-18 14:10 (giờ thật) | cline | **Ngoài repo:** áp 6 chỉnh sửa vào chuẩn cấu trúc dự án `~/.claude/docs/project-structure-standard.md` (bản 18b) — khối tool tự sinh = **config-first**, tag `[FE]/[BE]/[DB]`, ngân sách ≤6k token/phiên, mục nhiều repo, cấm | ngoài repo | ✅ |