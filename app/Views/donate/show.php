<?php
/** @var array $scheme */
/** @var int[] $presets */
/** @var array $errors */
/** @var array $old */
/** @var string $csrfToken */
/** @var bool $paymentsEnabled */

use App\Core\Money;

require dirname(__DIR__) . '/partials/page-header.php';

$goal = (int) ($scheme['goal_cents'] ?? 0);
$raised = (int) $scheme['raised_cents'];
$percent = $goal > 0 ? min(100, (int) floor($raised * 100 / $goal)) : null;
?>

<div class="container-fluid donate py-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center h-100 p-5">
                    <p class="section-title bg-white text-start text-primary pe-3">Campaign</p>
                    <h2 class="display-6 mb-3"><?= htmlspecialchars($scheme['name']) ?></h2>
                    <?php if (!empty($scheme['summary'])): ?>
                        <p class="h5 text-primary mb-4"><?= htmlspecialchars($scheme['summary']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($scheme['image_path'])): ?>
                        <img class="img-fluid w-100 mb-4" style="max-height:320px;object-fit:cover;" src="/<?= htmlspecialchars(ltrim($scheme['image_path'], '/')) ?>" alt="<?= htmlspecialchars($scheme['name']) ?>">
                    <?php endif; ?>
                    <?php if (!empty($scheme['description'])): ?>
                        <?php foreach (preg_split('/\R\s*\R/', trim((string) $scheme['description'])) as $block): ?>
                            <?php
                            $lines = preg_split('/\R/', trim($block));
                            $heading = null;
                            if (str_starts_with($lines[0], '## ')) {
                                $heading = trim(substr(array_shift($lines), 3));
                            }
                            ?>
                            <?php if ($heading !== null): ?>
                                <h3 class="h5 mt-3 mb-2"><?= htmlspecialchars($heading) ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($lines)): ?>
                                <p><?= nl2br(htmlspecialchars(implode("\n", $lines))) ?></p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($percent !== null): ?>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span><strong><?= htmlspecialchars(Money::format($raised)) ?></strong> raised</span>
                                <span>Goal <?= htmlspecialchars(Money::format($goal)) ?></span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Raised so far" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100" style="height: 12px;">
                                <div class="progress-bar bg-primary" style="width: <?= $percent ?>%"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                <div class="h-100 p-5 d-flex flex-column justify-content-center">
                    <?php require __DIR__ . '/_form.php'; ?>
                    <a href="/donate" class="d-inline-block text-dark mt-4 small">&larr; Other ways to give</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/donate.js" defer></script>
