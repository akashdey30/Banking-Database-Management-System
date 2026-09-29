/**
 * app.js - JavaScript shared by every BankEase page (vanilla JS, no jQuery)
 *
 * 1. fetchJson()       small wrapper around fetch() used by all AJAX calls
 * 2. escapeHtml()      makes text safe before it is placed into innerHTML
 * 3. formatMoney()     BDT 1,234.50
 * 4. data-confirm      "Are you sure?" popup on delete / deactivate buttons
 * 5. data-check-unique live "already used" check for phone, e-mail, NID ... (AJAX)
 */

/* ------------------------------------------------------------------
 * 1. fetchJson(url, options) -> Promise that gives the decoded JSON
 *    Every api/*.php file answers with {success: true/false, message: "..."}.
 *    If the server sends something that is not JSON (for example a PHP error),
 *    a readable message is returned instead of crashing the page.
 * ------------------------------------------------------------------ */
function fetchJson(url, options) {
  return fetch(url, options)
    .then(function (response) {
      return response.text().then(function (text) {
        try {
          return JSON.parse(text);
        } catch (e) {
          return { success: false, message: 'The server sent an unexpected response.' };
        }
      });
    })
    .catch(function () {
      return { success: false, message: 'Could not reach the server. Is Apache/MySQL running?' };
    });
}

/* 2. Escape text so user data can never be run as HTML */
function escapeHtml(value) {
  var text = (value === null || value === undefined) ? '' : String(value);
  // Replace the five characters that have a special meaning in HTML
  // (quotes too, because values are also placed inside attributes like data-message="...").
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* 3. 77730.5 -> "BDT 77,730.50" */
function formatMoney(amount) {
  var n = Number(amount);
  if (isNaN(n)) { return amount; }
  return 'BDT ' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.addEventListener('DOMContentLoaded', function () {

  /* 4. <button data-confirm="Delete this customer?"> shows a popup first */
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      if (!window.confirm(el.getAttribute('data-confirm'))) {
        event.preventDefault();
      }
    });
  });

  /* 5. Live "already used?" check.
   *    <input data-check-unique="customers.phone" data-exclude-id="5">
   *    When the user leaves the field, api/check_unique.php is asked whether the
   *    value already exists. The answer is shown under the field.            */
  document.querySelectorAll('[data-check-unique]').forEach(function (input) {
    var feedback = document.createElement('div');
    feedback.className = 'form-text';
    input.insertAdjacentElement('afterend', feedback);

    input.addEventListener('blur', function () {
      var value = input.value.trim();
      input.classList.remove('is-invalid', 'is-valid');
      feedback.textContent = '';
      feedback.className = 'form-text';
      if (value === '') { return; }

      var parts = input.getAttribute('data-check-unique').split('.');
      var query = new URLSearchParams({
        table: parts[0],
        column: parts[1],
        value: value,
        exclude_id: input.getAttribute('data-exclude-id') || '0'
      });

      // The api/ folder is always at <root>/api/, and data-base holds the path back to the root.
      var base = input.getAttribute('data-base') || '..';
      fetchJson(base + '/api/check_unique.php?' + query.toString()).then(function (result) {
        if (!result.success) { return; }         // stay quiet if the check itself failed
        if (result.available) {
          input.classList.add('is-valid');
          feedback.className = 'form-text text-success';
          feedback.textContent = 'Available.';
        } else {
          input.classList.add('is-invalid');
          feedback.className = 'form-text text-danger';
          feedback.textContent = 'This value is already used by another record.';
        }
      });
    });
  });
});
