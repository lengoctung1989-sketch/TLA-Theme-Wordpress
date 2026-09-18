# TLA Theme — hướng dẫn dự án cho Claude Code

WordPress **Classic Theme + theme.json** (hybrid, KHÔNG phải FSE/Block Theme).

**Mô hình nhân bản (đã chốt lại): `tungleads-theme` là PARENT theme classic.** Mỗi site khách = 1 **child theme mỏng** (`Template:` trong `style.css`) chỉ chứa skin + vài template override + hook site-specific. Child kế thừa toàn bộ bootstrap/FeatureRegistry/SiteMode/Features/WooCommerce integration của parent qua cơ chế parent/child sẵn có của WordPress (parent `functions.php` luôn chạy, `vendor/autoload.php` của parent nạp mọi class `TL\Theme\`).

- Composer **chỉ còn là dev-dep** (phpcs/phpstan) + autoload PSR-4 nội bộ. KHÔNG phải cơ chế phân phối — `vendor/` + `assets/dist/` đã commit sẵn để deploy không cần Composer/Node.
- Không có private Packagist. Pin version parent bằng **git tag** (`v0.x.y`); mỗi child ghi dòng `Base: tungleads-theme@vX.Y.Z` trong `CLAUDE.md`/`README.md`. Nâng parent = commit có chủ đích + `CHANGELOG.md` → update dòng version ở từng child → test lại.
- Fork repo: vẫn loại bỏ.
- (Ý tưởng V1 "Composer/Git package, không parent/child" trong `TL-Base-Theme-Claude-Code-Prompt.md` đã bị thay bằng đoạn này.)

- WordPress ≥ 6.5 · PHP ≥ 8.2 · `theme.json` schema v2
- Text domain: `tungleads-theme` · Namespace PHP: `TL\Theme\` (PSR-4 → `src/`)
- Site mode mặc định: `hybrid` (`config/site-config.php`)
- Local dev: `docker-compose.yml` ở gốc repo — WP core nằm trong `../../../` (`wordpress/`)

## Quyết định kỹ thuật đã chốt (từ spec `TL-Base-Theme-Claude-Code-Prompt.md`)

- WooCommerce **Blocks-first**: dùng Cart/Checkout/Mini-Cart/Product Collection block chính thức. Hạn chế tối đa override `woocommerce/*.php`, ưu tiên hook.
- KHÔNG viết custom Gutenberg block "phòng khi cần" — core block + pattern + Block Bindings API.
- KHÔNG dynamic `import()` tràn lan — chỉ khi component thực sự nặng. JS mặc định enqueue + `defer`.
- Theme KHÔNG quản lý: page cache, HTTP cache header, XML-RPC — đó là việc của hosting/CDN/security plugin.
- Vùng cart/checkout/mini-cart KHÔNG cache session-dependent trong theme — luôn dựa `wc-ajax=get_refreshed_fragments` / Store API.
- CSS hai tầng token: `theme.json` chỉ token editor-facing; `assets/src/tokens.css` token nội bộ (breakpoint, z-index, timing).
- `@layer` order: `reset, tokens, base, components, utilities, woocommerce, overrides`.
- Mọi CPT / taxonomy / business rule / API riêng khách hàng → nằm ở **plugin site-specific** (`tl-site-plugin`), KHÔNG trong theme. Theme chỉ trình bày.
- SEO: `SEO.php` tự tắt hoàn toàn khi có Yoast/RankMath. Không plugin → chỉ `title-tag` + canonical fallback. KHÔNG tự thêm OG/Twitter/JSON-LD.
- i18n: mọi chuỗi bọc `__()/_e()/esc_html__()` text domain `tungleads-theme`. A11y: WCAG 2.1 AA baseline.
- Lifecycle: `functions.php` → autoload → `Theme::boot()` hook `after_setup_theme` (prio 5) → `FeatureRegistry` gọi `shouldBoot()` (chỉ điều kiện tĩnh) rồi `boot()` (chỉ `add_action`, không chạy logic theo request ngay).

## Phối hợp giữa các model (BẮT BUỘC)

**Nguồn sự thật — phân biệt SỰ THẬT và LỊCH SỬ (Tùng chốt 2026-09-18):**

- **SỰ THẬT HIỆN TẠI** = **§1 `.ai/WORKLOG.md`** + **`.ai/FEATURE_MAP.md`** ⇒ **ĐƯỢC PHÉP và PHẢI SỬA TẠI CHỖ** khi có gì đổi.
- **LỊCH SỬ** = **§2 `.ai/WORKLOG.md`** + `.ai/WORKLOG-archive-<quý>.md` ⇒ **chỉ ghi thêm**, không bao giờ là nguồn sự thật (tra xong phải đối chiếu §1/FEATURE_MAP).
- **Khi phiên này phủ định điều đã ghi trước đó:** (1) sửa mục trong FEATURE_MAP **tại chỗ**; (2) dòng §2 mới mở đầu bằng **`❌ ĐÍNH CHÍNH <ngày>:`**; (3) **THÊM nhãn** vào dòng cũ nếu gây hiểu nhầm (`— ❌ đã đổi 2026-09-17`) — chỉ thêm nhãn, KHÔNG xoá/viết lại nội dung cũ.

Repo này chỉ dùng **2 model: `claude` (Claude Code) và `deepseek` (DeepSeek)** — **chạy tuần tự, không đồng thời**. Nhãn model chỉ được là 1 trong 2 tên này.
Trạng thái bàn giao nằm ở `.ai/WORKLOG.md` — không script, không cài thêm gì.

1. **Đầu phiên:** đọc `.ai/WORKLOG.md` (§1 ĐANG LÀM + 10 dòng cuối §2) trước khi làm bất cứ việc gì.
2. **Bắt đầu việc:** cập nhật §1 — model, việc đang làm, file sẽ chạm, trạng thái.
3. **Hết việc / hết phiên:** cập nhật lại §1 (xong / dang dở + việc tiếp theo) và ghi 1 dòng vào §2 (bảng nhật ký).
4. §1 **ghi đè** (chỉ giữ khối mới nhất) · §2 **chỉ ghi thêm**, không sửa/xoá dòng cũ.
5. §1 đang ghi việc **dang dở của model khác** → không tự sửa tiếp file đó, hỏi người dùng trước.
6. Commit kèm nhãn model — chỉ `[claude]` hoặc `[deepseek]`: `feat(P3.1)[deepseek]: ...`

## P-index — "số này NGHĨA LÀ GÌ"

Quy ước: `P<nhóm>.<số>`, số là ID bất biến, không renumber, gap thoải mái.
3 lớp đồng bộ: (1) file này — nghĩa · (2) tag `// Px.y` ở entry point code · (3) `.ai/FEATURE_MAP.md` — file/test.

### Nhóm

| Nhóm | Phạm vi |
| :--- | :--- |
| **P1** | Core / Bootstrap — autoload, `Theme::boot`, Feature Registry, Site Mode |
| **P2** | Features hạ tầng — Setup, Enqueue, Performance, Security, SEO, Admin |
| **P3** | Front-end — template hierarchy, `template-parts/`, `patterns/`, design token/CSS |
| **P4** | Tích hợp WooCommerce — `Integrations/WooCommerce/*`, override `woocommerce/` |
| **P5** | Tích hợp bên thứ 3 — SEO plugin, đa ngôn ngữ (WPML/Polylang) |
| **P6** | Build & Tooling & CI — Vite, Composer, phpcs, phpstan, `ci.yml` |
| **P7** | i18n & Accessibility (cross-cutting) — `.pot`, textdomain, WCAG, keyboard nav |

### Số đã đặt

| Số | Nghĩa |
| :--- | :--- |
| `P1.1` | Theme bootstrap — `functions.php` (load autoload + boot), `Core\Theme` (hook `after_setup_theme`) |
| `P1.2` | Feature Registry — `Core\FeatureRegistry` + `Core\Contracts\FeatureInterface` (`shouldBoot()`/`boot()`) |
| `P1.3` | Site Mode — `SiteMode\SiteModeResolver` + `config/site-config.php` (`service`\|`ecommerce`\|`hybrid`) |
| `P2.1` | Setup — theme supports, `appearance-tools`, nav menus, `load_theme_textdomain` (title-tag chuyển sang P2.5) |
| `P2.2` | Enqueue — đọc Vite manifest; dev HMR / prod file hash. `Enqueue::enqueueEntry()` static tái dùng cho entry khác (P4). Mọi script `tl-theme*` render `type=module` |
| `P2.3` | Performance — gỡ wp-emoji; preload stylesheet chính của theme (KHÔNG đụng cache header/XML-RPC). Preload font: thêm khi site thực sự có woff2 |
| `P2.4` | Security — gỡ `wp_generator` (ẩn version); helper `Security::ksesInline()` cho template (KHÔNG set header/disable XML-RPC) |
| `P2.5` | SEO — `shouldBoot()` = KHÔNG có plugin SEO; chỉ thêm `add_theme_support('title-tag')`. `SEO::hasSeoPlugin()` feature-detect Yoast/RankMath/AIOSEO/SEOPress/TSF |
| `P2.6` | Admin — `register_block_pattern_category('tungleads')` cho patterns GĐ4. KHÔNG đăng ký CPT/taxonomy |
| `P3.1` | Base templates — `index.php`, `header.php`, `footer.php` |
| `P3.2` | Design tokens & base CSS — `theme.json` (editor token), `assets/src/tokens.css`, `assets/src/style.css` |
| `P3.3` | Dịch vụ templates — `archive-dichvu.php`, `single-dichvu.php`, `template-parts/content-dichvu.php`. CPT `dichvu` đăng ký ở `tl-site-plugin` (KHÔNG trong theme), file template vẫn ở theme |
| `P3.4` | Template hierarchy chuẩn — `front-page.php`, `home.php`, `archive.php`, `single.php`, `page.php`, `comments.php`, `template-parts/content-post.php`. Nội dung tĩnh do editor dựng bằng pattern |
| `P3.5` | Patterns theo site mode — `patterns/{shared,service,shop}/*.php` + `SiteMode\PatternLoader` (WP không quét đệ quy → tự đăng ký; gate `service`↔service/hybrid, `shop`↔ecommerce/hybrid). Gộp thay cho `ServiceModePatterns`/`EcommerceModePatterns` |
| `P4.1` | WooCommerceFeature — `shouldBoot()` = mode ∈ {ecommerce,hybrid} && `class_exists('WooCommerce')`. `add_theme_support('woocommerce'` + gallery zoom/lightbox/slider). Gỡ `woocommerce_get_sidebar` (không có sidebar.php). Enqueue entry `woocommerce.js` |
| `P4.2` | ProductArchive — hook-only: `loop_shop_columns`→3, `loop_shop_per_page`→12. KHÔNG copy `archive-product.php`. Site con override trực tiếp qua chính 2 hook đó |
| `P4.3` | Cart/Checkout — style block `.wc-block-cart`/`.wc-block-checkout` bằng biến `--wc-*` map từ token, nằm trong `woocommerce.css` (@layer woocommerce). KHÔNG override markup, KHÔNG cache session-dependent. *Gộp vào P4.4, không còn class PHP riêng* |
| `P4.4` | woocommerce-styles — `assets/src/woocommerce.css` trong `@layer woocommerce`: map token → nút/giá/sale/notice/focus + biến `--wc-*` cho Cart/Checkout. Entry Vite `woocommerce.js` riêng |
| `P6.1` | Build — `vite.config.js` (2 entry: `main.js`, `woocommerce.js`; output `manifest.json`) |
| `P6.2` | Composer / autoload — `composer.json` (PSR-4 `TL\Theme\`) + dev tool (phpcs/phpstan) |
| `P6.3` | Lint & static analysis — `phpcs.xml` (WordPress-Extra; `src/` bỏ quy ước tên file/hàm WP do PSR-4), `phpstan.neon` (level 5, `phpstan-bootstrap.php` khai hằng số), `eslint.config.js`, `.stylelintrc.json`, `.prettierrc.json` |
| `P6.4` | CI — `.github/workflows/ci.yml`: job `php` (phpcs+phpstan), job `assets` (eslint+stylelint+prettier+vite build). Chạy mỗi PR + push `main` |
| `P7.1` | i18n — text domain `tungleads-theme`, `load_theme_textdomain` (P2.1), `languages/tungleads-theme.pot` sinh bằng `wp i18n make-pot` |
| `P7.2` | Accessibility — `.ai/ACCESSIBILITY.md` (contrast palette đạt AA, skip-link, `:focus-visible`, reduced-motion, quy ước ARIA) |

## Quy trình khi thêm/sửa tính năng

1. Thêm mới: định nghĩa số ở bảng trên TRƯỚC → gắn tag `// Px.y slug` vào entry point → thêm dòng vào `.ai/FEATURE_MAP.md`.
2. Sửa số đã có: cập nhật cả 3 nơi cùng lúc trước khi báo hoàn thành.
3. Commit: `feat(P2.3): ...`, `fix(P1.2): ...`.

## Definition of Done mỗi module

pass lint/static analysis · không PHP notice khi `WP_DEBUG` · chuỗi đã bọc i18n · a11y cơ bản (tab được, contrast đạt) · có docblock · đúng site mode khai báo · cập nhật `CHANGELOG.md`.
