document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('input[name="ban_selected"]').addEventListener('click', function() {
        this.clicked = true;
    });

    document.querySelector('input[name="kick_selected"]').addEventListener('click', function() {
        this.clicked = true;
    });

    document.querySelector('select[name="ban_reason"]').addEventListener('change', setBanLength);
    document.getElementById('perm').addEventListener('change', handlePermanentCheckbox);

    setBanLength(); // Set initial value on page load

    document.querySelector('input[name="user_reason"]').addEventListener('keyup', function () {
        var reasonInput = this;
        var reasonSelect = document.querySelector('select[name="ban_reason"]');
        reasonSelect.disabled = reasonInput.value.trim() !== '';
    });
});
