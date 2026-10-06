-- Bloom Beyond Borders database schema
-- MySQL 8+ / InnoDB / utf8mb4
-- Import: mysql -u root -p bloom < database/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------
-- roles / role_permissions — back-office RBAC (feature/admin-users-rbac).
-- A role with is_system = 1 (Super Admin) implicitly has every permission
-- and can't be edited or deleted. Permission slugs are defined in code
-- (App\Core\Permissions::ALL).
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission VARCHAR(50) NOT NULL,
    PRIMARY KEY (role_id, permission),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- admin_users — back-office accounts. Invited users have no password until
-- they follow their invite link (status 'invited' -> 'active').
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL DEFAULT '',
    email VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NULL,
    role_id INT UNSIGNED NOT NULL,
    status ENUM('invited', 'active', 'disabled') NOT NULL DEFAULT 'invited',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_users_email (email),
    CONSTRAINT fk_admin_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- admin_user_tokens — single-use invite / password-reset links. Only the
-- SHA-256 hash of the token is stored; the raw token lives in the emailed URL.
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_user_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    purpose ENUM('invite', 'reset') NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_user_tokens_hash (token_hash),
    CONSTRAINT fk_admin_user_tokens_user FOREIGN KEY (user_id) REFERENCES admin_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- settings — key/value store: social links, contact info, SMTP config,
-- site logo / favicon / footer logo paths (feature/admin-settings)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(100) NOT NULL PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- team_members (feature/admin-team-partners-crud)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    title VARCHAR(150) NOT NULL,
    bio TEXT NULL,
    photo_path VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- partners (feature/admin-team-partners-crud)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS partners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    logo_path VARCHAR(255) NULL,
    tagline VARCHAR(255) NULL,
    description TEXT NULL,
    link_url VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- gallery_albums / gallery_images (feature/gallery-public, feature/admin-gallery-crud)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_albums (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_gallery_albums_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    album_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gallery_images_album FOREIGN KEY (album_id)
        REFERENCES gallery_albums (id) ON DELETE CASCADE,
    KEY idx_gallery_images_album_id (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- blog_posts / blog_comments (feature/blog-public, feature/blog-comments, feature/admin-blog-crud)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    body LONGTEXT NOT NULL,
    featured_image_path VARCHAR(255) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_blog_posts_slug (slug),
    KEY idx_blog_posts_status_published_at (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    author_name VARCHAR(150) NOT NULL,
    author_email VARCHAR(191) NOT NULL,
    website VARCHAR(255) NULL,
    body TEXT NOT NULL,
    status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_comments_post FOREIGN KEY (post_id)
        REFERENCES blog_posts (id) ON DELETE CASCADE,
    KEY idx_blog_comments_post_status (post_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- contact_submissions (feature/contact-form, feature/admin-contact-story-crud)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- Our Story content blocks (feature/our-story-page, feature/admin-contact-story-crud)
-- Split into three focused tables rather than one polymorphic table, per CLAUDE.md.
-- --------------------------------------------------

-- Impact-stats dashboard (e.g. "Dollars Raised" -> "120,000")
CREATE TABLE IF NOT EXISTS story_stats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(100) NOT NULL,
    value VARCHAR(50) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Financial-allocation chart (e.g. 2018 / Campaigns / 25.00%)
CREATE TABLE IF NOT EXISTS story_finance_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year SMALLINT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL,
    percentage DECIMAL(5, 2) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Board letter — single editable row (back office always edits id = 1)
CREATE TABLE IF NOT EXISTS story_board_letter (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
    author_name VARCHAR(150) NOT NULL,
    author_title VARCHAR(150) NULL,
    body TEXT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_story_board_letter_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
