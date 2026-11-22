/**
 * public/assets/js/create-listing.js
 *
 * Locks the "Best Before / Expiry" date to today (the server also enforces
 * this; never trust the browser alone).
 */

(function () {
  const dateInput = document.getElementById('best_before_date');
  if (!dateInput || !dateInput.value) return;

  dateInput.min = dateInput.value;
  dateInput.max = dateInput.value;
})();
