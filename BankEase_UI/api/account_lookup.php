<?php
/**
 * api/account_lookup.php — AJAX: find one account by its account number
 *
 * Request (GET):  ?number=KHU0110000472591
 * Answer (JSON):  {"success": true, "account": { account_id, account_number, account_title,
 *                   customer_name, type_name, status, balance, balance_text }}
 *         or:     {"success": false, "message": "No account found with this number."}
 *
 * Tables used: accounts, customers, account_types (read-only)
 */
require_once __DIR__ . '/../includes/helpers.php';

$number = trim((string) ($_GET['number'] ?? ''));

if ($number === '') {
    json_response(['success' => false, 'message' => 'Enter an account number.'], 400);
}

try {
    $account = db_select_one(
        'SELECT a.account_id, a.account_number, a.account_title, a.status, a.balance,
                c.full_name AS customer_name, t.type_name
         FROM accounts a
         JOIN customers c     ON c.customer_id = a.customer_id
         JOIN account_types t ON t.account_type_id = a.account_type_id
         WHERE a.account_number = :number',
        [':number' => $number]
    );

    if ($account === null) {
        json_response(['success' => false, 'message' => 'No account found with this number.']);
    }

    $account['balance_text'] = format_money($account['balance']);
    json_response(['success' => true, 'account' => $account]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => exception_message($e)], 500);
}
