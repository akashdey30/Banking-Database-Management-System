<?php
/**
 * transactions/withdraw.php — Withdraw
 *
 * Same idea as deposit.php. The rule "the balance must never become negative" is
 * checked by post_transaction() (includes/helpers.php) and, as a second safety net,
 * by the CHECK (balance >= 0) constraint of the accounts table.
 *
 * Tables used (through the API): accounts (UPDATE balance), transactions (INSERT)
 */
require_once __DIR__ . '/_shared.php';

$pageTitle    = 'Withdraw';
$activeMenu   = 'withdraw';
$base         = '..';
$extraScripts = ['transactions.js'];

include __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Withdraw</h1>

<div id="txn-result"></div>

<form id="txn-form" class="card shadow-sm" data-type="withdraw" novalidate>
    <div class="card-body">
        <div class="row g-3">
            <?php txn_account_field('Account number', 'account_number'); ?>

            <div class="col-md-6">
                <label class="form-label" for="amount">Amount (BDT) <span class="required">*</span></label>
                <input class="form-control" type="number" id="amount" name="amount" min="0.01" step="0.01" required>
                <div class="form-text">The amount cannot be more than the current balance.</div>

                <label class="form-label mt-3" for="description">Description</label>
                <input class="form-control" type="text" id="description" name="description" maxlength="200"
                       placeholder="Optional, e.g. Cash withdrawal at counter">
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-danger">Withdraw</button>
        <a class="btn btn-outline-secondary" href="../dashboard/index.php">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
