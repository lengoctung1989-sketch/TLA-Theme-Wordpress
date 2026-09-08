# FEATURE_MAP — "số này LÀM GÌ và GỒM những file nào"

Định nghĩa 1 dòng của mỗi số ở `CLAUDE.md`. File này thêm: (1) chức năng/nhiệm vụ đầy đủ,
(2) danh sách file, (3) chi tiết kỹ thuật — để khỏi `grep` nhiều lần.
Cột "Chức năng / nhiệm vụ" chỉ ghi ở dòng đầu mỗi số; các dòng file tiếp theo để trống 2 cột đầu.
Kiểm tra chất lượng: phpcs (WordPress-Extra) + phpstan level 5 + eslint + stylelint + prettier + vite build (CI, P6.4). Chưa có e2e ở V1.

| P-index | Chức năng / nhiệm vụ | File | Chi tiết kỹ thuật |
| :--- | :--- | :--- | :--- |
| **P1.1** theme-bootstrap | Điểm khởi động duy nhất của theme: nạp autoload PSR-4 và giao quyền điều khiển cho lớp `Theme`. Không chứa logic nghiệp vụ. | `functions.php` | Load `vendor/autoload.php`, define `TL_THEME_*`, `TL_THEME_DEV`, gọi `Theme::boot()` |
| | | `src/Core/Theme.php` | `boot()` add_action `after_setup_theme` prio 5 → resolve mode → `FeatureRegistry` → `bootAll()`. `mode()` accessor |
| **P1.2** feature-registry | Cơ chế lắp ghép tính năng: mỗi tính năng là 1 đối tượng tự khai "có nên chạy không" và "chạy thế nào". Cho phép bật/tắt tính năng theo môi trường mà không sửa lõi. | `src/Core/FeatureRegistry.php` | `add()`, `bootAll()` — gọi `shouldBoot()` rồi `boot()` |
| | | `src/Core/Contracts/FeatureInterface.php` | `shouldBoot(): bool` (chỉ điều kiện tĩnh), `boot(): void` (chỉ `add_action`) |
| **P1.3** site-mode | Một base theme dùng cho 3 loại site (dịch vụ / TMĐT / lai). Lớp này đọc cấu hình để các tính năng biết đang ở chế độ nào mà bật đúng phần. | `src/SiteMode/SiteModeResolver.php` | Hằng `SERVICE`/`ECOMMERCE`/`HYBRID`, `fromConfig()`, `isEcommerce()`, `isService()` |
| | | `config/site-config.php` | `['mode' => 'hybrid']` — mỗi site override |
| **P2.1** setup | Khai báo năng lực nền của theme với WordPress (ảnh đại diện, block styles, HTML5, đa ngôn ngữ) và các vị trí menu. Chạy một lần khi khởi động. | `src/Features/Setup.php` | `themeSupports()` (post-thumbnails, appearance-tools, wp-block-styles, responsive-embeds, feed-links, html5, textdomain), `registerMenus()` (primary, footer). *title-tag đã chuyển sang P2.5* |
| **P2.2** enqueue | Cầu nối giữa bản build Vite và WordPress: dev thì nạp qua HMR, production thì đọc `manifest.json` lấy file đã băm. Cung cấp hàm dùng chung để module khác (P4) nạp thêm bundle. | `src/Features/Enqueue.php` | `enqueue()` (entry `main.js`) + `enqueueEntry(handle,entry)` **static** (P4 tái dùng) + `isDev()`. `scriptTag()` → mọi `tl-theme*` thành `type=module`. Manifest `assets/dist/.vite/manifest.json` |
| **P2.3** performance | Cắt phần thừa ở tầng trình bày: bỏ hẳn wp-emoji, khai báo preload cho stylesheet chính. Không đụng page cache / HTTP header (việc của hosting). Preload font: thêm khi site thực sự dùng woff2. | `src/Features/Performance.php` | `disableEmoji()` gỡ 7 hook + `wpemoji` TinyMCE. `preloadResources()` hook `wp_preload_resources`: preload style `tl-theme-0` |
| **P2.4** security | Hardening trong phạm vi theme: ẩn phiên bản WordPress khỏi `<head>`, cung cấp helper lọc HTML an toàn cho template. Không set security header, không đụng XML-RPC. | `src/Features/Security.php` | `boot()` gỡ `wp_head/wp_generator` + `the_generator` rỗng. static `ksesInline(string): string` — allowlist a/strong/em/b/i/span.class/br |
| **P2.5** seo | Fallback SEO tối thiểu và tự nhường sân: nếu phát hiện plugin SEO thì tắt hoàn toàn để tránh thẻ trùng; nếu không, chỉ bật `title-tag`. | `src/Features/SEO.php` | `shouldBoot()` = `! hasSeoPlugin()`. `boot()` = `add_theme_support('title-tag')`. static `hasSeoPlugin()`: `WPSEO_VERSION` \| `RankMath` \| `AIOSEO_VERSION` \| `SEOPRESS_VERSION` \| `The_SEO_Framework\Load` |
| **P2.6** admin | Tinh chỉnh phía trình soạn thảo trong phạm vi theme: đăng ký nhóm (category) cho block pattern để P3.5 gắn vào. Không đăng ký CPT/taxonomy (việc của tl-site-plugin). | `src/Features/Admin.php` | `registerPatternCategory()` on `init` → `register_block_pattern_category('tungleads', label 'TLA Theme')` |
| **P2.x** wiring | Nơi lắp toàn bộ Feature hạ tầng vào registry — đọc file này để biết thứ tự boot. | `src/Core/Theme.php` | `onAfterSetupTheme()` đăng ký: Setup, Enqueue, Performance, Security, SEO, Admin |
| **P4.x/P3.5** wiring | Lắp tích hợp WooCommerce và bộ nạp pattern vào vòng đời khởi động. | `src/Core/Theme.php` | `$registry` là biến cục bộ (không giữ static). Thêm `WooCommerceFeature` (sau Admin); sau `bootAll()` gọi `( new PatternLoader() )->register()` |
| **P4.1** woocommerce-feature | Cửa ngõ tích hợp WooCommerce: chỉ chạy khi site mode cho phép **và** plugin đang active. Khai báo hỗ trợ WooCommerce + gallery, nạp CSS shop riêng, gỡ sidebar classic. Cô lập 100% code phụ thuộc WooCommerce. | `src/Integrations/WooCommerce/WooCommerceFeature.php` | `shouldBoot()` mode+`class_exists`. `themeSupports()`. `enqueueStyles()`→`Enqueue::enqueueEntry('tl-theme-woo','woocommerce.js')`. `remove_action('woocommerce_sidebar','woocommerce_get_sidebar')` |
| **P4.2** product-archive | Tuỳ biến trang danh sách sản phẩm **chỉ bằng hook** (số cột, số sản phẩm mỗi trang), không copy template. Site con override trực tiếp qua chính 2 hook đó. | `src/Integrations/WooCommerce/ProductArchive.php` | `register()`: `loop_shop_columns`→`fn()=>3`, `loop_shop_per_page`→`fn()=>12` |
| **P4.3** cart-checkout | Style block Cart/Checkout **chỉ qua biến CSS** ánh xạ từ token theme, không sửa markup, không cache nội dung phụ thuộc phiên. *Không còn class PHP — chỉ là khối CSS trong P4.4.* | `assets/src/woocommerce.css` | `.wc-block-cart, .wc-block-checkout { --wc-form-border-radius / --wc-form-color-focus / --wc-button-border-radius }` |
| **P4.4** woocommerce-styles | Lớp CSS `@layer woocommerce` — kéo giao diện WooCommerce (nút, giá, nhãn sale, thông báo, focus, biến Cart/Checkout) về đúng token của theme. Bundle Vite tách riêng, chỉ nạp khi P4.1 chạy. | `assets/src/woocommerce.css` | `@layer woocommerce` — nút/giá/`ins`/`onsale`/notice/focus + `--wc-*` cho `.wc-block-cart/.wc-block-checkout`. `assets/src/woocommerce.js` = entry import CSS |
| **P3.1** base-templates | Bộ khung HTML tối thiểu cho mọi trang: `<head>`/`<body>`, header + menu, footer, template dự phòng. Đặt nền a11y (skip-link, focus). | `index.php`, `header.php`, `footer.php` | Skip-link, `:focus-visible`, `wp_nav_menu` primary, i18n |
| **P3.2** design-tokens | Hệ token 2 tầng: `theme.json` (token lộ ra Site Editor cho biên tập viên) và `tokens.css` (token nội bộ: breakpoint, z-index, timing). Định nghĩa thứ tự `@layer` và CSS nền. | `theme.json` | schema v2, chỉ token editor-facing: 8 màu, 5 font-size, 6 spacing, `appearanceTools` |
| | | `assets/src/tokens.css` | Token nội bộ + khai báo `@layer` order |
| | | `assets/src/style.css` | `@layer reset/base/components` — container, skip-link, focus, header/footer, `.tl-post-list`/`.tl-service-list` grid, card |
| **P3.3** dichvu-templates | Template hiển thị cho CPT `dichvu` (danh sách + chi tiết). CPT do `tl-site-plugin` định nghĩa; theme chỉ lo trình bày, không chứa business logic. Khung lặp tách ra template-part để tái dùng. | `archive-dichvu.php`, `single-dichvu.php` | Loop gọi `get_template_part('template-parts/content','dichvu')`, i18n, `tl-container`. Không business logic |
| | | `template-parts/content-dichvu.php` | Card 1 mục CPT `dichvu`: thumbnail lazy, tiêu đề link, excerpt, "Xem chi tiết" + `screen-reader-text` |
| **P3.4** blog-templates | Bộ template chuẩn theo WordPress Template Hierarchy: trang chủ, blog index, archive, bài viết, trang tĩnh, bình luận. Nội dung tĩnh do editor dựng bằng pattern, không hardcode PHP. | `front-page.php` | `the_content()` — editor dựng bằng pattern; blog thì WP tự dùng `home.php` |
| | | `home.php`, `archive.php` | Loop `template-parts/content-post`, `the_posts_pagination`, `the_archive_title/description` |
| | | `single.php` | `<article>` + meta `<time>` + `the_content` + `wp_link_pages` + `comments_template` + `the_post_navigation` |
| | | `page.php` | `the_content` + `comments_template` |
| | | `comments.php` | `wp_list_comments` (ol, avatar 48) + `the_comments_navigation` + `comment_form`, i18n `_n()` |
| | | `template-parts/content-post.php` | Card 1 bài: thumbnail lazy, tiêu đề link, `<time>`, `the_excerpt` |
| **P3.5** patterns | Block pattern tách theo site mode (`shared` luôn có, `service`/`shop` gate theo mode). WordPress không quét thư mục con của `patterns/` nên cần bộ nạp tự đăng ký. | `src/SiteMode/PatternLoader.php` | `register()` on `init` → quét `patterns/{shared[,service][,shop]}/*.php` theo mode → `get_file_data` (Title/Slug/Categories/Description) + `ob`/`include` → `register_block_pattern` |
| | | `patterns/shared/cta.php` | `tungleads-theme/cta` — group full + heading + paragraph + button (token màu/spacing) |
| | | `patterns/service/services-list.php` | `tungleads-theme/services-list` — 3 cột dịch vụ. Chỉ mode service/hybrid |
| | | `patterns/shop/promo-banner.php` | `tungleads-theme/promo-banner` — banner link `wc_get_page_permalink('shop')`. Chỉ mode ecommerce/hybrid |
| | | `assets/src/main.js` | Entry Vite, `import './style.css'` |
| **P6.1** build | Pipeline build asset: gom CSS/JS nguồn thành file có băm + `manifest.json` cho Enqueue đọc. Hai entry tách theo site mode (`main` luôn có, `woocommerce` chỉ khi cần). | `vite.config.js` | `root=assets/src`, `base=/wp-content/themes/tungleads-theme/assets/dist/`, `manifest:true`, entry `{ main, woocommerce }`, dev server 5173 |
| **P6.2** composer | Khai báo autoload PSR-4 và toàn bộ công cụ kiểm tra PHP (phpcs, phpstan). **Chỉ dev-dep + autoload nội bộ — KHÔNG phải cơ chế nhân bản** (nhân bản = parent/child theme; `vendor/` đã commit sẵn để deploy không cần Composer). | `composer.json` | PSR-4 `TL\Theme\` → `src/`. require-dev: php_codesniffer, wpcs, phpcompatibility-wp, phpstan ^2, phpstan-wordpress ^2. scripts `lint`/`lint:fix`/`analyze` |
| **P6.3** lint | Chuẩn code và phân tích tĩnh: WordPress-Extra cho phần WP, PSR-4/OOP cho `src/`, PHP 8.2 compat, phpstan level 5, ESLint/Stylelint/Prettier cho asset. | `phpcs.xml` | WordPress-Extra + PHPCompatibilityWP. `src/*` loại `WordPress.Files.FileName` + `ValidFunctionName` (PSR-4/camelCase). `patterns/*` loại `PrefixAllGlobals`. prefix `tl_theme`/`TL_THEME`/`TL\Theme`. text_domain `tungleads-theme` |
| | | `phpstan.neon` + `phpstan-bootstrap.php` | level 5, paths `src`+templates+`functions.php`. bootstrap khai `TL_THEME_*` |
| | | `eslint.config.js` | flat config, `@eslint/js` recommended + prettier, globals browser+node, ignore `assets/dist` |
| | | `.stylelintrc.json` | `stylelint-config-standard`; cho phép `@layer`, tắt `selector-class-pattern`/`custom-property-pattern` |
| | | `.prettierrc.json` | singleQuote, printWidth 100, 4 space |
| **P6.4** ci | GitHub Actions chạy mọi kiểm tra trên mỗi PR + push `main`: 2 job song song (PHP và asset). Không có e2e ở V1. | `.github/workflows/ci.yml` | job `php` (setup-php 8.2 → composer install → lint → analyze); job `assets` (node 20 → npm ci → lint:js → lint:css → format:check → build) |
| **P7.1** i18n | Bản mẫu dịch (`.pot`) cho toàn bộ chuỗi trong theme, sinh tự động từ mã nguồn. Text domain cố định `tungleads-theme`. | `languages/tungleads-theme.pot` | sinh bằng `wp i18n make-pot` (qua container wpcli). Text domain `tungleads-theme` |
| **P7.2** a11y | Hồ sơ kiểm tra accessibility V1: đối chiếu contrast toàn palette với WCAG AA, ghi lại cơ chế keyboard/focus và quy ước ARIA, liệt kê việc còn treo. | `.ai/ACCESSIBILITY.md` | bảng contrast palette (đạt AA), keyboard/focus, quy ước ARIA, việc còn treo |

## Hằng số / biến môi trường

| Tên | Nơi định nghĩa | Ý nghĩa |
| :--- | :--- | :--- |
| `TL_THEME_DIR` / `TL_THEME_URI` | `functions.php` | `get_template_directory[_uri]()` |
| `TL_THEME_VERSION` | `functions.php` | `0.1.0` — dùng cho asset version |
| `TL_THEME_DEV` | `functions.php` | `true` khi `wp_get_environment_type() === 'development'` → Enqueue dùng Vite dev server |
| `WP_ENVIRONMENT_TYPE` | `docker-compose.yml` | `development` cho local |
