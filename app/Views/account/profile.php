<?php
/** @var array $customer */
/** @var array $shipCountries */
/** @var array $errors */
/** @var string $csrfToken */

require __DIR__ . '/_start.php';

$countryNames = ['US' => 'United States', 'UG' => 'Uganda', 'KE' => 'Kenya', 'RW' => 'Rwanda', 'TZ' => 'Tanzania', 'CA' => 'Canada', 'GB' => 'United Kingdom'];
$countryOptions = $shipCountries;
if (!empty($customer['ship_country']) && !in_array($customer['ship_country'], $countryOptions, true)) {
    $countryOptions[] = $customer['ship_country'];
}
$v = fn (string $key) => htmlspecialchars((string) ($customer[$key] ?? ''));
?>
<h2 class="h3 mb-4">My Profile</h2>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
<?php endif; ?>

<form action="/account/profile" method="post" class="bg-light p-4 mb-4" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <h3 class="h5 mb-3">Details &amp; Shipping Address</h3>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="name">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" autocomplete="name" value="<?= $v('name') ?>">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="phone">Phone (optional)</label>
            <input type="tel" class="form-control" id="phone" name="phone" autocomplete="tel" value="<?= $v('phone') ?>">
        </div>
        <div class="col-12">
            <label class="form-label" for="ship_line1">Street Address</label>
            <input type="text" class="form-control<?= isset($errors['ship_line1']) ? ' is-invalid' : '' ?>" id="ship_line1" name="ship_line1" autocomplete="address-line1" value="<?= $v('ship_line1') ?>">
            <?php if (isset($errors['ship_line1'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['ship_line1']) ?></div><?php endif; ?>
        </div>
        <div class="col-12">
            <label class="form-label" for="ship_line2">Apartment, suite, etc. (optional)</label>
            <input type="text" class="form-control" id="ship_line2" name="ship_line2" autocomplete="address-line2" value="<?= $v('ship_line2') ?>">
        </div>
        <div class="col-md-5">
            <label class="form-label" for="ship_city">City</label>
            <input type="text" class="form-control" id="ship_city" name="ship_city" autocomplete="address-level2" value="<?= $v('ship_city') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="ship_state">State / Region</label>
            <input type="text" class="form-control" id="ship_state" name="ship_state" autocomplete="address-level1" value="<?= $v('ship_state') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="ship_postal_code">Postal Code</label>
            <input type="text" class="form-control" id="ship_postal_code" name="ship_postal_code" autocomplete="postal-code" value="<?= $v('ship_postal_code') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="ship_country">Country</label>
            <select class="form-select<?= isset($errors['ship_country']) ? ' is-invalid' : '' ?>" id="ship_country" name="ship_country" autocomplete="country">
                <option value="">&mdash;</option>
                <?php foreach ($countryOptions as $code): ?>
                    <option value="<?= htmlspecialchars($code) ?>"<?= ($customer['ship_country'] ?? '') === $code ? ' selected' : '' ?>><?= htmlspecialchars($countryNames[$code] ?? $code) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['ship_country'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['ship_country']) ?></div><?php endif; ?>
            <div class="form-text">Countries we currently ship to.</div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-4 px-4">Save Profile</button>
</form>

<div class="row g-4">
    <div class="col-md-6">
        <form action="/account/email" method="post" class="bg-light p-4 h-100" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <h3 class="h5 mb-3">Change Email</h3>
            <div class="mb-3">
                <label class="form-label" for="new_email">New Email</label>
                <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="new_email" name="email" autocomplete="email">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email_current_password">Current Password</label>
                <input type="password" class="form-control" id="email_current_password" name="current_password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-secondary">Change Email</button>
        </form>
    </div>
    <div class="col-md-6">
        <form action="/account/password" method="post" class="bg-light p-4 h-100" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <h3 class="h5 mb-3">Change Password</h3>
            <div class="mb-3">
                <label class="form-label" for="current_password">Current Password</label>
                <input type="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>" id="current_password" name="current_password" autocomplete="current-password">
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="password" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-secondary">Change Password</button>
        </form>
    </div>
</div>

<form action="/account/delete" method="post" class="border border-danger p-4 mt-4" onsubmit="return confirm('Permanently delete your account? This cannot be undone.');" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <h3 class="h5 text-danger mb-2">Delete Account</h3>
    <p class="small mb-3">Deletes your login, saved address and wishlist. Records of past orders and donations are kept by Bloom Beyond Borders for accounting, but are no longer linked to an account.</p>
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label" for="delete_password">Password</label>
            <input type="password" class="form-control<?= isset($errors['delete']) ? ' is-invalid' : '' ?>" id="delete_password" name="current_password" autocomplete="current-password">
            <?php if (isset($errors['delete'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['delete']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-outline-danger w-100">Delete My Account</button>
        </div>
    </div>
</form>
<?php require __DIR__ . '/_end.php'; ?>
