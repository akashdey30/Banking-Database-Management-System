<?php
/**
 * customers/create.php — New Customer
 *
 * Flow:
 *   GET   -> show an empty form
 *   POST  -> validate -> INSERT into customers + addresses (one DB transaction)
 *            -> success: redirect to the list with a message
 *            -> failure: show the form again with the errors and the typed values
 *
 * Tables used: customers (INSERT), addresses (INSERT), branches (SELECT for the dropdown)
 */
require_once __DIR__ . '/_shared.php';

$pageTitle  = 'New Customer';
$activeMenu = 'customer_new';
$base       = '..';

$errors = [];
$values = ['nationality' => 'Bangladeshi', 'id_type' => 'nid'];     // default values for an empty form

if (is_post()) {
    $values = customer_read_input();
    $errors = customer_validate($values);

    if (!$errors) {
        try {
            db_begin();

            // 1. the customer row (CIF number is generated; status and KYC use the column defaults)
            $cif = generate_cif_number();
            $customerId = db_insert(
                'INSERT INTO customers
                    (cif_number, full_name, full_name_bn, father_name, mother_name, spouse_name,
                     date_of_birth, gender, nationality, profession, monthly_income, source_of_fund,
                     tin_number, phone, email, id_type, id_number, branch_id)
                 VALUES
                    (:cif, :full_name, :full_name_bn, :father_name, :mother_name, :spouse_name,
                     :date_of_birth, :gender, :nationality, :profession, :monthly_income, :source_of_fund,
                     :tin_number, :phone, :email, :id_type, :id_number, :branch_id)',
                array_merge([':cif' => $cif], customer_params($values))
            );

            // 2. the address row, linked to the new customer (addresses.customer_id)
            db_execute(
                "INSERT INTO addresses
                    (customer_id, address_type, address_line, post_office, thana, city, district, postal_code, is_primary)
                 VALUES
                    (:customer_id, 'present', :address_line, :post_office, :thana, :city, :district, :postal_code, TRUE)",
                array_merge([':customer_id' => $customerId], customer_address_params($values))
            );

            db_commit();       // both rows saved together

            flash_set('success', 'Customer "' . $values['full_name'] . '" was saved. CIF number: ' . $cif . '.');
            redirect('index.php');
        } catch (Throwable $e) {
            db_rollback();     // neither row is kept
            $errors[] = exception_message($e);
        }
    }
}

$branches = db_select_all('SELECT branch_id, branch_name FROM branches ORDER BY branch_name');

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Customer</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Customer List</a>
</div>

<?php show_errors($errors); ?>
<?php customer_form($values, $branches); ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
