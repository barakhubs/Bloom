<?php
/** @var array<string,string> $settings set by partials/header.php */
/** @var array<string,string> $navLinks set by partials/header.php */
?>
    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <?php if (!empty($settings['footer_logo_path'])): ?>
                        <img src="/<?= htmlspecialchars(ltrim($settings['footer_logo_path'], '/')) ?>" alt="Bloom Beyond Borders" class="footer-logo mb-4">
                    <?php else: ?>
                        <h4 class="text-white mb-4">Bloom Beyond Borders</h4>
                    <?php endif; ?>
                    <p>Transforming lives with knowledge, care, and courage &mdash; empowering vulnerable children and women in Uganda, and supporting African immigrant families in the USA.</p>
                    <div class="d-flex mt-3">
                        <?php if (!empty($settings['social_linkedin'])): ?>
                            <a class="btn btn-square btn-outline-light rounded-circle me-2" href="<?= htmlspecialchars($settings['social_linkedin']) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_instagram'])): ?>
                            <a class="btn btn-square btn-outline-light rounded-circle me-2" href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_facebook'])): ?>
                            <a class="btn btn-square btn-outline-light rounded-circle me-2" href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_x'])): ?>
                            <a class="btn btn-square btn-outline-light rounded-circle me-2" href="<?= htmlspecialchars($settings['social_x']) ?>" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-white mb-4">Quick Links</h4>
                    <?php foreach ($navLinks as $href => $label): ?>
                        <a class="btn btn-link" href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($label) ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4 class="text-white mb-4">Contact</h4>
                    <p class="mb-2"><i class="fa fa-phone-alt me-2"></i><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></p>
                    <p class="mb-2"><i class="fa fa-envelope-open me-2"></i><?= htmlspecialchars($settings['contact_email'] ?? '') ?></p>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-2"></i><?= htmlspecialchars($settings['contact_address'] ?? '') ?></p>
                    <?php if (!empty($settings['whatsapp_us'])): ?>
                        <p class="mb-2"><i class="fab fa-whatsapp me-2"></i><a class="text-light" href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $settings['whatsapp_us'])) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($settings['whatsapp_us']) ?> (US)</a></p>
                    <?php endif; ?>
                    <?php if (!empty($settings['whatsapp_ug'])): ?>
                        <p class="mb-0"><i class="fab fa-whatsapp me-2"></i><a class="text-light" href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9]/', '', $settings['whatsapp_ug'])) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($settings['whatsapp_ug']) ?> (Uganda)</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <?= date('Y') ?> <a class="border-bottom" href="/">Bloom Beyond Borders</a>. All Rights Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        Created by <a class="border-bottom" href="https://kimdigitary.com" target="_blank" rel="noopener">Kim Digitary</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/lib/wow/wow.min.js"></script>
    <script src="/lib/easing/easing.min.js"></script>
    <script src="/lib/waypoints/waypoints.min.js"></script>
    <script src="/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/lib/counterup/counterup.min.js"></script>

    <!-- Template Javascript -->
    <script src="/js/main.js"></script>
</body>

</html>
