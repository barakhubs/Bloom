<?php
/** @var array $stats */
/** @var array $financeEntriesByYear */
/** @var array|null $boardLetter */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<!-- Intro Start -->
<div class="container-fluid py-5">
    <div class="container text-center">
        <p class="section-title bg-white text-center text-primary px-3">Our Story</p>
        <h2 class="display-6 mb-4">Making the World a Better Place For All of Us</h2>
        <p class="fs-5 mb-4 mx-auto" style="max-width: 700px;">Bloom Beyond Borders supports vulnerable children and women in Uganda while empowering African immigrant families in the US, breaking cycles of poverty and building resilient communities through comprehensive, data-driven programs.</p>
        <div class="d-flex justify-content-center">
            <a class="btn btn-primary py-3 px-4 me-3" href="/contact">Make a Donation</a>
            <a class="btn btn-secondary py-3 px-4" href="/contact">Join the Movement</a>
        </div>
    </div>
</div>
<!-- Intro End -->


<!-- Founder Start -->
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center">
                <p class="section-title bg-white text-center text-primary px-3">About the Founder</p>
                <img class="rounded-circle mb-4" style="width:160px;height:160px;object-fit:cover;object-position:top center;" src="/img/team-agatha-asiimwe.jpg" alt="Dr. Agatha Asiimwe">
                <h2 class="display-6 mb-4">Dr. Agatha Asiimwe</h2>
                <p class="fs-5">Raised in rural Uganda and shaped by life across Norway, Australia, and the United States, our founder knows both the challenges of limited resources and the struggles immigrants face in new environments.</p>
                <p class="fs-5">Dr. Asiimwe founded Bloom Beyond Borders, a mission-driven organization inspired by a lifetime of cross-cultural experiences and a deep commitment to empowering underserved communities. Born and raised in rural Uganda, she witnessed firsthand the daily challenges faced by children and women striving for opportunity.</p>
                <p class="fs-5">As a mother of three, Dr. Asiimwe learned to advocate within school systems and support her children as they bridged multiple cultures. Her mission is to support children and women in rural Uganda while empowering African immigrant families in the United States.</p>
            </div>
        </div>
    </div>
</div>
<!-- Founder End -->


<?php if (!empty($stats)): ?>
<!-- Impact Stats Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
            <p class="section-title bg-white text-center text-primary px-3">Our Impact</p>
            <h2 class="display-6 mb-4">Impact at a Glance</h2>
        </div>
        <div class="row g-0 text-center rounded overflow-hidden">
            <?php foreach ($stats as $i => $stat): ?>
                <?php $dark = $i % 2 === 0; ?>
                <div class="col-6 col-md-3 <?= $dark ? 'bg-primary' : 'bg-secondary' ?> py-5 px-3 wow fadeIn" data-wow-delay="<?= 0.1 * ($i + 1) ?>s">
                    <div class="display-5 mb-0 <?= $dark ? '' : 'text-white' ?>" data-toggle="counter-up"><?= htmlspecialchars($stat['value']) ?></div>
                    <span class="<?= $dark ? 'text-dark' : 'text-white' ?>"><?= htmlspecialchars($stat['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- Impact Stats End -->
<?php endif; ?>


<?php if (!empty($financeEntriesByYear)): ?>
<!-- Financial Allocation Start -->
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 500px;">
            <p class="section-title bg-white text-center text-primary px-3">Transparency</p>
            <h2 class="display-6 mb-4">How Donations Are Allocated</h2>
        </div>
        <?php foreach ($financeEntriesByYear as $year => $entries): ?>
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <h4 class="mb-4"><?= htmlspecialchars((string) $year) ?></h4>
                    <?php foreach ($entries as $entry): ?>
                        <?php $pct = rtrim(rtrim(number_format((float) $entry['percentage'], 2), '0'), '.'); ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= htmlspecialchars($entry['category']) ?></span>
                                <span><?= htmlspecialchars($pct) ?>%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= htmlspecialchars((string) $entry['percentage']) ?>%" aria-valuenow="<?= htmlspecialchars((string) $entry['percentage']) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Financial Allocation End -->
<?php endif; ?>


<?php if ($boardLetter): ?>
<!-- Board Letter Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center wow fadeIn" data-wow-delay="0.1s">
                <i class="fa fa-quote-left text-primary fa-2x mb-4"></i>
                <p class="fs-5 fst-italic mb-4"><?= nl2br(htmlspecialchars($boardLetter['body'])) ?></p>
                <h5 class="mb-0"><?= htmlspecialchars($boardLetter['author_name']) ?></h5>
                <?php if (!empty($boardLetter['author_title'])): ?>
                    <span class="text-muted"><?= htmlspecialchars($boardLetter['author_title']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Board Letter End -->
<?php endif; ?>
