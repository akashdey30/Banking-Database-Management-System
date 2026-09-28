<?php
/**
 * helpers.php — BankEase shared helper functions
 *
 * Included at the top of every page and API endpoint:
 *     require_once __DIR__ . '/helpers.php';      (from the project root)
 *     require_once __DIR__ . '/../helpers.php';   (from forms/, lists/, api/)
 *
 * Sections:
 *   1. Session and flash messages
 *   2. Output and input helpers
 *   3. Schema information (table keys and ENUM values from the DDL)
 *   4. Validation helpers
 *   5. Database error messages
 *   6. Dropdown (<select>) helpers
 *   7. Formatting helpers
 *   8. JSON responses (for the api/ endpoints)
 *   9. Transactions: reference numbers and balance updates
 *  10. Pagination
 */

require_once __DIR__ . '/db.php';

/* ==================================================================
 * 1. SESSION AND FLASH MESSAGES
 * ==================================================================
 * The session is used ONLY to carry a one-time success/error message
 * across a redirect (for example "Customer saved." after a form is
 * submitted). It is not used for login or access control.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Store a message to show on the next page.
 * $type is 'success' or 'error' (used as a CSS class).
 */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Print all stored flash messages, then forget them.
 */
function flash_show(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as $flash) {
        echo '<div class="message ' . e($flash['type']) . '">'
           . e($flash['message']) . '</div>' . "\n";
    }
    unset($_SESSION['flash']);
}

/* ==================================================================
 * 2. OUTPUT AND INPUT HELPERS
 * ================================================================== */

/**
 * Escape a value before printing it in HTML (prevents XSS).
 * Always use e() when echoing data from the database or from the user.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Send the browser to another page and stop this script.
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * True when the current request is a form submission (POST).
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Read one POST field as trimmed text ('' if missing).
 */
function posted(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

/**
 * Read one POST field, but return null when it is empty.
 * Use this for optional columns that allow NULL (for example
 * customers.spouse_name) so an empty box is saved as NULL, not ''.
 */
function posted_or_null(string $key): ?string
{
    $value = posted($key);
    return $value === '' ? null : $value;
}

/**
 * Read a whole-number value from the URL (?id=5). Returns 0 if missing/invalid.
 * Forms use this to decide between "create" (0) and "edit" (an id).
 */
function query_int(string $key): int
{
    return (int) filter_var($_GET[$key] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
}

/**
 * Print a form value safely, used to keep what the user typed.
 *   <input name="full_name" value="<?= old($values, 'full_name') ?>">
 */
function old(array $values, string $key): string
{
    return e($values[$key] ?? '');
}

/* ==================================================================
 * 3. SCHEMA INFORMATION (copied from the DDL)
 * ================================================================== */

/**
 * Primary key column of every table. Used by check_fk_exists() and
 * value_is_unique() so that table/column names can never come from user
 * input (only names listed here are accepted).
 */
function table_primary_keys(): array
{
    return [
        'roles'         => 'role_id',
        'branches'      => 'branch_id',
        'employees'     => 'employee_id',
        'customers'     => 'customer_id',
        'addresses'     => 'address_id',
        'users'         => 'user_id',
        'account_types' => 'account_type_id',
        'accounts'      => 'account_id',
        'transactions'  => 'transaction_id',
        'transfers'     => 'transfer_id',
        'beneficiaries' => 'beneficiary_id',
        'cards'         => 'card_id',
        'loans'         => 'loan_id',
        'loan_payments' => 'payment_id',
        'notifications' => 'notification_id',
        'audit_logs'    => 'audit_id',
    ];
}

/**
 * Allowed values of every ENUM column in the DDL.
 * The same lists feed the <select> dropdowns and the validation, so the
 * form can never accept a value that MySQL would reject.
 */
function enum_values(string $name): array
{
    $enums = [
        'branch_status'      => ['active', 'closed', 'under_renovation'],        // branches.status
        'employment_status'  => ['active', 'on_leave', 'terminated'],            // employees.employment_status
        'gender'             => ['male', 'female', 'other'],                     // customers.gender
        'id_type'            => ['nid', 'passport', 'birth_certificate', 'driving_license', 'other'], // customers.id_type
        'customer_status'    => ['active', 'inactive', 'blocked'],               // customers.status
        'kyc_status'         => ['pending', 'verified', 'expired', 'rejected'],  // customers.kyc_status
        'address_type'       => ['present', 'permanent'],                        // addresses.address_type
        'account_status'     => ['active', 'frozen', 'closed', 'pending'],       // accounts.status
        'transaction_type'   => ['deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'fee', 'interest'], // transactions.transaction_type
        'transaction_status' => ['pending', 'completed', 'failed', 'reversed'],  // transactions.status
        'transfer_type'      => ['internal', 'interbank'],                       // transfers.transfer_type
        'transfer_status'    => ['pending', 'completed', 'failed'],              // transfers.status
        'card_type'          => ['debit', 'credit'],                             // cards.card_type
        'card_status'        => ['active', 'blocked', 'expired', 'cancelled'],   // cards.status
        'loan_status'        => ['pending', 'approved', 'rejected', 'active', 'closed', 'defaulted'], // loans.status
        'payment_status'     => ['due', 'paid', 'overdue', 'partial'],           // loan_payments.status
    ];

    return $enums[$name] ?? [];
}

/* ==================================================================
 * 4. VALIDATION HELPERS
 * ==================================================================
 * Every check_* function receives the $errors array BY REFERENCE and adds
 * a message to it when the value is invalid. It returns true when the
 * value is valid. A form runs all its checks, and if $errors is not empty
 * it redisplays itself with show_errors($errors) at the top.
 */

/** The field must not be empty. */
function check_required(array &$errors, string $label, string $value): bool
{
    if (trim($value) === '') {
        $errors[] = $label . ' is required.';
        return false;
    }
    return true;
}

/** The text must not be longer than the column's VARCHAR size. */
function check_max_length(array &$errors, string $label, ?string $value, int $max): bool
{
    if ($value !== null && mb_strlen($value) > $max) {
        $errors[] = $label . ' must not be longer than ' . $max . ' characters.';
        return false;
    }
    return true;
}

/** Optional e-mail: an empty value is accepted, a filled one must look like an e-mail. */
function check_email(array &$errors, string $label, ?string $value): bool
{
    if ($value === null || $value === '') {
        return true;
    }
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $label . ' is not a valid e-mail address.';
        return false;
    }
    return true;
}

/** Phone number: digits, +, - and spaces, 6 to 20 characters (phone columns are VARCHAR(20)). */
function check_phone(array &$errors, string $label, string $value): bool
{
    if (!preg_match('/^[0-9+\-\s]{6,20}$/', $value)) {
        $errors[] = $label . ' must be 6-20 characters and contain only digits, +, - or spaces.';
        return false;
    }
    return true;
}

/** Date in YYYY-MM-DD format (what <input type="date"> sends) and a real calendar date. */
function check_date(array &$errors, string $label, string $value): bool
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        $errors[] = $label . ' must be a valid date (YYYY-MM-DD).';
        return false;
    }
    return true;
}

/**
 * Money amount with at most 2 decimal places (DECIMAL(x,2) columns).
 * $allowZero = false  -> the amount must be greater than 0
 * $allowZero = true   -> 0 is accepted as well
 * $maxIntDigits       -> digits before the decimal point (12 fits DECIMAL(14,2))
 */
function check_amount(array &$errors, string $label, string $value, bool $allowZero = false, int $maxIntDigits = 12): bool
{
    if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
        $errors[] = $label . ' must be a number with at most 2 decimal places.';
        return false;
    }
    $intPart = explode('.', $value)[0];
    if (strlen(ltrim($intPart, '0')) > $maxIntDigits) {
        $errors[] = $label . ' is too large.';
        return false;
    }
    if (!$allowZero && (float) $value <= 0) {
        $errors[] = $label . ' must be greater than 0.';
        return false;
    }
    return true;
}

/** Whole number between $min and $max (max = null means no upper limit). */
function check_int(array &$errors, string $label, string $value, int $min = 1, ?int $max = null): bool
{
    if (!preg_match('/^\d+$/', $value) || (int) $value < $min || ($max !== null && (int) $value > $max)) {
        $errors[] = $label . ' must be a whole number'
                  . ($max !== null ? ' between ' . $min . ' and ' . $max . '.' : ' of at least ' . $min . '.');
        return false;
    }
    return true;
}

/** The value must be one of the allowed values (used for ENUM columns). */
function check_in_list(array &$errors, string $label, string $value, array $allowed): bool
{
    if (!in_array($value, $allowed, true)) {
        $errors[] = $label . ' has an invalid value.';
        return false;
    }
    return true;
}

/**
 * Foreign key check: a row with this primary key must exist in $table.
 * Example: check_fk_exists($errors, 'Branch', 'branches', posted('branch_id'));
 */
function check_fk_exists(array &$errors, string $label, string $table, string $id): bool
{
    $keys = table_primary_keys();
    if (!isset($keys[$table]) || !ctype_digit($id) || (int) $id <= 0) {
        $errors[] = $label . ' must be selected.';
        return false;
    }
    $found = db_scalar(
        'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $keys[$table] . ' = :id',
        [':id' => (int) $id]
    );
    if ((int) $found === 0) {
        $errors[] = 'The selected ' . $label . ' does not exist.';
        return false;
    }
    return true;
}

/**
 * Is $value still unused in a UNIQUE column?
 *   value_is_unique('customers', 'cif_number', 'CIF-1001')        -> create
 *   value_is_unique('customers', 'cif_number', 'CIF-1001', $id)   -> edit (ignore own row)
 *
 * Only tables from table_primary_keys() and simple column names are accepted,
 * because the names are placed into the SQL text (values still use placeholders).
 */
function value_is_unique(string $table, string $column, string $value, int $excludeId = 0): bool
{
    $keys = table_primary_keys();
    if (!isset($keys[$table]) || !preg_match('/^[a-z_]+$/', $column)) {
        return false;
    }
    $count = db_scalar(
        'SELECT COUNT(*) FROM ' . $table
        . ' WHERE ' . $column . ' = :value AND ' . $keys[$table] . ' <> :id',
        [':value' => $value, ':id' => $excludeId]
    );
    return (int) $count === 0;
}

/**
 * Print all validation errors in one box (placed at the top of the form).
 * Prints nothing when the list is empty.
 */
function show_errors(array $errors): void
{
    if (empty($errors)) {
        return;
    }
    echo '<div class="message error"><strong>Please fix the following:</strong><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>' . "\n";
}

/* ==================================================================
 * 5. DATABASE ERROR MESSAGES
 * ================================================================== */

/**
 * Turn any exception into a message that is safe and readable on screen.
 *  - PDOException: MySQL error codes are translated (duplicate value,
 *    foreign key, CHECK constraint). This makes the relational rules from
 *    the ER diagram visible when a student demonstrates them.
 *  - Other exceptions (for example "Insufficient balance"): their own message.
 */
function exception_message(Throwable $e): string
{
    if (!($e instanceof PDOException)) {
        return $e->getMessage();
    }

    $code = (int) ($e->errorInfo[1] ?? 0);   // MySQL driver error code

    switch ($code) {
        case 1062:
            $message = 'A record with the same unique value already exists (duplicate).';
            break;
        case 1451:
            $message = 'This record cannot be deleted because other records depend on it (foreign key).';
            break;
        case 1452:
            $message = 'A selected related record does not exist (foreign key).';
            break;
        case 3819:
            $message = 'A value violates a database CHECK rule.';
            break;
        default:
            $message = 'A database error occurred.';
    }

    if (APP_DEBUG) {
        $message .= ' [' . $e->getMessage() . ']';
    }
    return $message;
}

/* ==================================================================
 * 6. DROPDOWN (<select>) HELPERS
 * ================================================================== */

/**
 * Build <option> tags from database rows (for foreign key dropdowns).
 *
 * $rows        rows returned by db_select_all()
 * $valueKey    column used as the option value (usually the primary key)
 * $labelKey    column shown to the user
 * $selected    the value that should be pre-selected
 *
 * Example:
 *   $branches = db_select_all('SELECT branch_id, branch_name FROM branches ORDER BY branch_name');
 *   echo options_html($branches, 'branch_id', 'branch_name', $values['branch_id'] ?? '');
 */
function options_html(array $rows, string $valueKey, string $labelKey, $selected = '', string $placeholder = '-- Select --'): string
{
    $html = '<option value="">' . e($placeholder) . '</option>';
    foreach ($rows as $row) {
        $isSelected = ((string) $row[$valueKey] === (string) $selected) ? ' selected' : '';
        $html .= '<option value="' . e($row[$valueKey]) . '"' . $isSelected . '>'
               . e($row[$labelKey]) . '</option>';
    }
    return $html;
}

/**
 * Build <option> tags from an ENUM list, e.g. enum_options_html(enum_values('gender'), $current).
 */
function enum_options_html(array $values, $selected = '', string $placeholder = '-- Select --'): string
{
    $html = ($placeholder === '') ? '' : '<option value="">' . e($placeholder) . '</option>';
    foreach ($values as $value) {
        $isSelected = ((string) $value === (string) $selected) ? ' selected' : '';
        $html .= '<option value="' . e($value) . '"' . $isSelected . '>'
               . e(pretty_enum($value)) . '</option>';
    }
    return $html;
}

/** 'under_renovation' becomes 'Under Renovation' (for display only). */
function pretty_enum(?string $value): string
{
    return ucwords(str_replace('_', ' ', (string) $value));
}

/* ==================================================================
 * 7. FORMATTING HELPERS
 * ================================================================== */

/** 77730.5 becomes 'BDT 77,730.50'. */
function format_money($amount): string
{
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

/** DATE column for display, using DATE_FORMAT from config.php ('-' when empty). */
function format_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '-';
    }
    return date(DATE_FORMAT, strtotime($value));
}

/** DATETIME column for display, using DATETIME_FORMAT from config.php ('-' when empty). */
function format_datetime(?string $value): string
{
    if ($value === null || $value === '') {
        return '-';
    }
    return date(DATETIME_FORMAT, strtotime($value));
}

/* ==================================================================
 * 8. JSON RESPONSES (for files in api/)
 * ================================================================== */

/**
 * Send a JSON response and stop.
 * Every api/*.php file ends by calling this, and assets/main.js reads the result with fetch().
 */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/* ==================================================================
 * 9. TRANSACTIONS: REFERENCE NUMBERS AND BALANCE UPDATES
 * ================================================================== */

/**
 * Create a unique value for transactions.reference_number (VARCHAR(30), UNIQUE).
 * Format: TXN + date/time + 4 random digits, e.g. TXN202609281530450123 (21 characters).
 * The loop checks the table so the same value is never returned twice.
 */
function generate_reference_number(): string
{
    do {
        $reference = 'TXN' . date('YmdHis') . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $exists = db_scalar(
            'SELECT COUNT(*) FROM transactions WHERE reference_number = :ref',
            [':ref' => $reference]
        );
    } while ((int) $exists > 0);

    return $reference;
}

/**
 * Post ONE transaction to an account and update the account balance.
 * This is the single place where balances change (the DDL has no triggers),
 * so deposit, withdrawal, transfer, loan disbursement and loan payment all use it.
 *
 * Parameters
 *   $accountId          accounts.account_id the money moves in or out of
 *   $type               one of the transactions.transaction_type ENUM values
 *                         adds money:     deposit, transfer_in, interest
 *                         removes money:  withdrawal, transfer_out, fee
 *   $amount             positive amount as text, e.g. '1500.00' (already validated)
 *   $description        stored in transactions.description (max 255 characters)
 *   $relatedAccountId   the other account for transfers (transactions.related_account_id)
 *
 * What it does, step by step
 *   1. Locks the account row (SELECT ... FOR UPDATE) so nobody changes it meanwhile.
 *   2. Checks the account is 'active' and, for money removed, that the balance is enough.
 *   3. Updates accounts.balance.
 *   4. Inserts the transactions row with balance_before and balance_after.
 *
 * Returns the new transaction_id. Throws an Exception with a readable message
 * when a rule fails. The CALLER must wrap it like this:
 *
 *   db_begin();
 *   try {
 *       $txnId = post_transaction($accountId, 'deposit', '500.00', 'Cash deposit');
 *       db_commit();
 *   } catch (Throwable $e) {
 *       db_rollback();
 *       $errors[] = exception_message($e);
 *   }
 */
function post_transaction(int $accountId, string $type, string $amount, string $description = '', ?int $relatedAccountId = null): int
{
    if (!db()->inTransaction()) {
        throw new RuntimeException('post_transaction() must be called between db_begin() and db_commit().');
    }

    $addsMoney    = ['deposit', 'transfer_in', 'interest'];
    $removesMoney = ['withdrawal', 'transfer_out', 'fee'];

    if (!in_array($type, $addsMoney, true) && !in_array($type, $removesMoney, true)) {
        throw new InvalidArgumentException('Unknown transaction type: ' . $type);
    }

    // Keep exactly two decimal places, e.g. '500' becomes '500.00'.
    $amount = number_format((float) $amount, 2, '.', '');

    // Step 1: read the account and lock the row until commit / rollback.
    $account = db_select_one(
        'SELECT balance, status FROM accounts WHERE account_id = :id FOR UPDATE',
        [':id' => $accountId]
    );
    if ($account === null) {
        throw new Exception('Account not found.');
    }

    // Step 2: business rules.
    if ($account['status'] !== 'active') {
        throw new Exception('The account is ' . $account['status'] . '. Only active accounts can be used for transactions.');
    }

    $balanceBefore = $account['balance'];
    $isDebit = in_array($type, $removesMoney, true);

    if ($isDebit && round((float) $balanceBefore, 2) < round((float) $amount, 2)) {
        throw new Exception('Insufficient balance. Available: ' . format_money($balanceBefore)
                          . ', requested: ' . format_money($amount) . '.');
    }

    // Step 3: change the balance. The sign comes from the fixed code above, never from user input.
    $sign = $isDebit ? '-' : '+';
    db_execute(
        'UPDATE accounts SET balance = balance ' . $sign . ' :amount WHERE account_id = :id',
        [':amount' => $amount, ':id' => $accountId]
    );
    $balanceAfter = db_scalar('SELECT balance FROM accounts WHERE account_id = :id', [':id' => $accountId]);

    // Step 4: write the transaction record (created_at uses the column default).
    return db_insert(
        'INSERT INTO transactions
            (reference_number, account_id, transaction_type, amount, balance_before, balance_after,
             related_account_id, description, status, performed_by_user_id)
         VALUES
            (:reference, :account_id, :type, :amount, :before, :after,
             :related, :description, :status, :user_id)',
        [
            ':reference'   => generate_reference_number(),
            ':account_id'  => $accountId,
            ':type'        => $type,
            ':amount'      => $amount,
            ':before'      => $balanceBefore,
            ':after'       => $balanceAfter,
            ':related'     => $relatedAccountId,
            ':description' => $description === '' ? null : mb_substr($description, 0, 255),
            ':status'      => 'completed',
            ':user_id'     => DEFAULT_USER_ID,
        ]
    );
}

/* ==================================================================
 * 10. PAGINATION
 * ================================================================== */

/** Current page number from ?page=N (minimum 1). */
function get_page(): int
{
    return max(1, (int) ($_GET['page'] ?? 1));
}

/**
 * Build Previous / page numbers / Next links for a list page.
 * Other URL parameters (like a search term) are kept.
 * Use with:  LIMIT RECORDS_PER_PAGE OFFSET ((get_page() - 1) * RECORDS_PER_PAGE)
 */
function pagination_html(int $totalRows): string
{
    $pages = (int) ceil($totalRows / RECORDS_PER_PAGE);
    if ($pages <= 1) {
        return '';
    }

    $current = min(get_page(), $pages);
    $html = '<div class="pagination">';

    for ($i = 1; $i <= $pages; $i++) {
        $url = '?' . http_build_query(array_merge($_GET, ['page' => $i]));
        if ($i === $current) {
            $html .= '<span class="current">' . $i . '</span> ';
        } else {
            $html .= '<a href="' . e($url) . '">' . $i . '</a> ';
        }
    }

    return $html . '</div>';
}