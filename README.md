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

3. **Configure the database connection**, if your setup differs from the
   defaults (host `127.0.0.1`, database `bloom`, user `root`, empty
   password). Either edit `app/Config/db.php` directly, or set environment
   variables before starting PHP: `DB_HOST`, `DB_PORT`, `DB_DATABASE`,
   `DB_USERNAME`, `DB_PASSWORD`.

4. **Run the dev server**

   ```
   php -S localhost:8000 -t public
   ```

   Visit `http://localhost:8000`.

## Default admin login

The seed data creates one admin account:

- **Email:** `admin@bloombeyondborders.org`
- **Password:** `ChangeMe123!`

Sign in at `/admin/login` and **change this password immediately** — there is
currently no in-app "change password" screen, so update it directly in the
database with a fresh bcrypt hash:

```
php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
```

```sql
UPDATE admin_users SET password_hash = '<hash from above>' WHERE email = 'admin@bloombeyondborders.org';
```

## Outgoing email (SMTP)

The contact form and comment notifications send through PHPMailer, configured
entirely from the back office at `/admin/settings` (SMTP host/port/username/
password/encryption/from address). Until SMTP is configured there, the app
still works normally — form submissions save to the database, but no
notification email is sent (this is logged, not a failure).

## Environment variables

All optional; sensible local defaults apply if unset.

| Variable | Purpose | Default |
|---|---|---|
| `APP_ENV` | `local` or `production` | `local` |
| `APP_DEBUG` | Force error display on/off, overriding the `APP_ENV`-based default | debug on unless `APP_ENV=production` |
| `APP_URL` | Base URL, currently informational only | `http://localhost:8000` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database connection | `127.0.0.1`, `3306`, `bloom`, `root`, *(empty)* |

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
  CSRF token, checked on every POST.
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
