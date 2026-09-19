<?php
/** @var array $post */
/** @var array $comments */
/** @var array $errors */
/** @var array $old */
/** @var bool $success */
/** @var string $csrfToken */
require dirname(__DIR__) . '/partials/page-header.php';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($post['featured_image_path'])): ?>
                    <img class="img-fluid w-100 mb-4" src="/<?= htmlspecialchars(ltrim($post['featured_image_path'], '/')) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                <?php endif; ?>
                <span class="text-primary small text-uppercase"><?= htmlspecialchars(date('F j, Y', strtotime((string) $post['published_at']))) ?></span>
                <h2 class="display-6 mb-4"><?= htmlspecialchars($post['title']) ?></h2>
                <p class="fs-5"><?= nl2br(htmlspecialchars((string) $post['body'])) ?></p>

                <a href="/blog" class="btn btn-secondary py-2 px-4 mt-4"><i class="fa fa-arrow-left me-2"></i>Back to Blog</a>

                <hr class="my-5">

                <div id="comments">
                    <h4 class="mb-4"><?= count($comments) ?> Comment<?= count($comments) === 1 ? '' : 's' ?></h4>

                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="mb-4 pb-4 border-bottom">
                                <h6 class="mb-1">
                                    <?php if (!empty($comment['website'])): ?>
                                        <a href="<?= htmlspecialchars($comment['website']) ?>" target="_blank" rel="noopener nofollow"><?= htmlspecialchars($comment['author_name']) ?></a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($comment['author_name']) ?>
                                    <?php endif; ?>
                                    <span class="text-muted small fw-normal ms-2"><?= htmlspecialchars(date('F j, Y', strtotime((string) $comment['created_at']))) ?></span>
                                </h6>
                                <p class="mb-0"><?= nl2br(htmlspecialchars((string) $comment['body'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <h4 class="mt-5 mb-4">Submit a Comment</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">Thanks for your comment &mdash; it's awaiting moderation and will appear once approved.</div>
                    <?php endif; ?>
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errors['general']) ?></div>
                    <?php endif; ?>

                    <form action="/blog/<?= htmlspecialchars($post['slug']) ?>/comments" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Honeypot: real users never see or fill this; simple bots that skip CSS often do -->
                        <div class="d-none" aria-hidden="true">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control<?= isset($errors['body']) ? ' is-invalid' : '' ?>" placeholder="Comment" id="body" name="body" style="height: 150px"><?= htmlspecialchars($old['body'] ?? '') ?></textarea>
                                    <label for="body">Comment *</label>
                                    <?php if (isset($errors['body'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['body']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" id="name" name="name" placeholder="Name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                    <label for="name">Name *</label>
                                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                    <label for="email">Email *</label>
                                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="website" name="website" placeholder="Website" value="<?= htmlspecialchars($old['website'] ?? '') ?>">
                                    <label for="website">Website</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary py-3 px-4" type="submit">Post Comment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
