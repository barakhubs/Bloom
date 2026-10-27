-- Shop: products, orders, order_items (feature/shop-cart-orders).
-- Apply to databases created from an older schema.sql (after 2026-10-27_donations.sql).

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    summary VARCHAR(255) NULL,
    description TEXT NULL,
    price_cents INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NULL,
    customer_email VARCHAR(191) NULL,
    customer_phone VARCHAR(50) NULL,
    ship_name VARCHAR(150) NULL,
    ship_line1 VARCHAR(255) NULL,
    ship_line2 VARCHAR(255) NULL,
    ship_city VARCHAR(120) NULL,
    ship_state VARCHAR(120) NULL,
    ship_postal_code VARCHAR(30) NULL,
    ship_country CHAR(2) NULL,
    subtotal_cents INT UNSIGNED NOT NULL,
    shipping_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'usd',
    status ENUM('pending', 'paid', 'shipped', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    stripe_session_id VARCHAR(255) NULL UNIQUE,
    stripe_payment_intent_id VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,
    shipped_at DATETIME NULL,
    KEY idx_orders_status (status),
    KEY idx_orders_email (customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- product_name / unit_price_cents are snapshots: the order keeps what was
-- actually bought even if the product is later edited or deleted.
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(150) NOT NULL,
    unit_price_cents INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id)
        REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id)
        REFERENCES products (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
    ('shop_enabled', '0'),
    ('shop_shipping_flat_cents', '1000'),
    ('shop_ship_countries', 'US,UG');
