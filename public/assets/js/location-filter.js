/* =========================================================
   2nd Harvest — Location filter (OpenStreetMap Nominatim).

   Wires the "Enter your address..." field on the consumer
   marketplace to Nominatim's free geocoding search. As the
   customer types, we debounce and query Nominatim for
   matching addresses, show them in a dropdown, and on pick
   drop the lat/lng straight into the hidden #location-lat /
   #location-lng inputs, then submit the filter form.

   No API key or billing account required. Nominatim's usage
   policy (https://operations.osmfoundation.org/policies/nominatim/)
   asks for max ~1 request/second and a descriptive User-Agent
   (set via the Referer header automatically by the browser),
   which this debounce comfortably respects.
   ========================================================= */

(function () {
  var DEBOUNCE_MS = 400;
  var MIN_CHARS = 3;

  var input = document.getElementById('location-input');
  var latField = document.getElementById('location-lat');
  var lngField = document.getElementById('location-lng');
  var form = document.getElementById('location-filter-form');
  var list = document.getElementById('location-suggestions');

  if (!input || !latField || !lngField || !form || !list) {
    return;
  }

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
        // mousedown (not click) so this fires before the input's blur.
        e.preventDefault();
        selectResult(result);
      });
      list.appendChild(li);
    });
    activeIndex = -1;
    list.hidden = false;
  }

  function selectResult(result) {
    latField.value = result.lat;
    lngField.value = result.lon;
    input.value = result.display_name;

    // Changing address invalidates any previously selected branch —
    // let the server re-pick "all nearby outlets" for the new location.
    var outletSelect = form.querySelector('[name="outlet_id"]');
    if (outletSelect) {
      outletSelect.value = '';
    }

    clearSuggestions();
    form.submit();
  }

  function search(query) {
    var requestId = ++currentRequestId;
    var url = 'https://nominatim.openstreetmap.org/search?' +
      'q=' + encodeURIComponent(query) +
      '&format=json' +
      '&addressdetails=0' +
      '&limit=5' +
      '&countrycodes=lk'; // bias to Sri Lanka, where this project's outlets are based

    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function (res) {
        if (!res.ok) throw new Error('Nominatim request failed: ' + res.status);
        return res.json();
      })
      .then(function (data) {
        // Ignore stale responses from an earlier keystroke.
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
    // Typing manually invalidates any previously picked coordinates.
    latField.value = '';
    lngField.value = '';

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
      // else: no suggestion picked, let the form submit as free text
      // (server-side falls back to "no coordinates set").
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
})();
