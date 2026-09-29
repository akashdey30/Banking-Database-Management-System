<?php
/**
 * accounts/edit.php — Edit Account   (opened as edit.php?id=7)
 *
 * Editable:  account title, branch, status (active / pending / frozen)
 * Read-only: account number, customer, type, balance, opening date
 *            (the balance must only change through transactions)
 *
 * The UI never sets 'closed': closing accounts permanently is not part of this version.
 * A frozen account is re-activated by choosing status "Active" here.
 *
 * Tables used: accounts (SELECT, UPDATE), customers, account_types, branches (SELECT)
 */
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle  = 'Edit Account';
$activeMenu = 'account_list';
$base       = '..';

$id = query_int('id');

$account = db_select_one(
    'SELECT a.*, c.full_name AS customer_name, t.type_name
     FROM accounts a
     JOIN customers c     ON c.customer_id = a.customer_id
     JOIN account_types t ON t.account_type_id = a.account_type_id
     WHERE a.account_id = :id',
    [':id' => $id]
);

if ($account === null) {
    flash_set('error', 'Account not found.');
    redirect('index.php');
}
if ($account['status'] === 'closed') {
    flash_set('error', 'Account ' . $account['account_number'] . ' is closed and cannot be edited.');
    redirect('index.php');
}

$editableStatuses = ['active', 'pending', 'frozen'];
$errors = [];
$values = [
    'account_title' => $account['account_title'],
    'branch_id'     => $account['branch_id'],
    'status'        => $account['status'],
];

if (is_post()) {
    $values = [
        'account_title' => posted('account_title'),
        'branch_id'     => posted('branch_id'),
        'status'        => posted('status'),
    ];

    check_required($errors, 'Account title', $values['account_title']) && check_max_length($errors, 'Account title', $values['account_title'], 150);
    check_fk_exists($errors, 'Branch', 'branches', $values['branch_id']);
    check_in_list($errors, 'Status', $values['status'], $editableStatuses);

    if (!$errors) {
        try {
            db_execute(
                'UPDATE accounts SET account_title = :title, branch_id = :branch, status = :status WHERE account_id = :id',
                [
                    ':title'  => $values['account_title'],
                    ':branch' => (int) $values['branch_id'],
                    ':status' => $values['status'],
                    ':id'     => $id,
                ]
            );
            flash_set('success', 'Account ' . $account['account_number'] . ' was updated.');
            redirect('index.php');
        } catch (Throwable $e) {
            $errors[] = exception_message($e);
        }
    }
}

$branches = db_select_all('SELECT branch_id, branch_name FROM branches ORDER BY branch_name');

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Edit Account</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Account List</a>
</div>

<?php show_errors($errors); ?>

<form method="post" class="card shadow-sm" novalidate>
    <div class="card-body">
        <div class="row g-3">

            <!-- Read-only information -->
            <div class="col-md-6">
                <label class="form-label">Account number</label>
                <input class="form-control" type="text" disabled value="<?= e($account['account_number']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Customer</label>
                <input class="form-control" type="text" disabled value="<?= e($account['customer_name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Account type</label>
                <input class="form-control" type="text" disabled value="<?= e($account['type_name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Current balance</label>
                <input class="form-control" type="text" disabled value="<?= e(format_money($account['balance'])) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Opening date</label>
                <input class="form-control" type="text" disabled value="<?= e(format_date($account['opening_date'])) ?>">
            </div>

            <!-- Editable fields -->
            <div class="col-md-6">
                <label class="form-label" for="account_title">Account title <span class="required">*</span></label>
                <input class="form-control" type="text" id="account_title" name="account_title" maxlength="150" required
                       value="<?= old($values, 'account_title') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="branch_id">Branch <span class="required">*</span></label>
                <select class="form-select" id="branch_id" name="branch_id" required>
                    <?= options_html($branches, 'branch_id', 'branch_name', $values['branch_id']) ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status <span class="required">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <?= enum_options_html($editableStatuses, $values['status'], '') ?>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a class="btn btn-outline-secondary" href="index.php">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
