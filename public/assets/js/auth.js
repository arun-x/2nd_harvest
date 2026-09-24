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

/**
 * Client-side check for file inputs marked with data-max-size (bytes):
 * rejects oversized or wrong-type files before the form is submitted.
 * The server re-validates everything, this is just for a faster message.
 */
(function () {
  const ALLOWED = ['application/pdf', 'image/jpeg', 'image/png'];

  document.querySelectorAll('input[type="file"][data-max-size]').forEach(function (input) {
    const maxSize = parseInt(input.getAttribute('data-max-size'), 10);
    const field = input.closest('.field');
    let warning = field ? field.querySelector('.field-warning') : null;

    input.addEventListener('change', function () {
      const file = input.files[0];
      let message = '';

      if (file) {
        if (ALLOWED.indexOf(file.type) === -1) {
          message = 'Only PDF, JPG or PNG files are allowed.';
        } else if (file.size > maxSize) {
          message = 'That file is too large. The maximum size is ' + Math.round(maxSize / 1048576) + ' MB.';
        }
      }

      if (message) {
        input.value = '';
        if (!warning && field) {
          warning = document.createElement('div');
          warning.className = 'field-warning';
          field.appendChild(warning);
        }
        if (warning) { warning.textContent = message; warning.style.display = ''; }
      } else if (warning) {
        warning.textContent = '';
        warning.style.display = 'none';
      }
    });
  });
})();
