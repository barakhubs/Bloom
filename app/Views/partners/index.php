<?php
/** @var array $partners */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container text-center">
        <p class="section-title bg-white text-center text-primary px-3">Our Partners</p>
        <h2 class="display-6 mb-4">Partnerships That Help Communities Bloom</h2>
        <p class="fs-5 mb-0 mx-auto" style="max-width: 700px;">We believe meaningful change begins with partnership: listening, learning, and coming alongside people who know their communities best.</p>
    </div>
</div>


<?php if (!empty($partners)): ?>
    <?php foreach ($partners as $i => $partner): ?>
        <!-- Partner: <?= htmlspecialchars($partner['name']) ?> Start -->
        <div class="container-fluid py-5<?= $i % 2 === 0 ? ' bg-light' : '' ?>">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-4 text-center wow fadeIn<?= $i % 2 === 1 ? ' order-lg-2' : '' ?>" data-wow-delay="0.1s">
                        <?php if (!empty($partner['link_url'])): ?><a href="<?= htmlspecialchars($partner['link_url']) ?>" target="_blank" rel="noopener"><?php endif; ?>
                            <?php if (!empty($partner['logo_path'])): ?>
                                <img class="img-fluid bg-white p-4" style="max-height:260px;" src="/<?= htmlspecialchars(ltrim($partner['logo_path'], '/')) ?>" alt="<?= htmlspecialchars($partner['name']) ?>">
                            <?php else: ?>
                                <div class="bg-white p-5 d-flex align-items-center justify-content-center" style="min-height:200px;">
                                    <span class="h3 text-secondary mb-0"><?= htmlspecialchars($partner['name']) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php if (!empty($partner['link_url'])): ?></a><?php endif; ?>
                    </div>
                    <div class="col-lg-8<?= $i % 2 === 1 ? ' order-lg-1' : '' ?>">
                        <h2 class="display-6 mb-2"><?= htmlspecialchars($partner['name']) ?></h2>
                        <?php if (!empty($partner['tagline'])): ?>
                            <p class="h5 text-primary mb-4"><?= htmlspecialchars($partner['tagline']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($partner['description'])): ?>
                            <?php foreach (preg_split('/\R\s*\R/', trim((string) $partner['description'])) as $block): ?>
                                <?php
                                $lines = preg_split('/\R/', trim($block));
                                $heading = null;
                                if (str_starts_with($lines[0], '## ')) {
                                    $heading = trim(substr(array_shift($lines), 3));
                                }
                                ?>
                                <?php if ($heading !== null): ?>
                                    <h3 class="h5 mt-4 mb-2"><?= htmlspecialchars($heading) ?></h3>
                                <?php endif; ?>
                                <?php if (!empty($lines)): ?>
                                    <p><?= nl2br(htmlspecialchars(implode("
", $lines))) ?></p>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="/contact" class="btn btn-primary py-3 px-4 mt-2">Give Now</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Partner End -->
    <?php endforeach; ?>
<?php else: ?>
<div class="container-fluid pb-5">
    <div class="container text-center">
        <p class="text-muted">Partner organizations will appear here once added via the back office.</p>
    </div>
</div>
<?php endif; ?>


<!-- Become a Sponsor CTA Start -->
<div class="container-fluid donate py-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                    <h2 class="display-6 mb-4">Become a Sponsor</h2>
                    <p class="fs-5 mb-0">Partner with Bloom Beyond Borders to help expand access to education, healthcare, and economic opportunity for the communities we serve.</p>
                </div>
            </div>
            <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                <div class="h-100 p-5 d-flex flex-column justify-content-center">
                    <p class="fs-5 text-dark mb-4">Get in touch to discuss a partnership.</p>
                    <a href="/contact" class="btn btn-secondary py-3 w-100">Become a Sponsor</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Become a Sponsor CTA End -->
