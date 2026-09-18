# tungleads-theme-cp — CHỈ MỤC (index) cho Claude Code

**Child theme** của `tungleads-theme` (parent, `Template:` trong `style.css`) — skin cho **caophat.vn** (cửa gỗ công nghiệp / cửa nhựa / cửa chống cháy). **Base pin:** `tungleads-theme@v0.1.0`.

> ⚠️ **File này CHỈ là chỉ mục** (để phiên nào cũng nạp ít token). **Trước khi code, đọc 3 file sau:**
> 1. **`AGENTS.md`** — luật làm việc: checklist đầu/cuối phiên · làn thay đổi A/B/C · luật bất biến · môi trường & lệnh chạy · kiểm chứng bắt buộc · `.github/instructions/` · hạn mức kích thước tài liệu.
> 2. **`.ai/WORKLOG.md`** — **§1** (việc đang làm / việc tiếp theo) + **10 dòng cuối §2**.
> 3. **`.ai/FEATURE_MAP.md`** — **chi tiết từng CP**: quyết định đã chốt sau khi đo · số đo thật · bẫy đã dính · file thật. ⚠️ File lớn: **chỉ đọc bằng `grep -n "^### CPx.y"` + `sed -n "X,Yp"`**, KHÔNG mở cả file.

- **Ranh giới:** child chỉ trình bày (skin CSS + template override + hook). Business logic → plugin **`pl-tien-ich-tungleads`**; widget nút liên hệ → plugin **`button-call-zalo-tungleads`**. Deploy bằng `deploy-caophat.sh` (gốc repo) — **chỉ chạy `--go` khi Tùng yêu cầu rõ ràng**.
- **Số CP** đánh theo `CP<nhóm>.<số>`, tách khỏi P-index (P1–P7) của parent để `grep` không lẫn. 3 lớp đồng bộ: chỉ mục dưới đây → tag `// CPx.y` trong code → `.ai/FEATURE_MAP.md`.

## Nhóm

| Nhóm | Phạm vi |
| :--- | :--- |
| **CP1** | Khung/chrome — bootstrap child, header (desktop/mobile/mega menu), footer, hotline, logo |
| **CP2** | Trang chủ — `front-page.php`, template-part, Customizer, các khối động |
| **CP3** | WooCommerce skin — trang danh mục/cửa hàng, chi tiết SP (thông số, tabs mô tả/đánh giá), giỏ, thanh toán, hoàn tất |
| **CP4** | Assets — `caophat.css` (skin chính), ảnh |
| **CP5** | Tin tức/Blog — lưu trữ tin (chuyên mục + thẻ), chi tiết bài viết, sidebar |
| **CP6** | Trang nội dung tĩnh — `page.php` + sub-nav cho trang phân cấp |
| **CP7** | Trang hệ thống — 404, kết quả tìm kiếm, vệ sinh shortcode Flatsome cũ |

## Chỉ mục CP (chức năng → tra chi tiết ở `.ai/FEATURE_MAP.md`)

| CP | Chức năng | Chi tiết |
| :--- | :--- | :--- |
| `CP1.1` | Bootstrap child | `.ai/FEATURE_MAP.md` → `### CP1.1` |
| `CP1.2` | Header | `.ai/FEATURE_MAP.md` → `### CP1.2` |
| `CP1.2b` | Header mobile | `.ai/FEATURE_MAP.md` → `### CP1.2b` |
| `CP1.3` | Footer | `.ai/FEATURE_MAP.md` → `### CP1.3` |
| `CP1.4` | Menu chính | `.ai/FEATURE_MAP.md` → `### CP1.4` |
| `CP1.4b` | Mega menu 3 cấp | `.ai/FEATURE_MAP.md` → `### CP1.4b` |
| `CP1.5` | Menu mobile | `.ai/FEATURE_MAP.md` → `### CP1.5` |
| `CP1.6` | Ô tìm kiếm trên header | `.ai/FEATURE_MAP.md` → `### CP1.6` |
| `CP1.7` | Icon giỏ hàng trên header | `.ai/FEATURE_MAP.md` → `### CP1.7` |
| `CP1.8` | Header + Topbar quản trị trong Customizer | `.ai/FEATURE_MAP.md` → `### CP1.8` |
| `CP1.9` | “Chi nhánh & Hotline Cao Phát” quản trị trong Customizer | `.ai/FEATURE_MAP.md` → `### CP1.9` |
| `CP2.1` | Trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.1` |
| `CP2.2` | Khối động trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.2` |
| `CP2.3` | Customizer | `.ai/FEATURE_MAP.md` → `### CP2.3` |
| `CP2.4` | Khối "Sản phẩm theo danh mục" trên trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.4` |
| `CP2.5` | Khối "Tin tức theo chuyên mục" trên trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.5` |
| `CP2.6` | Khối "Danh mục nổi bật" trên trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.6` |
| `CP2.7` | Chọn sản phẩm ưu tiên trong khối "Sản phẩm theo danh mục" | `.ai/FEATURE_MAP.md` → `### CP2.7` |
| `CP2.8` | Nút cuộn ‹ › cho bố cục "Cuộn ngang" | `.ai/FEATURE_MAP.md` → `### CP2.8` |
| `CP2.9` | Khối "tab sản phẩm" trên trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.9` |
| `CP2.10` | 4 cụm nội dung nổi bật trên trang chủ | `.ai/FEATURE_MAP.md` → `### CP2.10` |
| `CP3.1` | Trang danh mục / cửa hàng | `.ai/FEATURE_MAP.md` → `### CP3.1` |
| `CP3.2` | Chi tiết sản phẩm | `.ai/FEATURE_MAP.md` → `### CP3.2` |
| `CP3.3` | Đặt hàng nhanh | `.ai/FEATURE_MAP.md` → `### CP3.3` |
| `CP3.4` | Trang giỏ hàng | `.ai/FEATURE_MAP.md` → `### CP3.4` |
| `CP3.5` | Trang thanh toán | `.ai/FEATURE_MAP.md` → `### CP3.5` |
| `CP3.6` | Trang hoàn tất đơn hàng | `.ai/FEATURE_MAP.md` → `### CP3.6` |
| `CP3.7` | Dải “Sản phẩm tương tự” ở trang chi tiết SP | `.ai/FEATURE_MAP.md` → `### CP3.7` |
| `CP3.8` | Thu gọn tab “Mô tả” ở trang chi tiết SP | `.ai/FEATURE_MAP.md` → `### CP3.8` |
| `CP3.9` | Đánh giá & bình luận ở trang chi tiết SP | `.ai/FEATURE_MAP.md` → `### CP3.9` |
| `CP3.10` | Đổi đường dẫn đăng nhập + ẩn wp-admin | `.ai/FEATURE_MAP.md` → `### CP3.10` |
| `CP3.11` | Xác minh 2 lớp | `.ai/FEATURE_MAP.md` → `### CP3.11` |
| `CP3.12` | Ô “Miêu tả” của DANH MỤC SẢN PHẨM dùng TRÌNH SOẠN THẢO ĐẦY ĐỦ như trang thêm bài viết | `.ai/FEATURE_MAP.md` → `### CP3.12` |
| `CP4.1` | Skin trong `caophat.css` | `.ai/FEATURE_MAP.md` → `### CP4.1` |
| `CP5.1` | Trang danh mục tin tức | `.ai/FEATURE_MAP.md` → `### CP5.1` |
| `CP5.2` | Trang chi tiết tin tức | `.ai/FEATURE_MAP.md` → `### CP5.2` |
| `CP5.3` | Trang THẺ | `.ai/FEATURE_MAP.md` → `### CP5.3` |
| `CP5.4` | Bài viết KHÔNG in ảnh đại diện đầu bài | `.ai/FEATURE_MAP.md` → `### CP5.4` |
| `CP6.1` | Trang nội dung tĩnh | `.ai/FEATURE_MAP.md` → `### CP6.1` |
| `CP6.2` | Hộp sub-nav "Trong mục này" cho trang phân cấp | `.ai/FEATURE_MAP.md` → `### CP6.2` |
| `CP6.3` | Trang tĩnh KHÔNG in ảnh đại diện | `.ai/FEATURE_MAP.md` → `### CP6.3` |
| `CP7.1` | Trang 404 | `.ai/FEATURE_MAP.md` → `### CP7.1` |
| `CP7.2` | Trang kết quả tìm kiếm | `.ai/FEATURE_MAP.md` → `### CP7.2` |
| `CP7.3` | Bóc shortcode Flatsome còn sót trong nội dung cũ | `.ai/FEATURE_MAP.md` → `### CP7.3` |
| `CP8` | Mục lục nội dung | `.ai/FEATURE_MAP.md` → `### CP8` |

## Khi cần thêm

- **Script đo** (Playwright/wp-cli) + ý nghĩa từng script: `docs/measure/README.md` (gốc repo).
- **Quy trình kiểm chứng UI** (đo bằng trình duyệt, `elementFromPoint`, 6 khổ 1440→390): skill `.github/skills/cp-ui-verify/SKILL.md`.
- **Nhật ký theo phiên** (đã làm gì, số đo nào): `.ai/WORKLOG.md` §2.
- **Plugin**: `README.md` trong từng thư mục plugin (chi tiết tracking/maintenance/TOC/login-path/2FA/spec/đặt hàng nhanh…).
