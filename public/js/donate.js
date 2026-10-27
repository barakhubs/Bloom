// Donation amount picker: a preset and a custom amount are mutually exclusive.
// Typing a custom amount clears the preset; picking a preset clears the custom input.
document.querySelectorAll('.donate-amount-form').forEach(function (form) {
    var custom = form.querySelector('input[name="amount"]');
    var presets = form.querySelectorAll('input[name="preset"]');

    if (!custom) {
        return;
    }

    custom.addEventListener('input', function () {
        if (custom.value.trim() !== '') {
            presets.forEach(function (radio) { radio.checked = false; });
        }
    });

    presets.forEach(function (radio) {
        radio.addEventListener('change', function () {
            custom.value = '';
            custom.classList.remove('is-invalid');
        });
    });
});
