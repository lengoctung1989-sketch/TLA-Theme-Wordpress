# Changelog

## [Unreleased]

### Sửa lỗi — gỡ emoji/twemoji triệt để (P2.3)

- **`Performance::disableEmoji()` chạy ở CẢ `init` LẪN `admin_init`.** Lý do: hook `print_emoji_detection_script` ở front-end nằm tại `wp-includes/default-filters.php:359` (gỡ ở `init` là đủ), nhưng bản của admin nằm ở `wp-admin/includes/admin-filters.php:59` — file này được nạp **sau** `init` nên nó **gắn lại** hook vừa gỡ ⇒ trước đây **front-end sạch nhưng wp-admin vẫn nạp twemoji**. Nay gỡ thêm `embed_head` và `admin_enqueue_scripts` → `wp_enqueue_emoji_styles`.
- **Bỏ `add_filter( 'emoji_svg_url', '__return_false' )`.** `_wpemojiSettings.svgUrl` rỗng làm thư viện twemoji rơi về **base mặc định của chính nó** = `https://cdn.jsdelivr.net/gh/jdecked/twemoji@17.0.1/assets/`, rồi build `<base><code>.svg` ⇒ URL **404** (đúng phải có `svg/` hoặc dùng `72x72/*.png`) ⇒ mọi emoji trong wp-admin hiện thành **ảnh vỡ** (Tùng báo 2026-09-18, URL `…/assets/26a0.svg`). Gỡ sạch script thì URL không còn ý nghĩa; nếu plugin khác lỡ nạp emoji ở màn nào đó, core sẽ dùng mặc định `s.w.org/images/core/emoji/<ver>/svg/` (chạy tốt).
- **Đo sau khi sửa** (Playwright; 4 màn admin: Settings plugin · soạn bài viết · danh sách sản phẩm · người dùng; + front-end): `_wpemojiSettings` **không còn**, `window.twemoji` = `undefined`; tiêm `⚠️ ✅ 🔴 ★` vào DOM rồi chờ 1,5s ⇒ **0 `<img>`** (vẫn là ký tự). `php -l` + `vendor/bin/phpcs` sạch.


### Chốt lại mô hình nhân bản — parent/child thay cho "Composer package"
- Quyết định: `tungleads-theme` là **parent theme classic**; mỗi site khách = 1 **child theme mỏng** (`Template:` header). Child kế thừa bootstrap/FeatureRegistry/SiteMode/Features/WooCommerce integration của parent qua cơ chế parent/child sẵn có của WordPress — không cần tự viết lại `locate_template` fallback trong `vendor/`.
- Lý do đổi: mô hình V1 "Composer/Git package, không parent/child" (spec `TL-Base-Theme-Claude-Code-Prompt.md`) chưa từng được triển khai; `vendor/` + `assets/dist/` đã commit sẵn nên Composer/Node không còn cần lúc deploy → Composer thực chất chỉ còn là dev-dep.
- Composer: giữ cho autoload PSR-4 nội bộ + phpcs/phpstan. **Không** dùng làm cơ chế phân phối, không private Packagist.
- Version pin: git tag `v0.x.y` trên repo parent; mỗi child ghi `Base: tungleads-theme@vX.Y.Z`. Nâng parent = commit có chủ đích + cập nhật `CHANGELOG.md` → update dòng version ở từng child → test lại.
- Deploy: phải ship **cả parent + child** (+ `assets/dist/` của parent).
- Đồng bộ tài liệu: `style.css`, `CLAUDE.md`, `README.md`, `.ai/FEATURE_MAP.md` (P6.2), `composer.json`, root `.gitignore`, và ghi chú "superseded" ở `TL-Base-Theme-Claude-Code-Prompt.md`.

### Dọn over-engineering (ponytail-review) — ~-70 dòng
- `Theme` — bỏ accessor `registry()` không ai gọi; `$registry` thành biến cục bộ (bỏ static property + `?FeatureRegistry` import path); `$mode` default `'service'` literal.
- `Enqueue` — `manifest()` chỉ đọc `.vite/manifest.json` (Vite 5 chỉ sinh path này).
- `Performance` — bỏ vòng lặp preload font + filter `tl_theme_preload_fonts` (chưa site nào có woff2).
- (code-review) giữ lại 2 guard phòng thủ: `is_array($resources)` trong `preloadResources()` và `str_contains type="module"` trong `scriptTag()` — chặn trường hợp plugin bên thứ 3 truyền non-array / thêm `type=module` trước.
- `Integrations/WooCommerce/CartCheckout.php` — **xoá class**. 3 biến `--wc-*` chuyển vào `woocommerce.css` (@layer woocommerce). Bỏ wiring trong `WooCommerceFeature`.
- `ProductArchive` — bỏ lớp filter `tl_theme_shop_*` thừa; `loop_shop_columns`/`loop_shop_per_page` trả hằng qua closure; site con override trực tiếp qua chính 2 hook đó.
- `PatternLoader` — chỉ parse header `Title/Slug/Categories/Description` (bỏ `Block Types`/`Post Types`/`Viewport Width` không pattern nào dùng); inline `csv()`.
- Verify: phpcs/phpstan/eslint/stylelint/prettier sạch · vite build OK · 3 pattern vẫn đăng ký · template 200 · debug.log sạch.

### Quản lý dự án
- Seed hệ P-index 3 lớp: `CLAUDE.md` (nghĩa từng số) · tag `// Px.y` ở entry point · `.ai/FEATURE_MAP.md` (file/test).
- Nhóm: P1 Core · P2 Features hạ tầng · P3 Front-end · P4 WooCommerce · P5 Bên thứ 3 · P6 Build/CI · P7 i18n/A11y.

### GĐ5 — CI + i18n + Accessibility (P6, P7)
- `P6.2` — require-dev: php_codesniffer, wpcs ^3, phpcompatibility-wp, phpstan ^2, phpstan-wordpress ^2. Scripts `lint`/`lint:fix`/`analyze`.
- `P6.3` — `phpcs.xml` (WordPress-Extra; `src/` bỏ quy ước tên file/hàm WP vì PSR-4 + OOP camelCase; `patterns/` bỏ PrefixAllGlobals), `phpstan.neon` level 5 + `phpstan-bootstrap.php`, `eslint.config.js` (flat), `.stylelintrc.json`, `.prettierrc.json`.
- `P6.4` — `.github/workflows/ci.yml`: job PHP (phpcs + phpstan), job assets (eslint + stylelint + prettier + vite build).
- `P7.1` — `languages/tungleads-theme.pot` (56 chuỗi) sinh bằng `wp i18n make-pot`.
- `P7.2` — `.ai/ACCESSIBILITY.md`: rà contrast toàn palette (đạt WCAG AA), keyboard/focus, ARIA.
- Sửa để pass lint: prefix `$tl_theme_autoload`; `phpcs:ignore` có lý do cho Vite dev-server version + đọc manifest cục bộ; `wp_nav_menu` container `''` thay `false` (phpstan); `@import url()`; prettier-format CSS.
- Verify: phpcs exit 0 · phpstan "No errors" · eslint/stylelint/prettier sạch · vite build OK · site 200 · debug.log sạch.

### GĐ4 — Template hierarchy + Patterns theo Site Mode (P3)
- `P3.3` — `archive-dichvu.php`, `single-dichvu.php`, `template-parts/content-dichvu.php` (CPT `dichvu` do `tl-site-plugin` đăng ký; theme chỉ trình bày).
- `P3.4` — `front-page.php`, `home.php`, `archive.php`, `single.php`, `page.php`, `comments.php`, `template-parts/content-post.php`. Khung/loop tách vào `template-parts/`, nội dung tĩnh để editor dựng bằng pattern.
- `P3.5` — `patterns/{shared,service,shop}/*.php` (3 pattern: cta, services-list, promo-banner) + `SiteMode\PatternLoader` tự đăng ký thư mục con và gate theo site mode (WP không quét đệ quy). Thay cho 2 lớp `*ModePatterns` trong bản phác thảo.
- `P3.2` CSS — thêm `@layer components`: grid danh sách + card cho post/dịch vụ.
- `Core\Theme` — gọi `PatternLoader` sau `bootAll()`.
- Sửa deprecated: thêm `comments.php` (single/page gọi `comments_template()`).
- Verify: `php -l` toàn bộ OK · 15 classes · 6 URL template (home/page/single/archive/front) 200 · pattern gating service↔shop đúng · debug.log sạch.

### GĐ3 — Tích hợp WooCommerce (P4)
- `P4.1 WooCommerceFeature` — boot khi site mode ∈ {ecommerce,hybrid} && WooCommerce active. `add_theme_support('woocommerce')` + gallery zoom/lightbox/slider. Gỡ sidebar classic của WC (theme Blocks-first không có `sidebar.php`).
- `P4.2 ProductArchive` — hook-only: `loop_shop_columns` (3), `loop_shop_per_page` (12), có filter `tl_theme_shop_*` cho site con. KHÔNG copy template.
- `P4.3 CartCheckout` — bơm `<style>` biến CSS cho block Cart/Checkout từ token theme. KHÔNG đụng markup, KHÔNG cache session-dependent.
- `P4.4 woocommerce.css` — `@layer woocommerce`, map token → nút/giá/sale/notice/focus. Entry Vite `woocommerce.js` tách riêng, chỉ enqueue khi P4 boot.
- `P2.2 Enqueue` — refactor: `enqueueEntry()` static dùng chung; mọi script `tl-theme*` render `type=module`.
- `vite.config.js` — 2 entry (`main`, `woocommerce`).
- Verify: `php -l` OK · `current_theme_supports('woocommerce')` = true · `woocommerce.js` + `#tl-wc-cart-checkout` xuất hiện · shop 200 · debug.log sạch.

### GĐ2 — Features cơ bản
- `P2.3 Performance` — gỡ wp-emoji (7 hook + TinyMCE); hook `wp_preload_resources` preload stylesheet theme + filter `tl_theme_preload_fonts`.
- `P2.4 Security` — gỡ `wp_generator` (ẩn phiên bản WP); helper `Security::ksesInline()` cho template.
- `P2.5 SEO` — chỉ boot khi KHÔNG có plugin SEO; thêm `title-tag`. `SEO::hasSeoPlugin()` feature-detect 5 plugin. `title-tag` gỡ khỏi `Setup` (P2.1).
- `P2.6 Admin` — đăng ký block pattern category `tungleads` (cho patterns GĐ4).
- `Core\Theme` đăng ký 6 Feature vào registry.
- Verify: `php -l` OK · site 200 · `<title>` xuất hiện · không còn `meta name="generator"` / script emoji trong `<head>`.

### GĐ1 — Khung xương
- Composer PSR-4 (`TL\Theme\` → `src/`), `functions.php` chỉ load autoload + `Theme::boot()`.
- `Core\Theme`, `Core\FeatureRegistry`, `Core\Contracts\FeatureInterface`.
- `SiteMode\SiteModeResolver` + `config/site-config.php` (mặc định `hybrid`).
- `theme.json` v2 — token editor-facing (palette, spacing, font size), `appearanceTools`.
- `assets/src/tokens.css` — token nội bộ tầng 2 + thứ tự `@layer`.
- Vite + `Features\Enqueue` đọc manifest (dev HMR / prod hash + module/defer).
- `Features\Setup` — theme supports, nav menus, textdomain.
- Template tối thiểu: `index.php`, `header.php`, `footer.php`.
- `docker-compose.yml` (gốc repo) — WordPress + MariaDB + wp-cli; toàn bộ WP core nằm trong `./wordpress/`, theme phát triển tại `./wordpress/wp-content/themes/tungleads-theme/`.
