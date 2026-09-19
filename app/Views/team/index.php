<?php
/** @var array $teamMembers */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mx-auto wow fadeIn" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="section-title bg-white text-center text-primary px-3">Board of Directors</p>
            <h1 class="display-6 mb-4">Meet the People Behind Bloom Beyond Borders</h1>
        </div>

        <?php if (!empty($teamMembers)): ?>
            <div class="row g-4 team-grid">
                <?php foreach ($teamMembers as $member): ?>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                        <div class="donation-item h-100 p-4">
                            <div class="d-flex align-items-center mb-3">
                                <?php if (!empty($member['photo_path'])): ?>
                                    <img class="rounded-circle flex-shrink-0 me-3" style="width:80px;height:80px;object-fit:cover;object-position:top center;" src="/<?= htmlspecialchars(ltrim($member['photo_path'], '/')) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle flex-shrink-0 me-3 d-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                                        <i class="fa fa-user fa-2x text-primary"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="mb-0"><?= htmlspecialchars($member['name']) ?></h3>
                                    <span><?= htmlspecialchars($member['title']) ?></span>
                                </div>
                            </div>
                            <?php if (!empty($member['bio'])): ?>
                                <p class="mb-0"><?= htmlspecialchars($member['bio']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Team member profiles will appear here once added via the back office.</p>
        <?php endif; ?>
    </div>
</div>


<!-- CTA Start -->
<div class="container-fluid donate py-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                    <h1 class="display-6 mb-4">Join Our Team</h1>
                    <p class="fs-5 mb-0">Interested in volunteering or joining the board? We'd love to hear from you.</p>
                </div>
            </div>
            <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                <div class="h-100 p-5 d-flex flex-column justify-content-center">
                    <p class="fs-5 text-dark mb-4">Reach out to learn more about getting involved.</p>
                    <a href="/contact" class="btn btn-secondary py-3 w-100">Join Today</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- CTA End -->
