<?php
/** @var array $schemes */
/** @var int[] $presets */
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */
/** @var bool $paymentsEnabled */

use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';
$scheme = null;
?>

<!-- General Donation Start -->
<div class="container-fluid donate py-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                    <p class="section-title bg-white text-start text-primary pe-3">Give Any Amount</p>
                    <h2 class="display-6 mb-4">Help Us Break the Cycle of Poverty</h2>
                    <p class="fs-5 mb-0">A general gift goes where it's needed most: scholarships, healthcare, and skills training for children and women in Uganda, and integration support for African immigrant families in the United States.</p>
                </div>
            </div>
            <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                <div class="h-100 p-5 d-flex flex-column justify-content-center">
                    <?php require __DIR__ . '/_form.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- General Donation End -->


<?php if (!empty($schemes)): ?>
<!-- Schemes Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="section-title bg-white text-center text-primary px-3">Campaigns</p>
            <h2 class="display-6 mb-4">Support a Specific Cause</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($schemes as $i => $item): ?>
                <?php
                $goal = (int) ($item['goal_cents'] ?? 0);
                $raised = (int) $item['raised_cents'];
                $percent = $goal > 0 ? min(100, (int) floor($raised * 100 / $goal)) : null;
                ?>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="<?= number_format(0.1 + 0.2 * ($i % 3), 1) ?>s">
                    <div class="donation-item h-100 p-4">
                        <?php if (!empty($item['image_path'])): ?>
                            <div class="position-relative mb-4">
                                <img class="img-fluid w-100" style="height:240px;object-fit:cover;" src="/<?= htmlspecialchars(ltrim($item['image_path'], '/')) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                        <?php endif; ?>
                        <a href="/donate/<?= htmlspecialchars($item['slug']) ?>" class="h3 d-inline-block"><?= htmlspecialchars($item['name']) ?></a>
                        <?php if (!empty($item['summary'])): ?>
                            <p><?= htmlspecialchars($item['summary']) ?></p>
                        <?php endif; ?>
                        <?php if ($percent !== null): ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><strong><?= htmlspecialchars(Money::format($raised)) ?></strong> raised</span>
                                    <span>Goal <?= htmlspecialchars(Money::format($goal)) ?></span>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Raised so far" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="/donate/<?= htmlspecialchars($item['slug']) ?>" class="btn btn-primary w-100 py-3">Give to This Cause</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Schemes End -->
<?php endif; ?>

<script src="/js/donate.js" defer></script>
