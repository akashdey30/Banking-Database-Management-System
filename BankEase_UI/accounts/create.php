<?php
/**
 * accounts/create.php — New Account
 *
 * Flow:
 *   GET   -> empty form; the customer dropdown is filled by AJAX (assets/js/accounts.js)
 *   POST  -> validate -> INSERT the account (balance 0) -> if an initial deposit was
 *            entered, post it as a normal 'deposit' transaction -> COMMIT
 *            Any failure -> ROLLBACK, so no half-created account is left behind.
 *
 * Business rules taken from the database design:
 *   - only ACTIVE customers can get an account
 *   - account_types.single_per_customer = TRUE -> the customer may hold only one such account
 *   - an ACTIVE account must start with at least account_types.minimum_balance
 *
 * Tables used: accounts (INSERT), transactions (INSERT via post_transaction),
 *              customers, account_types, branches (SELECT for dropdowns / checks)
 */
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle    = 'New Account';
$activeMenu   = 'account_new';
$base         = '..';
$extraScripts = ['accounts.js'];

$errors = [];
$values = ['opening_date' => date('Y-m-d'), 'initial_deposit' => '0', 'status' => 'active'];

if (is_post()) {
    $values = [
        'account_number'  => posted('account_number'),
        'customer_id'     => posted('customer_id'),
        'account_title'   => posted('account_title'),
        'account_type_id' => posted('account_type_id'),
        'branch_id'       => posted('branch_id'),
        'opening_date'    => posted('opening_date'),
        'initial_deposit' => posted('initial_deposit'),
        'status'          => posted('status'),
    ];

    // ---------- validation ----------
    $customerOk = check_fk_exists($errors, 'Customer', 'customers', $values['customer_id']);
    if ($customerOk) {
        $customerStatus = db_scalar('SELECT status FROM customers WHERE customer_id = :id', [':id' => (int) $values['customer_id']]);
        if ($customerStatus !== 'active') {
            $errors[] = 'Accounts can only be opened for active customers (this customer is ' . $customerStatus . ').';
            $customerOk = false;
        }
    }

    $typeOk = check_fk_exists($errors, 'Account type', 'account_types', $values['account_type_id']);
    check_fk_exists($errors, 'Branch', 'branches', $values['branch_id']);

    check_required($errors, 'Account title', $values['account_title']) && check_max_length($errors, 'Account title', $values['account_title'], 150);

    if ($values['account_number'] !== '') {                      // blank = generate automatically
        if (!preg_match('/^[A-Za-z0-9]{6,20}$/', $values['account_number'])) {
            $errors[] = 'Account number must be 6-20 letters or digits.';
        } elseif (!value_is_unique('accounts', 'account_number', $values['account_number'])) {
            $errors[] = 'This account number already exists.';
        }
    }

    if (check_required($errors, 'Opening date', $values['opening_date']) && check_date($errors, 'Opening date', $values['opening_date'])) {
        if ($values['opening_date'] > date('Y-m-d')) {
            $errors[] = 'Opening date cannot be in the future.';
        }
    }

    check_in_list($errors, 'Status', $values['status'], ['active', 'pending']);
    $depositOk = check_amount($errors, 'Initial deposit', $values['initial_deposit'], true);

    // Rules that depend on the account type
    if ($typeOk && $depositOk) {
        $type = db_select_one('SELECT type_name, minimum_balance, single_per_customer FROM account_types WHERE account_type_id = :id',
                              [':id' => (int) $values['account_type_id']]);

        if ($values['status'] === 'active' && (float) $values['initial_deposit'] < (float) $type['minimum_balance']) {
            $errors[] = 'A ' . $type['type_name'] . ' needs an initial deposit of at least ' . format_money($type['minimum_balance']) . '.';
        }
        if ($values['status'] === 'pending' && (float) $values['initial_deposit'] > 0) {
            $errors[] = 'An initial deposit can only be recorded on an active account. Set the status to Active or use a deposit of 0.';
        }
        if ($customerOk && (int) $type['single_per_customer'] === 1) {
            $already = (int) db_scalar(
                "SELECT COUNT(*) FROM accounts WHERE customer_id = :c AND account_type_id = :t AND status <> 'closed'",
                [':c' => (int) $values['customer_id'], ':t' => (int) $values['account_type_id']]
            );
            if ($already > 0) {
                $errors[] = 'This customer already has a ' . $type['type_name'] . ' (only one is allowed per customer).';
            }
        }
    }

    // ---------- save ----------
    if (!$errors) {
        try {
            db_begin();

            $number = $values['account_number'] !== '' ? $values['account_number'] : generate_account_number((int) $values['branch_id']);

            // Step 1: the account starts with balance 0 (the deposit below adds the money)
            $accountId = db_insert(
                'INSERT INTO accounts (account_number, account_title, customer_id, account_type_id, branch_id, balance, status, opening_date)
                 VALUES (:number, :title, :customer, :type, :branch, 0, :status, :opening)',
                [
                    ':number'   => $number,
                    ':title'    => $values['account_title'],
                    ':customer' => (int) $values['customer_id'],
                    ':type'     => (int) $values['account_type_id'],
                    ':branch'   => (int) $values['branch_id'],
                    ':status'   => $values['status'],
                    ':opening'  => $values['opening_date'],
                ]
            );

            // Step 2: initial deposit = a normal deposit transaction (updates the balance + writes history)
            if ((float) $values['initial_deposit'] > 0) {
                post_transaction($accountId, 'deposit', $values['initial_deposit'], 'Initial deposit');
            }

            db_commit();

            flash_set('success', 'Account ' . $number . ' was opened for "' . $values['account_title'] . '".');
            redirect('index.php');
        } catch (Throwable $e) {
            db_rollback();
            $errors[] = exception_message($e);
        }
    }
}

// ---------- data for the dropdowns ----------
$accountTypes = db_select_all(
    "SELECT account_type_id, CONCAT(type_name, ' (minimum balance ', FORMAT(minimum_balance, 2), ')') AS label
     FROM account_types ORDER BY type_name"
);
$branches = db_select_all('SELECT branch_id, branch_name FROM branches ORDER BY branch_name');

// If the form is shown again after an error, keep the chosen customer in the dropdown.
$selectedCustomer = null;
if (!empty($values['customer_id']) && ctype_digit((string) $values['customer_id'])) {
    $selectedCustomer = db_select_one(
        'SELECT customer_id, full_name, cif_number, branch_id FROM customers WHERE customer_id = :id',
        [':id' => (int) $values['customer_id']]
    );
}

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">New Account</h1>
    <a class="btn btn-outline-secondary btn-sm" href="index.php">Account List</a>
</div>

<?php show_errors($errors); ?>

<form method="post" class="card shadow-sm" novalidate>
    <div class="card-body">
        <div class="row g-3">

            <!-- Customer: the dropdown is filled by AJAX (accounts.js -> api/customers_options.php) -->
            <div class="col-md-6">
                <label class="form-label" for="customer-filter">Find customer</label>
                <input type="search" id="customer-filter" class="form-control" autocomplete="off"
                       placeholder="Type a name, CIF number or phone...">
                <div class="form-text">Type to narrow the list below. Only active customers are shown.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="customer_id">Customer <span class="required">*</span></label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                    <?php if ($selectedCustomer): ?>
                        <option value="<?= e($selectedCustomer['customer_id']) ?>" selected
                                data-name="<?= e($selectedCustomer['full_name']) ?>"
                                data-branch="<?= e($selectedCustomer['branch_id']) ?>">
                            <?= e($selectedCustomer['full_name']) ?> (CIF <?= e($selectedCustomer['cif_number']) ?>)
                        </option>
                    <?php else: ?>
                        <option value="">Loading customers...</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="account_number">Account number</label>
                <input class="form-control" type="text" id="account_number" name="account_number" maxlength="20"
                       value="<?= old($values, 'account_number') ?>" placeholder="Leave blank to generate automatically"
                       data-check-unique="accounts.account_number" data-exclude-id="0" data-base="..">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="account_title">Account title <span class="required">*</span></label>
                <input class="form-control" type="text" id="account_title" name="account_title" maxlength="150" required
                       value="<?= old($values, 'account_title') ?>">
                <div class="form-text">Filled with the customer's name; you can change it.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="account_type_id">Account type <span class="required">*</span></label>
                <select class="form-select" id="account_type_id" name="account_type_id" required>
                    <?= options_html($accountTypes, 'account_type_id', 'label', $values['account_type_id'] ?? '') ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="branch_id">Branch <span class="required">*</span></label>
                <select class="form-select" id="branch_id" name="branch_id" required>
                    <?= options_html($branches, 'branch_id', 'branch_name', $values['branch_id'] ?? '') ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="opening_date">Opening date <span class="required">*</span></label>
                <input class="form-control" type="date" id="opening_date" name="opening_date" required
                       max="<?= date('Y-m-d') ?>" value="<?= old($values, 'opening_date') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="initial_deposit">Initial deposit (BDT)</label>
                <input class="form-control" type="number" id="initial_deposit" name="initial_deposit"
                       min="0" step="0.01" value="<?= old($values, 'initial_deposit') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Status <span class="required">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <?= enum_options_html(['active', 'pending'], $values['status'] ?? 'active', '') ?>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">Open account</button>
        <a class="btn btn-outline-secondary" href="index.php">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
