# Drape — online store

A self-hosted clothing store built on Laravel 12: collections, products with size and colour variants, a slide-out cart, guest checkout with cash on delivery, customer accounts, discount codes, reviews and an admin panel.

## Requirements

- PHP 8.2+ with the `gd`, `pdo_mysql` (or `pdo_sqlite`), `mbstring` and `fileinfo` extensions
- Composer
- MySQL 8 / MariaDB 10.6+ (SQLite works for local development)

No Node build step is needed — storefront CSS/JS live in `public/assets/drape/`.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # or set DB_* in .env for MySQL
php artisan migrate
php artisan db:seed                     # Size/Colour options, store settings, FAQs
php artisan db:seed --class=DemoCatalogSeeder   # optional sample catalogue
php artisan store:create-admin you@example.com
php artisan serve
```

Storefront: http://127.0.0.1:8000 · Admin: http://127.0.0.1:8000/admin/login

The demo catalogue uses illustrated placeholder images and the discount code `WELCOME10`. Delete the demo products (or run `php artisan migrate:fresh --seed`) before launch.

## Running the store

| Task | Where |
| --- | --- |
| Add a product with sizes/colours | Admin → Products → Add Product → tick colours and sizes → **Build variants** → set stock per row |
| Add colours, sizes or swatches | Admin → Options (Size, Color) |
| Collections (Women, Men…) and their URL | Admin → Collections |
| Announcement bar, currency, delivery fee, free-shipping threshold, size guide, policies | Admin → Site Info |
| Homepage slides | Admin → Banners |
| Discount codes | Admin → Coupons |
| Orders, status, tracking number | Admin → Orders. Cancelling an order restocks it. |

Product photos should be portrait 3:4 (recommended 1080 × 1440 px).

## Going live (shared hosting)

1. Upload the project; point the domain at `public/` (a sibling `public_html/` folder is detected automatically).
2. In `.env` set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`, the database login, and a long random `MAINTENANCE_TOKEN`.
3. Run `php artisan migrate --force`, `php artisan db:seed --force` and `php artisan store:create-admin`.
4. Admin → **Generate sitemap** writes `sitemap.xml` and `robots.txt` for your domain.
5. Without SSH, `/system/{MAINTENANCE_TOKEN}/cache` rebuilds the config cache from `.env`.

Optional: Google sign-in (`GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET`), and `TRUSTED_PROXIES` if the site sits behind Cloudflare.

## Tests

```bash
php artisan test
```
