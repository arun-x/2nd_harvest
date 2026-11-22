/**
 * public/assets/js/create-listing.js
 *
 * Locks the "Best Before / Expiry" date to today's date. The server also
 * enforces this — never trust the browser alone.
 */

(function () {
  const dateInput = document.getElementById('best_before_date');
  if (!dateInput) return;

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }

  const today = todayStr();
  dateInput.min = today;
  dateInput.max = today;
  dateInput.value = today;
})();
