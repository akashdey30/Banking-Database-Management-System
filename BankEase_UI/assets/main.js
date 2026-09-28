document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      if (!window.confirm(el.dataset.confirm)) event.preventDefault();
    });
  });
  document.querySelectorAll('[data-table-search]').forEach(function (search) {
    var table = search.closest('.container') ? search.closest('.container').querySelector('.data-table') : null;
    if (!table) return;
    search.addEventListener('input', function () {
      var q = search.value.toLowerCase().trim();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().indexOf(q) >= 0 ? '' : 'none';
      });
    });
  });
});
