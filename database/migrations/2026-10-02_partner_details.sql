-- Partner write-ups on /partners: optional logo, plus tagline + description.
-- Apply to databases created from an older schema.sql.
ALTER TABLE partners
    MODIFY logo_path VARCHAR(255) NULL,
    ADD COLUMN tagline VARCHAR(255) NULL AFTER logo_path,
    ADD COLUMN description TEXT NULL AFTER tagline;
