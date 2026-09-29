<?php
/**
 * customers/delete.php — delete one customer (POST only, no page is shown)
 *
 * Rules from the database design:
 *   - addresses of the customer are deleted automatically (ON DELETE CASCADE)
 *   - a customer who owns accounts CANNOT be deleted (accounts.customer_id has no cascade)
 *
 * Tables used: customers (DELETE), accounts (SELECT COUNT), addresses (cascade)
 */
require_once __DIR__ . '/../includes/helpers.php';

// Only a form submission may delete; opening this address in the browser does nothing.
if (!is_post()) {
    redirect('index.php');
}

$id = (int) posted('customer_id');

try {
    $customer = db_select_one('SELECT customer_id, full_name FROM customers WHERE customer_id = :id', [':id' => $id]);

    if ($customer === null) {
        flash_set('error', 'Customer not found.');
        redirect('index.php');
    }

    // Friendly rule check first (the foreign key would also stop the delete, but with a technical message)
    $accountCount = (int) db_scalar('SELECT COUNT(*) FROM accounts WHERE customer_id = :id', [':id' => $id]);
    if ($accountCount > 0) {
        flash_set('error', 'Customer "' . $customer['full_name'] . '" cannot be deleted because they own '
                         . $accountCount . ' account(s). Deactivating the customer instead is possible from the Edit page (Status).');
        redirect('index.php');
    }

    db_execute('DELETE FROM customers WHERE customer_id = :id', [':id' => $id]);
    flash_set('success', 'Customer "' . $customer['full_name'] . '" was deleted.');
} catch (Throwable $e) {
    // e.g. the customer is still referenced by another table (foreign key)
    flash_set('error', exception_message($e));
}

redirect('index.php');
