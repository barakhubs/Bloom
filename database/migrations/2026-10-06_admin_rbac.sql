-- Back-office users + role-based permissions (feature/admin-users-rbac).
-- Apply to databases created from an older schema.sql. Existing admin
-- accounts become active Super Admins, so current logins keep working.

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

INSERT INTO roles (id, name, is_system) VALUES (1, 'Super Admin', 1);

ALTER TABLE admin_users
    ADD COLUMN name VARCHAR(150) NOT NULL DEFAULT '' AFTER id,
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN role_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER password_hash,
    ADD COLUMN status ENUM('invited', 'active', 'disabled') NOT NULL DEFAULT 'active' AFTER role_id,
    ADD COLUMN last_login_at DATETIME NULL AFTER status;

-- Defaults above only exist to backfill existing rows; new rows set both explicitly.
ALTER TABLE admin_users
    ALTER COLUMN role_id DROP DEFAULT,
    ALTER COLUMN status SET DEFAULT 'invited',
    ADD CONSTRAINT fk_admin_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT;

UPDATE admin_users SET name = 'Administrator' WHERE name = '';

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
