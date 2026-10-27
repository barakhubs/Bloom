# Bloom Beyond Borders — Website Rebuild

## Project overview

Bloom Beyond Borders is a nonprofit supporting vulnerable children and women in Uganda (education, healthcare, economic opportunity) and African immigrant families in the US (culturally responsive integration support). The live site (bloombeyondborders.org) is currently WordPress/Divi and partly filled with placeholder content.

This project rebuilds it as a **vanilla PHP MVC** backend + **vanilla HTML/CSS/JS** frontend, using the free Bootstrap 5 template already in this folder ("Charitize" by HTML Codex) as the visual base — restyled to the org's brand colors and re-pointed at the real site's page set instead of the template's generic charity pages.

**Non-goals (explicitly out of scope):**
- ~~No shop/cart/e-commerce, no payment gateway integration.~~ Reversed by client request (2026-10-27): Stripe-powered donations (`feature/stripe-donations`) a guest-checkout shop (`feature/shop-cart-orders`), and optional customer accounts (`feature/customer-accounts`). See **Payments (Stripe)** below.
- No npm / JS build step / bundler — frontend stays static vanilla JS + the template's existing jQuery-plugin vendor libs.
- No newsletter signup feature.
- No multi-language (EN/FR) toggle — English only for v1.

## Tech stack

- **Backend:** PHP, hand-rolled MVC (no framework). Dev server: `php -S localhost:8000 -t public`.
- **Database:** MySQL/MariaDB (local dev via XAMPP — database `bloom`, user `root`, empty password, matching the defaults in `app/Config/db.php`), accessed via PDO with prepared statements only (no raw string-interpolated SQL, anywhere).
- **Dependencies:** Composer, scoped to **PHPMailer + stripe/stripe-php** (SMTP sending; Stripe Checkout + webhook verification). No other Composer or npm packages, and no Stripe.js; payments use the hosted Checkout redirect.
- **Frontend:** the template's existing stack stays as-is for visuals — Bootstrap 5 (compiled CSS, no Sass toolchain available), jQuery + wow.js/waypoints/owl-carousel/counterup vendor libs (`lib/`). Any *new* interactive code (admin panel forms, honeypot handling, comment forms, image upload previews) is written in plain vanilla JS — no new jQuery usage, no new frontend libraries.
- **Auth:** session-based admin auth, `password_hash()`/`password_verify()`, CSRF tokens on all state-changing admin forms.

## Color system

Old template colors → new brand colors:

| Role | Old | New |
|---|---|---|
| Primary | `#ffac00` | `#7EC11C` (green) |
| Secondary | `#1a685b` | `#003893` (blue) |
| Dark | `#051311` | keep as-is unless it visibly clashes |
| Light | `#fdf8ea` | keep as-is unless it visibly clashes |

`css/bootstrap.min.css` is a compiled/minified build with no Sass source, so there's no recompile step available. Approach:
1. Add a `:root` custom-properties block (extending the existing single `--bs-tertiary` var in `css/style.css`) defining `--bs-primary`, `--bs-secondary`, etc. as the new hex values.
2. Override Bootstrap's compiled `.btn-primary`, `.text-primary`, `.bg-primary`, `.btn-secondary`, `.text-secondary`, `.bg-secondary` (and any other primary/secondary-driven selectors) in `css/style.css` — which loads after `bootstrap.min.css` — to reference the new custom properties instead of hardcoded hex.
3. Do not hand-edit `bootstrap.min.css` directly; all overrides live in `style.css`.

## Public site map

| Page | Route | Built from (template reference) |
|---|---|---|
| Home | `/` | `index.html` (hero carousel, about, features/counters, donate-CTA, team, testimonials, footer gallery teaser) |
| Our Story | `/our-story` | `about.html` as base, extended with: founder bio, mission, impact-stats dashboard, financial-allocation chart, board letter (see Back office) |
| Our Services | `/our-services` | `feature.html` as base — Education, Early Childhood Education, Immigrant Family Support, upcoming initiatives (skills training, entrepreneurship, community networks) |
| Our Team | `/our-team` | `team.html` — board member cards (photo, name, title, bio) |
| Partners | `/partners` | new page, styled from the footer/donation-card patterns — partner logos + "Become a Sponsor" CTA into Contact |
| Gallery | `/gallery` | new page, extends the footer gallery grid — organized into **categorized albums** |
| Blog | `/blog`, `/blog/{slug}` | new — no equivalent in the template; list view (cards) + single-post view with comment form + approved comments list |
| Shop | `/shop`, `/shop/{slug}`, `/cart`, `/checkout/success` | `.donation-item` cards for products; session cart; guest checkout on Stripe's hosted page (email, phone, shipping address collected there). Hidden (404 + no nav link) until **Settings → Shop** is switched on |
| Customer account | `/account/*` (register, login, forgot/reset, verify, orders, donations, wishlist, profile) | public layout + `account/_start.php`/`_end.php` sidebar layout. **Optional**: see Customer accounts below |
| Contact | `/contact` | `contact.html` — form (name, email, subject, message) + honeypot, org phone/email/address, social links |
| Donate | `/donate`, `/donate/{slug}`, `/donate/thank-you` | the template's `.donate` split-panel + `.donation-item` cards — general "any amount" form + active donation-scheme cards (progress bar when a goal is set); all Donate buttons/CTAs and a nav button link here |

404 page (`404.html`) is carried over as-is, restyled.

## Back office (admin)

Multiple back-office users with role-based permissions (client feedback, `feature/admin-users-rbac`). Login at `/admin/login`, session-based.

**Users & permissions:**
- Permissions are a fixed list in code, one per section: `App\Core\Permissions::ALL` (`settings`, `team`, `partners`, `gallery`, `blog`, `comments`, `story`, `contact`, `donations`, `shop`, `orders`, `customers`, `users`). Dashboard and My Account are open to every logged-in user.
- **Roles** (`/admin/roles`) — admins create custom roles and tick which sections each one can manage. The built-in **Super Admin** role (`roles.is_system = 1`) implicitly has everything and can't be edited or deleted. A role can't be deleted while users are assigned to it.
- **Users** (`/admin/users`) — admin enters name + email + role, and the user gets a single-use **invite link** (72h) by email to set their own password. Admins can resend invites, send password-reset links (2h), and enable/disable or delete users. If SMTP isn't configured, the link is shown to the admin to copy and send themselves.
- Guards: you can't delete or disable yourself or change your own role; the last active Super Admin can't be removed or demoted; only a Super Admin can grant the Super Admin role or edit/delete a Super Admin.
- **My Account** (`/admin/account`) — change your own name/password. **Forgot password** on the login page sends a reset link (same response whether or not the email exists).
- Enforcement: each admin controller declares `protected ?string $permission = '<slug>';` and `AdminController`'s constructor returns 403 if `Auth::can()` fails. The sidebar and dashboard tiles hide sections the user can't access.

**Sections:**
- **Dashboard** — quick counts (pending comments, contact submissions, team/partner/gallery counts), limited to the sections the user can access.
- **Site Settings** — social links (LinkedIn, Instagram, etc.), SMTP config (host/port/username/password/encryption/from-name/from-email) used by PHPMailer, site logo, favicon, footer logo (three separate uploads).
- **Team** — CRUD for board/team members (name, title, bio, photo).
- **Partners** — CRUD for partner orgs (name, logo, optional link).
- **Gallery** — CRUD for albums, and CRUD for images within an album (caption, album assignment).
- **Blog** — CRUD for posts (title, slug, body, featured image, published/draft, date); comment moderation queue (approve/reject, flat list per post).
- **Contact submissions** — inbox/list of submitted contact-form messages.
- **Shop** — Products (`shop` permission: name, slug, summary, description, price, image, stock, active, sort order) and Orders (`orders` permission: filter by status, detail with items + ship-to address, **Mark shipped** → emails the buyer, **Cancel** with optional restock after refunding in Stripe). Shop on/off, flat shipping rate, and ship-to countries live in Site Settings.
- **Customers** (`customers` permission): list/search customer accounts, view their orders + donations, email a password-reset link, or delete (orders/donations are kept, unlinked). Admins never see or set customer passwords.
- **Donations** (`donations` permission) — Donation Schemes CRUD (name, slug, summary, description, image, suggested amounts, allow-custom-amount, optional goal, active, sort order) and a read-only donations ledger (filter by status/scheme, totals, link to the Stripe payment). Refunds happen in the Stripe dashboard.
- **Our Story content blocks** — editable fields for: impact-stats counters (label + number, repeatable), financial-allocation chart data (year + category + percentage, repeatable), board letter (author name/title + body text). Seed these with the current live-site placeholder text as starting content.

## MVC architecture

```
public/            # web root — only this is web-accessible
  index.php        # front controller
  css/ js/ img/ lib/  # static assets carried over from the template
app/
  Config/          # db.php, mail.php, app.php
  Core/            # Router, Controller base, Model base, Auth/session helpers, CSRF helper
  Controllers/     # Home, Story, Services, Team, Partners, Gallery, Blog, Contact,
                   # Admin/AuthController, Admin/DashboardController, Admin/*CrudControllers
  Models/          # TeamMember, Partner, GalleryAlbum, GalleryImage, BlogPost, BlogComment,
                   # ContactSubmission, Setting, AdminUser, StoryContentBlock
  Views/           # .php templates; shared partials for header/nav/footer
database/
  schema.sql       # table definitions
  seed.sql         # optional: placeholder Our Story content, admin user
composer.json      # PHPMailer only
CLAUDE.md
```

Routing is a small hand-written `Router` (method + path → controller action), not a framework. All DB access goes through PDO in the Models — no query building elsewhere.

## Database schema outline

Implemented in `database/schema.sql`, seeded via `database/seed.sql` — both verified by direct import into the local XAMPP `bloom` database (MariaDB 10.4.32).

- `roles` (id, name unique, is_system, created_at) + `role_permissions` (role_id FK → roles ON DELETE CASCADE, permission slug; PK both)
- `admin_users` (id, name, email, password_hash nullable until the invite is accepted, role_id FK → roles ON DELETE RESTRICT, status[invited/active/disabled], last_login_at, created_at)
- `admin_user_tokens` (id, user_id FK → admin_users ON DELETE CASCADE, token_hash = SHA-256 of the emailed token, purpose[invite/reset], expires_at, used_at, created_at)
- Existing DBs: apply `database/migrations/2026-10-06_admin_rbac.sql` (existing admins become active Super Admins)
- `settings` (key varchar primary key, value text) — social links, contact phone/email/address, SMTP config, logo/favicon/footer-logo paths
- `team_members` (id, name, title, bio, photo_path, sort_order)
- `partners` (id, name, logo_path nullable, tagline, description, link_url, sort_order) — description is plain text: blank line = new paragraph, `## ` line = subheading. Existing DBs: apply `database/migrations/2026-10-02_partner_details.sql`
- `gallery_albums` (id, name, slug, sort_order)
- `gallery_images` (id, album_id FK → gallery_albums, ON DELETE CASCADE, image_path, caption, sort_order)
- `blog_posts` (id, title, slug, body, featured_image_path, status[draft/published], published_at)
- `blog_comments` (id, post_id FK → blog_posts, ON DELETE CASCADE, author_name, author_email, body, status[pending/approved], created_at — honeypot flag not persisted, rejected silently before insert)
- `contact_submissions` (id, name, email, subject, message, created_at)
- Our Story content, split into three focused tables (settled from the "or split" option): `story_stats` (id, label, value, sort_order), `story_finance_entries` (id, year, category, percentage, sort_order), `story_board_letter` (id fixed to 1 via CHECK constraint, author_name, author_title, body, updated_at)
- `donation_schemes` (id, name, slug unique, summary, description, image_path, preset_amounts = CSV of cents, allow_custom_amount, goal_cents nullable, is_active, sort_order, created_at)
- `donations` (id, scheme_id FK → donation_schemes ON DELETE SET NULL, amount_cents, currency, donor_name, donor_email, message, status[pending/paid/expired/failed], stripe_session_id unique, stripe_payment_intent_id, created_at, paid_at). Existing DBs: apply `database/migrations/2026-10-27_donations.sql`
- `products` (id, name, slug unique, summary, description, price_cents, image_path, stock, is_active, sort_order, created_at)
- `orders` (id, reference unique e.g. `BBB-7K3Q9X`, customer_name/email/phone, ship_name/line1/line2/city/state/postal_code/country, subtotal_cents, shipping_cents, total_cents, currency, status[pending/paid/shipped/cancelled/expired], stripe_session_id unique, stripe_payment_intent_id, created_at, paid_at, shipped_at)
- `order_items` (id, order_id FK → orders ON DELETE CASCADE, product_id FK → products ON DELETE SET NULL, product_name + unit_price_cents snapshots, quantity). Existing DBs: apply `database/migrations/2026-10-27_shop.sql`
- `customers` (id, name, email unique, password_hash, phone, email_verified_at, stripe_customer_id, ship_line1/line2/city/state/postal_code/country, created_at, last_login_at)
- `customer_tokens` (same shape and rules as `admin_user_tokens`, purpose[verify/reset], 48h verify / 2h reset)
- `wishlist_items` (customer_id FK CASCADE, product_id FK CASCADE, created_at; PK both)
- `orders.customer_id` / `donations.customer_id`: nullable FK → customers ON DELETE SET NULL. Existing DBs: apply `database/migrations/2026-10-27_customer_accounts.sql`

## Customer accounts

Added at the user's request on top of the client's "no accounts" brief, so **guest checkout and guest donations stay the default and never require an account**.
- `App\Core\CustomerAuth` mirrors `Auth` but uses its own session key (`customer_id`), so admin and customer sessions never interfere. `CustomerController` is the base for signed-in `/account` pages (signed-out visitors are redirected to `/account/login?next=…`; `CustomerAuth::safeNext()` only allows local paths).
- Register / forgot-password give the same response whether or not the email exists. Registering with an existing email sends that owner a verify or reset link instead of creating a duplicate.
- **Guest history is linked only after the email is proven**: on verification (or a password reset, since that link went to the same inbox), `Customer::linkGuestHistory()` attaches `customer_id IS NULL` orders/donations with that email. The webhook also links new guest purchases to a *verified* account with the same email. Changing email resets verification and voids every outstanding token.
- Orders/donations placed while signed in carry `customer_id` from the start and show in the account immediately. Account order pages are scoped `WHERE customer_id = ?`, so other references 404.
- Signed-in checkout creates/refreshes a Stripe Customer (`Stripe::customerFor()`) so Checkout pre-fills email, name and saved address; on any Stripe error it falls back to the guest flow. Donations just pre-fill `customer_email`.
- No self-service cancellation: customers contact the org, and admins cancel/refund.

## Payments (Stripe)

- Hosted **Stripe Checkout** redirect (`App\Core\Stripe` wraps the SDK); keys live in `settings` (`stripe_secret_key`, `stripe_webhook_secret`), entered in Site Settings and never re-displayed. No keys = the Donate page falls back to "contact us".
- Money is integer **cents**, USD (`App\Core\Money`). Amounts are validated and priced server-side ($1–$50,000; presets must match the scheme; custom amounts only if the scheme allows them) and sent to Stripe as `price_data`. Nothing price-related is trusted from the client.
- Rows are created `pending` before redirecting to Stripe. **Only the signed webhook** (`POST /stripe/webhook` → `StripeWebhookController`) marks them `paid`/`failed`/`expired`; the thank-you page is display-only. Handlers are idempotent (`UPDATE … WHERE status = 'pending'`, side effects only when `rowCount() === 1`), so Stripe retries/replays don't double-send receipts. The session's `metadata[type]` routes the event (`donation` / `order`).
- **Shop:** the cart (`App\Core\Cart`) stores only product id → qty in the session; `Cart::resolve()` re-reads live prices/stock and caps quantities. Checkout re-validates, writes a pending order with snapshot line items, then opens a 30-minute Checkout Session (flat-rate `shipping_options`, `shipping_address_collection`, phone collection). The cancel URL expires that session immediately. When an order is paid, the webhook stores the address (from `collected_information.shipping_details`), decrements stock (floored at 0; oversell between checkout and payment is possible and is flagged in the admin email), and emails the order confirmation to the buyer and a notification to the org.

## Forms & spam handling

- **Honeypot pattern** (shared by contact form and blog comment form): a hidden input (visually hidden via CSS, not `type="hidden"`, so real bots that skip CSS still fill it) with a plausible name (e.g. `website`); server rejects silently (fake-success response) if it's non-empty.
- Contact form: server-side validation → persist to `contact_submissions` → send notification email via PHPMailer using the admin-configured SMTP settings.
- Blog comments: server-side validation → persist as `status = pending` → visible on the post only after admin approval in the moderation queue.

## Security notes

Verified in `polish/security-hardening-docs` via a systematic audit (not just written intent) — see that branch's commit for the specifics of what was checked.

- `password_hash()` / `password_verify()` for all back-office accounts; no plaintext passwords anywhere.
- RBAC: permission checked server-side in `AdminController`'s constructor (403 for GET and POST alike), not just hidden in the nav. `Auth::user()` re-reads the user + role permissions from the DB on every request, so role edits apply immediately and disabling/deleting a user ends their existing session on their next request. Invited and disabled accounts get the same generic login error as a wrong password.
- Invite/reset tokens: 32 random bytes, only the SHA-256 hash stored, single-use (consumed atomically), expiring (72h invite / 2h reset), and issuing a new one voids earlier unused ones.
- PDO prepared statements for every query — no string-concatenated SQL anywhere in the codebase (confirmed: zero raw `->exec()` calls, zero variable-interpolated SQL strings).
- CSRF token (session-bound, checked on POST) on every state-changing form, public and admin alike — confirmed all registered POST routes (30 at audit time, 40 after `feature/admin-users-rbac`) trace back to a `Csrf::verify()` check, and every check fails closed (blocks + redirects on mismatch, never silently continues). **Sole exception:** `POST /stripe/webhook` (server-to-server from Stripe), which is authenticated by `Stripe-Signature` verification instead and returns 400 on failure.
- File uploads (team photos, partner logos, gallery images, site logo/favicon/footer logo, blog featured images) all go through one `App\Core\Upload::store()` helper: extension allowlist, actual file content checked against the claimed extension via `finfo` for raster formats, a max size cap, and a randomly-generated filename on save (the original filename is never trusted or reused). SVG uploads (logos only) are additionally scanned for `<script>` tags, `on*=` event-handler attributes, and `javascript:` URIs before being accepted — verified against both a clean and a deliberately malicious SVG through the real upload pipeline.
- Session cookie hardened: `HttpOnly` always, `SameSite=Lax`, `Secure` auto-enabled when served over HTTPS.
- `debug` config now derives from `APP_ENV` (off in production unless explicitly overridden via `APP_DEBUG`) instead of being hardcoded on; when off, uncaught exceptions show a generic message and log the real error server-side rather than leaking stack traces/file paths — verified directly (a test exception with a fake "sensitive" string appeared in the log but not in the HTTP response).

## License note

The base template is HTML Codex's free "Charitize" template (CC-BY-style license). Even though colors, pages, and the entire backend are being reworked, the free tier requires attribution — keep a small "Designed by HTML Codex" credit/link in the footer.

## Implementation roadmap

One feature branch per row, in order — each builds on the ones above it and should be independently mergeable/testable before starting the next. `main` starts as the current static template (git init + baseline commit) before Branch 1.

| # | Branch | Delivers | Verify |
|---|---|---|---|
| 1 | `feature/mvc-skeleton` | `composer.json` (PHPMailer dep only), full `public/` + `app/Config,Core,Controllers,Models,Views` folder structure, front controller, hand-rolled `Router`, base `Controller`/`Model`, PDO DB connection bootstrap | `php -S localhost:8000 -t public` serves one placeholder route end-to-end |
| 2 | `feature/database-schema` | `database/schema.sql` (all 10 tables), `database/seed.sql` (admin user, placeholder Our Story content, default settings row) | Import cleanly into a fresh MySQL DB, no FK errors |
| 3 | `feature/brand-theme` | `:root` color overrides in `css/style.css` (`#7EC11C` / `#003893`), favicon/logo placeholder swap | Visually diff key pages against old template colors |
| 4 | `feature/public-layout` | Shared header/nav/footer view partials wired to the real site map, restyled 404 | Nav links resolve to correct (even if stubbed) routes on every page |
| 5 | `feature/home-page` | Home controller + view: hero, mission, programs, donate-CTA, team teaser (DB), gallery teaser (DB), partners teaser (DB, hidden when empty) | Manual browse of `/` |
| 6 | `feature/our-story-page` | Founder bio/mission static content + DB-backed stats/finance/board-letter blocks (reads `story_content_blocks`) | Content renders from seeded DB data |
| 7 | `feature/our-services-page` | Services controller/view (Education, ECE, Immigrant Family Support, upcoming initiatives) | Manual browse of `/our-services` |
| 8 | `feature/team-partners-public` | Team + Partners pages, DB-backed read-only display | Manual browse of `/our-team`, `/partners` |
| 9 | `feature/gallery-public` | Gallery page, albums + images, DB-backed | Manual browse of `/gallery` |
| 10 | `feature/blog-public` | Blog list + single-post view, published posts only, no comments yet | Manual browse of `/blog`, `/blog/{slug}` |
| 11 | `feature/contact-form` | Contact page, honeypot, server validation, persist to `contact_submissions`, PHPMailer SMTP notification | Submit form → row in DB + email received (or logged if SMTP unset) |
| 12 | `feature/blog-comments` | Comment form on single post, honeypot, `pending` status, only `approved` shown publicly | Submit comment → not visible until manually flipped to approved in DB |
| 13 | `feature/admin-auth` | `/admin/login`, `/admin/logout`, session auth, CSRF helper, `/admin/*` route protection | Unauthenticated access to `/admin/*` redirects to login |
| 14 | `feature/admin-settings` | Dashboard counts, Site Settings screen (socials, SMTP config, site logo/favicon/footer-logo upload) | Change a setting → reflected on public site (e.g. social link) |
| 15 | `feature/admin-team-partners-crud` | Team + Partners CRUD (photo/logo upload, sort order) | Create/edit/delete reflected on public `/our-team`, `/partners` |
| 16 | `feature/admin-gallery-crud` | Album CRUD + image CRUD/upload/assign | Reflected on public `/gallery` |
| 17 | `feature/admin-blog-crud` | Post CRUD + comment moderation queue (approve/reject) | Publishing a post shows it on `/blog`; approving a comment shows it on the post |
| 18 | `feature/admin-contact-story-crud` | Contact submissions inbox, Our Story content-block CRUD | Editing a stat/finance/letter block updates `/our-story` |
| 19 | `polish/security-hardening-docs` | CSRF coverage audit, upload validation audit, prepared-statement audit, `README.md` setup/run instructions | Manual pass through the security notes checklist above |
| 20 | `feature/stripe-donations` | stripe-php, Stripe settings, donation schemes + general donations via Stripe Checkout, signed idempotent webhook, receipt emails, admin schemes CRUD + donations ledger | Test-mode donation with `stripe listen` → row `paid`, receipt sent; replayed event sends nothing new |
| 21 | `feature/shop-cart-orders` | Products, session cart, guest checkout via Stripe Checkout (shipping address, flat-rate shipping), order emails, admin products + orders (mark shipped / cancel), shop on/off + shipping settings | Test-mode order → `paid`, stock decremented, confirmation email |
| 22 | `feature/customer-accounts` | Optional customer sign-up/sign-in/reset, order + donation history, wishlist, profile + saved address, Stripe pre-fill, admin Customers; guest history linked only after email verification | Guest order → register same email → hidden until verified, then listed; another customer's order reference 404s |

Branches 1–4 are foundation (not independently useful to an end user); 5–12 build out the full public site; 13–18 build out the back office; 19 closes out with a security/docs pass.
