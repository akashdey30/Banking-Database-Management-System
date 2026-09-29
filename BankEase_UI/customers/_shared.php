<?php
/**
 * customers/_shared.php — code shared by create.php and edit.php
 *
 * (The file name starts with "_" to show it is a helper, not a page.)
 *
 * Contains:
 *   customer_read_input()   read every form field from $_POST
 *   customer_validate()     check the values, return a list of error messages
 *   customer_params()       turn the values into the array used by the SQL statements
 *   customer_form()         print the HTML form (used for both "new" and "edit")
 *
 * Tables used:
 *   customers  - personal / identity / KYC data
 *   addresses  - the customer's present address (one row, address_type = 'present')
 *   branches   - dropdown of branches (foreign key customers.branch_id)
 */
require_once __DIR__ . '/../includes/helpers.php';

/** Read all form fields (trimmed text; '' when empty). */
function customer_read_input(): array
{
    $fields = [
        // customers table
        'full_name', 'full_name_bn', 'father_name', 'mother_name', 'spouse_name',
        'date_of_birth', 'gender', 'nationality', 'profession', 'monthly_income',
        'source_of_fund', 'tin_number', 'phone', 'email', 'id_type', 'id_number',
        'branch_id', 'status',
        // addresses table
        'address_line', 'post_office', 'thana', 'city', 'district', 'postal_code',
    ];

    $values = [];
    foreach ($fields as $field) {
        $values[$field] = posted($field);
    }
    return $values;
}

/**
 * Validate the values. Returns a list of error messages (empty list = valid).
 * $excludeId is the customer being edited (0 when creating), so a customer
 * does not clash with their own phone number or ID number.
 */
function customer_validate(array $v, int $excludeId = 0): array
{
    $errors = [];

    // ---- names ----
    check_required($errors, 'Full name', $v['full_name'])     && check_max_length($errors, 'Full name', $v['full_name'], 100);
    check_max_length($errors, 'Full name (Bangla)', $v['full_name_bn'], 100);
    check_required($errors, "Father's name", $v['father_name']) && check_max_length($errors, "Father's name", $v['father_name'], 100);
    check_required($errors, "Mother's name", $v['mother_name']) && check_max_length($errors, "Mother's name", $v['mother_name'], 100);
    check_max_length($errors, "Spouse's name", $v['spouse_name'], 100);

    // ---- personal ----
    if (check_required($errors, 'Date of birth', $v['date_of_birth']) && check_date($errors, 'Date of birth', $v['date_of_birth'])) {
        if ($v['date_of_birth'] > date('Y-m-d')) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
    }
    check_in_list($errors, 'Gender', $v['gender'], enum_values('gender'));
    check_required($errors, 'Nationality', $v['nationality']) && check_max_length($errors, 'Nationality', $v['nationality'], 50);
    check_required($errors, 'Profession', $v['profession'])   && check_max_length($errors, 'Profession', $v['profession'], 100);
    if ($v['monthly_income'] !== '') {
        check_amount($errors, 'Monthly income', $v['monthly_income'], true, 10);   // DECIMAL(12,2): 10 digits before the point
    }
    check_required($errors, 'Source of fund', $v['source_of_fund']) && check_max_length($errors, 'Source of fund', $v['source_of_fund'], 255);

    // ---- contact (phone, e-mail and TIN are UNIQUE in the database) ----
    if (check_required($errors, 'Phone', $v['phone']) && check_phone($errors, 'Phone', $v['phone'])) {
        if (!value_is_unique('customers', 'phone', $v['phone'], $excludeId)) {
            $errors[] = 'This phone number is already registered to another customer.';
        }
    }
    if ($v['email'] !== '' && check_email($errors, 'E-mail', $v['email']) && check_max_length($errors, 'E-mail', $v['email'], 100)) {
        if (!value_is_unique('customers', 'email', $v['email'], $excludeId)) {
            $errors[] = 'This e-mail address is already registered to another customer.';
        }
    }
    if ($v['tin_number'] !== '' && check_max_length($errors, 'TIN', $v['tin_number'], 20)) {
        if (!value_is_unique('customers', 'tin_number', $v['tin_number'], $excludeId)) {
            $errors[] = 'This TIN is already registered to another customer.';
        }
    }

    // ---- identification (NID / passport ...) ----
    check_in_list($errors, 'ID type', $v['id_type'], enum_values('id_type'));
    if (check_required($errors, 'ID number', $v['id_number']) && check_max_length($errors, 'ID number', $v['id_number'], 50)) {
        if (!value_is_unique('customers', 'id_number', $v['id_number'], $excludeId)) {
            $errors[] = 'This ID number is already registered to another customer.';
        }
    }

    // ---- branch (foreign key) and status ----
    check_fk_exists($errors, 'Branch', 'branches', $v['branch_id']);
    if ($excludeId > 0) {                                   // status can only be changed when editing
        check_in_list($errors, 'Status', $v['status'], enum_values('customer_status'));
    }

    // ---- address (stored in the addresses table) ----
    check_required($errors, 'Address', $v['address_line']) && check_max_length($errors, 'Address', $v['address_line'], 255);
    check_max_length($errors, 'Post office', $v['post_office'], 100);
    check_max_length($errors, 'Thana', $v['thana'], 100);
    check_required($errors, 'City', $v['city'])         && check_max_length($errors, 'City', $v['city'], 100);
    check_required($errors, 'District', $v['district']) && check_max_length($errors, 'District', $v['district'], 100);
    check_max_length($errors, 'Postal code', $v['postal_code'], 20);

    return $errors;
}

/** Empty text becomes NULL, so optional columns are stored as NULL and not as ''. */
function customer_null(string $value): ?string
{
    return $value === '' ? null : $value;
}

/** Values for the customers table, keyed by the :placeholder names used in the SQL. */
function customer_params(array $v): array
{
    return [
        ':full_name'      => $v['full_name'],
        ':full_name_bn'   => customer_null($v['full_name_bn']),
        ':father_name'    => $v['father_name'],
        ':mother_name'    => $v['mother_name'],
        ':spouse_name'    => customer_null($v['spouse_name']),
        ':date_of_birth'  => $v['date_of_birth'],
        ':gender'         => $v['gender'],
        ':nationality'    => $v['nationality'],
        ':profession'     => $v['profession'],
        ':monthly_income' => customer_null($v['monthly_income']),
        ':source_of_fund' => $v['source_of_fund'],
        ':tin_number'     => customer_null($v['tin_number']),
        ':phone'          => $v['phone'],
        ':email'          => customer_null($v['email']),
        ':id_type'        => $v['id_type'],
        ':id_number'      => $v['id_number'],
        ':branch_id'      => (int) $v['branch_id'],
    ];
}

/** Values for the addresses table. */
function customer_address_params(array $v): array
{
    return [
        ':address_line' => $v['address_line'],
        ':post_office'  => customer_null($v['post_office']),
        ':thana'        => customer_null($v['thana']),
        ':city'         => $v['city'],
        ':district'     => $v['district'],
        ':postal_code'  => customer_null($v['postal_code']),
    ];
}

/** Print one text-like input inside a Bootstrap column. */
function customer_field(string $label, string $name, array $v, array $o = []): void
{
    $type     = $o['type'] ?? 'text';
    $col      = $o['col'] ?? 'col-md-6';
    $required = !empty($o['required']);
    $attrs    = $o['attrs'] ?? '';                 // extra attributes written by us, never by the user

    echo '<div class="' . e($col) . '">';
    echo '<label class="form-label" for="' . e($name) . '">' . e($label)
       . ($required ? ' <span class="required">*</span>' : '') . '</label>';
    echo '<input class="form-control" type="' . e($type) . '" id="' . e($name) . '" name="' . e($name)
       . '" value="' . old($v, $name) . '"' . ($required ? ' required' : '') . ' ' . $attrs . '>';
    echo '</div>';
}

/**
 * Print the whole customer form.
 *   $v          current values (from $_POST or from the database)
 *   $branches   rows from: SELECT branch_id, branch_name FROM branches
 *   $editId     0 = new customer, otherwise the customer_id being edited
 *   $cif        the CIF number to show when editing
 */
function customer_form(array $v, array $branches, int $editId = 0, string $cif = ''): void
{
    $isEdit  = $editId > 0;
    $exclude = 'data-exclude-id="' . $editId . '" data-base=".."';
    ?>
    <form method="post" class="card shadow-sm" novalidate>
        <div class="card-body">

            <h2 class="h6 text-uppercase text-muted mb-3">Personal details</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">CIF number</label>
                    <input class="form-control" type="text" disabled
                           value="<?= $isEdit ? e($cif) : 'Generated automatically when saved' ?>">
                </div>
                <div class="col-md-6"></div>
                <?php
                customer_field('Full name', 'full_name', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field('Full name (Bangla)', 'full_name_bn', $v, ['attrs' => 'maxlength="100"']);
                customer_field("Father's name", 'father_name', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field("Mother's name", 'mother_name', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field("Spouse's name", 'spouse_name', $v, ['attrs' => 'maxlength="100"']);
                customer_field('Date of birth', 'date_of_birth', $v, ['type' => 'date', 'required' => true, 'attrs' => 'max="' . date('Y-m-d') . '"']);
                ?>
                <div class="col-md-6">
                    <label class="form-label" for="gender">Gender <span class="required">*</span></label>
                    <select class="form-select" id="gender" name="gender" required>
                        <?= enum_options_html(enum_values('gender'), $v['gender'] ?? '') ?>
                    </select>
                </div>
                <?php
                customer_field('Nationality', 'nationality', $v, ['required' => true, 'attrs' => 'maxlength="50"']);
                customer_field('Profession', 'profession', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field('Monthly income (BDT)', 'monthly_income', $v, ['type' => 'number', 'attrs' => 'min="0" step="0.01"']);
                customer_field('Source of fund', 'source_of_fund', $v, ['required' => true, 'attrs' => 'maxlength="255"']);
                ?>
            </div>

            <h2 class="h6 text-uppercase text-muted mb-3">Contact and identification</h2>
            <div class="row g-3 mb-4">
                <?php
                // data-check-unique makes app.js ask api/check_unique.php when the user leaves the field (AJAX)
                customer_field('Phone', 'phone', $v, ['required' => true,
                    'attrs' => 'maxlength="20" data-check-unique="customers.phone" ' . $exclude]);
                customer_field('E-mail', 'email', $v, ['type' => 'email',
                    'attrs' => 'maxlength="100" data-check-unique="customers.email" ' . $exclude]);
                ?>
                <div class="col-md-6">
                    <label class="form-label" for="id_type">ID type <span class="required">*</span></label>
                    <select class="form-select" id="id_type" name="id_type" required>
                        <?= enum_options_html(enum_values('id_type'), $v['id_type'] ?? 'nid') ?>
                    </select>
                </div>
                <?php
                customer_field('NID / ID number', 'id_number', $v, ['required' => true,
                    'attrs' => 'maxlength="50" data-check-unique="customers.id_number" ' . $exclude]);
                customer_field('TIN', 'tin_number', $v, [
                    'attrs' => 'maxlength="20" data-check-unique="customers.tin_number" ' . $exclude]);
                ?>
            </div>

            <h2 class="h6 text-uppercase text-muted mb-3">Present address</h2>
            <div class="row g-3 mb-4">
                <?php
                customer_field('Address (road / village)', 'address_line', $v, ['col' => 'col-12', 'required' => true, 'attrs' => 'maxlength="255"']);
                customer_field('Post office', 'post_office', $v, ['attrs' => 'maxlength="100"']);
                customer_field('Thana', 'thana', $v, ['attrs' => 'maxlength="100"']);
                customer_field('City', 'city', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field('District', 'district', $v, ['required' => true, 'attrs' => 'maxlength="100"']);
                customer_field('Postal code', 'postal_code', $v, ['attrs' => 'maxlength="20"']);
                ?>
            </div>

            <h2 class="h6 text-uppercase text-muted mb-3">Bank details</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="branch_id">Branch <span class="required">*</span></label>
                    <select class="form-select" id="branch_id" name="branch_id" required>
                        <?= options_html($branches, 'branch_id', 'branch_name', $v['branch_id'] ?? '') ?>
                    </select>
                </div>
                <?php if ($isEdit): ?>
                <div class="col-md-6">
                    <label class="form-label" for="status">Status <span class="required">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <?= enum_options_html(enum_values('customer_status'), $v['status'] ?? 'active', '') ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Save customer' ?></button>
            <a class="btn btn-outline-secondary" href="index.php">Cancel</a>
        </div>
    </form>
    <?php
}
