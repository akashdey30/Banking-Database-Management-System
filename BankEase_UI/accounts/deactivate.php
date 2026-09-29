<?php
/**
 * accounts/deactivate.php — deactivate one account (POST only, no page is shown)
 *
 * "Deactivate" sets accounts.status = 'frozen'. This is reversible (Edit Account ->
 * Status -> Active) and the UI never closes an account permanently.
 * post_transaction() in includes/helpers.php refuses to use a frozen account.
 *
 * Tables used: accounts (SELECT, UPDATE)
 */
require_once __DIR__ . '/../includes/helpers.php';

if (!is_post()) {
    redirect('index.php');
}

$id = (int) posted('account_id');

try {
    $account = db_select_one('SELECT account_number, status FROM accounts WHERE account_id = :id', [':id' => $id]);

    if ($account === null) {
        flash_set('error', 'Account not found.');
    } elseif ($account['status'] === 'frozen') {
        flash_set('error', 'Account ' . $account['account_number'] . ' is already deactivated (frozen).');
    } elseif ($account['status'] === 'closed') {
        flash_set('error', 'Account ' . $account['account_number'] . ' is closed.');
    } else {
        db_execute("UPDATE accounts SET status = 'frozen' WHERE account_id = :id", [':id' => $id]);
        flash_set('success', 'Account ' . $account['account_number'] . ' was deactivated (frozen).');
    }
} catch (Throwable $e) {
    flash_set('error', exception_message($e));
}

redirect('index.php');
