<?php
/** @var array $partners */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container text-center">
        <p class="section-title bg-white text-center text-primary px-3">Our Partners</p>
        <h1 class="display-6 mb-4">Working Together for Lasting Change</h1>
        <p class="fs-5 mb-0 mx-auto" style="max-width: 700px;">Empowering women and children creates a ripple effect &mdash; healthier children, stronger families, more resilient economies, and vibrant communities. Together, we can create a world where every woman and child has the opportunity to thrive.</p>
    </div>
</div>


<?php if (!empty($partners)): ?>
<div class="container-fluid pb-5">
    <div class="container">
        <div class="row g-4 justify-content-center align-items-center">
            <?php foreach ($partners as $partner): ?>
                <div class="col-6 col-md-3 col-lg-2 text-center wow fadeIn" data-wow-delay="0.1s">
                    <?php if (!empty($partner['link_url'])): ?><a href="<?= htmlspecialchars($partner['link_url']) ?>" target="_blank" rel="noopener"><?php endif; ?>
                    <img class="img-fluid" src="/<?= htmlspecialchars(ltrim($partner['logo_path'], '/')) ?>" alt="<?= htmlspecialchars($partner['name']) ?>">
                    <?php if (!empty($partner['link_url'])): ?></a><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
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
                    <h1 class="display-6 mb-4">Become a Sponsor</h1>
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
