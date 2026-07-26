# Running THE PALM CRAFTS Locally

## 1. Prerequisites

Install these once on your machine:

- **PHP 8.2+** — check with `php -v`
- **Composer** — check with `composer -V` (https://getcomposer.org)
- **Node.js 18+** and npm — check with `node -v`

If you're on Windows and don't want to install PHP/Composer separately,
**Laravel Herd** (https://herd.laravel.com) bundles PHP, Composer, and a
local dev server in one installer — probably the fastest path. Mac has a
Herd version too.

## 2. Unzip and install dependencies

```bash
cd palm-crafts-laravel
composer install
npm install
```

## 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

### Fastest option: SQLite (no database server to install)

Open `.env` and replace the `DB_*` block with:
```
DB_CONNECTION=sqlite
```
(delete or comment out the other `DB_*` lines)

Then create the empty database file:
```bash
touch database/database.sqlite
```
(Windows PowerShell: `New-Item database/database.sqlite`)

### Alternative: MySQL

If you'd rather use MySQL (e.g. via XAMPP/Laravel Herd's MySQL, or a local
install), keep the `.env.example` defaults and create a database named
`palmcrafts` first:
```sql
CREATE DATABASE palmcrafts;
```

## 4. Migrate and seed

```bash
php artisan migrate --seed
php artisan storage:link
```

This creates all tables and adds 5 sample products (including the
Pattamadai Tote from your original design) so the storefront isn't empty.
Product photos aren't included — the pages show a placeholder block where
an image would go until you upload real ones via the admin panel (Phase 3)
or manually into `storage/app/public/products/`.

## 5. Set up authentication scaffolding

The routes file references `routes/auth.php`, which isn't generated yet:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
php artisan migrate
```

## 6. Razorpay test keys

Sign up at https://dashboard.razorpay.com (free), switch to **Test Mode**,
and copy your test key/secret into `.env`:
```
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxx
```
Leave `RAZORPAY_WEBHOOK_SECRET` blank for now — webhooks need a public URL
(covered when we get to deployment). Locally, the payment page will still
work using Razorpay's test card numbers: card `4111 1111 1111 1111`, any
future expiry, any CVV.

## 7. Run it

Open two terminals:

```bash
# Terminal 1 — Laravel app server
php artisan serve
```
```bash
# Terminal 2 — frontend asset watcher
npm run dev
```

Visit **http://127.0.0.1:8000** — you should see the homepage with the
5 seeded products, the Kolam Studio, and a working cart → checkout flow
using Razorpay test mode.

## Troubleshooting

- **Blank/broken styling** → `npm run dev` needs to stay running in a
  terminal; if you close it, run `npm run build` instead for a one-time
  compile.
- **"could not find driver" DB error** → your PHP install is missing the
  sqlite or mysql PDO extension; Laravel Herd includes both by default.
- **419 Page Expired on submit** → usually a stale `.env` `APP_KEY`; rerun
  `php artisan key:generate` and restart `php artisan serve`.
- **Razorpay checkout doesn't open** → check the browser console; almost
  always a missing/incorrect `RAZORPAY_KEY` in `.env`.
