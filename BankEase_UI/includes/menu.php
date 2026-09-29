<?php
/**
 * menu.php — the list of every BankEase module
 *
 * The sidebar (includes/header.php) and the dashboard cards (dashboard/index.php)
 * both read this list, so a module is described in ONE place only.
 *
 * Each item has:
 *   key          short name, compared with $activeMenu on each page
 *   label        text shown to the user
 *   url          path from the project root (the page adds $base in front)
 *   description  one line shown on the dashboard card
 *   implemented  true  = fully working module
 *                false = placeholder page ("not yet implemented")
 *   group        section title used to group the sidebar
 *
 * To switch a placeholder into a real module later, build the page and
 * change 'implemented' to true. Nothing else needs to change.
 */

function menu_items(): array
{
    return [
        ['key' => 'dashboard',    'label' => 'Dashboard',           'url' => 'dashboard/index.php',        'description' => 'Overview of the system',                     'implemented' => true,  'group' => 'Main'],

        ['key' => 'customer_new',  'label' => 'New Customer',        'url' => 'customers/create.php',       'description' => 'Register a new customer (KYC details)',      'implemented' => true,  'group' => 'Customers'],
        ['key' => 'customer_list', 'label' => 'Customer List',       'url' => 'customers/index.php',        'description' => 'Search, edit and delete customers',          'implemented' => true,  'group' => 'Customers'],

        ['key' => 'account_new',   'label' => 'New Account',         'url' => 'accounts/create.php',        'description' => 'Open a bank account for a customer',         'implemented' => true,  'group' => 'Accounts'],
        ['key' => 'account_list',  'label' => 'Account List',        'url' => 'accounts/index.php',         'description' => 'View, edit and deactivate accounts',         'implemented' => true,  'group' => 'Accounts'],

        ['key' => 'deposit',       'label' => 'Deposit',             'url' => 'transactions/deposit.php',   'description' => 'Add money to an account',                    'implemented' => true,  'group' => 'Transactions'],
        ['key' => 'withdraw',      'label' => 'Withdraw',            'url' => 'transactions/withdraw.php',  'description' => 'Take money out of an account',               'implemented' => true,  'group' => 'Transactions'],
        ['key' => 'transfer',      'label' => 'Transfer',            'url' => 'transactions/transfer.php',  'description' => 'Move money between two accounts',            'implemented' => true,  'group' => 'Transactions'],
        ['key' => 'history',       'label' => 'Transaction History', 'url' => 'transactions/history.php',   'description' => 'List of all past transactions',              'implemented' => false, 'group' => 'Transactions'],

        ['key' => 'branches',      'label' => 'Branch Management',   'url' => 'branches/index.php',         'description' => 'Manage bank branches',                       'implemented' => false, 'group' => 'Administration'],
        ['key' => 'employees',     'label' => 'Employee Management', 'url' => 'employees/index.php',        'description' => 'Manage bank employees',                      'implemented' => false, 'group' => 'Administration'],
        ['key' => 'loans',         'label' => 'Loan Management',     'url' => 'loans/index.php',            'description' => 'Loan applications and payments',             'implemented' => false, 'group' => 'Administration'],
        ['key' => 'reports',       'label' => 'Reports',             'url' => 'reports/index.php',          'description' => 'Summary and statistical reports',            'implemented' => false, 'group' => 'Administration'],
        ['key' => 'settings',      'label' => 'Settings',            'url' => 'settings/index.php',         'description' => 'System settings',                            'implemented' => false, 'group' => 'Administration'],
    ];
}
