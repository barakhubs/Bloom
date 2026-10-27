<?php
/**
 * Heart toggle for a product. Signed-out visitors are sent to sign in first.
 *
 * @var array $product
 * @var int[] $wishlistIds
 * @var string $csrfToken
 * @var string $wishlistReturnTo
 * @var string $wishlistClass
 */
$saved = in_array((int) $product['id'], $wishlistIds, true);
?>
<form action="/wishlist/<?= (int) $product['id'] ?>/toggle" method="post" class="<?= htmlspecialchars($wishlistClass) ?>" style="z-index: 2;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="return_to" value="<?= htmlspecialchars($wishlistReturnTo) ?>">
    <button type="submit" class="btn btn-light btn-sm rounded-circle shadow-sm" style="width:40px;height:40px;" aria-pressed="<?= $saved ? 'true' : 'false' ?>" aria-label="<?= $saved ? 'Remove from' : 'Save to' ?> wishlist" title="<?= $saved ? 'Remove from' : 'Save to' ?> wishlist">
        <i class="<?= $saved ? 'fa-solid' : 'fa-regular' ?> fa-heart text-primary"></i>
    </button>
</form>
