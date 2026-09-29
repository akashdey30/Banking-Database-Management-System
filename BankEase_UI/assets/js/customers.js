/**
 * customers.js - AJAX customer search for customers/index.php
 *
 * 1. On page load, and every time the search text changes, ask api/customers_search.php
 * 2. Draw the returned rows into the table
 * 3. Ask "are you sure?" before a Delete form is submitted
 */
document.addEventListener('DOMContentLoaded', function () {
  var searchBox = document.getElementById('customer-search');
  var tbody     = document.getElementById('customer-rows');
  var statusBox = document.getElementById('customer-status');
  var timer     = null;
  var lastRequest = 0;            // used to ignore answers that arrive late

  function loadCustomers() {
    var thisRequest = ++lastRequest;
    var url = '../api/customers_search.php?q=' + encodeURIComponent(searchBox.value.trim());

    fetchJson(url).then(function (result) {
      if (thisRequest !== lastRequest) { return; }     // an older, slower request: ignore it

      if (!result.success) {
        tbody.innerHTML = '';
        statusBox.className = 'small text-danger mb-2';
        statusBox.textContent = result.message || 'Search failed.';
        return;
      }

      statusBox.className = 'small text-muted mb-2';
      statusBox.textContent = result.count + ' customer(s) shown' +
        (result.count >= 50 ? ' (showing the first 50 - refine the search)' : '');

      if (result.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No customers found.</td></tr>';
        return;
      }

      tbody.innerHTML = result.data.map(renderRow).join('');
    });
  }

  function statusBadge(status) {
    var colour = { active: 'success', inactive: 'secondary', blocked: 'danger' }[status] || 'secondary';
    return '<span class="badge text-bg-' + colour + '">' + escapeHtml(status) + '</span>';
  }

  function renderRow(c) {
    var id = escapeHtml(c.customer_id);
    return '<tr>' +
      '<td>' + escapeHtml(c.cif_number) + '</td>' +
      '<td>' + escapeHtml(c.full_name) + '</td>' +
      '<td>' + escapeHtml(c.phone) + '</td>' +
      '<td>' + escapeHtml(c.id_type) + ': ' + escapeHtml(c.id_number) + '</td>' +
      '<td>' + escapeHtml(c.branch_name) + '</td>' +
      '<td>' + statusBadge(c.status) + '</td>' +
      '<td class="text-end table-actions">' +
        '<a class="btn btn-outline-primary btn-sm" href="edit.php?id=' + id + '">Edit</a> ' +
        '<form method="post" action="delete.php" class="delete-form" ' +
              'data-message="Delete customer ' + escapeHtml(c.full_name) + '? This cannot be undone.">' +
          '<input type="hidden" name="customer_id" value="' + id + '">' +
          '<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>' +
        '</form>' +
      '</td></tr>';
  }

  /* Search while typing, but wait 300 ms after the last key press */
  searchBox.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(loadCustomers, 300);
  });

  /* The rows are created by JavaScript, so one listener on the table catches every Delete form */
  tbody.addEventListener('submit', function (event) {
    var form = event.target;
    if (form.classList.contains('delete-form') && !window.confirm(form.getAttribute('data-message'))) {
      event.preventDefault();
    }
  });

  loadCustomers();
});
