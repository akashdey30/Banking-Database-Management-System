<?php
/**
 * transactions/transfer.php — Transfer between two accounts
 *
 * One transfer creates (all inside ONE database transaction, in api/transaction_post.php):
 *   1. a 'transfer_out' row in transactions for the source account
 *   2. a 'transfer_in'  row in transactions for the destination account
 *   3. a row in transfers that links the two transaction rows
 * and updates both balances. If any step fails, ROLLBACK undoes everything.
 *
 * Tables used (through the API): accounts (UPDATE x2), transactions (INSERT x2), transfers (INSERT)
 */
require_once __DIR__ . '/_shared.php';

$pageTitle    = 'Transfer';
$activeMenu   = 'transfer';
$base         = '..';
$extraScripts = ['transactions.js'];

include __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-3">Transfer</h1>

<div id="txn-result"></div>

<form id="txn-form" class="card shadow-sm" data-type="transfer" novalidate>
    <div class="card-body">
        <div class="row g-3">
            <?php
            txn_account_field('Source account', 'from_account', 'The account the money is taken from.');
            txn_account_field('Destination account', 'to_account', 'The account that receives the money.');
            ?>

            <div class="col-md-6">
                <label class="form-label" for="amount">Amount (BDT) <span class="required">*</span></label>
                <input class="form-control" type="number" id="amount" name="amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="description">Description</label>
                <input class="form-control" type="text" id="description" name="description" maxlength="200"
                       placeholder="Optional note for both statements">
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">Transfer</button>
        <a class="btn btn-outline-secondary" href="../dashboard/index.php">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
