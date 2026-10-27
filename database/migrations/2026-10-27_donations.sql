-- Stripe donations: schemes + donations (feature/stripe-donations).
-- Apply to databases created from an older schema.sql.

CREATE TABLE IF NOT EXISTS donation_schemes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    summary VARCHAR(255) NULL,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    preset_amounts VARCHAR(255) NOT NULL DEFAULT '',
    allow_custom_amount TINYINT(1) NOT NULL DEFAULT 1,
    goal_cents INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scheme_id INT UNSIGNED NULL,
    amount_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'usd',
    donor_name VARCHAR(150) NULL,
    donor_email VARCHAR(191) NULL,
    message TEXT NULL,
    status ENUM('pending', 'paid', 'expired', 'failed') NOT NULL DEFAULT 'pending',
    stripe_session_id VARCHAR(255) NULL UNIQUE,
    stripe_payment_intent_id VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,
    CONSTRAINT fk_donations_scheme FOREIGN KEY (scheme_id)
        REFERENCES donation_schemes (id) ON DELETE SET NULL,
    KEY idx_donations_status (status),
    KEY idx_donations_scheme_status (scheme_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
    ('stripe_secret_key', ''),
    ('stripe_webhook_secret', '');

INSERT INTO donation_schemes (name, slug, summary, description, preset_amounts, allow_custom_amount, goal_cents, is_active, sort_order) VALUES
    ('School Fees for a Child', 'school-fees-for-a-child',
     'Keep a child in the classroom for a full school year.',
     'Your gift covers tuition, uniforms, and learning materials for a child in Uganda whose family cannot afford them.

Every child we support is followed up through the year so they stay in school.',
     '2500,5000,10000,25000', 1, 1000000, 1, 1),
    ('Early Childhood Centre', 'early-childhood-centre',
     'Give young children a safe, joyful start to learning.',
     'Supports our early childhood education programme: trained caregivers, play-based learning materials, and nutritious meals for children under six.',
     '2000,5000,10000', 1, 500000, 1, 2),
    ('Immigrant Family Support', 'immigrant-family-support',
     'Help African immigrant families settle and thrive in the US.',
     'Funds culturally responsive integration support: navigating schools, healthcare, and community services, and connecting families to local networks.',
     '2500,5000,10000', 1, NULL, 1, 3);
