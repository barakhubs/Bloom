# Bloom Beyond Borders — Website Rebuild

## Project overview

Bloom Beyond Borders is a nonprofit supporting vulnerable children and women in Uganda (education, healthcare, economic opportunity) and African immigrant families in the US (culturally responsive integration support). The live site (bloombeyondborders.org) is currently WordPress/Divi and partly filled with placeholder content.

This project rebuilds it as a **vanilla PHP MVC** backend + **vanilla HTML/CSS/JS** frontend, using the free Bootstrap 5 template already in this folder ("Charitize" by HTML Codex) as the visual base — restyled to the org's brand colors and re-pointed at the real site's page set instead of the template's generic charity pages.

**Non-goals (explicitly out of scope):**
- No shop/cart/e-commerce, no payment gateway integration.
- No npm / JS build step / bundler — frontend stays static vanilla JS + the template's existing jQuery-plugin vendor libs.
- No newsletter signup feature.
- No multi-language (EN/FR) toggle — English only for v1.
- No roles/permissions system in the back office — a single admin account.

## Tech stack

- **Backend:** PHP, hand-rolled MVC (no framework). Dev server: `php -S localhost:8000 -t public`.
- **Database:** MySQL/MariaDB (local dev via XAMPP — database `bloom`, user `root`, empty password, matching the defaults in `app/Config/db.php`), accessed via PDO with prepared statements only (no raw string-interpolated SQL, anywhere).
- **Dependencies:** Composer, scoped to **PHPMailer only** (SMTP sending for contact-form notifications and comment-moderation emails). No other Composer or npm packages.
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
| Contact | `/contact` | `contact.html` — form (name, email, subject, message) + honeypot, org phone/email/address, social links |
| Donate CTA | section on Home/Our Story/Contact, not its own page | informational only — suggested amounts as display, no payment processing, "Donate" buttons link to `/contact` |

404 page (`404.html`) is carried over as-is, restyled.

## Back office (admin)

Single admin account, login at `/admin/login`, session-based.

**Sections:**
- **Dashboard** — quick counts (pending comments, contact submissions, team/partner/gallery counts).
- **Site Settings** — social links (LinkedIn, Instagram, etc.), SMTP config (host/port/username/password/encryption/from-name/from-email) used by PHPMailer, site logo, favicon, footer logo (three separate uploads).
- **Team** — CRUD for board/team members (name, title, bio, photo).
- **Partners** — CRUD for partner orgs (name, logo, optional link).
- **Gallery** — CRUD for albums, and CRUD for images within an album (caption, album assignment).
- **Blog** — CRUD for posts (title, slug, body, featured image, published/draft, date); comment moderation queue (approve/reject, flat list per post).
- **Contact submissions** — inbox/list of submitted contact-form messages.
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

- `admin_users` (id, email, password_hash, created_at)
- `settings` (key varchar primary key, value text) — social links, contact phone/email/address, SMTP config, logo/favicon/footer-logo paths
- `team_members` (id, name, title, bio, photo_path, sort_order)
- `partners` (id, name, logo_path, link_url, sort_order)
- `gallery_albums` (id, name, slug, sort_order)
- `gallery_images` (id, album_id FK → gallery_albums, ON DELETE CASCADE, image_path, caption, sort_order)
- `blog_posts` (id, title, slug, body, featured_image_path, status[draft/published], published_at)
- `blog_comments` (id, post_id FK → blog_posts, ON DELETE CASCADE, author_name, author_email, body, status[pending/approved], created_at — honeypot flag not persisted, rejected silently before insert)
- `contact_submissions` (id, name, email, subject, message, created_at)
- Our Story content, split into three focused tables (settled from the "or split" option): `story_stats` (id, label, value, sort_order), `story_finance_entries` (id, year, category, percentage, sort_order), `story_board_letter` (id fixed to 1 via CHECK constraint, author_name, author_title, body, updated_at)

## Forms & spam handling

- **Honeypot pattern** (shared by contact form and blog comment form): a hidden input (visually hidden via CSS, not `type="hidden"`, so real bots that skip CSS still fill it) with a plausible name (e.g. `website`); server rejects silently (fake-success response) if it's non-empty.
- Contact form: server-side validation → persist to `contact_submissions` → send notification email via PHPMailer using the admin-configured SMTP settings.
- Blog comments: server-side validation → persist as `status = pending` → visible on the post only after admin approval in the moderation queue.

## Security notes

- `password_hash()` / `password_verify()` for the single admin account; no plaintext passwords anywhere.
- PDO prepared statements for every query — no string-concatenated SQL.
- CSRF token (session-bound, checked on POST) on every admin form.
- File uploads (team photos, partner logos, gallery images, site logo/favicon/footer logo, blog featured images): validate MIME type and extension against an allowlist (jpg/png/webp/svg-for-logos, ico for favicon), enforce a max size, store outside of directly-executable paths where feasible, rename on save (don't trust the original filename).

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
| 5 | `feature/home-page` | Home controller + view: hero, mission, programs, donate-CTA, team teaser, testimonials, partners teaser | Manual browse of `/` |
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

Branches 1–4 are foundation (not independently useful to an end user); 5–12 build out the full public site; 13–18 build out the back office; 19 closes out with a security/docs pass.
