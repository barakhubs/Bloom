<?php
/** @var array $errors */
/** @var array $old */
/** @var bool $success */
/** @var string $csrfToken */
/** @var array $settings */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5 wow fadeIn" data-wow-delay="0.1s">
                <p class="section-title bg-white text-start text-primary pe-3">Contact</p>
                <h1 class="display-6 mb-4">If You Have Any Query, Please Contact Us</h1>
                <p class="mb-4"><i class="fa fa-phone-alt text-primary me-2"></i><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></p>
                <p class="mb-4"><i class="fa fa-envelope-open text-primary me-2"></i><?= htmlspecialchars($settings['contact_email'] ?? '') ?></p>
                <p class="mb-4"><i class="fa fa-map-marker-alt text-primary me-2"></i><?= htmlspecialchars($settings['contact_address'] ?? '') ?></p>
                <?php if (!empty($settings['social_linkedin']) || !empty($settings['social_instagram'])): ?>
                    <div class="d-flex mt-4">
                        <?php if (!empty($settings['social_linkedin'])): ?>
                            <a class="btn btn-square btn-primary me-2" href="<?= htmlspecialchars($settings['social_linkedin']) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_instagram'])): ?>
                            <a class="btn btn-square btn-primary me-2" href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-7 wow fadeIn" data-wow-delay="0.3s">
                <h4 class="lh-base mb-4">Get In Touch</h4>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">Thank you &mdash; your message has been sent. We'll be in touch soon.</div>
                <?php endif; ?>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form action="/contact" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <!-- Honeypot: real users never see or fill this; simple bots that skip CSS often do -->
                    <div class="d-none" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" placeholder="Your Name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                <label for="name">Your Name</label>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                <label for="email">Your Email</label>
                                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" placeholder="Leave a message here" id="message" name="message" style="height: 250px"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                                <label for="message">Message</label>
                                <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['message']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary py-3 px-4" type="submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Donate CTA Start -->
<div class="container-fluid donate py-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-7 donate-text bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
                <div class="d-flex flex-column justify-content-center h-100 p-5 wow fadeIn" data-wow-delay="0.3s">
                    <h1 class="display-6 mb-4">Give Today</h1>
                    <p class="fs-5 mb-0">Your support funds scholarships, healthcare, and skills training for children and women in Uganda, and integration support for African immigrant families in the US.</p>
                </div>
            </div>
            <div class="col-lg-5 donate-form bg-primary py-5 text-center wow fadeIn" data-wow-delay="0.5s">
                <div class="h-100 p-5 d-flex flex-column justify-content-center">
                    <p class="fs-5 text-dark mb-4">Use the form above to let us know how you'd like to help.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Donate CTA End -->
