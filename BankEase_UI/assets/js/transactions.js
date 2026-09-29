/**
 * transactions.js - AJAX behaviour of deposit.php, withdraw.php and transfer.php
 *
 * 1. Account lookup    leave an account-number field -> api/account_lookup.php -> show details
 * 2. Client-side check obvious mistakes are flagged before anything is sent
 * 3. Submit            api/transaction_post.php -> green / red message, no page reload
 *
 * The SERVER repeats every check (insufficient balance, frozen account ...), so the
 * checks here only make the form friendlier; they are not a security measure.
 */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('txn-form');
  if (!form) { return; }

  var type       = form.getAttribute('data-type');            // deposit | withdraw | transfer
  var resultBox  = document.getElementById('txn-result');
  var submitBtn  = form.querySelector('button[type="submit"]');
  var accountInputs = form.querySelectorAll('.account-input');

  /* ---------------------------------------------------------------
   * 1. Account lookup
   * --------------------------------------------------------------- */
  function showInfo(input, account, message) {
    var box = document.getElementById(input.getAttribute('data-info'));
    input.classList.remove('is-valid', 'is-invalid');

    if (!account) {
      box.className = 'account-info-box small mt-2' + (message ? ' text-danger' : '');
      box.textContent = message || '';
      if (message) { input.classList.add('is-invalid'); }
      return;
    }

    var statusColour = { active: 'success', pending: 'warning', frozen: 'info', closed: 'secondary' }[account.status] || 'secondary';
    box.className = 'account-info-box small mt-2 border rounded p-2 bg-light';
    box.innerHTML =
      '<div><strong>' + escapeHtml(account.account_title) + '</strong> ' +
        '<span class="badge text-bg-' + statusColour + '">' + escapeHtml(account.status) + '</span></div>' +
      '<div class="text-muted">' + escapeHtml(account.customer_name) + ' &middot; ' + escapeHtml(account.type_name) + '</div>' +
      '<div>Balance: <strong class="balance-text">' + escapeHtml(account.balance_text) + '</strong></div>' +
      (account.status !== 'active'
        ? '<div class="text-danger">This account is ' + escapeHtml(account.status) + ' - transactions are not allowed.</div>'
        : '');
    input.classList.add(account.status === 'active' ? 'is-valid' : 'is-invalid');
  }

  function lookup(input) {
    var number = input.value.trim();
    if (number === '') { showInfo(input, null, ''); return Promise.resolve(); }

    var box = document.getElementById(input.getAttribute('data-info'));
    box.className = 'account-info-box small mt-2 text-muted';
    box.textContent = 'Looking up...';

    return fetchJson('../api/account_lookup.php?number=' + encodeURIComponent(number)).then(function (result) {
      if (input.value.trim() !== number) { return; }           // the user typed something else meanwhile
      showInfo(input, result.success ? result.account : null, result.success ? '' : result.message);
    });
  }

  accountInputs.forEach(function (input) {
    var timer = null;
    input.addEventListener('blur', function () { clearTimeout(timer); lookup(input); });
    input.addEventListener('input', function () {                       // also look up ~0.6 s after typing stops
      clearTimeout(timer);
      timer = setTimeout(function () { lookup(input); }, 600);
    });
  });

  /* ---------------------------------------------------------------
   * 2. Simple checks before sending
   * --------------------------------------------------------------- */
  function clientErrors() {
    var errors = [];
    accountInputs.forEach(function (input) {
      if (input.value.trim() === '') { errors.push('Please enter the ' + input.previousElementSibling.textContent.replace('*', '').trim().toLowerCase() + '.'); }
    });

    var amount = form.elements['amount'].value.trim();
    if (!/^\d+(\.\d{1,2})?$/.test(amount) || parseFloat(amount) <= 0) {
      errors.push('Amount must be greater than 0 with at most 2 decimal places.');
    }

    if (type === 'transfer' &&
        form.elements['from_account'].value.trim() !== '' &&
        form.elements['from_account'].value.trim() === form.elements['to_account'].value.trim()) {
      errors.push('Source and destination accounts must be different.');
    }
    return errors;
  }

  /* ---------------------------------------------------------------
   * 3. Submit with AJAX
   * --------------------------------------------------------------- */
  function showResult(kind, html) {
    resultBox.innerHTML = '<div class="alert alert-' + kind + ' alert-dismissible fade show" role="alert">' + html +
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();                                   // stay on the page
    resultBox.innerHTML = '';

    var errors = clientErrors();
    if (errors.length > 0) {
      showResult('danger', '<ul class="mb-0">' + errors.map(function (m) { return '<li>' + escapeHtml(m) + '</li>'; }).join('') + '</ul>');
      return;
    }

    var data = new FormData(form);
    data.append('type', type);

    submitBtn.disabled = true;
    var oldText = submitBtn.textContent;
    submitBtn.textContent = 'Processing...';

    fetchJson('../api/transaction_post.php', { method: 'POST', body: data }).then(function (result) {
      submitBtn.disabled = false;
      submitBtn.textContent = oldText;

      if (result.success) {
        showResult('success',
          '<strong>' + escapeHtml(result.message) + '</strong><br>' +
          'Reference: ' + escapeHtml(result.reference) + ' &middot; ' + escapeHtml(result.balance_label || 'New balance') + ': ' + escapeHtml(result.balance_text));
        form.elements['amount'].value = '';
        form.elements['description'].value = '';
        accountInputs.forEach(lookup);                        // refresh the balances shown
      } else {
        var html = '<strong>' + escapeHtml(result.message || 'The transaction failed.') + '</strong>';
        if (result.errors && result.errors.length) {
          html += '<ul class="mb-0 mt-1">' + result.errors.map(function (m) { return '<li>' + escapeHtml(m) + '</li>'; }).join('') + '</ul>';
        }
        showResult('danger', html);
        accountInputs.forEach(lookup);
      }
    });
  });
});
