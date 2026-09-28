<?php
/**
 * index.php — BankEase home page
 *
 * Dashboard presentation for the existing BankEase database.
 * Database queries and existing links are preserved.
 */

require_once __DIR__ . '/helpers.php';

$pageTitle = 'Home';
$activeNav = 'home';
$basePath  = '';

$entities = [
    [
        'name'  => 'Branch',
        'table' => 'branches',
        'pk'    => 'branch_id',
        'fks'   => 'manager_employee_id → employees',
        'links' => ['Form' => 'forms/branch.php', 'List' => 'lists/branches.php'],
    ],
    [
        'name'  => 'Employee',
        'table' => 'employees',
        'pk'    => 'employee_id',
        'fks'   => 'branch_id → branches',
        'links' => ['Form' => 'forms/employee.php', 'List' => 'lists/employees.php'],
    ],
    [
        'name'  => 'Customer (with KYC details)',
        'table' => 'customers',
        'pk'    => 'customer_id',
        'fks'   => 'branch_id → branches; kyc_verified_by_employee_id → employees',
        'links' => ['Form' => 'forms/customer.php', 'List' => 'lists/customers.php'],
    ],
    [
        'name'  => 'Address',
        'table' => 'addresses',
        'pk'    => 'address_id',
        'fks'   => 'customer_id → customers',
        'links' => ['Form' => 'forms/address.php'],
    ],
    [
        'name'  => 'Account',
        'table' => 'accounts',
        'pk'    => 'account_id',
        'fks'   => 'customer_id → customers; account_type_id → account_types; branch_id → branches',
        'links' => ['Form' => 'forms/account.php', 'List' => 'lists/accounts.php'],
    ],
    [
        'name'  => 'Transaction',
        'table' => 'transactions',
        'pk'    => 'transaction_id',
        'fks'   => 'account_id → accounts; related_account_id → accounts; performed_by_user_id → users',
        'links' => [
            'Deposit'    => 'forms/deposit.php',
            'Withdrawal' => 'forms/withdrawal.php',
            'List'       => 'lists/transactions.php'
        ],
    ],
    [
        'name'  => 'Transfer',
        'table' => 'transfers',
        'pk'    => 'transfer_id',
        'fks'   => 'debit_transaction_id → transactions; credit_transaction_id → transactions',
        'links' => ['Form' => 'forms/transfer.php'],
    ],
    [
        'name'  => 'Beneficiary',
        'table' => 'beneficiaries',
        'pk'    => 'beneficiary_id',
        'fks'   => 'customer_id → customers; target_account_id → accounts',
        'links' => [
            'Form' => 'forms/beneficiary.php',
            'List' => 'lists/beneficiaries.php'
        ],
    ],
    [
        'name'  => 'Card',
        'table' => 'cards',
        'pk'    => 'card_id',
        'fks'   => 'customer_id → customers; account_id → accounts',
        'links' => [
            'Form' => 'forms/card.php',
            'List' => 'lists/cards.php'
        ],
    ],
    [
        'name'  => 'Loan',
        'table' => 'loans',
        'pk'    => 'loan_id',
        'fks'   => 'customer_id → customers; disbursement_account_id → accounts; approved_by_user_id → users',
        'links' => [
            'Form' => 'forms/loan.php',
            'List' => 'lists/loans.php'
        ],
    ],
    [
        'name'  => 'Loan Payment',
        'table' => 'loan_payments',
        'pk'    => 'payment_id',
        'fks'   => 'loan_id → loans; related_transaction_id → transactions',
        'links' => ['Form' => 'forms/loan_payment.php'],
    ],
];


/* =========================================================
   EXISTING DATABASE QUERIES
   ========================================================= */

$accountSummary = db_select_all(
    'SELECT branch_name, type_name, status, account_count, total_balance
     FROM v_account_status_summary
     ORDER BY branch_name, type_name, status'
);

$dailySummary = db_select_all(
    'SELECT txn_date, branch_name, txn_count, total_credits, total_debits
     FROM v_daily_transaction_summary
     ORDER BY txn_date DESC, branch_name
     LIMIT 10'
);

$cashActivity = db_select_all(
    'SELECT txn_date, branch_name, cash_in, cash_out
     FROM v_cash_activity
     ORDER BY txn_date DESC, branch_name
     LIMIT 10'
);


/* =========================================================
   ENTITY COUNTS
   ========================================================= */

$entityCounts = [];

foreach ($entities as $entity) {
    $entityCounts[$entity['table']] = (int) db_scalar(
        'SELECT COUNT(*) FROM ' . $entity['table']
    );
}


/* =========================================================
   EXISTING HEADER
   ========================================================= */

require __DIR__ . '/partials/header.php';
?>

<!--
    Explicit stylesheet load.

    This ensures the dashboard uses:
    /BankEase_UI/style.css

    even if the shared header does not currently include it.
-->
<link
    rel="stylesheet"
    href="/BankEase_UI/style.css"
>

<?php flash_show(); ?>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="dashboard-hero">

    <div class="dashboard-hero-content">

        <span class="dashboard-kicker">
            BANKING DATABASE MANAGEMENT SYSTEM
        </span>

        <h1 class="dashboard-title">
            Welcome to BankEase
        </h1>

        <p class="dashboard-subtitle">
            Manage branches, customers, accounts, transactions, cards and loans
            from one organized banking database interface.
        </p>

        <div class="dashboard-hero-actions">

            <a class="btn" href="forms/customer.php">
                + New Customer
            </a>

            <a class="btn secondary" href="forms/account.php">
                + New Account
            </a>

            <a class="dashboard-text-link" href="lists/accounts.php">
                View Accounts →
            </a>

        </div>

    </div>


    <div class="dashboard-hero-mark" aria-hidden="true">

        <div class="bank-icon">
            B
        </div>

        <span>
            BankEase
        </span>

    </div>

</section>



<!-- =========================================================
     SYSTEM OVERVIEW
     ========================================================= -->

<section class="dashboard-section">

    <div class="section-heading">

        <div>

            <h2>
                System Overview
            </h2>

            <p>
                Current records stored in the main banking entities.
            </p>

        </div>

    </div>


    <div class="dashboard-stats">


        <a class="stat-card" href="lists/branches.php">

            <div class="stat-icon">
                BR
            </div>

            <div>

                <span class="stat-label">
                    Branches
                </span>

                <strong>
                    <?= $entityCounts['branches'] ?? 0 ?>
                </strong>

            </div>

        </a>


        <a class="stat-card" href="lists/employees.php">

            <div class="stat-icon">
                EM
            </div>

            <div>

                <span class="stat-label">
                    Employees
                </span>

                <strong>
                    <?= $entityCounts['employees'] ?? 0 ?>
                </strong>

            </div>

        </a>


        <a class="stat-card" href="lists/customers.php">

            <div class="stat-icon">
                CU
            </div>

            <div>

                <span class="stat-label">
                    Customers
                </span>

                <strong>
                    <?= $entityCounts['customers'] ?? 0 ?>
                </strong>

            </div>

        </a>


        <a class="stat-card" href="lists/accounts.php">

            <div class="stat-icon">
                AC
            </div>

            <div>

                <span class="stat-label">
                    Accounts
                </span>

                <strong>
                    <?= $entityCounts['accounts'] ?? 0 ?>
                </strong>

            </div>

        </a>


        <a class="stat-card" href="lists/transactions.php">

            <div class="stat-icon">
                TX
            </div>

            <div>

                <span class="stat-label">
                    Transactions
                </span>

                <strong>
                    <?= $entityCounts['transactions'] ?? 0 ?>
                </strong>

            </div>

        </a>


        <a class="stat-card" href="lists/loans.php">

            <div class="stat-icon">
                LN
            </div>

            <div>

                <span class="stat-label">
                    Loans
                </span>

                <strong>
                    <?= $entityCounts['loans'] ?? 0 ?>
                </strong>

            </div>

        </a>


    </div>

</section>



<!-- =========================================================
     QUICK ACTIONS
     ========================================================= -->

<section class="dashboard-section">

    <div class="section-heading">

        <div>

            <h2>
                Quick Actions
            </h2>

            <p>
                Common banking operations and record management.
            </p>

        </div>

    </div>


    <div class="quick-actions-grid">


        <a class="quick-action" href="forms/deposit.php">

            <span class="quick-action-icon deposit-icon">
                +
            </span>

            <span>

                <strong>
                    Deposit
                </strong>

                <small>
                    Add funds to an account
                </small>

            </span>

            <span class="quick-arrow">
                →
            </span>

        </a>


        <a class="quick-action" href="forms/withdrawal.php">

            <span class="quick-action-icon withdrawal-icon">
                −
            </span>

            <span>

                <strong>
                    Withdrawal
                </strong>

                <small>
                    Withdraw funds from an account
                </small>

            </span>

            <span class="quick-arrow">
                →
            </span>

        </a>


        <a class="quick-action" href="forms/transfer.php">

            <span class="quick-action-icon transfer-icon">
                ↔
            </span>

            <span>

                <strong>
                    Transfer
                </strong>

                <small>
                    Move funds between accounts
                </small>

            </span>

            <span class="quick-arrow">
                →
            </span>

        </a>


        <a class="quick-action" href="forms/loan.php">

            <span class="quick-action-icon loan-icon">
                L
            </span>

            <span>

                <strong>
                    New Loan
                </strong>

                <small>
                    Create a loan record
                </small>

            </span>

            <span class="quick-arrow">
                →
            </span>

        </a>


    </div>

</section>



<!-- =========================================================
     DATABASE ENTITIES
     ========================================================= -->

<section class="dashboard-section">

    <div class="section-heading">

        <div>

            <h2>
                Database Entities
            </h2>

            <p>
                Access forms, lists and transaction operations for each entity.
            </p>

        </div>

    </div>


    <div class="table-wrap dashboard-entity-table">

        <table class="data-table">

            <thead>

                <tr>

                    <th>
                        Entity
                    </th>

                    <th>
                        Table
                    </th>

                    <th>
                        Primary Key
                    </th>

                    <th>
                        Rows
                    </th>

                    <th>
                        Available Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php foreach ($entities as $entity): ?>

                    <tr>

                        <td>

                            <strong>
                                <?= e($entity['name']) ?>
                            </strong>

                        </td>


                        <td class="mono">

                            <?= e($entity['table']) ?>

                        </td>


                        <td class="mono">

                            <?= e($entity['pk']) ?>

                            <span class="key-pk">
                                PK
                            </span>

                        </td>


                        <td class="num">

                            <span class="row-count">

                                <?= $entityCounts[$entity['table']] ?? 0 ?>

                            </span>

                        </td>


                        <td class="actions">

                            <?php foreach ($entity['links'] as $text => $page): ?>

                                <a
                                    class="btn btn-small"
                                    href="<?= e($page) ?>"
                                >
                                    <?= e($text) ?>
                                </a>

                            <?php endforeach; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>


    <div class="supporting-note">

        <strong>
            Supporting tables:
        </strong>

        <span class="mono">
            roles
        </span>,

        <span class="mono">
            users
        </span>,

        <span class="mono">
            account_types
        </span>,

        <span class="mono">
            notifications
        </span>,

        <span class="mono">
            audit_logs
        </span>.

        These are used through foreign-key relationships and do not have
        separate forms.

    </div>

</section>



<!-- =========================================================
     BANKING OPERATIONS
     ========================================================= -->

<section class="dashboard-section">

    <div class="section-heading">

        <div>

            <h2>
                Banking Operations
            </h2>

            <p>
                How the main financial operations affect the database.
            </p>

        </div>

    </div>


    <div class="operation-grid">


        <div class="operation-card">

            <span class="operation-number">
                01
            </span>

            <h3>
                Deposit
            </h3>

            <p>
                Creates a deposit transaction and adds the amount to the account balance.
            </p>

        </div>


        <div class="operation-card">

            <span class="operation-number">
                02
            </span>

            <h3>
                Withdrawal
            </h3>

            <p>
                Creates a withdrawal transaction and subtracts the amount from the account balance.
            </p>

        </div>


        <div class="operation-card">

            <span class="operation-number">
                03
            </span>

            <h3>
                Fund Transfer
            </h3>

            <p>
                Creates debit and credit transaction records and links them through a transfer record.
            </p>

        </div>


        <div class="operation-card">

            <span class="operation-number">
                04
            </span>

            <h3>
                Loan Disbursement
            </h3>

            <p>
                Creates the loan record and records the disbursement as a deposit transaction.
            </p>

        </div>


        <div class="operation-card">

            <span class="operation-number">
                05
            </span>

            <h3>
                Loan Repayment
            </h3>

            <p>
                Records a withdrawal transaction and links it to the corresponding loan payment.
            </p>

        </div>


    </div>

</section>



<!-- =========================================================
     REPORTS & INSIGHTS
     ========================================================= -->

<section class="dashboard-section reports-section">

    <div class="section-heading">

        <div>

            <h2>
                Reports & Insights
            </h2>

            <p>
                Live read-only summaries from the existing database views.
            </p>

        </div>

    </div>


    <!-- ACCOUNT OVERVIEW -->

    <div class="report-card">

        <div class="report-header">

            <div>

                <span class="report-kicker">
                    ACCOUNT OVERVIEW
                </span>

                <h3>
                    Account Status Summary
                </h3>

            </div>

            <span class="report-source">
                v_account_status_summary
            </span>

        </div>


        <div class="table-wrap">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Branch
                        </th>

                        <th>
                            Account Type
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Accounts
                        </th>

                        <th>
                            Total Balance
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (empty($accountSummary)): ?>

                        <tr class="empty-row">

                            <td colspan="5">
                                No accounts yet.
                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach ($accountSummary as $row): ?>

                        <tr>

                            <td>
                                <?= e($row['branch_name']) ?>
                            </td>

                            <td>
                                <?= e($row['type_name']) ?>
                            </td>

                            <td>
                                <?= e(pretty_enum($row['status'])) ?>
                            </td>

                            <td class="num">
                                <?= e($row['account_count']) ?>
                            </td>

                            <td class="num">
                                <?= e(format_money($row['total_balance'])) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>



    <!-- DAILY TRANSACTIONS + CASH FLOW -->

    <div class="report-grid">


        <!-- DAILY TRANSACTION SUMMARY -->

        <div class="report-card">

            <div class="report-header">

                <div>

                    <span class="report-kicker">
                        TRANSACTIONS
                    </span>

                    <h3>
                        Daily Transaction Summary
                    </h3>

                </div>

                <span class="report-source">
                    Latest 10 rows
                </span>

            </div>


            <div class="table-wrap">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Branch
                            </th>

                            <th>
                                Transactions
                            </th>

                            <th>
                                Credits
                            </th>

                            <th>
                                Debits
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($dailySummary)): ?>

                            <tr class="empty-row">

                                <td colspan="5">
                                    No transactions yet.
                                </td>

                            </tr>

                        <?php endif; ?>


                        <?php foreach ($dailySummary as $row): ?>

                            <tr>

                                <td>
                                    <?= e(format_date($row['txn_date'])) ?>
                                </td>

                                <td>
                                    <?= e($row['branch_name']) ?>
                                </td>

                                <td class="num">
                                    <?= e($row['txn_count']) ?>
                                </td>

                                <td class="num">
                                    <?= e(format_money($row['total_credits'])) ?>
                                </td>

                                <td class="num">
                                    <?= e(format_money($row['total_debits'])) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>



        <!-- CASH ACTIVITY -->

        <div class="report-card">

            <div class="report-header">

                <div>

                    <span class="report-kicker">
                        CASH FLOW
                    </span>

                    <h3>
                        Cash Activity
                    </h3>

                </div>

                <span class="report-source">
                    Latest 10 rows
                </span>

            </div>


            <div class="table-wrap">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Branch
                            </th>

                            <th>
                                Cash In
                            </th>

                            <th>
                                Cash Out
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($cashActivity)): ?>

                            <tr class="empty-row">

                                <td colspan="4">
                                    No cash activity yet.
                                </td>

                            </tr>

                        <?php endif; ?>


                        <?php foreach ($cashActivity as $row): ?>

                            <tr>

                                <td>
                                    <?= e(format_date($row['txn_date'])) ?>
                                </td>

                                <td>
                                    <?= e($row['branch_name']) ?>
                                </td>

                                <td class="num">
                                    <?= e(format_money($row['cash_in'])) ?>
                                </td>

                                <td class="num">
                                    <?= e(format_money($row['cash_out'])) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>


    </div>

</section>



<?php
/* =========================================================
   EXISTING FOOTER
   ========================================================= */

require __DIR__ . '/partials/footer.php';
?>