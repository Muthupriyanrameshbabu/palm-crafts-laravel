# Getting a Live Link — Railway (Temporary, No Domain Needed)

This gets you a real public URL like `palm-crafts-production.up.railway.app`
in about 10 minutes. When you buy your domain later, we'll move this to
Forge for full production control — Railway is the fast path to a link
today.

## 1. Push the project to GitHub

Railway deploys from a GitHub repo, so this needs to exist there first.

```bash
cd palm-crafts-laravel
git init
git add .
git commit -m "Initial commit"
```

Create a new **empty** repository on https://github.com/new (don't
initialize it with a README), then:
```bash
git remote add origin https://github.com/YOUR_USERNAME/palm-crafts-laravel.git
git branch -M main
git push -u origin main
```

## 2. Create the Railway project

1. Go to https://railway.com and sign up/log in with GitHub
2. **New Project → Deploy from GitHub repo** → select `palm-crafts-laravel`
3. Railway auto-detects it's a Laravel app via Nixpacks (the `nixpacks.toml`
   in this project tells it exactly how to build and start it)

## 3. Add a MySQL database

In your Railway project: **Create → Database → Add MySQL**. Railway
provisions it and generates connection credentials automatically.

## 4. Set environment variables

On your Laravel service → **Variables** tab → **Raw Editor**, paste:

```
APP_NAME="THE PALM CRAFTS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-service-name.up.railway.app

DB_CONNECTION=mysql
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database

RAZORPAY_KEY=your_test_key
RAZORPAY_SECRET=your_test_secret
```

You'll get the exact `APP_URL` after step 5 — come back and update it once
you have it (Railway will let you edit and redeploy).

Generate `APP_KEY` locally first and paste the value in:
```bash
php artisan key:generate --show
```

## 5. Generate your public domain

Laravel service → **Settings → Networking → Generate Domain**. This gives
you the `*.up.railway.app` link — copy it back into `APP_URL` above and
redeploy.

## 6. Seed initial data and create your admin

Railway gives you a shell into the running service. From the service page,
open the **Deployments** tab → latest deployment → **View Logs**, or use
the Railway CLI for a one-off command:

```bash
railway run php artisan db:seed
railway run php artisan storage:link
railway run php artisan app:make-admin you@example.com
```

(You'll need to register that email at `/register` on the live site first,
*then* run the `make-admin` command against it.)

## 7. Visit your link

`https://your-service-name.up.railway.app` should now show the live
storefront with seeded products, working cart, and Razorpay test-mode
checkout.

## Known limitation of this setup

`nixpacks.toml` starts the app with `php artisan serve`, which is fine for
a demo link but isn't meant for real production traffic (no proper process
manager, no queue worker running for order emails). This is intentionally
the fast path to *a link* — when you're ready for the real domain and
production traffic, we'll set this up properly on Forge with Nginx,
PHP-FPM, and a supervised queue worker.
