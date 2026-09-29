<?php
/**
 * transactions/deposit.php — Deposit
 *
 * Only the form is built here. Everything else happens through AJAX:
 *   assets/js/transactions.js  ->  api/account_lookup.php   (show the account)
 *                              ->  api/transaction_post.php (save the deposit)
 *
 * Tables used (through the API): accounts (UPDATE balance), transactions (INSERT)
 */
require_once __DIR__ . '/_shared.php';

$pageTitle    = 'Deposit';
$activeMenu   = 'deposit';
$base         = '..';
$extraScripts = ['transactions.js'];

include __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Deposit</h1>

<div id="txn-result"></div>

<form id="txn-form" class="card shadow-sm" data-type="deposit" novalidate>
    <div class="card-body">
        <div class="row g-3">
            <?php txn_account_field('Account number', 'account_number'); ?>

            <div class="col-md-6">
                <label class="form-label" for="amount">Amount (BDT) <span class="required">*</span></label>
                <input class="form-control" type="number" id="amount" name="amount" min="0.01" step="0.01" required>

                <label class="form-label mt-3" for="description">Description</label>
                <input class="form-control" type="text" id="description" name="description" maxlength="200"
                       placeholder="Optional, e.g. Cash deposit at counter">
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-success">Deposit</button>
        <a class="btn btn-outline-secondary" href="../dashboard/index.php">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
