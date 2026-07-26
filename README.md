# THE PALM CRAFTS — Laravel E-Commerce Backend

Phase 1 scaffold: database schema, models, cart logic, and Razorpay checkout.
Views/Blade templates matching your current design are **not** included yet —
that's Phase 2, once you confirm this data layer and flow look right.

## What's in this scaffold

- **Migrations**: categories, products, product_images, product_variants,
  addresses, carts, cart_items, orders, order_items
- **Models**: full relationships + business logic helpers (price formatting,
  stock checks, line totals) — all money stored as **integer paise**, never
  float, to avoid rounding bugs
- **Services**:
  - `CartService` — add/update/remove, guest-to-user cart merging on login,
    row-locked stock checks to prevent overselling under concurrent requests
  - `OrderService` — cart → order conversion, re-validates stock at checkout
    time (not just add-to-cart time), idempotent payment confirmation
  - `RazorpayService` — order creation + signature verification wrapper
- **Controllers**: Cart, Checkout (2-step: create order → pay), Razorpay
  webhook handler
- **Security**: CSRF exclusion scoped to only the webhook route (verified by
  signature instead), OWASP security headers middleware, signature
  verification required before ANY order is marked paid

## Why payment confirmation works this way

Two paths can mark an order "paid": the browser redirect callback
(`CheckoutController::callback`) and the server-to-server webhook
(`RazorpayWebhookController`). **The webhook is the source of truth** — browsers
can lose connection right after a successful payment. Both paths call the
same `OrderService::confirmPayment()`, which is idempotent (checked via a
row lock), so whichever fires first wins and the second is a no-op.

Stock is deducted **only after confirmed payment**, not at add-to-cart time,
so abandoned checkouts don't hold inventory hostage — but it IS re-validated
at cart-add and at checkout-initiate, so customers get "out of stock"
feedback before typing in card details.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# fill in DB_* and RAZORPAY_* values in .env
php artisan migrate
php artisan storage:link
```

Merge `config/services.php`'s `razorpay` array into your existing
`config/services.php` if you already have one with mail/aws entries.

Install Razorpay Checkout.js in your pay view:
```html
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
```

Set up the webhook in your Razorpay dashboard pointing to:
`https://yourdomain.com/webhooks/razorpay`, subscribed to the
`payment.captured` event, and copy its signing secret into
`RAZORPAY_WEBHOOK_SECRET`.

## Phase 2 — storefront views (added)

Blade views now cover: homepage (hero, featured collection, craft process,
the interactive Kolam Studio dot-grid), collections/product listing, product
detail with variant + quantity picker, cart, and the full checkout flow
(address → Razorpay payment → confirmation).

**Design tokens** (see `tailwind.config.js`): a warm palm-cream/palmyra-brown
palette with an oxide-red Kolam accent and brass-gold highlights, Fraunces
for display type, Work Sans for body, IBM Plex Mono for labels — chosen to
extend the direction already set on your live Netlify site rather than
replace it.

**Kolam Studio** is built as an Alpine.js-driven SVG dot grid: click two dots
to draw a connecting line, or load one of the two preset patterns. This is
hand-rolled (no canvas library) so it stays lightweight and fully
server-rendered-friendly.

To build the frontend assets locally:
```bash
npm install
npm run dev    # local development with hot reload
npm run build   # production build
```

You'll also need Laravel Breeze (or your own auth scaffolding) for
`routes/auth.php`, which `routes/web.php` requires — run:
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

## Phase 3 — admin panel, emails, tests (added)

**Admin panel** at `/admin` (Filament):
- Products: full CRUD with inline image upload (drag-to-reorder) and variant
  management, prices entered in ₹ and converted to paise automatically
- Categories: simple CRUD
- Orders: **deliberately read-only on financial fields** — you can update
  fulfillment status and add internal notes, but line items, amounts, and
  payment IDs can't be edited, because an order is a record of what actually
  happened and shouldn't be editable after the fact

No one can register their way into the admin panel — access is gated by an
`is_admin` flag that nothing in the app UI can set. To grant yourself access
after registering a normal account:
```bash
php artisan app:make-admin you@example.com
```

**Order confirmation emails** are queued and sent automatically the moment a
payment is confirmed — dispatched outside the database transaction so a slow
mail server never holds a stock-decrement row lock.

**Rate limiting**: cart routes capped at 60 req/min, checkout at 20 req/min,
the webhook at 120 req/min (generous, since Razorpay may legitimately retry —
the real gatekeeper there is the signature check, not the rate limit).

**Tests** cover the two places a bug here costs real money:
- `CartServiceTest` — stock limits are enforced correctly across repeated
  add-to-cart calls, and prices are snapshotted (not live) once added
- `OrderServiceTest` — calling `confirmPayment()` twice (simulating the
  webhook and the browser redirect racing each other) deducts stock and
  sends the confirmation email exactly once, not twice

Run them with:
```bash
php artisan test
```

**Bugs I caught and fixed while building this phase:**
- The `addresses` table required a `user_id`, which would have crashed
  every guest checkout (no account) the moment someone tried to order
  without logging in — fixed to allow `NULL` for guest addresses.
- `checkout_email` session wasn't being set anywhere (caught in Phase 2),
  which would have 403'd every guest trying to view their own order
  confirmation page.



## Still to build (Phase 4+)

1. **Seed real product photography** — upload actual images via the admin
   panel's product image manager; the storefront degrades gracefully to a
   placeholder block until you do
2. **Forge deployment**: zero-downtime deploys, queue worker (`php artisan
   queue:work` — needed for order emails to actually send), scheduler, SSL
   via Let's Encrypt, `.env` secrets management
3. **Mobile cart drawer** (currently a full cart page — functional, but a
   slide-out drawer would match the "shopping_bag" icon UX)
4. **GST/tax handling** if/when you register for GST — `tax_in_paise` is
   already wired through the order pipeline as a placeholder, currently
   always 0

## Security notes (OWASP-aligned)

- All money handled as integer paise — no float arithmetic on currency
- Stock checks use `lockForUpdate()` inside transactions to prevent
  overselling under concurrent add-to-cart or checkout requests
- Razorpay webhook signature is verified before trusting ANY payment event
- CSRF protection is on by default everywhere except the one
  signature-verified webhook endpoint
- Security headers (CSP, X-Frame-Options, HSTS) applied globally via
  `SecurityHeaders` middleware — tighten the CSP `script-src` further once
  you know all third-party scripts you'll load
- Mass assignment is restricted via explicit `$fillable` on every model —
  never switched to `$guarded = []`
