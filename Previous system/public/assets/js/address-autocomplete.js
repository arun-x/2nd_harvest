/* =========================================================
   2nd Harvest — generic address autocomplete (Nominatim).

   Reusable across any page: attach to an <input> by adding
   data-address-autocomplete to it. On pick, just fills the
   input's value with the full matched address — it does NOT
   submit any form. Actual lat/lng lookup for that address
   happens server-side (see app/Core/Geocoder.php) when the
   form is submitted, so this is purely a typing aid to help
   people enter a well-formed, geocodable address.

   Free, no API key or billing account needed.
   ========================================================= */

(function () {
  var DEBOUNCE_MS = 400;
  var MIN_CHARS = 3;

  function setUp(input) {
    // Needs a positioned ancestor to anchor the dropdown to —
    // .input-icon-wrap already has position: relative site-wide.
    var wrap = input.closest('.input-icon-wrap') || input.parentElement;
    if (!wrap) return;

    var list = document.createElement('ul');
    list.className = 'address-suggestions';
    list.hidden = true;
    wrap.appendChild(list);

    var debounceTimer = null;
    var activeIndex = -1;
    var currentResults = [];
    var currentRequestId = 0;

    function clearSuggestions() {
      list.innerHTML = '';
      list.hidden = true;
      activeIndex = -1;
      currentResults = [];
    }

    function renderMessage(text, className) {
      list.innerHTML = '';
      var li = document.createElement('li');
      li.className = className;
      li.textContent = text;
      list.appendChild(li);
      list.hidden = false;
    }

    function renderResults(results) {
      list.innerHTML = '';
      if (results.length === 0) {
        renderMessage('No matching addresses found', 'is-empty');
        return;
      }
      results.forEach(function (result, i) {
        var li = document.createElement('li');
        li.textContent = result.display_name;
        li.setAttribute('data-index', String(i));
        li.addEventListener('mousedown', function (e) {
          e.preventDefault(); // fire before the input's blur
          selectResult(result);
        });
        list.appendChild(li);
      });
      activeIndex = -1;
      list.hidden = false;
    }

    function selectResult(result) {
      input.value = result.display_name;
      clearSuggestions();
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function search(query) {
      var requestId = ++currentRequestId;
      var url = 'https://nominatim.openstreetmap.org/search?' +
        'q=' + encodeURIComponent(query) +
        '&format=json' +
        '&addressdetails=0' +
        '&limit=5' +
        '&countrycodes=lk'; // bias to Sri Lanka

      fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (res) {
          if (!res.ok) throw new Error('Nominatim request failed: ' + res.status);
          return res.json();
        })
        .then(function (data) {
          if (requestId !== currentRequestId) return;
          currentResults = data || [];
          renderResults(currentResults);
        })
        .catch(function (err) {
          if (requestId !== currentRequestId) return;
          console.warn('2nd Harvest: address lookup failed —', err);
          renderMessage('Address lookup unavailable right now', 'is-empty');
        });
    }

    input.addEventListener('input', function () {
      var query = input.value.trim();
      clearTimeout(debounceTimer);

      if (query.length < MIN_CHARS) {
        clearSuggestions();
        return;
      }

      renderMessage('Searching…', 'is-loading');
      debounceTimer = setTimeout(function () {
        search(query);
      }, DEBOUNCE_MS);
    });

    input.addEventListener('keydown', function (e) {
      var items = list.querySelectorAll('li[data-index]');
      if (list.hidden || items.length === 0) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        highlight(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        highlight(items);
      } else if (e.key === 'Enter') {
        if (activeIndex >= 0 && currentResults[activeIndex]) {
          e.preventDefault();
          selectResult(currentResults[activeIndex]);
        }
      } else if (e.key === 'Escape') {
        clearSuggestions();
      }
    });

    function highlight(items) {
      items.forEach(function (li, i) {
        li.classList.toggle('is-active', i === activeIndex);
      });
    }

    document.addEventListener('click', function (e) {
      if (e.target !== input && !list.contains(e.target)) {
        clearSuggestions();
      }
    });
  }

  document.querySelectorAll('[data-address-autocomplete]').forEach(setUp);
})();
