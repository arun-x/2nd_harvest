/**
 * public/assets/js/create-listing.js
 *
 * Locks the "Best Before / Expiry" date to the day the server chose (today,
 * or tomorrow for listings posted after 7 PM). The server also enforces
 * this; never trust the browser alone.
 */

(function () {
  const dateInput = document.getElementById('best_before_date');
  if (!dateInput || !dateInput.value) return;

  dateInput.min = dateInput.value;
  dateInput.max = dateInput.value;
})();
