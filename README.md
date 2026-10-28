# Bloom Beyond Borders — Website

Vanilla PHP MVC rebuild of the Bloom Beyond Borders nonprofit website. See
[CLAUDE.md](CLAUDE.md) for the full project spec, architecture, and
branch-by-branch implementation roadmap.

## Requirements

- PHP 8.1+ (with `pdo_mysql`, `gd`, and `fileinfo` extensions — all bundled
  with most standard PHP installs)
- MySQL or MariaDB
- Composer

## Setup

1. **Install dependencies**

   ```
   composer install
   ```

2. **Create a database** (default name `bloom`, matching `app/Config/db.php`'s
   defaults) and import the schema, then the seed data:

   ```
   mysql -u root -p bloom < database/schema.sql
   mysql -u root -p bloom < database/seed.sql
   ```

3. **Configure the database connection.** Copy the example config (the real
   one is git-ignored so server credentials never get committed):

   ```
   cp app/Config/db.example.php app/Config/db.php
   ```

   The defaults are host `127.0.0.1`, database `bloom`, user `root`, empty
   password. If your setup differs, either edit `app/Config/db.php`, or set
   environment variables before starting PHP: `DB_HOST`, `DB_PORT`,
   `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

4. **Run the dev server**

   ```
   php -S localhost:8000 -t public
   ```

   Visit `http://localhost:8000`.

## Default admin login

The seed data creates one **Super Admin** account:

- **Email:** `admin@bloombeyondborders.org`
- **Password:** `ChangeMe123!`

Sign in at `/admin/login` and **change this password immediately** under
**My Account** (`/admin/account`).

## Users & roles

More people can be given back-office access from **Users** (`/admin/users`):
enter their name, email and role, and they receive an invite link to set their
own password. **Roles** (`/admin/roles`) control which sections each role can
manage; the built-in Super Admin role can do everything. Invite and
password-reset emails go through the SMTP settings below — until SMTP is
configured, the admin is shown the link to copy and send manually.

**Upgrading an existing database** (created before users & roles existed):

```
mysql -u root -p bloom < database/migrations/2026-10-06_admin_rbac.sql
```

Existing admin accounts become active Super Admins, so current logins keep
working.

## Outgoing email (SMTP)

The contact form and comment notifications send through PHPMailer, configured
entirely from the back office at `/admin/settings` (SMTP host/port/username/
password/encryption/from address). Until SMTP is configured there, the app
still works normally — form submissions save to the database, but no
notification email is sent (this is logged, not a failure).

## Online donations (Stripe)

Donations go through hosted [Stripe Checkout](https://docs.stripe.com/payments/checkout):
the visitor picks an amount on `/donate` (a general gift of any amount) or
`/donate/{scheme}` (a campaign created in `/admin/donation-schemes`), is sent
to Stripe's payment page, and comes back to a thank-you page. Card details
never touch this server.

A donation is recorded as `pending` when checkout starts and is only marked
`paid` by Stripe's signed webhook (`POST /stripe/webhook`), which also sends
the donor's receipt email. Until a secret key is saved in `/admin/settings`,
the Donate page shows a "contact us to donate" message instead of the form.

**Local testing**

1. In the Stripe dashboard (test mode), copy the secret key (`sk_test_…`)
   into Admin → Settings → Payments.
2. Install the [Stripe CLI](https://docs.stripe.com/stripe-cli) and forward
   events to the dev server:

   ```
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```

   Paste the `whsec_…` secret it prints into Admin → Settings → Payments.
3. Donate on `/donate` with test card `4242 4242 4242 4242` (any future
   expiry, any CVC). The donation shows as Paid in `/admin/donations`.

**Production:** use live keys, set `APP_URL` to the public HTTPS URL (it's
used for the Stripe success/cancel redirects), and in the Stripe dashboard
add a webhook endpoint at `https://<your-domain>/stripe/webhook` for the
events `checkout.session.completed`, `checkout.session.async_payment_succeeded`,
`checkout.session.async_payment_failed` and `checkout.session.expired`, then
save its signing secret in settings. Refunds are issued from the Stripe
dashboard.

## Shop

The shop is off by default. Add products in `/admin/products`, set the flat
shipping charge and ship-to countries under Admin → Settings → Shop, then
switch the shop on there; the Shop link and cart icon then appear in the
navigation.

Shoppers don't need an account: they add items to a session cart, check out
on Stripe's hosted page (which collects their email, phone and shipping
address), and are emailed their order details once the webhook confirms
payment. Paid orders appear under `/admin/orders`; **Mark as Shipped** emails
the buyer. Stock goes down automatically when an order is paid.

Test it the same way as donations (test key + `stripe listen`).

## Customer accounts (optional)

Visitors can create an account at `/account/register` to see their order and
donation history, keep a wishlist, and save a shipping address that Stripe
pre-fills at checkout. Accounts are entirely optional: guest checkout and
guest donations work exactly the same without one.

Earlier guest orders and donations made with the same email are attached to
the account only after the customer confirms their email via the emailed
link, so nobody can claim someone else's history by registering their
address. Confirmation and reset emails need SMTP configured; without it they
are logged, not sent. Admins can look up accounts under `/admin/customers`.

## Environment variables

All optional; sensible local defaults apply if unset. Put them in a `.env` file
in the project root (`cp .env.example .env`; it is git-ignored) or set them in
the server environment, which takes priority over the file.

| Variable | Purpose | Default |
|---|---|---|
| `APP_ENV` | `local` or `production` | `local` |
| `APP_DEBUG` | Force error display on/off, overriding the `APP_ENV`-based default | debug on unless `APP_ENV=production` |
| `APP_URL` | Public base URL, used for emailed links and Stripe redirect URLs — must be set in production | `http://localhost:8000` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database connection | `127.0.0.1`, `3306`, `bloom`, `root`, *(empty)* |
| `MAIL_FROM_EMAIL`, `MAIL_FROM_NAME` | Fallback sender identity (SMTP itself is configured in Admin → Settings) | `no-reply@bloombeyondborders.org`, `Bloom Beyond Borders` |
| `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET` | Stripe keys; override the ones saved in Admin → Settings → Payments | *(use the saved settings)* |

**Production checklist:** set `APP_ENV=production` (this alone turns off
error display and switches to a generic error page + server-side logging —
see `public/index.php`), serve over HTTPS (the session cookie automatically
adds the `Secure` flag once it detects HTTPS), and change the default admin
password.

## Project structure

```
public/            # web root - only this directory should be served
  index.php        # front controller: routing table + dispatch
  css/ js/ img/ lib/  # static assets
  uploads/         # user-uploaded images (gitignored, created on first upload)
app/
  Config/          # app.php, db.php, mail.php
  Core/            # Router, Controller/AdminController base classes, Auth,
                   # Csrf, Upload, Slug, Mailer, Database, Model base class
  Controllers/     # public site controllers + Controllers/Admin (back office)
  Models/          # one class per DB table (plain PDO, prepared statements)
  Views/           # .php templates; Views/admin for the back office
database/
  schema.sql       # table definitions
  seed.sql         # admin account + starter/placeholder content
tools/             # one-off maintenance scripts (see file headers), not part
                   # of the running app
```

## Security notes

- All SQL goes through PDO prepared statements — no raw string interpolation
  anywhere in the codebase.
- Every state-changing form (public and admin) is protected by a session-bound
  CSRF token, checked on every POST. The one exception is the Stripe webhook
  (`POST /stripe/webhook`), which Stripe calls server-to-server; it is
  authenticated by verifying the `Stripe-Signature` header instead and
  rejects anything unsigned.
- Payment amounts are always validated and priced server-side; payment
  status only changes via the signed webhook, never from the browser
  redirect, and webhook handling is idempotent (replayed events don't send
  duplicate receipts).
- The public contact form and blog comment form each include a honeypot field,
  invisible to real users, to catch simple spam bots without a CAPTCHA.
- Blog comments are moderated: a submitted comment is never publicly visible
  until approved from `/admin/comments`.
- File uploads (logos, photos, gallery images) are validated by extension and
  actual file content (not just the claimed MIME type), capped in size,
  renamed to a random filename on save, and SVGs are scanned for embedded
  `<script>`/event-handler content before being accepted.
- Passwords are hashed with `password_hash()` (bcrypt) and checked with
  `password_verify()` — never stored or compared in plain text.
