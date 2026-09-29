<?php
/**
 * customers/edit.php — Edit Customer   (opened as edit.php?id=5)
 *
 * Flow:
 *   GET   -> load the customer + present address, show the filled form
 *   POST  -> validate -> UPDATE customers + addresses (one DB transaction)
 *
 * The CIF number is never changed. KYC fields are left as they are.
 *
 * Tables used: customers (SELECT, UPDATE), addresses (SELECT, UPDATE / INSERT), branches (SELECT)
 */
require_once __DIR__ . '/_shared.php';

$pageTitle  = 'Edit Customer';
$activeMenu = 'customer_list';
$base       = '..';

$id = query_int('id');

// ---- load the customer (the row must exist) ----
$customer = db_select_one('SELECT * FROM customers WHERE customer_id = :id', [':id' => $id]);
if ($customer === null) {
    flash_set('error', 'Customer not found.');
    redirect('index.php');
}

// The present address (if the customer has none yet, the address fields start empty)
$address = db_select_one(
    "SELECT * FROM addresses WHERE customer_id = :id AND address_type = 'present' ORDER BY address_id LIMIT 1",
    [':id' => $id]
);

$errors = [];

if (is_post()) {
    $values = customer_read_input();
    $errors = customer_validate($values, $id);           // $id = ignore this customer's own unique values

    if (!$errors) {
        try {
            db_begin();

            db_execute(
                'UPDATE customers SET
                    full_name = :full_name, full_name_bn = :full_name_bn, father_name = :father_name,
                    mother_name = :mother_name, spouse_name = :spouse_name, date_of_birth = :date_of_birth,
                    gender = :gender, nationality = :nationality, profession = :profession,
                    monthly_income = :monthly_income, source_of_fund = :source_of_fund, tin_number = :tin_number,
                    phone = :phone, email = :email, id_type = :id_type, id_number = :id_number,
                    branch_id = :branch_id, status = :status
                 WHERE customer_id = :id',
                array_merge(customer_params($values), [':status' => $values['status'], ':id' => $id])
            );

            if ($address !== null) {
                db_execute(
                    'UPDATE addresses SET address_line = :address_line, post_office = :post_office, thana = :thana,
                            city = :city, district = :district, postal_code = :postal_code
                     WHERE address_id = :address_id',
                    array_merge(customer_address_params($values), [':address_id' => $address['address_id']])
                );
            } else {
                db_execute(
                    "INSERT INTO addresses
                        (customer_id, address_type, address_line, post_office, thana, city, district, postal_code, is_primary)
                     VALUES
                        (:customer_id, 'present', :address_line, :post_office, :thana, :city, :district, :postal_code, TRUE)",
                    array_merge(customer_address_params($values), [':customer_id' => $id])
                );
            }

            db_commit();

            flash_set('success', 'Customer "' . $values['full_name'] . '" was updated.');
            redirect('index.php');
        } catch (Throwable $e) {
            db_rollback();
            $errors[] = exception_message($e);
        }
    }
} else {
    // First visit: fill the form from the database (customers + addresses columns)
    $values = array_merge($customer, $address ?? []);
    $values['monthly_income'] = $customer['monthly_income'] ?? '';
}

$branches = db_select_all('SELECT branch_id, branch_name FROM branches ORDER BY branch_name');

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Customer</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Customer List</a>
</div>

<?php show_errors($errors); ?>
<?php customer_form($values, $branches, $id, $customer['cif_number']); ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
