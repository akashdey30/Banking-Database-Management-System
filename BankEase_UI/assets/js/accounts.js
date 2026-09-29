/**
 * accounts.js - AJAX behaviour of accounts/create.php
 *
 * 1. Load the customer dropdown from api/customers_options.php
 * 2. Narrow the dropdown while the user types in "Find customer"
 * 3. When a customer is chosen, fill the account title and the branch
 */
document.addEventListener('DOMContentLoaded', function () {
  var select     = document.getElementById('customer_id');
  var filterBox  = document.getElementById('customer-filter');
  var titleInput = document.getElementById('account_title');
  var branchSel  = document.getElementById('branch_id');
  var timer      = null;
  var autoTitle  = titleInput.value;      // the last title WE filled in (so we never overwrite the user's own text)
  var lastRequest = 0;

  /* 1 + 2. Fill the dropdown with customers that match the search text */
  function loadCustomers() {
    var thisRequest = ++lastRequest;
    var keepOption  = select.options[select.selectedIndex];      // the customer selected right now (if any)
    var keepValue   = select.value;

    fetchJson('../api/customers_options.php?q=' + encodeURIComponent(filterBox.value.trim())).then(function (result) {
      if (thisRequest !== lastRequest) { return; }

      if (!result.success) {
        select.innerHTML = '<option value="">' + escapeHtml(result.message || 'Could not load customers') + '</option>';
        return;
      }

      select.innerHTML = '<option value="">-- Select customer --</option>';
      var found = false;

      result.data.forEach(function (c) {
        var option = document.createElement('option');
        option.value = c.id;
        option.textContent = c.label;                 // textContent: safe, never interpreted as HTML
        option.dataset.name = c.name;
        option.dataset.branch = c.branch_id;
        if (String(c.id) === keepValue) { option.selected = true; found = true; }
        select.appendChild(option);
      });

      // Keep the current choice even if the search text no longer matches it
      if (keepValue !== '' && !found && keepOption) {
        select.insertBefore(keepOption, select.options[1] || null);
        select.value = keepValue;
      }

      if (result.data.length === 0 && keepValue === '') {
        select.innerHTML = '<option value="">No active customers found</option>';
      }
    });
  }

  filterBox.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(loadCustomers, 300);
  });

  /* 3. A customer was chosen: suggest the title and the branch */
  select.addEventListener('change', function () {
    var option = select.options[select.selectedIndex];
    if (!option || !option.dataset.name) { return; }

    if (titleInput.value.trim() === '' || titleInput.value === autoTitle) {
      titleInput.value = option.dataset.name;
      autoTitle = option.dataset.name;
    }
    if (option.dataset.branch) {
      branchSel.value = option.dataset.branch;        // the user can still change the branch afterwards
    }
  });

  loadCustomers();
});
