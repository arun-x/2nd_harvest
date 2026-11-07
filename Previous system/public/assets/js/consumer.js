/* =========================================================
   2nd Harvest — Consumer client-side helpers.
   Plain vanilla JS. No frameworks, per project constraint.
   ========================================================= */
(function () {
  'use strict';

  /* ----- 1. Live search on the marketplace -----
     Multiple inputs may carry [data-live-search] on the same page
     (e.g. the topnav search and the marketplace filter bar). They
     all filter the same product cards and stay in sync as the user
     types in either. */
  var searchInputs = document.querySelectorAll('[data-live-search]');
  if (searchInputs.length) {
    var cards = document.querySelectorAll('[data-product-card]');
    var applyFilter = function (q) {
      q = q.trim().toLowerCase();
      cards.forEach(function (card) {
        var haystack = (card.getAttribute('data-search-index') || '').toLowerCase();
        card.style.display = (q === '' || haystack.indexOf(q) !== -1) ? '' : 'none';
      });
      searchInputs.forEach(function (other) {
        if (other.value !== q) other.value = q;
      });
    };
    searchInputs.forEach(function (input) {
      input.addEventListener('input', function (e) { applyFilter(e.target.value); });
    });
  }

  /* ----- 2. Modal open/close (used by the token viewer) ----- */
  document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-open-modal');
      var modal = document.getElementById(id);
      if (modal) modal.style.display = 'flex';
    });
  });
  document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = btn.closest('.modal-backdrop');
      if (modal) modal.style.display = 'none';
    });
  });
  document.querySelectorAll('.modal-backdrop').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) modal.style.display = 'none';
    });
  });

  /* ----- 3. Confirm destructive actions (cancel reservation) ----- */
  document.querySelectorAll('[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var msg = form.getAttribute('data-confirm') || 'Are you sure?';
      if (!window.confirm(msg)) e.preventDefault();
    });
  });

  /* ----- 4. Auto-dismiss flash messages after 5s ----- */
  document.querySelectorAll('.flash').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.4s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 5000);
  });
})();
