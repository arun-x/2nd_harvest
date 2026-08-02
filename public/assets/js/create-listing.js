/**
 * public/assets/js/create-listing.js
 *
 * Enforces, for the "Best Before / Expiry" fields on the Create Listing form:
 *   - Date: only today, tomorrow, or the day after (no past dates, nothing further out)
 *   - Time: only between 9:00 AM and 10:00 PM
 *   - If today is selected, the time field can't go earlier than the current
 *     time (rounded up to the next 15 minutes)
 *
 * Native <input type="date"/time"> min/max handles most of this in-browser,
 * but the "no past times, only when today is picked" part needs JS since
 * that constraint depends on which date is currently selected.
 *
 * NOTE: this is client-side convenience only. The server must re-validate
 * these same rules when the form is submitted — never trust the browser.
 */

(function () {
  const dateInput = document.getElementById('best_before_date');
  const timeInput = document.getElementById('best_before_time');

  if (!dateInput || !timeInput) return;

  const DAY_MIN_TIME = '09:00';
  const DAY_MAX_TIME = '22:00';

  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function toDateStr(d) {
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }

  function toTimeStr(d) {
    return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  // Round a Date up to the next 15-minute mark (matches the time input's step="900").
  function roundUpToNextQuarterHour(d) {
    const rounded = new Date(d);
    const remainder = 15 - (rounded.getMinutes() % 15);
    if (remainder !== 15) {
      rounded.setMinutes(rounded.getMinutes() + remainder);
    }
    rounded.setSeconds(0, 0);
    return rounded;
  }

  function setDateRange() {
    const now = new Date();
    const todayStr = toDateStr(now);

    const maxDate = new Date(now);
    maxDate.setDate(maxDate.getDate() + 2);
    const maxDateStr = toDateStr(maxDate);

    dateInput.min = todayStr;
    dateInput.max = maxDateStr;

    // If nothing selected yet, default to today so the time constraint
    // below has something sensible to react to.
    if (!dateInput.value) {
      dateInput.value = todayStr;
    }
  }

  function updateTimeConstraints() {
    const now = new Date();
    const todayStr = toDateStr(now);

    if (dateInput.value === todayStr) {
      // Today is selected: time can't be in the past, and can't be
      // outside the 9AM-10PM window either.
      const earliestAllowed = roundUpToNextQuarterHour(now);
      const earliestStr = toTimeStr(earliestAllowed);

      timeInput.min = earliestStr > DAY_MIN_TIME ? earliestStr : DAY_MIN_TIME;
      timeInput.max = DAY_MAX_TIME;

      // If the currently entered time is now invalid (in the past, or
      // outside the window), clear it so the user re-picks a valid one.
      if (timeInput.value && timeInput.value < timeInput.min) {
        timeInput.value = '';
      }
    } else {
      // Tomorrow or the day after: full 9AM-10PM window is available.
      timeInput.min = DAY_MIN_TIME;
      timeInput.max = DAY_MAX_TIME;
    }
  }

  setDateRange();
  updateTimeConstraints();

  dateInput.addEventListener('change', updateTimeConstraints);

  // Re-check periodically in case the form is left open across a time
  // boundary (e.g. sitting on the page as it ticks past 10:00 PM).
  setInterval(updateTimeConstraints, 60 * 1000);
})();
