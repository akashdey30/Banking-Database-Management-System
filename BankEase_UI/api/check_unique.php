<?php
/**
 * api/check_unique.php — AJAX: is a value still free in a UNIQUE column?
 *
 * Request (GET):  ?table=customers&column=phone&value=01711000000&exclude_id=5
 * Answer (JSON):  {"success": true, "available": true}
 *
 * exclude_id is the row being edited, so a record does not clash with itself.
 * Only the table.column pairs listed below are accepted.
 *
 * Tables used: customers, accounts (read-only)
 */
require_once __DIR__ . '/../includes/helpers.php';

// Whitelist: table => [columns that are UNIQUE in the DDL]
$allowed = [
    'customers' => ['cif_number', 'phone', 'email', 'id_number', 'tin_number'],
    'accounts'  => ['account_number'],
];

$table   = (string) ($_GET['table']  ?? '');
$column  = (string) ($_GET['column'] ?? '');
$value   = trim((string) ($_GET['value'] ?? ''));
$exclude = (int) ($_GET['exclude_id'] ?? 0);

if (!isset($allowed[$table]) || !in_array($column, $allowed[$table], true)) {
    json_response(['success' => false, 'message' => 'This field cannot be checked.'], 400);
}
if ($value === '') {
    json_response(['success' => true, 'available' => true]);
}

try {
    json_response(['success' => true, 'available' => value_is_unique($table, $column, $value, $exclude)]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => exception_message($e)], 500);
}
