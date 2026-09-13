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
- **Việc:** CP3.2 — nhãn nút mua "Thêm giỏ hàng" (hiển thị "THÊM GIỎ HÀNG") + ẩn số hotline trên mobile
- **File đang chạm:** `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md`
- **Trạng thái:** XONG
- **Đã xong:** Thêm filter `woocommerce_product_single_add_to_cart_text` → "Thêm giỏ hàng" (CSS `text-transform: uppercase` sẵn có → "THÊM GIỎ HÀNG"); bọc số hotline trong `.cp-tel-num` ở nút "Gọi ngay" (`.cp-contact-btn--call`) + nút CTA cuối trang, ẩn `≤768px`. Kiểm chứng `localhost:8888`: desktop nút mua hiển thị "THÊM GIỎ HÀNG", nút gọi "Gọi ngay 0834.021.021"; viewport 390px → cả 2 nút chỉ còn "Gọi ngay"; `php -l` sạch
- **Việc tiếp theo:** (không) — chờ việc tiếp theo. Lưu ý còn sót: nút CTA ở `front-page.php` (CP2.1) vẫn in "Gọi ngay <số>" trên mobile — chưa đụng vì ngoài phạm vi CP3.2
- **Cập nhật lúc:** 2026-09-13 20:15

---

## §2 NHẬT KÝ (chỉ ghi thêm, mới nhất ở dưới)

| Thời gian | Model | Việc đã làm | File chính | Trạng thái |
|---|---|---|---|---|
| 2026-09-13 19:45 | deepseek | Tạo worklog bàn giao + luật phối hợp model | `.ai/WORKLOG.md`, `CLAUDE.md`, `AGENTS.md` | xong |
| 2026-09-13 19:50 | deepseek | Chốt nhãn model chỉ còn `claude`/`deepseek` (bỏ codex/copilot) | như trên + 3 file parent | xong |
| 2026-09-13 20:05 | deepseek | CP3.2: chiều cao thu gọn mô tả ngắn `7.5em` → `15em` + đồng bộ 3 lớp tài liệu | `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
| 2026-09-13 20:15 | deepseek | CP3.2: nhãn nút mua → "THÊM GIỎ HÀNG"; ẩn số hotline trên mobile (chỉ còn "Gọi ngay") | `inc/woocommerce.php`, `assets/caophat.css`, `CLAUDE.md`, `AGENTS.md`, `.ai/FEATURE_MAP.md` | xong |
