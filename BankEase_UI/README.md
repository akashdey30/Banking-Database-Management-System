# BankEase - Database Systems course project (Group 19)

PHP + MySQL + Bootstrap 5 (local copy, works offline) + AJAX (vanilla JavaScript).

## Functional modules
Dashboard - New Customer (+ list, search, edit, delete) - New Account (+ list, edit, deactivate) - Deposit - Withdraw - Transfer.
Every other module (Transaction History, Branch, Employee, Loan, Reports, Settings) has its page and menu entry and shows
"This module is not yet implemented in the current project version."

## Setup (XAMPP)
1. Start Apache and MySQL in the XAMPP control panel.
2. Import `database/bankease.sql` (phpMyAdmin -> Import). It creates the `bankease` database with the tables and sample data.
3. Copy this folder to `C:\xampp\htdocs\BankEase`.
4. Open http://localhost/BankEase/ (the database settings are in `config/config.php`; XAMPP defaults are used).

## Folder structure
```
index.php            redirects to the dashboard
dashboard/           Dashboard
customers/           create.php  index.php  edit.php  delete.php  (_shared.php = validation + form)
accounts/            create.php  index.php  edit.php  deactivate.php
transactions/        deposit.php  withdraw.php  transfer.php  (_shared.php)   history.php = placeholder
branches/ employees/ loans/ reports/ settings/     placeholders
api/                 AJAX endpoints (JSON): customers_search, customers_options, account_lookup,
                     check_unique, transaction_post
includes/            helpers.php, header.php, footer.php, menu.php (all modules), placeholder.php
config/              config.php, db.php
assets/              css/ (Bootstrap + app.css)   js/ (Bootstrap, app.js, customers.js, accounts.js, transactions.js)
database/            bankease.sql (DDL + DML)
```

## Where the database rules live
- Every query is a PDO prepared statement (`config/db.php`).
- Money movement: `post_transaction()` in `includes/helpers.php` (row lock, active-account check, no negative balance,
  balance_before / balance_after). Deposit, withdraw and transfer all go through it inside a DB transaction
  (`api/transaction_post.php`); any failure triggers ROLLBACK.
- Transfer = 2 rows in `transactions` (transfer_out, transfer_in) + 1 row in `transfers`.
- "Deactivate account" sets `accounts.status = 'frozen'` (reversible via Edit Account). Accounts are never closed from the UI.
- The user recorded on every transaction is `DEFAULT_USER_ID` in `config/config.php` (the project has no login).
