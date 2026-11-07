/**
 * public/assets/js/auth.js
 * Toggles a password field between hidden/visible when its eye-icon button
 * is clicked. Works for any input marked up with data-password-toggle
 * pointing at the input's id — supports multiple fields per page.
 */
 
(function () {
  document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
    const targetId = btn.getAttribute('data-password-toggle');
    const input = document.getElementById(targetId);
    if (!input) return;
 
    btn.addEventListener('click', function () {
      const isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
  });
})();