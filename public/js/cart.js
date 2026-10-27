// Cart page: submit the update form as soon as a quantity changes, so the
// "Update Cart" button is only needed when JavaScript is off.
(function () {
    var form = document.getElementById('cart-form');

    if (!form) {
        return;
    }

    form.querySelectorAll('.cart-qty').forEach(function (input) {
        input.addEventListener('change', function () {
            form.submit();
        });
    });

    var button = form.querySelector('.cart-update-btn');
    if (button) {
        button.classList.add('d-none');
    }
})();
