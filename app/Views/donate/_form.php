<?php
/** @var array|null $scheme */
/** @var int[] $presets */
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */
/** @var bool $paymentsEnabled */

use App\Core\Money;

$allowCustom = $scheme === null || (int) $scheme['allow_custom_amount'] === 1;
$oldPreset = (int) ($old['preset'] ?? 0);
$oldAmount = (string) ($old['amount'] ?? '');
// Pre-select the middle preset when nothing was chosen yet.
$checkedPreset = $oldPreset ?: ($oldAmount === '' && !empty($presets) ? $presets[intdiv(count($presets) - 1, 2)] : 0);
?>
<?php if (!$paymentsEnabled): ?>
    <p class="fs-5 text-dark mb-4">Online giving is being set up. In the meantime, please get in touch and we'll help you make a donation.</p>
    <a href="/contact" class="btn btn-secondary py-3 w-100">Contact Us to Donate</a>
<?php else: ?>
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger text-start" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form action="/donate/checkout" method="post" class="donate-amount-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <?php if ($scheme !== null): ?>
            <input type="hidden" name="scheme" value="<?= htmlspecialchars($scheme['slug']) ?>">
        <?php endif; ?>

        <!-- Honeypot: real users never see or fill this; simple bots that skip CSS often do -->
        <div class="d-none" aria-hidden="true">
            <label for="donate-website">Website</label>
            <input type="text" id="donate-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="row g-3">
            <?php if (!empty($presets)): ?>
                <div class="col-12">
                    <div class="btn-group d-flex flex-wrap" role="group" aria-label="Choose an amount">
                        <?php foreach ($presets as $i => $cents): ?>
                            <input type="radio" class="btn-check" name="preset" value="<?= (int) $cents ?>" id="preset-<?= $i ?>" autocomplete="off"<?= $cents === $checkedPreset ? ' checked' : '' ?>>
                            <label class="btn btn-light py-3 flex-fill" for="preset-<?= $i ?>"><?= htmlspecialchars(Money::format($cents)) ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($allowCustom): ?>
                <div class="col-12">
                    <div class="form-floating">
                        <input type="text" inputmode="decimal" class="form-control<?= isset($errors['amount']) ? ' is-invalid' : '' ?>" id="donate-amount" name="amount" placeholder="Other amount" value="<?= htmlspecialchars($oldAmount) ?>">
                        <label for="donate-amount"><?= empty($presets) ? 'Amount (USD)' : 'Or enter any amount (USD)' ?></label>
                        <?php if (isset($errors['amount'])): ?><div class="invalid-feedback text-start"><?= htmlspecialchars($errors['amount']) ?></div><?php endif; ?>
                    </div>
                </div>
            <?php elseif (isset($errors['amount'])): ?>
                <div class="col-12"><div class="text-danger small text-start"><?= htmlspecialchars($errors['amount']) ?></div></div>
            <?php endif; ?>

            <div class="col-12">
                <div class="form-floating">
                    <textarea class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" id="donate-message" name="message" placeholder="Message" style="height: 100px" maxlength="500"><?= htmlspecialchars((string) ($old['message'] ?? '')) ?></textarea>
                    <label for="donate-message">Message (optional)</label>
                    <?php if (isset($errors['message'])): ?><div class="invalid-feedback text-start"><?= htmlspecialchars($errors['message']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="col-12">
                <button class="btn btn-secondary py-3 w-100" type="submit">Donate Now</button>
                <p class="small text-dark mt-3 mb-0"><i class="fa fa-lock me-1"></i>Secure payment by Stripe. You'll receive an email receipt.</p>
            </div>
        </div>
    </form>
<?php endif; ?>
