<?php
/**
 * customers/index.php — Customer List
 *
 * The table body is filled by assets/js/customers.js, which calls
 * api/customers_search.php (AJAX). Typing in the search box refreshes the
 * table without reloading the page.
 *
 * Tables used (through the API): customers, branches, accounts
 */
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle    = 'Customer List';
$activeMenu   = 'customer_list';
$base         = '..';
$extraScripts = ['customers.js'];

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Customer List</h1>
    <a class="btn btn-primary btn-sm" href="create.php">+ New Customer</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <input type="search" id="customer-search" class="form-control mb-3" autocomplete="off"
               placeholder="Search by name, CIF number, phone, ID number or e-mail...">

        <div id="customer-status" class="small text-muted mb-2">Loading...</div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>CIF</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>ID</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="customer-rows"></tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
