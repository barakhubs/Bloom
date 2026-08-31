<?php

// Live SMTP credentials are stored in the `settings` DB table (feature/database-schema)
// and managed from the admin back office (feature/admin-settings). These are just the
// dev-time fallback sender identity used before that table is seeded/configured.
return [
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'no-reply@bloombeyondborders.org',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'Bloom Beyond Borders',
];
