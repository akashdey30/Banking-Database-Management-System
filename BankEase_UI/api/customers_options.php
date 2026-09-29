<?php
/**
 * api/customers_options.php — AJAX: customers for the "New Account" dropdown
 *
 * Request (GET):  ?q=salma       (optional filter; empty = newest customers)
 * Answer (JSON):  {"success": true, "data": [ {"id": 2, "label": "Salma Khatun (CIF 10052390)",
 *                                              "name": "Salma Khatun", "branch_id": 1}, ... ]}
 *
 * Only ACTIVE customers are returned: an account should not be opened for an
 * inactive or blocked customer.
 *
 * Tables used: customers (read-only)
 */
require_once __DIR__ . '/../includes/helpers.php';

$q = trim((string) ($_GET['q'] ?? ''));

try {
    $limit  = (int) AJAX_MAX_RESULTS;
    $sql    = "SELECT customer_id, cif_number, full_name, branch_id
               FROM customers
               WHERE status = 'active'";
    $params = [];

    if ($q !== '') {
        $sql .= ' AND (full_name LIKE :q1 OR cif_number LIKE :q2 OR phone LIKE :q3)';
        $like = '%' . $q . '%';
        $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
    }

    $sql .= ' ORDER BY full_name LIMIT ' . $limit;

    $data = [];
    foreach (db_select_all($sql, $params) as $row) {
        $data[] = [
            'id'        => (int) $row['customer_id'],
            'label'     => $row['full_name'] . ' (CIF ' . $row['cif_number'] . ')',
            'name'      => $row['full_name'],
            'branch_id' => (int) $row['branch_id'],
        ];
    }
    json_response(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => exception_message($e)], 500);
}
