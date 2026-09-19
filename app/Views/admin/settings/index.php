<?php
/** @var array $settings */
/** @var array $errors */
/** @var bool $success */
/** @var string $csrfToken */
$val = fn (string $key) => htmlspecialchars($settings[$key] ?? '');
?>
<h2 class="mb-4">Site Settings</h2>

<?php if ($success): ?>
    <div class="alert alert-success" role="alert">Settings saved.</div>
<?php endif; ?>
<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>

<form action="/admin/settings" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="bg-white p-4 mb-4 shadow-sm">
        <h5 class="mb-3">Contact Info</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="contact_phone" value="<?= $val('contact_phone') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="contact_email" value="<?= $val('contact_email') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="contact_address" value="<?= $val('contact_address') ?>">
            </div>
        </div>
    </div>

    <div class="bg-white p-4 mb-4 shadow-sm">
        <h5 class="mb-3">Social Links</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">LinkedIn URL</label>
                <input type="url" class="form-control" name="social_linkedin" value="<?= $val('social_linkedin') ?>" placeholder="https://linkedin.com/company/...">
            </div>
            <div class="col-md-6">
                <label class="form-label">Instagram URL</label>
                <input type="url" class="form-control" name="social_instagram" value="<?= $val('social_instagram') ?>" placeholder="https://instagram.com/...">
            </div>
            <div class="col-md-6">
                <label class="form-label">Facebook URL</label>
                <input type="url" class="form-control" name="social_facebook" value="<?= $val('social_facebook') ?>" placeholder="https://facebook.com/...">
            </div>
            <div class="col-md-6">
                <label class="form-label">X (Twitter) URL</label>
                <input type="url" class="form-control" name="social_x" value="<?= $val('social_x') ?>" placeholder="https://x.com/...">
            </div>
            <div class="col-md-6">
                <label class="form-label">WhatsApp Number (US)</label>
                <input type="text" class="form-control" name="whatsapp_us" value="<?= $val('whatsapp_us') ?>" placeholder="+1 360-803-5283">
            </div>
            <div class="col-md-6">
                <label class="form-label">WhatsApp Number (Uganda)</label>
                <input type="text" class="form-control" name="whatsapp_ug" value="<?= $val('whatsapp_ug') ?>" placeholder="+256 749 257996">
            </div>
        </div>
    </div>

    <div class="bg-white p-4 mb-4 shadow-sm">
        <h5 class="mb-3">SMTP (Outgoing Email)</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">SMTP Host</label>
                <input type="text" class="form-control" name="smtp_host" value="<?= $val('smtp_host') ?>" placeholder="smtp.example.com">
            </div>
            <div class="col-md-2">
                <label class="form-label">Port</label>
                <input type="text" class="form-control" name="smtp_port" value="<?= $val('smtp_port') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Encryption</label>
                <select class="form-select" name="smtp_encryption">
                    <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'] as $optValue => $optLabel): ?>
                        <option value="<?= $optValue ?>" <?= ($settings['smtp_encryption'] ?? 'tls') === $optValue ? 'selected' : '' ?>><?= $optLabel ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">SMTP Username</label>
                <input type="text" class="form-control" name="smtp_username" value="<?= $val('smtp_username') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">SMTP Password</label>
                <input type="password" class="form-control" name="smtp_password" placeholder="<?= !empty($settings['smtp_password']) ? 'Leave blank to keep the current password' : '' ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">From Email</label>
                <input type="email" class="form-control" name="smtp_from_email" value="<?= $val('smtp_from_email') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">From Name</label>
                <input type="text" class="form-control" name="smtp_from_name" value="<?= $val('smtp_from_name') ?>">
            </div>
        </div>
    </div>

    <div class="bg-white p-4 mb-4 shadow-sm">
        <h5 class="mb-3">Logos</h5>
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label d-block">Site Logo</label>
                <?php if (!empty($settings['site_logo_path'])): ?>
                    <img src="/<?= htmlspecialchars(ltrim($settings['site_logo_path'], '/')) ?>" alt="Site logo" class="img-fluid mb-2" style="max-height: 60px;">
                <?php else: ?>
                    <p class="text-muted small">No logo uploaded &mdash; site name text is shown instead.</p>
                <?php endif; ?>
                <input type="file" class="form-control<?= isset($errors['site_logo']) ? ' is-invalid' : '' ?>" name="site_logo" accept=".jpg,.jpeg,.png,.webp,.svg">
                <?php if (isset($errors['site_logo'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['site_logo']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">Favicon</label>
                <?php if (!empty($settings['favicon_path'])): ?>
                    <img src="/<?= htmlspecialchars(ltrim($settings['favicon_path'], '/')) ?>" alt="Favicon" class="img-fluid mb-2" style="max-height: 40px;">
                <?php else: ?>
                    <p class="text-muted small">Using the default placeholder favicon.</p>
                <?php endif; ?>
                <input type="file" class="form-control<?= isset($errors['favicon']) ? ' is-invalid' : '' ?>" name="favicon" accept=".ico,.png">
                <?php if (isset($errors['favicon'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['favicon']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">Footer Logo</label>
                <?php if (!empty($settings['footer_logo_path'])): ?>
                    <img src="/<?= htmlspecialchars(ltrim($settings['footer_logo_path'], '/')) ?>" alt="Footer logo" class="img-fluid mb-2" style="max-height: 60px;">
                <?php else: ?>
                    <p class="text-muted small">No footer logo uploaded &mdash; site name text is shown instead.</p>
                <?php endif; ?>
                <input type="file" class="form-control<?= isset($errors['footer_logo']) ? ' is-invalid' : '' ?>" name="footer_logo" accept=".jpg,.jpeg,.png,.webp,.svg">
                <?php if (isset($errors['footer_logo'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['footer_logo']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary py-2 px-4">Save Settings</button>
</form>
