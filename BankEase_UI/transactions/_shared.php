<?php
/**
 * transactions/_shared.php — helper used by deposit.php, withdraw.php and transfer.php
 *
 * (The file name starts with "_" to show it is a helper, not a page.)
 *
 * txn_account_field() prints:
 *   - an input for the account number (class "account-input")
 *   - an info box below it, which assets/js/transactions.js fills with the account title,
 *     owner, type, status and balance after an AJAX lookup (api/account_lookup.php)
 *
 * Tables used: none here (the lookup itself reads accounts, customers, account_types)
 */
require_once __DIR__ . '/../includes/helpers.php';

/**
 * @param string $label   text above the input, e.g. "Account number"
 * @param string $name    the field name sent to the server, e.g. "account_number"
 * @param string $hint    small help text under the input
 */
function txn_account_field(string $label, string $name, string $hint = 'Enter the account number and leave the field to see the account details.'): void
{
    ?>
    <div class="col-md-6">
        <label class="form-label" for="<?= e($name) ?>"><?= e($label) ?> <span class="required">*</span></label>
        <input class="form-control account-input" type="text" id="<?= e($name) ?>" name="<?= e($name) ?>"
               maxlength="20" autocomplete="off" placeholder="e.g. KHU0110000472591"
               data-info="info-<?= e($name) ?>">
        <div class="form-text"><?= e($hint) ?></div>
        <div id="info-<?= e($name) ?>" class="account-info-box small mt-2"></div>
    </div>
    <?php
}
