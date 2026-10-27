<?php
/** @var array|null $scheme */
/** @var bool $isEdit */
/** @var array $errors */
/** @var string $csrfToken */

use App\Core\Money;
use App\Models\DonationScheme;

$actionUrl = $isEdit ? '/admin/donation-schemes/' . (int) $scheme['id'] : '/admin/donation-schemes';
$presetsValue = $scheme['preset_amounts_input']
    ?? implode(', ', array_map([Money::class, 'toInput'], $scheme !== null ? DonationScheme::presets($scheme) : [2500, 5000, 10000]));
$goalValue = $scheme['goal_input'] ?? (!empty($scheme['goal_cents']) ? Money::toInput((int) $scheme['goal_cents']) : '');
$allowCustom = $scheme === null || (int) ($scheme['allow_custom_amount'] ?? 1) === 1;
$isActive = $scheme === null || (int) ($scheme['is_active'] ?? 1) === 1;
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit Donation Scheme' : 'Add Donation Scheme' ?></h2>

<form action="<?= $actionUrl ?>" method="post" enctype="multipart/form-data" class="bg-white p-4 shadow-sm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($scheme['name'] ?? '') ?>" placeholder="e.g. School Fees for a Child">
            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">URL Slug (optional)</label>
            <div class="input-group has-validation">
                <span class="input-group-text">/donate/</span>
                <input type="text" class="form-control<?= isset($errors['slug']) ? ' is-invalid' : '' ?>" name="slug" value="<?= htmlspecialchars($scheme['slug'] ?? '') ?>" placeholder="generated from name">
                <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['slug']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Summary (optional)</label>
            <input type="text" class="form-control" name="summary" maxlength="255" value="<?= htmlspecialchars($scheme['summary'] ?? '') ?>" placeholder="One line shown on the scheme card">
        </div>
        <div class="col-12">
            <label class="form-label">Description (optional)</label>
            <textarea class="form-control" name="description" rows="8"><?= htmlspecialchars($scheme['description'] ?? '') ?></textarea>
            <div class="form-text">Separate paragraphs with a blank line. Start a line with <code>## </code> to make it a subheading.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Suggested Amounts (USD)</label>
            <input type="text" class="form-control<?= isset($errors['preset_amounts']) ? ' is-invalid' : '' ?>" name="preset_amounts" value="<?= htmlspecialchars($presetsValue) ?>" placeholder="25, 50, 100">
            <?php if (isset($errors['preset_amounts'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['preset_amounts']) ?></div><?php endif; ?>
            <div class="form-text">Dollar amounts separated by commas, shown as quick-pick buttons.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Fundraising Goal (USD, optional)</label>
            <input type="text" inputmode="decimal" class="form-control<?= isset($errors['goal']) ? ' is-invalid' : '' ?>" name="goal" value="<?= htmlspecialchars($goalValue) ?>" placeholder="e.g. 10000">
            <?php if (isset($errors['goal'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['goal']) ?></div><?php endif; ?>
            <div class="form-text">When set, a progress bar shows how much has been raised.</div>
        </div>
        <div class="col-md-8">
            <label class="form-label d-block">Image (optional)</label>
            <?php if (!empty($scheme['image_path'])): ?>
                <img src="/<?= htmlspecialchars(ltrim($scheme['image_path'], '/')) ?>" alt="" style="max-height:80px;" class="mb-2 d-block">
            <?php endif; ?>
            <input type="file" class="form-control<?= isset($errors['image']) ? ' is-invalid' : '' ?>" name="image" accept=".jpg,.jpeg,.png,.webp">
            <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= (int) ($scheme['sort_order'] ?? 0) ?>">
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="allow_custom_amount" id="allow_custom_amount" value="1"<?= $allowCustom ? ' checked' : '' ?>>
                <label class="form-check-label" for="allow_custom_amount">Let donors enter their own amount</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"<?= $isActive ? ' checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active (shown on the Donate page)</label>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary px-4"><?= $isEdit ? 'Save Changes' : 'Add Scheme' ?></button>
        <a href="/admin/donation-schemes" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
