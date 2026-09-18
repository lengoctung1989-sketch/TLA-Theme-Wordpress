# TLA Theme

WordPress Classic Theme + `theme.json` (hybrid). Feature Registry + Site Mode. WooCommerce Blocks-first.

- WordPress ≥ 6.5 · PHP ≥ 8.2 · `theme.json` schema v2
- Site mode: `service` | `ecommerce` | `hybrid` — cấu hình ở `config/site-config.php`
- Text domain: `tungleads-theme`

## Nhân bản cho site khách — parent/child

`tungleads-theme` là **parent theme**. Mỗi site khách = 1 **child theme mỏng** (VD `tungleads-theme-cp` cho caophat.vn):

- Child khai `Template: tungleads-theme` trong `style.css`, chỉ chứa: skin CSS, vài template override, hook site-specific, `config`/theme.json override cấp site.
- Bootstrap, FeatureRegistry, SiteMode, Features, WooCommerce integration → **parent lo hết** (parent `functions.php` luôn chạy trước child; `vendor/autoload.php` của parent nạp mọi class `TL\Theme\`).
- Business logic (CPT, taxonomy, API riêng) → **plugin `tl-site-plugin`**, không nằm trong child theme.
- Version: parent gắn git tag `v0.x.y`; child ghi `Base: tungleads-theme@vX.Y.Z`. Nâng parent là hành động có chủ đích (commit + `CHANGELOG.md` → update child → test).
- Deploy: ship **cả parent + child** + `assets/dist/` của parent. Composer/Node không cần khi deploy (`vendor/` + `assets/dist/` đã commit).

## Cài đặt (dev)

```bash
cd tungleads-theme
composer install      # vendor/autoload.php + phpcs/phpstan
npm install           # vite + eslint/stylelint/prettier
npm run build         # sinh assets/dist + manifest
```

## Kiểm tra chất lượng (giống CI)

```bash
composer run lint      # phpcs — WordPress-Extra
composer run analyze   # phpstan level 5 (+ WordPress stubs)
npm run lint:js        # eslint (flat config)
npm run lint:css       # stylelint (standard)
npm run format:check   # prettier
npm run build          # Vite build phải thành công
```

`composer run lint:fix` (phpcbf) và `npm run format` để tự sửa.
CI chạy toàn bộ trên mỗi PR + push `main` (`.github/workflows/ci.yml`).

## i18n

Sinh lại file dịch mẫu (cần wp-cli — dùng qua Docker):

```bash
docker compose run --rm --user root --entrypoint sh wpcli -c \
  'wp --allow-root i18n make-pot /var/www/html/wp-content/themes/tungleads-theme \
   /var/www/html/wp-content/themes/tungleads-theme/languages/tungleads-theme.pot \
   --domain=tungleads-theme --exclude=vendor,node_modules,assets/dist'
```

## Local WordPress (Docker Compose — cần Docker Desktop)

Toàn bộ code WordPress nằm trong repo tại `./wordpress/`. Theme phát triển trực tiếp tại
`./wordpress/wp-content/themes/tungleads-theme/`. Chạy từ thư mục gốc repo:

```bash
docker compose up -d          # khởi động WordPress + MariaDB
docker compose down           # dừng (giữ DB)
docker compose down -v        # dừng + xoá DB
docker compose run --rm wpcli <lệnh wp-cli>
```

- Site: http://localhost:8888 — Admin: http://localhost:8888/wp-admin (`admin` / `password`)
- DB MariaDB: `127.0.0.1:3307` (user/pass/db đều là `wordpress`)
- `WP_ENVIRONMENT_TYPE=development` → theme nạp asset từ Vite dev server (`npm run dev`).
  Không chạy dev server thì `npm run build` rồi tạm bỏ biến này để test bản prod.

## Asset workflow

- Dev (HMR): `npm run dev` — `WP_ENVIRONMENT_TYPE=development` khiến theme nạp asset từ `http://127.0.0.1:5173`.
- Prod: `npm run build` — `Enqueue.php` đọc `assets/dist/.vite/manifest.json`, nạp file hash dạng ES module.

## Kiến trúc

`functions.php` → Composer autoload → `TL\Theme\Core\Theme::boot()` → hook `after_setup_theme` (prio 5)
→ `SiteModeResolver` đọc config → `FeatureRegistry` gọi `shouldBoot()`/`boot()` từng Feature.
