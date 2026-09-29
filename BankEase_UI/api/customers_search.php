<?php
/**
 * api/customers_search.php — AJAX: search customers
 *
 * Request (GET):  ?q=karim        (empty q = newest customers)
 * Answer (JSON):  {"success": true, "count": 2, "data": [ {customer row}, ... ]}
 *
 * Searches: full name, CIF number, phone, ID number (NID / passport ...), e-mail.
 *
 * Tables used: customers, branches (JOIN), accounts (count per customer)
 */
require_once __DIR__ . '/../includes/helpers.php';

$q = trim((string) ($_GET['q'] ?? ''));

try {
    // The LIMIT is a fixed integer constant from config.php (not user input),
    // so it is safe to write into the SQL text.
    $limit = (int) AJAX_MAX_RESULTS;

    $sql = 'SELECT c.customer_id, c.cif_number, c.full_name, c.phone, c.email,
                   c.id_type, c.id_number, c.status, b.branch_name,
                   (SELECT COUNT(*) FROM accounts a WHERE a.customer_id = c.customer_id) AS account_count
            FROM customers c
            JOIN branches b ON b.branch_id = c.branch_id';
    $params = [];

    if ($q !== '') {
        // One placeholder per column: with real prepared statements a name cannot be reused.
        $sql .= ' WHERE c.full_name LIKE :q1 OR c.cif_number LIKE :q2 OR c.phone LIKE :q3
                     OR c.id_number LIKE :q4 OR c.email LIKE :q5';
        $like = '%' . $q . '%';
        $params = [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like];
    }

    $sql .= ' ORDER BY c.customer_id DESC LIMIT ' . $limit;

    $rows = db_select_all($sql, $params);
    json_response(['success' => true, 'count' => count($rows), 'data' => $rows]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => exception_message($e)], 500);
}
