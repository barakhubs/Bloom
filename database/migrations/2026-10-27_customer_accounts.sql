-- Optional customer accounts (feature/customer-accounts).
-- Apply to databases created from an older schema.sql (after 2026-10-27_shop.sql).

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email_verified_at DATETIME NULL,
    stripe_customer_id VARCHAR(255) NULL,
    ship_line1 VARCHAR(255) NULL,
    ship_line2 VARCHAR(255) NULL,
    ship_city VARCHAR(120) NULL,
    ship_state VARCHAR(120) NULL,
    ship_postal_code VARCHAR(30) NULL,
    ship_country CHAR(2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Same rules as admin_user_tokens: SHA-256 hash only, single-use, expiring.
CREATE TABLE IF NOT EXISTS customer_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    purpose ENUM('verify', 'reset') NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customer_tokens_hash (token_hash),
    CONSTRAINT fk_customer_tokens_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wishlist_items (
    customer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, product_id),
    CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE donations
    ADD COLUMN customer_id INT UNSIGNED NULL AFTER stripe_payment_intent_id,
    ADD CONSTRAINT fk_donations_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL;

ALTER TABLE orders
    ADD COLUMN customer_id INT UNSIGNED NULL AFTER stripe_payment_intent_id,
    ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL;
