<?php
/**
 * api/transaction_post.php — AJAX: process a deposit, withdrawal or transfer
 *
 * Request (POST):
 *   type            deposit | withdraw | transfer
 *   account_number  (deposit, withdraw)  the account to use
 *   from_account    (transfer)           source account number
 *   to_account      (transfer)           destination account number
 *   amount          e.g. 1500 or 1500.50
 *   description     optional note (max 255 characters)
 *
 * Answer (JSON):
 *   {"success": true,  "message": "...", "reference": "TXN...", "balance": "...", "balance_text": "..."}
 *   {"success": false, "message": "...", "errors": ["...", ...]}
 *
 * HOW THE DATABASE TRANSACTION WORKS
 *   db_begin()      start: nothing is permanent yet
 *   ... post_transaction() changes accounts.balance and inserts into transactions ...
 *   db_commit()     everything succeeded: save all changes together
 *   db_rollback()   something failed: undo ALL changes, so money is never lost or created
 *
 * Tables used: accounts, transactions, transfers
 */
require_once __DIR__ . '/../includes/helpers.php';

require_post_json();

$type        = posted('type');
$amount      = posted('amount');
$description = posted('description');
$errors      = [];

// ---------- 1. Validate the input (before touching the database) ----------
check_in_list($errors, 'Transaction type', $type, ['deposit', 'withdraw', 'transfer']);
check_amount($errors, 'Amount', $amount);
check_max_length($errors, 'Description', $description, 200);   // 200 leaves room for the "Transfer to ..." prefix

/** Find an account by its number, or add an error. Returns the row or null. */
function find_account(array &$errors, string $label, string $number): ?array
{
    if ($number === '') {
        $errors[] = $label . ' is required.';
        return null;
    }
    $row = db_select_one(
        'SELECT account_id, account_number FROM accounts WHERE account_number = :n',
        [':n' => $number]
    );
    if ($row === null) {
        $errors[] = $label . ' "' . $number . '" was not found.';
    }
    return $row;
}

try {
    $account = $from = $to = null;

    if ($type === 'transfer') {
        $from = find_account($errors, 'Source account', posted('from_account'));
        $to   = find_account($errors, 'Destination account', posted('to_account'));
        if ($from && $to && (int) $from['account_id'] === (int) $to['account_id']) {
            $errors[] = 'Source and destination accounts must be different.';
        }
    } elseif ($type === 'deposit' || $type === 'withdraw') {
        $account = find_account($errors, 'Account', posted('account_number'));
    }

    if ($errors) {
        json_response(['success' => false, 'message' => 'Please correct the input.', 'errors' => $errors]);
    }

    // ---------- 2. Do the work inside ONE database transaction ----------
    db_begin();
    try {

        if ($type === 'deposit' || $type === 'withdraw') {

            $dbType = ($type === 'deposit') ? 'deposit' : 'withdrawal';       // transactions.transaction_type value
            $note   = $description !== '' ? $description : (($type === 'deposit') ? 'Cash deposit' : 'Cash withdrawal');

            $txnId = post_transaction((int) $account['account_id'], $dbType, $amount, $note);
            db_commit();

            $row = db_select_one(
                'SELECT t.reference_number, a.balance
                 FROM transactions t JOIN accounts a ON a.account_id = t.account_id
                 WHERE t.transaction_id = :id',
                [':id' => $txnId]
            );

            json_response([
                'success'      => true,
                'message'      => ($type === 'deposit' ? 'Deposit' : 'Withdrawal') . ' of ' . format_money($amount) . ' completed.',
                'reference'    => $row['reference_number'],
                'balance'      => $row['balance'],
                'balance_text' => format_money($row['balance']),
            ]);
        }

        // ----- transfer -----
        // Lock both account rows in ascending id order. If two transfers run at the
        // same moment in opposite directions, locking in the same order prevents a deadlock.
        $ids = [(int) $from['account_id'], (int) $to['account_id']];
        sort($ids);
        db_select_all(
            'SELECT account_id FROM accounts WHERE account_id IN (:a, :b) ORDER BY account_id FOR UPDATE',
            [':a' => $ids[0], ':b' => $ids[1]]
        );

        $fromId = (int) $from['account_id'];
        $toId   = (int) $to['account_id'];
        $note   = $description !== '' ? ' - ' . $description : '';

        // Money leaves the source account, then arrives in the destination account.
        $debitId  = post_transaction($fromId, 'transfer_out', $amount, 'Transfer to ' . $to['account_number'] . $note, $toId);
        $creditId = post_transaction($toId,   'transfer_in',  $amount, 'Transfer from ' . $from['account_number'] . $note, $fromId);

        // The transfers table links the two transaction rows together.
        db_insert(
            "INSERT INTO transfers (debit_transaction_id, credit_transaction_id, transfer_type, fee, status)
             VALUES (:debit, :credit, 'internal', 0, 'completed')",
            [':debit' => $debitId, ':credit' => $creditId]
        );

        db_commit();

        $row = db_select_one(
            'SELECT t.reference_number, a.balance
             FROM transactions t JOIN accounts a ON a.account_id = t.account_id
             WHERE t.transaction_id = :id',
            [':id' => $debitId]
        );

        json_response([
            'success'      => true,
            'message'      => 'Transfer of ' . format_money($amount) . ' from ' . $from['account_number']
                              . ' to ' . $to['account_number'] . ' completed.',
            'reference'    => $row['reference_number'],
            'balance'      => $row['balance'],                 // new balance of the SOURCE account
            'balance_text' => format_money($row['balance']),
            'balance_label'=> 'Source account balance',
        ]);

    } catch (Throwable $e) {
        db_rollback();          // undo every change made since db_begin()
        throw $e;               // handled below
    }

} catch (Throwable $e) {
    json_response(['success' => false, 'message' => exception_message($e)]);
}
