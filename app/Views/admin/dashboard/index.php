<?php
/** @var array|null $currentAdmin */
?>
<div class="row">
    <div class="col-12">
        <h2 class="mb-3">Dashboard</h2>
        <p class="text-muted">Logged in as <?= htmlspecialchars($currentAdmin['email'] ?? '') ?>.</p>
        <p class="text-muted">Site settings, content management (Team, Partners, Gallery, Blog, Our Story), and the contact-submissions inbox land here across the remaining <code>feature/admin-*</code> branches.</p>
    </div>
</div>
