-- ============================================================
-- BankEase -- Banking Management System
-- Data Definition Language (DDL)
-- Group 19: Akash Kumer Dey (240230), Joyee Mitra Roti (240237)
-- Target: MySQL 8.0.16 or later (CHECK constraints are enforced)
--
-- Customer table includes KYC details (CIF number, Bangla name, father / mother /
-- spouse name, nationality, profession, monthly income, source of fund, TIN,
-- KYC status, KYC verifier and verified-at time); addresses are stored as
-- present / permanent with post office and thana.
-- These follow the BRAC Bank "KYC Form for Beneficial Owner".
-- Also included: performance indexes and four reporting views.
-- ============================================================

CREATE DATABASE IF NOT EXISTS bankease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bankease;

-- ------------------------------------------------------------
-- 1. roles
-- ------------------------------------------------------------
CREATE TABLE roles (
    role_id     INT AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. branches  (manager_employee_id FK added later -- circular
--    reference with employees, see ALTER TABLE below)
-- ------------------------------------------------------------
CREATE TABLE branches (
    branch_id            INT AUTO_INCREMENT PRIMARY KEY,
    branch_name          VARCHAR(100) NOT NULL UNIQUE,
    address              VARCHAR(255) NOT NULL,
    phone                VARCHAR(20) NOT NULL,
    manager_employee_id  INT,
    opening_date         DATE NOT NULL,
    status               ENUM('active','closed','under_renovation') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. employees  (works_at)
--    position holds the job title: Branch Manager, Teller,
--    Senior Teller, Customer Service Officer, Loan Officer ...
-- ------------------------------------------------------------
CREATE TABLE employees (
    employee_id        INT AUTO_INCREMENT PRIMARY KEY,
    full_name          VARCHAR(100) NOT NULL,
    phone              VARCHAR(20) NOT NULL,
    email              VARCHAR(100) NOT NULL UNIQUE,
    position           VARCHAR(100) NOT NULL,
    branch_id          INT NOT NULL,
    hire_date          DATE NOT NULL,
    employment_status  ENUM('active','on_leave','terminated') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_employees_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Close the circular reference: branches.manager_employee_id -> employees
-- ------------------------------------------------------------
ALTER TABLE branches
    ADD CONSTRAINT fk_branches_manager FOREIGN KEY (manager_employee_id)
        REFERENCES employees(employee_id) ON DELETE SET NULL;

-- ------------------------------------------------------------
-- 4. customers  (registers_at, kyc_verified_by)
--    Personal / identity / KYC details of a customer.
-- ------------------------------------------------------------
CREATE TABLE customers (
    customer_id                  INT AUTO_INCREMENT PRIMARY KEY,
    cif_number                   VARCHAR(20) NOT NULL UNIQUE,
    full_name                    VARCHAR(100) NOT NULL,
    full_name_bn                 VARCHAR(100),
    father_name                  VARCHAR(100) NOT NULL,
    mother_name                  VARCHAR(100) NOT NULL,
    spouse_name                  VARCHAR(100),
    date_of_birth                DATE NOT NULL,
    gender                       ENUM('male','female','other') NOT NULL,
    nationality                  VARCHAR(50) NOT NULL DEFAULT 'Bangladeshi',
    profession                   VARCHAR(100) NOT NULL,
    monthly_income               DECIMAL(12,2) CHECK (monthly_income IS NULL OR monthly_income >= 0),
    source_of_fund               VARCHAR(255) NOT NULL,
    tin_number                   VARCHAR(20) UNIQUE,
    phone                        VARCHAR(20) NOT NULL UNIQUE,
    email                        VARCHAR(100) UNIQUE,
    id_type                      ENUM('nid','passport','birth_certificate','driving_license','other') NOT NULL,
    id_number                    VARCHAR(50) NOT NULL UNIQUE,
    branch_id                    INT NOT NULL,
    status                       ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
    kyc_status                   ENUM('pending','verified','expired','rejected') NOT NULL DEFAULT 'pending',
    kyc_verified_by_employee_id  INT,
    kyc_verified_at              DATETIME,
    registration_date            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_customers_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    -- no ON DELETE action here: MySQL does not allow a referential action on a column that is
    -- also used in a CHECK constraint, and a verifier's record should not vanish anyway
    CONSTRAINT fk_customers_kyc_employee FOREIGN KEY (kyc_verified_by_employee_id)
        REFERENCES employees(employee_id),
    -- any customer whose KYC has been looked at must record who did it and when
    CONSTRAINT chk_customers_kyc CHECK (
        kyc_status = 'pending'
        OR (kyc_verified_by_employee_id IS NOT NULL AND kyc_verified_at IS NOT NULL)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. addresses  (belongs_to)
--    The KYC form asks for a present and a permanent address:
--    Road/Vill, P.O, Thana, District.
-- ------------------------------------------------------------
CREATE TABLE addresses (
    address_id    INT AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT NOT NULL,
    address_type  ENUM('present','permanent') NOT NULL DEFAULT 'present',
    address_line  VARCHAR(255) NOT NULL,
    post_office   VARCHAR(100),
    thana         VARCHAR(100),
    city          VARCHAR(100) NOT NULL,
    district      VARCHAR(100) NOT NULL,
    postal_code   VARCHAR(20),
    country       VARCHAR(100) NOT NULL DEFAULT 'Bangladesh',
    is_primary    BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. users  (login_for customer / login_for employee, has_role)
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id                INT AUTO_INCREMENT PRIMARY KEY,
    email                  VARCHAR(100) NOT NULL UNIQUE,
    password_hash          VARCHAR(255) NOT NULL,
    role_id                INT NOT NULL,
    linked_customer_id     INT UNIQUE,
    linked_employee_id     INT UNIQUE,
    failed_login_attempts  INT NOT NULL DEFAULT 0,
    status                 ENUM('active','locked','disabled') NOT NULL DEFAULT 'active',
    last_login_at          DATETIME,
    created_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(role_id),
    CONSTRAINT fk_users_customer FOREIGN KEY (linked_customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_users_employee FOREIGN KEY (linked_employee_id) REFERENCES employees(employee_id)
        ON DELETE CASCADE,
    CONSTRAINT chk_users_link CHECK (linked_customer_id IS NOT NULL OR linked_employee_id IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. account_types  (of_type)
-- ------------------------------------------------------------
CREATE TABLE account_types (
    account_type_id      INT AUTO_INCREMENT PRIMARY KEY,
    type_name            VARCHAR(50) NOT NULL UNIQUE,
    minimum_balance      DECIMAL(12,2) NOT NULL DEFAULT 0 CHECK (minimum_balance >= 0),
    interest_rate        DECIMAL(5,2) NOT NULL DEFAULT 0 CHECK (interest_rate >= 0),
    single_per_customer  BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 8. accounts  (owned_by, of_type, managed_by)
-- ------------------------------------------------------------
CREATE TABLE accounts (
    account_id       INT AUTO_INCREMENT PRIMARY KEY,
    account_number   VARCHAR(20) NOT NULL UNIQUE,
    account_title    VARCHAR(150) NOT NULL,
    customer_id      INT NOT NULL,
    account_type_id  INT NOT NULL,
    branch_id        INT NOT NULL,
    balance          DECIMAL(14,2) NOT NULL DEFAULT 0 CHECK (balance >= 0),
    currency         VARCHAR(10) NOT NULL DEFAULT 'BDT',
    status           ENUM('active','frozen','closed','pending') NOT NULL DEFAULT 'pending',
    opening_date     DATE NOT NULL,
    closing_date     DATE,
    CONSTRAINT fk_accounts_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_accounts_type FOREIGN KEY (account_type_id) REFERENCES account_types(account_type_id),
    CONSTRAINT fk_accounts_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    CONSTRAINT chk_accounts_dates CHECK (closing_date IS NULL OR closing_date >= opening_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 9. transactions  (posted_to, counterparty, performed_by)
-- ------------------------------------------------------------
CREATE TABLE transactions (
    transaction_id        INT AUTO_INCREMENT PRIMARY KEY,
    reference_number      VARCHAR(30) NOT NULL UNIQUE,
    account_id            INT NOT NULL,
    transaction_type      ENUM('deposit','withdrawal','transfer_in','transfer_out','fee','interest') NOT NULL,
    amount                DECIMAL(14,2) NOT NULL CHECK (amount > 0),
    balance_before        DECIMAL(14,2) NOT NULL,
    balance_after         DECIMAL(14,2) NOT NULL,
    related_account_id    INT,
    description           VARCHAR(255),
    status                ENUM('pending','completed','failed','reversed') NOT NULL DEFAULT 'completed',
    performed_by_user_id  INT NOT NULL,
    created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transactions_account FOREIGN KEY (account_id) REFERENCES accounts(account_id),
    CONSTRAINT fk_transactions_related_account FOREIGN KEY (related_account_id) REFERENCES accounts(account_id),
    CONSTRAINT fk_transactions_user FOREIGN KEY (performed_by_user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 10. transfers  (debit_side, credit_side)
-- ------------------------------------------------------------
CREATE TABLE transfers (
    transfer_id            INT AUTO_INCREMENT PRIMARY KEY,
    debit_transaction_id   INT NOT NULL UNIQUE,
    credit_transaction_id  INT NOT NULL UNIQUE,
    transfer_type          ENUM('internal','interbank') NOT NULL DEFAULT 'internal',
    fee                    DECIMAL(10,2) NOT NULL DEFAULT 0 CHECK (fee >= 0),
    status                 ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
    CONSTRAINT fk_transfers_debit FOREIGN KEY (debit_transaction_id) REFERENCES transactions(transaction_id),
    CONSTRAINT fk_transfers_credit FOREIGN KEY (credit_transaction_id) REFERENCES transactions(transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 11. beneficiaries  (saved_by, points_to)
-- ------------------------------------------------------------
CREATE TABLE beneficiaries (
    beneficiary_id     INT AUTO_INCREMENT PRIMARY KEY,
    customer_id        INT NOT NULL,
    nickname           VARCHAR(50),
    target_account_id  INT NOT NULL,
    added_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_beneficiaries_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_beneficiaries_account FOREIGN KEY (target_account_id) REFERENCES accounts(account_id),
    CONSTRAINT uq_beneficiary_target UNIQUE (customer_id, target_account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 12. cards  (held_by, linked_to)
-- ------------------------------------------------------------
CREATE TABLE cards (
    card_id           INT AUTO_INCREMENT PRIMARY KEY,
    customer_id       INT NOT NULL,
    account_id        INT NOT NULL,
    card_type         ENUM('debit','credit') NOT NULL,
    masked_number     VARCHAR(20) NOT NULL,
    card_number_hash  VARCHAR(255) NOT NULL UNIQUE,
    credit_limit      DECIMAL(12,2) CHECK (credit_limit IS NULL OR credit_limit >= 0),
    issue_date        DATE NOT NULL,
    expiry_date       DATE NOT NULL,
    status            ENUM('active','blocked','expired','cancelled') NOT NULL DEFAULT 'active',
    CONSTRAINT fk_cards_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_cards_account FOREIGN KEY (account_id) REFERENCES accounts(account_id),
    CONSTRAINT chk_cards_dates CHECK (expiry_date > issue_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 13. loans  (borrowed_by, disbursed_to, approved_by)
-- ------------------------------------------------------------
CREATE TABLE loans (
    loan_id                  INT AUTO_INCREMENT PRIMARY KEY,
    customer_id              INT NOT NULL,
    loan_type                VARCHAR(50) NOT NULL,
    principal_amount         DECIMAL(14,2) NOT NULL CHECK (principal_amount > 0),
    interest_rate            DECIMAL(5,2) NOT NULL CHECK (interest_rate >= 0),
    duration_months          INT NOT NULL CHECK (duration_months > 0),
    application_date         DATE NOT NULL,
    approval_date            DATE,
    disbursement_account_id  INT,
    monthly_installment      DECIMAL(12,2) NOT NULL,
    outstanding_balance      DECIMAL(14,2) NOT NULL,
    status                   ENUM('pending','approved','rejected','active','closed','defaulted') NOT NULL DEFAULT 'pending',
    approved_by_user_id      INT,
    CONSTRAINT fk_loans_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    CONSTRAINT fk_loans_disbursement_account FOREIGN KEY (disbursement_account_id) REFERENCES accounts(account_id),
    CONSTRAINT fk_loans_approver FOREIGN KEY (approved_by_user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 14. loan_payments  (installment_of, paid_via)
-- ------------------------------------------------------------
CREATE TABLE loan_payments (
    payment_id              INT AUTO_INCREMENT PRIMARY KEY,
    loan_id                 INT NOT NULL,
    installment_number      INT NOT NULL CHECK (installment_number > 0),
    due_date                DATE NOT NULL,
    scheduled_principal     DECIMAL(12,2) NOT NULL,
    scheduled_interest      DECIMAL(12,2) NOT NULL,
    paid_amount             DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_date               DATE,
    status                  ENUM('due','paid','overdue','partial') NOT NULL DEFAULT 'due',
    related_transaction_id  INT,
    CONSTRAINT fk_loanpay_loan FOREIGN KEY (loan_id) REFERENCES loans(loan_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_loanpay_transaction FOREIGN KEY (related_transaction_id) REFERENCES transactions(transaction_id),
    CONSTRAINT uq_loan_installment UNIQUE (loan_id, installment_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 15. notifications  (sent_to)
-- ------------------------------------------------------------
CREATE TABLE notifications (
    notification_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    type             VARCHAR(50) NOT NULL,
    message          VARCHAR(255) NOT NULL,
    is_read          BOOLEAN NOT NULL DEFAULT FALSE,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 16. audit_logs  (logged_by)
-- ------------------------------------------------------------
CREATE TABLE audit_logs (
    audit_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT,
    action        VARCHAR(100) NOT NULL,
    target_table  VARCHAR(50) NOT NULL,
    target_id     INT,
    description   VARCHAR(255),
    ip_address    VARCHAR(45),
    result        ENUM('success','failure') NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Indexes (foreign-key columns are indexed automatically by InnoDB;
-- these cover the common report / lookup patterns)
-- ============================================================
CREATE INDEX idx_transactions_account_date ON transactions (account_id, created_at);
CREATE INDEX idx_transactions_created      ON transactions (created_at);
CREATE INDEX idx_customers_kyc_status      ON customers (kyc_status);
CREATE INDEX idx_loans_status              ON loans (status);
CREATE INDEX idx_loanpay_due               ON loan_payments (status, due_date);
CREATE INDEX idx_notifications_unread      ON notifications (user_id, is_read);
CREATE INDEX idx_audit_created             ON audit_logs (created_at);

-- ============================================================
-- Reporting views -- the branch-management reports named in the
-- BRAC Bank interview (daily transactions, account activity/status,
-- cash-related activity) plus a KYC overview
-- ============================================================

-- Daily transaction summary per branch (completed transactions only)
CREATE VIEW v_daily_transaction_summary AS
SELECT DATE(t.created_at) AS txn_date,
       b.branch_id,
       b.branch_name,
       COUNT(*) AS txn_count,
       SUM(CASE WHEN t.transaction_type IN ('deposit','transfer_in','interest')
                THEN t.amount ELSE 0 END) AS total_credits,
       SUM(CASE WHEN t.transaction_type IN ('withdrawal','transfer_out','fee')
                THEN t.amount ELSE 0 END) AS total_debits
FROM transactions t
JOIN accounts a ON a.account_id = t.account_id
JOIN branches b ON b.branch_id = a.branch_id
WHERE t.status = 'completed'
GROUP BY DATE(t.created_at), b.branch_id, b.branch_name;

-- Account count and total balance by branch, account type and status
CREATE VIEW v_account_status_summary AS
SELECT b.branch_name,
       at.type_name,
       a.status,
       COUNT(*) AS account_count,
       SUM(a.balance) AS total_balance
FROM accounts a
JOIN branches b ON b.branch_id = a.branch_id
JOIN account_types at ON at.account_type_id = a.account_type_id
GROUP BY b.branch_name, at.type_name, a.status;

-- Cash in (deposits) and cash out (withdrawals) per branch per day
CREATE VIEW v_cash_activity AS
SELECT DATE(t.created_at) AS txn_date,
       b.branch_name,
       SUM(CASE WHEN t.transaction_type = 'deposit' THEN t.amount ELSE 0 END) AS cash_in,
       SUM(CASE WHEN t.transaction_type = 'withdrawal' THEN t.amount ELSE 0 END) AS cash_out
FROM transactions t
JOIN accounts a ON a.account_id = t.account_id
JOIN branches b ON b.branch_id = a.branch_id
WHERE t.status = 'completed'
  AND t.transaction_type IN ('deposit','withdrawal')
GROUP BY DATE(t.created_at), b.branch_name;

-- Customer KYC overview: who verified each customer, and when
CREATE VIEW v_customer_kyc_overview AS
SELECT c.customer_id,
       c.cif_number,
       c.full_name,
       c.id_type,
       c.id_number,
       c.profession,
       c.monthly_income,
       c.source_of_fund,
       c.tin_number,
       c.kyc_status,
       e.full_name AS verified_by,
       c.kyc_verified_at,
       b.branch_name
FROM customers c
JOIN branches b ON b.branch_id = c.branch_id
LEFT JOIN employees e ON e.employee_id = c.kyc_verified_by_employee_id;
