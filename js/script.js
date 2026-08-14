// Confirm destructive actions
document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (el && !confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
    }
});

// Live preview for file inputs that have a matching [data-preview="#id"]
document.addEventListener('change', function (e) {
    if (e.target.matches('input[type="file"][data-preview]')) {
        const target = document.querySelector(e.target.getAttribute('data-preview'));
        const file = e.target.files[0];
        if (target && file) {
            const reader = new FileReader();
            reader.onload = ev => { target.src = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }
});

// Auto-hide flash messages after a few seconds
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.flash-msg').forEach(function (msg) {
        setTimeout(() => { msg.style.transition = 'opacity .4s'; msg.style.opacity = '0'; }, 4000);
    });
});
