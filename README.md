# 🏦 BankEase — Web-Based Banking Database Management System

**A university-grade banking simulation: relational database design, REST API, and a responsive banking website — built to look and behave like a real bank's internal system.**

![Frontend](https://img.shields.io/badge/Frontend-HTML%2FCSS%2FJS-e34c26)
![Backend](https://img.shields.io/badge/Backend-Node.js%20%2B%20Express-339933)
![Database](https://img.shields.io/badge/Database-MySQL%208-4479A1)
![Auth](https://img.shields.io/badge/Auth-JWT%20%2B%20bcrypt-000000)
![Status](https://img.shields.io/badge/Status-Frontend%20Demo%20Live-yellow)
![License](https://img.shields.io/badge/License-Academic%20Use-lightgrey)

> **Logo placeholder:** `assets/logo.png` — replace this line with `![BankEase Logo](assets/logo.png)` once a logo is added.

---

## 📌 Current Implementation Status — Read This First

This repository currently contains **two things at two different stages of completion**, and this README documents both honestly rather than presenting unfinished work as done:

| Layer | Status | What exists right now |
|---|---|---|
| **Frontend (public site)** | ✅ **Built and working** | `index.html` — a complete, single-page, responsive banking website (branded in-demo as **"Meridian Trust Bank"**) with hero, about, services, account types, loan calculator, savings/FD calculators, a **demo** transfer form, branch listings, security messaging, and an FAQ. All interactivity (calculators, tab switching, the transfer "success" message) runs **client-side only** — no network calls are made. |
| **Backend API (Node.js/Express)** | 🔲 **Specified, not yet implemented** | Fully designed in [`docs/Banking_System_Master_Documentation.docx`](docs/) — endpoints, auth, validation, error handling — but no `backend/` code exists in this repo yet. |
| **Database (MySQL)** | 🔲 **Specified, not yet implemented** | Full 16-table schema, ER diagram, normalization analysis and SQL requirements are documented (Section 6 below) but `database/schema.sql` has not been written yet. |

**In short:** what you can currently open in a browser is a polished, front-end-only demo of the public banking website. Sections 4, 6, 8, and 12 of this README describe the **designed** backend/database system so that whoever picks up this project next — including a future version of the same author — knows exactly what to build. Per the project's own documentation standard, nothing below is invented: everything under "Designed" or "Specified" comes directly from the project's master documentation, and everything under "Implemented" reflects what is actually in the repository today.

---

## 📖 Table of Contents

1. [Project Overview](#-project-overview)
2. [Features](#-features)
3. [System Architecture](#-system-architecture)
4. [Technology Stack](#-technology-stack)
5. [Database Design](#-database-design)
6. [Entity Relationship Diagram](#-entity-relationship-diagram)
7. [Database Normalization](#-database-normalization)
8. [Authentication & Security](#-authentication--security)
9. [Module Documentation](#-module-documentation)
10. [Business Process Workflows](#-business-process-workflows)
11. [API Documentation (Designed)](#-api-documentation-designed)
12. [Folder Structure](#-folder-structure)
13. [Installation Guide](#-installation-guide)
14. [Pages & Screens](#-pages--screens)
15. [Testing Strategy](#-testing-strategy)
16. [Future Improvements](#-future-improvements)
17. [Project Limitations](#-project-limitations)
18. [Learning Outcomes](#-learning-outcomes)
19. [Contributors](#-contributors)
20. [License](#-license)

---

## 🧭 Project Overview

### What is BankEase?

**BankEase** is a university-level academic project that simulates the core operations of a retail bank — customer onboarding, account management, deposits, withdrawals, fund transfers, card issuance, and loan management — on top of a properly normalized relational database.

The project is deliberately built across three layers, mirroring how real banking software is structured:

- **Frontend** — HTML5, CSS3, and vanilla JavaScript, rendered as a responsive, multi-page banking website with public pages and role-based authenticated dashboards.
- **Backend** — a REST API built with Node.js and Express.js that serves as the single gatekeeper between the browser and the database, applying all business rules, validation, and security checks.
- **Database** — MySQL 8, used to store every persistent fact in the system: customers, accounts, transactions, cards, loans, employees, branches, and comprehensive audit data.

### Why it was built

This project was developed to demonstrate, in a single coherent system, the full lifecycle of a database-backed web application:

- **Schema design and normalization** — understanding how to structure data for correctness and efficiency
- **Transactional integrity** — ensuring that money movements are atomic and consistent
- **Authentication and role-based access control** — protecting sensitive operations through proper security layers
- **REST API design** — building a professional, scalable backend interface
- **Professional frontend development** — creating a polished, responsive user experience

The kind of end-to-end system a Database Systems or Web Development course expects a student to be able to design, reason about, and implement — not just code from templates.

### What it simulates

Real-world retail banking concepts and workflows:

- **Multiple account types** — Savings, Current, Student, and other account products with different rules and fees
- **Branch-scoped customers and staff** — customers are registered at a specific branch; employees work at their branch
- **Append-only transaction ledger** — every balance-affecting event is recorded permanently
- **Paired debit/credit transfers** — money movements are atomic pairs, never single-sided
- **Loan origination and amortized repayment** — loan applications, approvals, disbursements, and monthly installment schedules
- **Debit/credit card lifecycle** — card issuance, activation, blocking, and expiry
- **In-app notifications** — customers and employees receive timely alerts
- **Full audit trail** — every security-relevant and money-relevant action is logged immutably

### Who it's for

Three primary user roles, each with distinct permissions:

- **Customers** — individual account holders using the system for personal banking (opening accounts, transferring funds, applying for loans, managing cards).
- **Employees** — branch staff who service customers and perform actions customers can't do themselves (opening accounts, approving loans, issuing cards, processing transactions on a customer's behalf).
- **Administrators** — manage the system itself: user accounts, branches, account products, system configuration, bank-wide reporting, and audit trails.

### Important disclaimer

**No real money is ever moved.** Every balance is a row in this project's own MySQL database. There is no connection to real banking networks (SWIFT/ACH/RTGS), no real card processing, and no real KYC/AML verification. This is an academic simulator, and it must never be used to store real customers' real financial information, government ID numbers, or payment card data.

---

## ✨ Features

### 👤 Customer Features

| Feature | What it does | Details |
|---|---|---|
| **Registration** | Self-service sign-up | Provide name, date of birth, contact info, KYC document type/number, and home branch selection. The system validates age (≥ 18) and uniqueness of email/phone/ID number before creating the customer record and associated login credentials. |
| **Login** | Email + password authentication | Enter email and password; the system verifies credentials against the `users` table, hashes the provided password with bcrypt, and on success returns a JWT session token for subsequent API calls. |
| **Account Creation** | View and open new accounts | Browse available account types (Savings, Current, Student, etc.) with their terms; select one and apply. An employee at the customer's home branch approves/rejects the application. Approved accounts are assigned a unique account number. |
| **Deposit** | Add funds to an owned account | Select an account, enter an amount, and confirm. A new `transactions` row is created with type `Deposit`; the account's balance is incremented atomically within a database transaction. |
| **Withdrawal** | Remove funds from an owned account | Select an account, enter an amount, and confirm. The system checks that the withdrawal would not take the balance below zero (enforced by a `CHECK` constraint). On success, a `transactions` row is created with type `Withdrawal` and the balance is decremented atomically. |
| **Fund Transfer** | Move money between accounts | Transfer from one of the customer's own accounts to any other account in the bank (owned by the customer or another). The system wraps the debit and credit in a single database transaction and creates a `transfers` row linking the two `transactions` rows. |
| **Beneficiary Management** | Save, edit, and remove frequent recipients | Add a nickname and target account number, then save as a beneficiary for faster future transfers. Prevent duplicate saves of the same target account per customer. |
| **Transaction History** | Filterable, sortable list of all transactions | View every transaction across all of the customer's accounts; filter by type, date range, or amount; sort by date or amount. Transactions are read from the `transactions` table with the customer's account IDs. |
| **Loan Application** | Apply for Personal, Home, Auto, or Education loans | Select a loan type, enter the desired principal amount and term (in months). The application is submitted with status `Pending` and assigned to a loan officer at the customer's branch for review. |
| **Card Services** | View issued cards (masked), activate, block, or request replacement | See a list of cards linked to the customer's accounts, with card type (Debit/Credit), masked number (e.g., •••• •••• •••• 1234), issue/expiry dates, and current status. Customers can activate inactive cards, block active cards, or request a replacement (which generates a task for an employee). |
| **Notifications** | In-app alerts for transactions, loan decisions, account changes | Receive notifications for successful/failed transactions, loan application decisions, account freezing, card issuance, etc. Notifications can be marked as read/unread and are stored in the `notifications` table. |
| **Profile Management** | Edit non-KYC contact details; KYC fields require employee-assisted changes | Customers can update their phone, email (if not already in use), and address. KYC fields (ID type, ID number) and sensitive attributes can only be updated by an employee or administrator. |

### 🧑‍💼 Employee Features

| Feature | What it does | Details |
|---|---|---|
| **Customer Management** | Search, view, create, and update customer records at their branch | Employees can view a list of customers registered at their branch, search by name/email/phone, view full customer details (name, DOB, contact, KYC, account list), and manually create a new customer record if needed. |
| **Account Approval** | Open, freeze, or close customer accounts | Review pending account applications; click "Approve" to activate the account (assign account number, set opening date, status to Active) or "Reject" to close the application and notify the customer. Employees can also freeze an active account (e.g., on suspicion of fraud) or close it. |
| **Loan Processing** | Review, approve, or reject loan applications up to their authorization level | Employees (especially loan officers) see pending loan applications for their branch's customers. They can review the application (loan type, principal, term), compute the monthly installment, and then approve (moving status to `Approved`, setting approval_date and monthly_installment) or reject (setting status to `Rejected` with a reason). Approval limits may apply by employee position or seniority. |
| **Card Issuance** | Issue, block, or cancel customer cards | Employees can issue a new debit or credit card to a customer (generating a row in the `cards` table with status `Inactive`); the customer then activates it in their dashboard. Employees can also block a card (status → `Blocked`) if reported lost/stolen, or cancel it (status → `Cancelled`). |
| **Customer Verification** | Update KYC fields that customers cannot self-edit | Employees can update a customer's KYC information (ID type, ID number) after reviewing identity documents, or update the customer's status (Active, Suspended, Closed) based on compliance or account closure. |
| **Reports** | Generate branch-level operational reports | Produce daily/weekly/monthly reports on transaction volume, total deposits/withdrawals, pending/approved loans, active customer count, and card issuance rate for the employee's branch. Data is pulled from `transactions`, `loans`, `customers`, and `cards` tables grouped by branch. |

### 🛡️ Administrator Features

| Feature | What it does | Details |
|---|---|---|
| **User Management** | Create, suspend, and deactivate customer and employee login accounts | Administrators view all users across the bank, create new user accounts (assigning role and initial password), suspend accounts (status → `Locked`) temporarily, or deactivate them permanently (status → `Suspended`). |
| **Branch Management** | Full CRUD on bank branches | Create new branches (name, address, phone, opening date); view, edit, and delete existing branches. Branch managers can be assigned at creation or updated later. |
| **Account Type Management** | Manage available account products and their rules | View, create, and edit account types (e.g., Savings, Current, Student) with their properties: minimum balance requirement, annual interest rate, and whether customers can only hold one per person (e.g., `single_per_customer = TRUE` for Student accounts). |
| **System Configuration** | Manage bank-wide settings | Configure system-wide parameters such as default interest rates, transaction limits, overdraft allowances, maximum loan amounts by type, and system-wide notification preferences. |
| **Audit Logs** | Search and filter the complete, immutable audit trail | View all entries in the `audit_logs` table; search by user, action (e.g., LOGIN, ACCOUNT_FROZEN, WITHDRAWAL, LOAN_APPROVED), date range, or result (Success/Failure). Audit logs are append-only and cannot be modified or deleted. |
| **Reporting Dashboard** | Bank-wide KPIs and financial summaries across all branches | View aggregated metrics: total deposits/withdrawals across all branches, total loan portfolio outstanding, number of active customers, number of flagged/blocked accounts, and trends over time. |
| **Permission Control** | View and adjust role-to-permission mapping | Administrators can configure which roles (Customer, Employee, Admin) have permission to perform which actions (e.g., "Employee can approve loans up to $50,000"). This enables role-based authorization (RBAC) across the system. |

A full role-by-feature access matrix is provided in [Module Documentation](#-module-documentation).

---

## 🏗️ System Architecture

The system is organized into three distinct layers, each with clear responsibilities and communication boundaries. This separation of concerns ensures that the frontend can be developed and tested independently of the backend, the backend can validate and apply business logic without trusting the frontend, and the database can enforce constraints that the application might otherwise violate.

```
Browser (Customer / Employee / Admin)
        │
        ▼
┌───────────────────────────┐
│   FRONTEND LAYER           │   HTML5 + CSS3 + Vanilla JavaScript
│   Public site + role-based │   Responsive layout, client-side validation,
│   dashboards                │   fetch()-based API calls
└─────────────┬──────────────┘
              │ HTTPS, JSON, JWT bearer token
              ▼
┌───────────────────────────┐
│   BACKEND LAYER (API)       │   Node.js + Express.js
│   Auth • Validation •       │   Business rules, RBAC, error handling,
│   Business Logic            │   database transactions
└─────────────┬──────────────┘
              │ mysql2 (parameterized queries)
              ▼
┌───────────────────────────┐
│   DATABASE LAYER            │   MySQL 8
│   16 normalized tables,     │   ACID transactions, constraints,
│   views, procedures,        │   triggers, indexes
│   triggers                  │
└───────────────────────────┘
```

### Frontend Layer

**Technology:** HTML5, CSS3, vanilla JavaScript  
**Purpose:** render the public marketing site and, once the backend is implemented, the authenticated dashboards; collect and validate form input; communicate with the API.

#### Architecture & Design Decisions

- **No frameworks by design** — The frontend uses plain HTML, CSS, and JavaScript without external frameworks (React, Vue, etc.). This is a deliberate educational choice: every DOM interaction is explicit and easy to follow for evaluators. There is no build step; the page runs by opening a file or with any static HTTP server.

- **Responsive design** — CSS Grid and Flexbox layouts with mobile-first breakpoints. The navigation is a collapsible hamburger menu on small screens (< 768px), expanding to a horizontal navbar on desktop.

- **Client-side validation** — Input validation is performed at the field level (required fields, number ranges, email format, etc.) as a first line of defense for user experience. **This is never the only defense:** the backend re-validates everything server-side, since the frontend is not trusted.

- **API communication** — The frontend uses the Fetch API (`fetch()`) to make asynchronous HTTP requests to the backend. Requests include a JWT bearer token in the `Authorization` header for authenticated endpoints.

- **Component-style patterns** — Although no component framework is used, the frontend is structured with CSS classes and reusable HTML patterns (e.g., a `.card` class for visual cards, `.table-sortable` for tables with sorting, `.modal` for dialog boxes).

#### Current Frontend Status (Implemented)

The current implementation is a **single-page, public-facing landing page** (`frontend/index.html`):

- **Hero section** — high-impact introduction with call-to-action buttons.
- **About & History** — bank overview and background.
- **Leadership** — management team profiles.
- **Services** — tabbed navigation (Accounts, Cards, Loans, Corporate) with service descriptions.
- **Account Types** — detailed product information for Savings, Current, Student, and other accounts.
- **Calculators** — client-side interactive tools:
  - **Loan Calculator** — enter principal, term, and rate; displays monthly EMI.
  - **Savings Calculator** — enter principal and rate; calculates compound interest.
  - **Fixed Deposit Calculator** — similar to Savings, targeted for FD products.
- **Demo Transfer Form** — a non-functional form demonstrating the UI/UX for fund transfers (no data is submitted).
- **Branch Locator** — map or list of branch locations with contact information.
- **Security & Trust** — messaging about data security, fraud prevention, and compliance.
- **Rates & Offers** — current interest rates and promotional offers.
- **FAQ & Footer** — frequently asked questions and site footer with links.

All interactivity (form submissions, tab switching, calculator updates) runs **entirely client-side**; no API calls are made in the current version.

#### Dashboard Pages (Designed, not yet implemented)

Once the backend is built, the frontend will need to implement the following authenticated pages:

**Customer Dashboard:**
- Overview / Dashboard
- Profile Management
- Accounts & Account Details
- Transaction History
- Deposit / Withdrawal / Transfer Forms
- Beneficiaries
- Cards Management
- Loan Application & Management
- Notifications
- Account Statements
- Settings (Password, Preferences)

**Employee Dashboard:**
- Dashboard / Branch Summary
- Customer Search & Management
- Account Approval Workflows
- Transaction Processing
- Loan Application Review & Approval
- Card Issuance
- Reports & Analytics
- Activity Log

**Admin Dashboard:**
- System Overview / KPIs
- User & Employee Management
- Role & Permission Management
- Branch Management
- Account Type Configuration
- System Reports & Audit Logs
- Security Monitoring

### Backend Layer (Designed)

**Technology:** Node.js + Express.js  
**Purpose:** the single gatekeeper between the browser and the database; apply all business rules, validation, and security checks; execute SQL operations.

#### Architecture & Design Decisions

- **Layered approach** — The backend is organized into:
  - **Routes** — Express routers for each resource/module, mounted under `/api`. Example: `/api/auth`, `/api/accounts`, `/api/transfers`, `/api/loans`, `/api/admin`.
  - **Middleware** — Authentication (JWT verification), authorization (role-based access checks), logging, error handling.
  - **Controllers** — Handle HTTP requests and responses; coordinate between routes and services.
  - **Services** — Encapsulate business logic (e.g., `TransferService` wraps debit and credit transactions in a single database transaction; `LoanService` computes monthly EMI and creates a repayment schedule).
  - **Models/Database** — Direct SQL queries (via `mysql2`), parameterized to prevent SQL injection. Queries return data structures that controllers/services consume.
  - **Utilities** — Helpers (validation functions, date utilities, password hashing, JWT generation).

- **Authentication & Authorization**
  - **JWT-based stateless sessions** — On successful login, the backend issues a signed JWT containing the user's ID and role. The frontend stores this token and includes it in the `Authorization: Bearer <token>` header of subsequent requests.
  - **Role-based access control (RBAC)** — A middleware layer checks the caller's role (and, for customers, that they own the record being accessed) before any handler runs. Invalid access returns a 403 Forbidden response.

- **Request lifecycle:**
  ```
  Browser (fetch) → Express Route
                 → Authentication Middleware (verify JWT, identify user)
                 → Authorization Middleware (check role permissions)
                 → Controller (validate input, call service)
                 → Service (execute business logic, possibly multiple queries)
                 → Database (parameterized queries, transactions if money moves)
                 → Service (process response)
                 → Controller (format response)
                 → Express Response (JSON with { success, data, error })
                 → Browser (render)
  ```

- **Validation & Error Handling**
  - **Server-side validation** — Every field is re-validated server-side, regardless of frontend checks. Invalid input returns a 400 Bad Request with details.
  - **Centralized error handling** — A global error handler catches exceptions, logs them, and returns consistent JSON error responses (e.g., `{ success: false, error: "Account not found" }`).
  - **Transactions** — Operations that move money (deposits, withdrawals, transfers, loan disbursement, loan payments) are wrapped in database transactions (`BEGIN`, `COMMIT` on success, `ROLLBACK` on error).

- **Security**
  - **SQL Injection prevention** — All queries use parameterized queries via `mysql2/promise`, never string concatenation.
  - **Password hashing** — Passwords are hashed with bcrypt (cost factor ~10) before storage; plaintext passwords are never logged or stored.
  - **HTTPS (in production)** — All API communication is encrypted in transit.
  - **Audit logging** — Security-relevant actions (login, account freeze, loan approval, large withdrawals) are logged to the `audit_logs` table.

#### API Communication Format

All endpoints follow a consistent request/response envelope:

**Request:**
```json
{
  "email": "customer@example.com",
  "password": "plaintext_password"
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "userId": 123,
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "Invalid email or password"
}
```

#### Status

The backend is **fully designed** in the project's master documentation but **not yet implemented**. No `backend/` code currently exists in the repository. The next phase of this project is to implement the Express.js application according to the specification.

### Database Layer (Designed)

**Technology:** MySQL 8  
**Why MySQL:** Free, widely taught in university, strong ACID transactional support via InnoDB, and the natural fit for a strictly relational domain like banking where referential integrity and consistency are non-negotiable.

#### Relational Concepts

- **Primary keys** — Every table uses a surrogate `BIGINT UNSIGNED AUTO_INCREMENT` key for simplicity and performance. This avoids composite or natural keys that can change over time.

- **Foreign keys** — Foreign key constraints enforce referential integrity (e.g., an account can't reference a nonexistent customer). Deletion behavior is set to `ON DELETE RESTRICT / ON UPDATE CASCADE` by default, preserving historical/audit data.

- **Constraints** — `NOT NULL`, `UNIQUE` (to prevent duplicates), `CHECK` (to enforce business rules, e.g., balance ≥ 0), and `DEFAULT` values.

- **Normalization** — The schema satisfies Third Normal Form (3NF); see [Database Normalization](#-database-normalization).

- **ACID properties & transactions**
  - **Atomicity** — A transfer is all-or-nothing: either the debit and credit both succeed, or both roll back. Enforced via `BEGIN...COMMIT` blocks.
  - **Consistency** — Constraints and triggers ensure the database is never left in an inconsistent state (e.g., a debit without a matching credit, or an overdraft).
  - **Isolation** — Transactions are isolated from each other; one customer's withdrawal doesn't see the uncommitted state of another customer's deposit.
  - **Durability** — Once committed, data is permanently stored and survives crashes.

- **Indexing** — Beyond automatic indexes on primary/foreign keys, secondary indexes exist on frequently filtered/sorted columns (e.g., `customers.email` for login lookup, `accounts.customer_id` for account list queries).

- **Referential integrity** — Foreign key relationships are never left dangling. Historical data (past transactions, audit logs) is protected by `ON DELETE RESTRICT`, preventing accidental erasure.

#### Status

The complete database schema, ER diagram, and normalization analysis are documented in [Database Design](#-database-design) (Section 5). The `database/schema.sql` SQL file has **not been written yet**. The next phase is to convert the documented schema into a working MySQL database.

---

## 🧰 Technology Stack

| Layer | Technology | Purpose | Why it was chosen |
|---|---|---|---|
| **Structure** | **HTML5** | Semantic markup for pages and forms | Universal standard; no dependencies; accessible by default; easy for evaluators to inspect |
| **Styling** | **CSS3** (Grid/Flexbox) | Responsive layout and visual design | No external UI framework needed; full control over look-and-feel; modern layout tools (Grid/Flexbox) handle complex responsive designs cleanly |
| **Interactivity** | **JavaScript (Vanilla)** | Client-side validation, calculators, form handling, `fetch()` API calls | Keeps the frontend dependency-free; every interaction is traceable; demonstrates understanding of DOM APIs, event handling, and async/await |
| **Backend Runtime** | **Node.js** | Executes the backend API application | Uses JavaScript everywhere (frontend and backend); large, mature ecosystem of npm packages; suitable for I/O-bound applications like banking APIs |
| **Backend Framework** | **Express.js** | HTTP routing, middleware, request/response handling | Minimal and explicit; the de-facto standard for teaching REST APIs in Node.js; allows focus on business logic rather than framework concepts |
| **Database** | **MySQL 8** | Persistent relational storage of all entities (customers, accounts, transactions, loans, etc.) | Relational model is the natural fit for banking; strong ACID guarantees via InnoDB; widely taught in university; mature, stable, free |
| **DB Driver** | **mysql2/promise** | Node.js connection pooling and query execution | Supports parameterized queries (prevents SQL injection by design); Promise-based API works well with async/await; actively maintained |
| **Authentication** | **JWT (JSON Web Tokens)** | Stateless session tokens after login | Scales horizontally without server-side session storage; industry-standard bearer token authentication; enables API consumption by multiple client types (web, mobile) |
| **Password Security** | **bcrypt** | One-way password hashing with per-password salt | Industry standard; resists brute-force and rainbow-table attacks; configurable cost factor slows down attacks without affecting normal login speed |
| **Version Control** | **Git** | Track every change to code, schema, and documentation | Standard for any real or academic software project; enables collaboration and history review |
| **Collaboration & Hosting** | **GitHub** | Repository hosting, issue tracking, project submission | Standard platform for academics and industry; enables peer review; straightforward access control |

### Why no frameworks (React, Vue, etc.) or build tools?

The frontend deliberately avoids frameworks and build tools:

- **Learning clarity** — Understanding vanilla DOM APIs is foundational; frameworks abstract these away. For an academic project, it's more valuable to demonstrate DOM expertise than to show proficiency with a specific framework.
- **Evaluation simplicity** — No build step means evaluators can open `index.html` directly in a browser without installing dependencies or running `npm install && npm run build`.
- **Minimal moving parts** — Fewer dependencies = fewer things that can break or go out of date.

### Why Express.js and not Django / Spring / .NET?

- **Stack coherence** — JavaScript on both frontend and backend allows sharing validation logic and data models, reducing duplication.
- **Lightweight** — Express.js is minimal; the developer directly implements routing, validation, error handling, and RBAC instead of relying on framework defaults. This is ideal for learning.
- **Node.js ecosystem** — npm has extensive packages for JWT handling, password hashing, and database drivers.

---

## 🗄️ Database Design

The database is named **`banking_system`** and consists of **sixteen core tables** organized around four conceptual hubs:

1. **Customers & Users** — `customers`, `addresses`, `users`, `roles`, `employees` (who employees are)
2. **Accounts & Money Movement** — `branches`, `account_types`, `accounts`, `transactions`, `transfers`, `cards`
3. **Lending** — `loans`, `loan_payments`
4. **Audit & Notifications** — `beneficiaries`, `notifications`, `audit_logs`

Every table follows these standards:

- **Primary key:** `BIGINT UNSIGNED AUTO_INCREMENT` (surrogate key)
- **Foreign keys:** `ON DELETE RESTRICT / ON UPDATE CASCADE` (preserve historical data; cascade updates when a PK is modified)
- **Timestamps:** `DATETIME` with `DEFAULT CURRENT_TIMESTAMP` for audit trail clarity

### Table Specifications

<details>
<summary><strong>1. branches</strong> — physical and logical bank branches</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| branch_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| branch_name | VARCHAR(100) | NOT NULL, UNIQUE | e.g., "Dhaka Main Branch", "Chittagong Sub-Branch" |
| address | VARCHAR(255) | NOT NULL | Full postal address |
| phone | VARCHAR(20) | NOT NULL | Contact number |
| manager_employee_id | BIGINT UNSIGNED | FK → employees.employee_id, NULL | Set after the branch's first employee is hired |
| opening_date | DATE | NOT NULL | |
| status | ENUM('Active','Closed') | NOT NULL, DEFAULT 'Active' | |

**Purpose:** Organize customers, employees, and accounts by geographic or logical branch. Customers register at a branch, and employees work at a branch.

**Business rules:**
- A branch name must be unique across the bank.
- A branch can be closed (status = 'Closed') but historical data (customers, accounts, transactions) linked to it is never deleted.

</details>

<details>
<summary><strong>2. customers</strong> — bank customers (account holders)</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| customer_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| full_name | VARCHAR(100) | NOT NULL | |
| date_of_birth | DATE | NOT NULL | CHECK: age ≥ 18 at registration |
| gender | ENUM('Male','Female','Other') | NULL | Optional for privacy |
| phone | VARCHAR(20) | NOT NULL, UNIQUE | Login identifier + contact |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Login identifier + contact |
| id_type | ENUM('NID','Passport','Birth Certificate') | NOT NULL | KYC (Know Your Customer) document type |
| id_number | VARCHAR(50) | NOT NULL, UNIQUE | KYC document number; must be unique bank-wide |
| branch_id | BIGINT UNSIGNED | FK → branches.branch_id, NOT NULL | Home branch for this customer |
| status | ENUM('Active','Suspended','Closed') | NOT NULL, DEFAULT 'Active' | |
| registration_date | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Purpose:** Store customer profile information. Each customer has a linked `users` row for login credentials.

**Business rules:**
- Age must be ≥ 18 (checked at insertion).
- Email and phone must be globally unique (prevent duplicate accounts).
- ID number must be unique (can't register twice with the same ID).
- KYC fields (id_type, id_number) are read-only for the customer but can be updated by employees.

**Relationships:**
- One customer has many accounts (`accounts.customer_id`).
- One customer has many loans (`loans.customer_id`).
- One customer has many cards (`cards.customer_id`).
- One customer has many beneficiaries (`beneficiaries.customer_id`).
- One customer has one `users` row for login (`users.linked_customer_id`).

</details>

<details>
<summary><strong>3. addresses</strong> — customer mailing/residential addresses (separated for normalization)</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| address_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NOT NULL | |
| address_line | VARCHAR(150) | NOT NULL | Street address |
| city | VARCHAR(80) | NOT NULL | |
| district | VARCHAR(80) | NOT NULL | Administrative district |
| postal_code | VARCHAR(20) | NULL | May be missing in some regions |
| country | VARCHAR(60) | NOT NULL, DEFAULT 'Bangladesh' | |
| is_primary | BOOLEAN | NOT NULL, DEFAULT TRUE | One per customer can be marked primary |

**Purpose:** Store multiple addresses per customer (e.g., residential and mailing). Separated from `customers` for 3NF compliance.

**Business rules:**
- A customer can have multiple addresses.
- One address per customer should be marked as primary (is_primary = TRUE).

</details>

<details>
<summary><strong>4. users</strong> — login credentials for every role (Customer, Employee, Admin)</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| user_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Login identifier |
| password_hash | VARCHAR(255) | NOT NULL | bcrypt hash; never plaintext |
| role_id | TINYINT UNSIGNED | FK → roles.role_id, NOT NULL | Determines permissions |
| linked_customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NULL | Set when role = Customer |
| linked_employee_id | BIGINT UNSIGNED | FK → employees.employee_id, NULL | Set when role = Employee or Admin |
| failed_login_attempts | TINYINT UNSIGNED | NOT NULL, DEFAULT 0 | Incremented on failed login; reset to 0 on success |
| status | ENUM('Active','Locked','Suspended') | NOT NULL, DEFAULT 'Active' | `Locked` after N failed attempts; `Suspended` by admin |
| last_login_at | DATETIME | NULL | Updated on each successful login |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Purpose:** Central table for authentication. Every login goes through the `users` table.

**Business rules:**
- Email must be unique bank-wide (can't have two accounts with the same email).
- Every user has exactly one role.
- Customers have `linked_customer_id` set; employees/admins have `linked_employee_id` set.
- Passwords are always hashed; plaintext passwords are never stored.
- After 5 failed login attempts, the account is locked (status = 'Locked') and requires manual admin unlock.

**Relationships:**
- One user has one role (`roles.role_id`).
- One user has many notifications (`notifications.user_id`).
- One user has many audit log entries (`audit_logs.user_id`).
- One user may link to one customer or one employee.

</details>

<details>
<summary><strong>5. roles</strong> — role definitions for role-based access control (RBAC)</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| role_id | TINYINT UNSIGNED | PK, AUTO_INCREMENT | |
| role_name | ENUM('Customer','Employee','Admin') | NOT NULL, UNIQUE | Immutable |
| description | VARCHAR(150) | NULL | "Customer with account access", "Bank employee", "System administrator" |

**Purpose:** Lookup table defining the three roles in the system. Used by the `users` table and authorization middleware.

**Sample data:**
- (1, 'Customer', 'Individual account holder')
- (2, 'Employee', 'Bank employee / branch staff')
- (3, 'Admin', 'System administrator')

</details>

<details>
<summary><strong>6. employees</strong> — bank staff records</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| employee_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| full_name | VARCHAR(100) | NOT NULL | |
| phone | VARCHAR(20) | NOT NULL, UNIQUE | |
| email | VARCHAR(100) | NOT NULL, UNIQUE | |
| position | VARCHAR(60) | NOT NULL | e.g., "Teller", "Loan Officer", "Branch Manager", "Operations Manager" |
| branch_id | BIGINT UNSIGNED | FK → branches.branch_id, NOT NULL | Which branch this employee works at |
| hire_date | DATE | NOT NULL | |
| employment_status | ENUM('Active','On Leave','Terminated') | NOT NULL, DEFAULT 'Active' | |

**Purpose:** Store employee profile information. Each employee has a linked `users` row for login credentials.

**Business rules:**
- Email and phone must be unique (can't hire two people with the same contact).
- An employee works at exactly one branch.
- An employee can be a Teller, Loan Officer, Branch Manager, etc. (no separate `positions` table; position is a string).

**Relationships:**
- One employee has one linked `users` row (via `users.linked_employee_id`).
- One branch may have one manager (via `branches.manager_employee_id`).
- One employee performs many transactions (via `transactions.performed_by_user_id` → `users` → employee).

</details>

<details>
<summary><strong>7. account_types</strong> — lookup table of account products</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| account_type_id | TINYINT UNSIGNED | PK, AUTO_INCREMENT | |
| type_name | VARCHAR(40) | NOT NULL, UNIQUE | e.g., "Savings", "Current", "Student", "Senior Citizen", "Joint" |
| minimum_balance | DECIMAL(15,2) | NOT NULL, DEFAULT 0.00 | Monthly minimum; may be zero for some accounts |
| interest_rate | DECIMAL(5,2) | NOT NULL, DEFAULT 0.00 | Annual interest rate; 0 for Current accounts |
| single_per_customer | BOOLEAN | NOT NULL, DEFAULT FALSE | TRUE if customers can only have one (e.g., Student account) |

**Purpose:** Define the bank's account products. Customers can hold one or more accounts of different types.

**Sample data:**
- (1, 'Savings', 1000.00, 3.50, FALSE)
- (2, 'Current', 0.00, 0.00, FALSE)
- (3, 'Student', 0.00, 5.00, TRUE)

**Business rules:**
- An account type name must be unique.
- If `single_per_customer = TRUE`, a customer can hold at most one account of that type.

</details>

<details>
<summary><strong>8. accounts</strong> — customer bank accounts</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| account_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| account_number | VARCHAR(20) | NOT NULL, UNIQUE | System-generated; e.g., "ACC-2024-000001" |
| customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NOT NULL | Account owner |
| account_type_id | TINYINT UNSIGNED | FK → account_types.account_type_id, NOT NULL | Savings, Current, etc. |
| branch_id | BIGINT UNSIGNED | FK → branches.branch_id, NOT NULL | Branch managing this account |
| balance | DECIMAL(15,2) | NOT NULL, DEFAULT 0.00, CHECK (balance >= 0) | Current balance; never negative |
| currency | CHAR(3) | NOT NULL, DEFAULT 'BDT' | ISO currency code |
| status | ENUM('Active','Frozen','Suspended','Dormant','Closed') | NOT NULL, DEFAULT 'Active' | Lifecycle status |
| opening_date | DATE | NOT NULL | |
| closing_date | DATE | NULL | Set when status = 'Closed' |

**Purpose:** Store customer account information and balance.

**Business rules:**
- Balance is never written directly by the API; it's updated only via transactions.
- Balance is never allowed to go negative (enforced by `CHECK` constraint and transaction logic).
- Account number must be unique and globally identifiable.
- An account can be frozen (no deposits/withdrawals allowed) or closed (archived).

**Relationships:**
- One account has many transactions (`transactions.account_id`).
- One account has many cards (`cards.account_id`).
- One account may be involved in many transfers (via `transfers` table).

</details>

<details>
<summary><strong>9. transactions</strong> — append-only ledger of every balance-affecting event</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| transaction_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| reference_number | VARCHAR(30) | NOT NULL, UNIQUE | System-generated; e.g., "TXN-20240115-000042" |
| account_id | BIGINT UNSIGNED | FK → accounts.account_id, NOT NULL | Account on which transaction occurred |
| transaction_type | ENUM('Deposit','Withdrawal','TransferOut','TransferIn','Fee','InterestCredit','LoanDisbursement','LoanPayment','Adjustment') | NOT NULL | Classifies the transaction |
| amount | DECIMAL(15,2) | NOT NULL, CHECK (amount > 0) | Transaction amount (always positive) |
| balance_before | DECIMAL(15,2) | NOT NULL | Balance before transaction (snapshot) |
| balance_after | DECIMAL(15,2) | NOT NULL | Balance after transaction (snapshot) |
| related_account_id | BIGINT UNSIGNED | FK → accounts.account_id, NULL | For transfers: the other account involved |
| description | VARCHAR(255) | NULL | Free-text description; e.g., "Transfer to Savings Account" |
| status | ENUM('Completed','Failed','Reversed') | NOT NULL, DEFAULT 'Completed' | Most transactions are 'Completed'; reversal sets this to 'Reversed' |
| performed_by_user_id | BIGINT UNSIGNED | FK → users.user_id, NOT NULL | Which user (customer or employee) executed it |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Purpose:** Immutable append-only ledger. Every balance change is a transaction. This enables complete audit trail and accurate historical reporting.

**Business rules:**
- Transactions are never updated or deleted; only new ones are inserted.
- `balance_before` and `balance_after` are snapshots, allowing reconstruction of account history even if account record is deleted (rare).
- For transfers, `transaction_type` is 'TransferOut' on the sending account and 'TransferIn' on the receiving account; `related_account_id` links them.
- `amount` is always positive; the direction is indicated by `transaction_type`.

**Relationships:**
- One transaction may link to another via `transfers` table.
- One transaction may link to a loan payment via `loan_payments.related_transaction_id`.

</details>

<details>
<summary><strong>10. transfers</strong> — junction row pairing a debit and credit transaction</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| transfer_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| debit_transaction_id | BIGINT UNSIGNED | FK → transactions.transaction_id, NOT NULL, UNIQUE | The "out" side of the transfer |
| credit_transaction_id | BIGINT UNSIGNED | FK → transactions.transaction_id, NULL, UNIQUE | The "in" side; NULL for external/simulated transfers |
| transfer_type | ENUM('Own','Internal','External') | NOT NULL | Own (between customer's own accounts), Internal (another customer in bank), External (outside bank, simulated) |
| fee | DECIMAL(10,2) | NOT NULL, DEFAULT 0.00 | Transfer fee, if any |
| status | ENUM('Completed','Failed') | NOT NULL, DEFAULT 'Completed' | Status of the transfer pair |

**Purpose:** Pair two transactions (debit and credit) into a single logical transfer, ensuring atomicity.

**Business rules:**
- A transfer logically represents money moving from one account to another.
- `debit_transaction_id` is always set (the outgoing transaction).
- `credit_transaction_id` is set for internal/own transfers, but NULL for external transfers (where we simulate the transfer without an internal recipient).
- A fee may be charged on the debit side; the credit side receives the full amount.

</details>

<details>
<summary><strong>11. beneficiaries</strong> — saved transfer recipients per customer</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| beneficiary_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NOT NULL | Customer who saved this beneficiary |
| nickname | VARCHAR(50) | NOT NULL | User-friendly name; e.g., "My Savings", "Mom's Account" |
| target_account_id | BIGINT UNSIGNED | FK → accounts.account_id, NOT NULL | The account to transfer to |
| added_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Constraints:** `UNIQUE (customer_id, target_account_id)` — prevents duplicate saves of the same target account.

**Purpose:** Let customers save frequently-used transfer recipients for faster future transfers (UX improvement).

**Business rules:**
- A customer can save multiple beneficiaries.
- A beneficiary points to a target account; that account can be owned by the same customer (own transfer) or another customer.
- The same target account can't be saved twice (prevented by UNIQUE constraint).

</details>

<details>
<summary><strong>12. cards</strong> — debit and credit cards linked to accounts</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| card_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NOT NULL | Cardholder |
| account_id | BIGINT UNSIGNED | FK → accounts.account_id, NOT NULL | Linked account |
| card_type | ENUM('Debit','Credit') | NOT NULL | Card type |
| masked_number | VARCHAR(19) | NOT NULL | e.g., "•••• •••• •••• 1234" |
| card_number_hash | VARCHAR(255) | NOT NULL | Hashed card number; never stored plaintext |
| credit_limit | DECIMAL(15,2) | NULL | Set only for Credit cards |
| issue_date | DATE | NOT NULL | |
| expiry_date | DATE | NOT NULL | Card validity period |
| status | ENUM('Active','Inactive','Blocked','Expired','Cancelled') | NOT NULL, DEFAULT 'Inactive' | Lifecycle state |

**Purpose:** Track debit and credit cards issued to customers.

**Business rules:**
- A card is linked to one customer and one account.
- Debit cards withdraw from the linked account; credit cards draw against a credit limit.
- New cards are issued in 'Inactive' state and activated by the customer in their dashboard.
- A card is 'Expired' after `expiry_date` or manually 'Blocked' (lost/stolen) or 'Cancelled' (closed by bank).

**Lifecycle:**
1. Employee issues card (status = 'Inactive')
2. Customer activates it (status = 'Active')
3. Customer uses it, or blocks it, or it expires (status = 'Blocked' or 'Expired')
4. Bank can cancel it (status = 'Cancelled')

</details>

<details>
<summary><strong>13. loans</strong> — customer loan accounts</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| loan_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| customer_id | BIGINT UNSIGNED | FK → customers.customer_id, NOT NULL | Borrower |
| loan_type | ENUM('Personal','Home','Auto','Education') | NOT NULL | Type of loan |
| principal_amount | DECIMAL(15,2) | NOT NULL, CHECK (principal_amount > 0) | Original loan amount |
| interest_rate | DECIMAL(5,2) | NOT NULL | Annual interest rate (%) |
| duration_months | SMALLINT UNSIGNED | NOT NULL | Loan term in months |
| application_date | DATE | NOT NULL | When customer applied |
| approval_date | DATE | NULL | When loan was approved (set on approval) |
| disbursement_account_id | BIGINT UNSIGNED | FK → accounts.account_id, NULL | Account to which loan is disbursed |
| monthly_installment | DECIMAL(15,2) | NULL | Computed on approval (EMI) |
| outstanding_balance | DECIMAL(15,2) | NOT NULL, DEFAULT 0.00 | Remaining principal to be repaid |
| status | ENUM('Pending','Approved','Rejected','Active','Completed','Defaulted') | NOT NULL, DEFAULT 'Pending' | Lifecycle status |
| approved_by_user_id | BIGINT UNSIGNED | FK → users.user_id, NULL | User (employee) who approved |

**Purpose:** Track loan applications and active loans.

**Lifecycle:**
1. Customer applies (status = 'Pending')
2. Employee reviews and approves (status = 'Approved') or rejects (status = 'Rejected')
3. Loan is disbursed to the customer's account (status = 'Active')
4. Customer makes monthly payments (tracked in `loan_payments`)
5. Loan is fully repaid (status = 'Completed') or defaulted (status = 'Defaulted')

**Business rules:**
- `principal_amount` must be > 0.
- On approval, `monthly_installment` is calculated (EMI formula).
- `outstanding_balance` starts equal to `principal_amount` and decreases with each payment.
- `disbursement_account_id` is set when the loan is approved and the funds are transferred to the customer's account.

</details>

<details>
<summary><strong>14. loan_payments</strong> — repayment schedule and recorded payments</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| payment_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| loan_id | BIGINT UNSIGNED | FK → loans.loan_id, NOT NULL | Loan being repaid |
| installment_number | SMALLINT UNSIGNED | NOT NULL | 1st, 2nd, ... nth installment |
| due_date | DATE | NOT NULL | When payment is due |
| scheduled_principal | DECIMAL(15,2) | NOT NULL | Principal component of the installment |
| scheduled_interest | DECIMAL(15,2) | NOT NULL | Interest component of the installment |
| paid_amount | DECIMAL(15,2) | NOT NULL, DEFAULT 0.00 | Actual amount paid so far |
| paid_date | DATETIME | NULL | When the payment was made (NULL if not yet paid) |
| status | ENUM('Due','Paid','Late','Missed') | NOT NULL, DEFAULT 'Due' | Payment status |
| related_transaction_id | BIGINT UNSIGNED | FK → transactions.transaction_id, NULL | Links to the transaction when paid |

**Purpose:** Track loan repayment schedule and actual payments.

**Business rules:**
- The full repayment schedule is created when the loan is approved. Rows are inserted for each month with `status = 'Due'`.
- `scheduled_principal + scheduled_interest = monthly_installment`.
- When a payment is made, `paid_amount` is updated and `status` is set to 'Paid', and a transaction is linked.
- If `paid_date` is NULL, the payment has not been made yet.
- Late/missed payments trigger notifications and may affect credit score (future enhancement).

</details>

<details>
<summary><strong>15. notifications</strong> — in-app notifications per user</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| notification_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | FK → users.user_id, NOT NULL | Recipient user |
| type | VARCHAR(40) | NOT NULL | e.g., 'TransactionSuccess', 'LoanApproved', 'CardBlocked' |
| message | VARCHAR(255) | NOT NULL | Notification text; e.g., "Your transfer of $500 was successful" |
| is_read | BOOLEAN | NOT NULL, DEFAULT FALSE | User has viewed the notification |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Purpose:** In-app notification system. Customers and employees see notifications in their dashboards.

**Sample events that trigger notifications:**
- Successful/failed deposit, withdrawal, transfer
- Loan application submitted/approved/rejected
- Card issued/activated/blocked
- Account frozen/closed
- Failed login attempt (security alert)
- Large transaction (anti-fraud alert)

**Business rules:**
- Notifications are created by the backend when events occur.
- Users can mark notifications as read (is_read = TRUE).
- Notifications are never deleted; they're archived by age (e.g., keep 90 days).

</details>

<details>
<summary><strong>16. audit_logs</strong> — immutable record of security and money-relevant actions</summary>

| Column | Data Type | Constraints | Notes |
|---|---|---|---|
| audit_id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | FK → users.user_id, NULL | User who performed the action (NULL for system-initiated) |
| action | VARCHAR(80) | NOT NULL | e.g., 'LOGIN', 'LOGOUT', 'ACCOUNT_FROZEN', 'WITHDRAWAL', 'TRANSFER', 'LOAN_APPROVED' |
| target_table | VARCHAR(40) | NULL | Table affected; e.g., 'accounts', 'loans', 'users' |
| target_id | BIGINT UNSIGNED | NULL | PK of the affected row; e.g., account_id if action is 'WITHDRAWAL' |
| description | VARCHAR(255) | NULL | Free-text notes; e.g., "Withdrew $500 from Savings" |
| ip_address | VARCHAR(45) | NULL | Client IP address (IPv4 or IPv6) |
| result | ENUM('Success','Failure') | NOT NULL | Did the action succeed or fail? |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Purpose:** Immutable log of all security-relevant and money-relevant actions. Used for compliance, audit, and fraud investigation.

**Sample events logged:**
- LOGIN (success or failure), LOGOUT
- DEPOSIT, WITHDRAWAL, TRANSFER
- ACCOUNT_FROZEN, ACCOUNT_CLOSED
- LOAN_APPLICATION, LOAN_APPROVED, LOAN_REJECTED
- CARD_ISSUED, CARD_BLOCKED
- USER_CREATED, USER_LOCKED, PASSWORD_CHANGED
- PERMISSION_CHANGED (admin action)

**Business rules:**
- Audit logs are never modified or deleted (append-only).
- `user_id` is NULL for system-initiated actions (e.g., interest credit, card expiry).
- `result` indicates success or failure (useful for detecting attack patterns, e.g., repeated failed login from same IP).

</details>

### Indexing Strategy

Beyond the automatic indexes on every primary key and foreign key, the following secondary indexes are recommended:

| Table | Indexed Column(s) | Reason |
|---|---|---|
| customers | email, phone, id_number (all UNIQUE) | Fast login lookup; duplicate-prevention checks |
| accounts | account_number (UNIQUE), customer_id | Looked up on every transfer/deposit/withdrawal; customer's account list |
| transactions | (account_id, created_at) composite, reference_number (UNIQUE) | Transaction history queries filter by account and sort by date |
| loans | customer_id, status | Dashboard/report queries filter by customer and status |
| cards | customer_id, account_id | Looked up when displaying an account's linked cards |
| users | email (UNIQUE) | Login lookup; customer registration duplicate check |
| audit_logs | user_id, created_at | Security audit queries |

**Trade-off:** Each index speeds up filtered reads but slightly slows INSERT/UPDATE/DELETE, since the index must also be maintained. These columns were chosen because they're read far more often than written — typical for a banking workload.

---

## 🔗 Entity Relationship Diagram

### Text-Based Representation

```
┌─────────────┐
│  customers  │
│  (account   │
│  holders)   │
└──────┬──────┘
       │ 1 ─────────────────────┐
       │                        │
       ▼ *                      ▼ *
  ┌──────────┐            ┌──────────┐
  │ accounts │            │   loans  │
  │          │            │          │
  └────┬─────┘            └──────────┘
       │
       │ 1 ───────────────┐
       │                  │
       ▼ *                ▼ *
  ┌──────────────┐  ┌──────────┐
  │transactions  │  │  cards   │
  │ (ledger)     │  │          │
  └──────────────┘  └──────────┘

┌──────────────┐
│    users     │  (login credentials; links to customers or employees)
│              │
└──────────────┘

┌──────────────┐     ┌───────────┐     ┌────────────────┐
│ beneficiaries│     │employees  │     │ account_types  │
│ (saved       │     │ (staff)   │     │ (product      │
│  recipients) │     │           │     │  definitions) │
└──────────────┘     └───────────┘     └────────────────┘

┌──────────────┐     ┌───────────────┐     ┌──────────────┐
│    branches  │     │   transfers   │     │ loan_payments│
│ (locations)  │     │ (debit+credit │     │ (repayment   │
│              │     │   pairing)    │     │  schedule)   │
└──────────────┘     └───────────────┘     └──────────────┘

┌──────────────┐     ┌───────────────┐     ┌──────────────┐
│notifications │     │  audit_logs   │     │   addresses  │
│ (in-app)     │     │ (security +   │     │ (customer    │
│              │     │  compliance)  │     │  addresses)  │
└──────────────┘     └───────────────┘     └──────────────┘

┌──────────────┐
│   roles      │  (RBAC: Customer, Employee, Admin)
│              │
└──────────────┘
```

### Relationship Summary

| Relationship | Type | Description |
|---|---|---|
| customers ↔ accounts | 1:M | One customer has many accounts (checking, savings, etc.) |
| customers ↔ loans | 1:M | One customer applies for/holds many loans |
| customers ↔ cards | 1:M | One customer is issued many cards |
| customers ↔ beneficiaries | 1:M | One customer saves many beneficiary targets |
| accounts ↔ transactions | 1:M | One account has many transactions (deposits, withdrawals, transfers) |
| accounts ↔ cards | 1:M | One account is linked to many cards |
| transactions ↔ transfers | 1:1 (debit) / 1:1 (credit) | Two transactions (debit & credit) are paired by a single transfer row |
| loans ↔ loan_payments | 1:M | One loan has many payment installments |
| branches ↔ customers | 1:M | One branch has many registered customers |
| branches ↔ employees | 1:M | One branch has many staff |
| branches ↔ accounts | 1:M | One branch manages many accounts |
| employees ↔ users | 1:1 | One employee has one login (users row) |
| customers ↔ users | 1:1 | One customer has one login (users row) |
| users ↔ roles | M:1 | Many users have the same role (Customer, Employee, Admin) |
| users ↔ audit_logs | 1:M | One user has many audit log entries |
| users ↔ notifications | 1:M | One user receives many notifications |

### Relationship Rationale

- **1:M (One-to-Many)** — Common in banking: one customer has many accounts; one account has many transactions. Implemented via foreign key.
- **1:1 (One-to-One)** — Less common but used for login separation: one customer has exactly one users row (for privacy/security). Implemented via UNIQUE foreign key.
- **M:M (Many-to-Many)** — Not present in this schema. If needed (e.g., joint accounts), would be implemented via a junction table.

---

## 🔄 Database Normalization

The schema is normalized to **Third Normal Form (3NF)**, ensuring data integrity, minimizing redundancy, and enabling efficient updates.

### First Normal Form (1NF) — Atomic Values

**Requirement:** Every column contains only atomic (indivisible) values; no repeating groups.

**How the schema satisfies 1NF:**
- No columns contain multiple values (e.g., a customer's addresses are in a separate `addresses` table, not a comma-separated string).
- Every row in every table is uniquely identifiable by its primary key.
- No arrays or nested structures; every value is a scalar (number, string, date, etc.).

**Example:**
❌ **Not 1NF:**
```
customer_id | name  | addresses
1           | Alice | "123 Main St, 456 Oak Ave"  ← comma-separated: not atomic
```

✅ **Is 1NF:**
```
customer_id | name  | (separate addresses table with foreign key)
1           | Alice | (customers.customer_id FK in addresses.customer_id)
```

### Second Normal Form (2NF) — No Partial Dependencies

**Requirement:** All non-key columns depend on the **entire** primary key, not just part of it. (Relevant when using composite keys; our tables use surrogate keys, so this is generally satisfied.)

**How the schema satisfies 2NF:**
- Every table uses a single-column surrogate primary key (`*_id`), avoiding composite key issues.
- Non-key columns (e.g., `balance` in `accounts`) depend on the entire key (account_id), not a subset.

**Example:**
❌ **Not 2NF** (with composite key):
```
(student_id, course_id) → PK
instructor_name        ← depends only on course_id, not the full key
```

✅ **Is 2NF:**
```
accounts table:
account_id (PK) → balance, status, etc. (all depend on account_id)

separate courses table:
course_id (PK) → instructor_name
```

### Third Normal Form (3NF) — No Transitive Dependencies

**Requirement:** Non-key columns don't depend on other non-key columns (transitively). Every non-key fact is stored in the table whose primary key it depends on.

**How the schema satisfies 3NF:**
- Customer's address is in a separate `addresses` table (not in `customers`), because address is an attribute of an address, not of the customer directly.
- Loan's `monthly_installment` is in the `loans` table (computed from principal, rate, term), not stored redundantly in `loan_payments` where it would become stale.
- Transaction's `balance_before` and `balance_after` are snapshots (acceptable for audit), not redundant copies of the account balance (which is derived elsewhere).

**Example:**
❌ **Not 3NF** (transitive dependency):
```
customers table:
customer_id → full_name → home_city  ← home_city depends on full_name? No.
customer_id → branch_id → branch_manager_name  ← manager_name depends on branch, not customer
```

✅ **Is 3NF:**
```
customers: customer_id → full_name, branch_id (which is an FK, not a data fact)
branches: branch_id → branch_name, manager_employee_id
```

### Benefits of 3NF

- **Data integrity** — No redundancy means no update anomalies (e.g., changing a branch manager's name in one place breaks queries that expect the same name elsewhere).
- **Storage efficiency** — No repeated data; smaller tables fit in cache and are faster to scan.
- **Consistency** — A single source of truth for each fact (e.g., a branch manager's name is stored once).

### Denormalization for Performance (Rare)

In rare cases, denormalization is acceptable:
- `transactions.balance_before` and `balance_after` are snapshots, not live references to the account balance. This is acceptable because transactions are immutable and historical; they don't cause update anomalies.
- `loans.monthly_installment` is computed once and stored, avoiding recomputation. This is acceptable because the loan terms don't change after approval.

---

## 🔐 Authentication & Security

Security is built in at multiple layers: password hashing, stateless JWT tokens, role-based authorization, SQL injection prevention, and audit logging.

### Authentication Flow

**Step 1: User Registration**
1. Customer fills in name, email, password, phone, DOB, ID type/number, and home branch.
2. The backend validates:
   - Email and phone are not already registered (unique constraint check).
   - Age ≥ 18.
   - Password meets minimum strength requirements (e.g., ≥ 8 characters, mixed case, number).
3. Password is hashed using bcrypt (cost factor 10) and never stored plaintext.
4. A new row is inserted into `customers` and a new row into `users` with `role_id = 1` (Customer).
5. User is redirected to login or auto-logged in (per specification).

**Step 2: User Login**
1. User enters email and password.
2. Backend queries the `users` table for the email:
   ```sql
   SELECT user_id, password_hash, role_id, linked_customer_id, status
   FROM users
   WHERE email = ?
   ```
3. If the row is found and status is 'Active':
   - Compare the provided password against the stored hash using bcrypt (`bcrypt.compare(password, hash)`).
   - If the hash matches, generate a JWT token.
   - Reset `failed_login_attempts` to 0.
   - Update `last_login_at` to current timestamp.
4. If the hash doesn't match:
   - Increment `failed_login_attempts`.
   - If `failed_login_attempts >= 5`, set status to 'Locked' and lock the account.
   - Return "Invalid email or password" (generic message; don't reveal which is wrong).
5. Return the JWT token to the frontend.

**Step 3: Token Storage & Use**
- Frontend stores the JWT in memory (or, optionally, in `localStorage` if session persistence is desired).
- Every subsequent request includes the token in the `Authorization: Bearer <token>` header.
- Backend validates the token on every protected route (see Authorization below).

### JWT Structure

A JWT consists of three parts: `header.payload.signature`.

**Payload example:**
```json
{
  "userId": 42,
  "email": "customer@example.com",
  "role": "Customer",
  "linkedCustomerId": 7,
  "iat": 1705330800,
  "exp": 1705417200
}
```

- `iat` (issued at) — Unix timestamp when the token was created.
- `exp` (expiration) — Unix timestamp when the token expires (e.g., 24 hours later).
- Backend signs the token with a secret key (e.g., a long random string stored in environment variables).

### Authorization (Role-Based Access Control)

After verifying the JWT, the backend checks whether the user is allowed to perform the requested action.

**Role hierarchy (not strict, but typical):**
```
Admin > Employee > Customer
```

**Example authorization rules:**
- `POST /api/accounts` (open a new account) — requires role = Employee or Admin.
- `GET /api/accounts/123` — requires role = Customer AND linkedCustomerId matches account owner, OR role = Employee/Admin.
- `POST /api/loans/123/approve` — requires role = Employee AND branch_id matches, OR role = Admin.
- `POST /api/admin/users` (create a user) — requires role = Admin.

**Middleware implementation:**
```javascript
function requireRole(requiredRoles) {
  return (req, res, next) => {
    if (!requiredRoles.includes(req.user.role)) {
      return res.status(403).json({ success: false, error: "Forbidden" });
    }
    next();
  };
}

// Usage:
router.post('/api/loans/:id/approve', 
  authenticate,                    // verify JWT
  requireRole(['Employee', 'Admin']), // check role
  approveLoanHandler               // handle request
);
```

### Password Security

**Hashing algorithm: bcrypt**

- **Why bcrypt?** It's specifically designed for password hashing, not just general-purpose hashing. It includes a salt (per-password randomness) and a cost factor (configurable slowness) that resists brute-force attacks.
- **Cost factor:** Typically 10. This means hashing a password takes ~100ms; an attacker would need 100ms × 2^30 ≈ 30+ years to brute-force a single password on modern hardware (and that assumes they have the hash).
- **Storing:** Only the hash is stored, never the plaintext password.

**Example (Node.js with bcrypt package):**
```javascript
const bcrypt = require('bcrypt');

// Hashing (registration)
const plainPassword = 'MySecurePassword123';
const hash = await bcrypt.hash(plainPassword, 10);
// Store hash in users.password_hash

// Verifying (login)
const isMatch = await bcrypt.compare(plainPassword, storedHash);
if (isMatch) {
  // Password is correct
  issueJWT();
} else {
  // Password is incorrect
  incrementFailedAttempts();
}
```

### SQL Injection Prevention

Every query uses **parameterized queries** (prepared statements), never string concatenation.

❌ **Vulnerable (never do this):**
```javascript
const email = req.body.email; // Could be: ' OR '1'='1
const query = `SELECT * FROM users WHERE email = '${email}'`;
// Results in: SELECT * FROM users WHERE email = '' OR '1'='1' ← all rows!
```

✅ **Safe (parameterized):**
```javascript
const query = 'SELECT * FROM users WHERE email = ?';
const [rows] = await connection.execute(query, [email]);
// The email value is sent separately, never interpreted as SQL
```

The `mysql2/promise` library handles parameterization automatically.

### HTTPS (Encryption in Transit)

- **Requirement:** In production, all API communication must be encrypted via HTTPS (TLS/SSL).
- **Effect:** The JWT and sensitive data (passwords during registration, account details) are encrypted in transit and unreadable to eavesdroppers.
- **For development:** HTTP is acceptable, but production must enforce HTTPS.

### Account Lockout & Failed Attempts

Protects against brute-force attacks:

1. User has 5 chances to enter the correct password.
2. On the 5th failed attempt, the account is locked (status = 'Locked').
3. An administrator or the user (via email verification) must unlock the account.

**Implementation:**
```javascript
const failedAttempts = user.failed_login_attempts;
if (!isMatch) {
  failedAttempts++;
  if (failedAttempts >= 5) {
    await updateUserStatus(userId, 'Locked');
  }
  return res.status(401).json({ error: "Invalid credentials" });
}
```

### Audit Logging

Every security-relevant action is logged:
- LOGIN (success/failure)
- LOGOUT
- PASSWORD_CHANGE
- ACCOUNT_LOCKED
- PERMISSION_CHANGED (admin)
- Suspicious activities (e.g., large withdrawal, access from unusual location)

**Purpose:** Enable fraud investigation, compliance audits, and detection of attack patterns.

**Example audit log row:**
```
audit_id: 50001
user_id: 42
action: 'LOGIN'
result: 'Success'
ip_address: '192.168.1.100'
created_at: '2024-01-15 09:30:45'
```

---

## 📋 Module Documentation

The system is organized into functional modules, each with distinct responsibilities and user roles.

### 1. Authentication & User Management Module

**Purpose:** Login, registration, password management, account lockout.

**Users:** All roles (Customers, Employees, Admins).

**Key operations:**
- Customer registration (self-service)
- Employee/Admin account creation (admin-initiated)
- Login (all roles)
- Password change
- Account unlock (admin)
- Session logout

**Database tables involved:** `users`, `roles`, `customers`, `employees`, `audit_logs`

**Security highlights:**
- Passwords are hashed with bcrypt; never plaintext.
- JWT tokens are issued at login and required for subsequent requests.
- Failed login attempts lock the account after 5 failures.
- All login attempts are logged.

---

### 2. Customer Profile & KYC Module

**Purpose:** Manage customer personal information, KYC (Know Your Customer) verification.

**Users:** Customers (read/edit non-KYC fields), Employees (read/edit all), Admins (read/edit all).

**Key operations:**
- Register (self-service)
- View profile
- Edit contact details (phone, email, address)
- Edit KYC fields (employee/admin only)
- Update customer status (Active, Suspended, Closed)

**Database tables involved:** `customers`, `addresses`, `audit_logs`

**Business rules:**
- Age must be ≥ 18 at registration.
- Email and phone are globally unique.
- KYC fields (ID type, ID number) are read-only for the customer; only employees can update.
- Customer status can be changed by employees or admins (e.g., suspend for fraud).

---

### 3. Account Management Module

**Purpose:** Open, manage, and close bank accounts.

**Users:** Customers (view, apply), Employees (approve/reject, freeze, close).

**Key operations:**
- **Customer:** View available account types, apply to open an account, view account list, view account details.
- **Employee:** Review pending applications, approve (generate account number, set opening date), reject, freeze, close.

**Database tables involved:** `accounts`, `account_types`, `branches`, `customers`, `transactions`, `audit_logs`

**Account lifecycle:**
1. Customer applies (via web form or employee creates manually).
2. Employee approves (status = 'Active') or rejects.
3. Account is active (customer can deposit, withdraw, transfer).
4. Account can be frozen (status = 'Frozen') — no transactions allowed.
5. Account can be closed (status = 'Closed') — archived, no transactions allowed.

**Validation rules:**
- Account number is unique bank-wide.
- If account type has `single_per_customer = TRUE`, customer can only have one.
- Minimum balance is enforced (if configured).

---

### 4. Transaction Module

**Purpose:** Record and track all money-movement events.

**Users:** Customers (initiated), Employees (process on behalf of customer), Admins (review).

**Key operations:**
- **Deposit:** Customer adds funds to an account.
- **Withdrawal:** Customer removes funds from an account (must not overdraft).
- **Transfer:** Customer moves money between own accounts or to another account (possibly with fee).
- **Transaction history:** View all transactions, filterable and sortable.

**Database tables involved:** `transactions`, `transfers`, `accounts`, `audit_logs`

**Append-only ledger model:**
- Every transaction is immutable (never updated); only new transactions are inserted.
- `balance_before` and `balance_after` are snapshots, preserving history even if the account record is deleted.
- Reference number is unique for compliance/audit purposes.

**Validation rules:**
- Withdrawal amount must not cause balance to go below 0 (enforced by CHECK constraint).
- Transfer from account A to account B must debit A and credit B in a single atomic transaction.
- Transaction type describes the nature (Deposit, Withdrawal, TransferOut, TransferIn, etc.).

---

### 5. Transfer Module

**Purpose:** Move money between accounts with optional fees and paired debit/credit logic.

**Users:** Customers (initiate own/internal transfers), Employees (process on behalf).

**Key operations:**
- Own transfer (between customer's own accounts)
- Internal transfer (to another customer in the bank)
- External transfer (to outside the bank, simulated)
- Add/remove beneficiaries (saved recipients for faster transfers)
- View transfer history

**Database tables involved:** `transfers`, `transactions`, `beneficiaries`, `accounts`, `audit_logs`

**Atomic transfer logic:**
1. Debit transaction is created (TransferOut, amount = transfer amount).
2. Credit transaction is created (TransferIn, amount = transfer amount).
3. A transfers row links the two transactions.
4. If a fee is charged, it's debited from the sending account only.
5. Everything is wrapped in a single database transaction; all succeed or all rollback.

**Example (internal transfer of $100 with $5 fee):**
- Debit: account_from: -$105 (amount $100 + fee $5)
- Credit: account_to: +$100
- Fee: $5 goes to a bank account or is retained as revenue

**Beneficiary feature:**
- Customers save frequently-used recipients (nickname + target account).
- On a future transfer, customer can select a beneficiary instead of entering the account number each time.

---

### 6. Card Management Module

**Purpose:** Issue, activate, block, and track debit/credit cards.

**Users:** Employees (issue, block, cancel), Customers (activate, block, request replacement).

**Key operations:**
- Issue a card (Debit or Credit)
- Activate a card (customer)
- Block a card (lost/stolen)
- Request a replacement
- View card details (masked number, expiry, status)
- Cancel a card

**Database tables involved:** `cards`, `accounts`, `customers`, `audit_logs`

**Card lifecycle:**
1. Employee issues a card (status = 'Inactive', issue_date set).
2. Customer activates it (status = 'Active').
3. Customer uses it or blocks it if lost/stolen (status = 'Blocked').
4. Card expires (status = 'Expired' after expiry_date).
5. Bank cancels it (status = 'Cancelled').

**Sensitive data:**
- Card number is never displayed in full; only masked (•••• •••• •••• 1234).
- Card number is hashed and stored securely; never stored plaintext.

**Types:**
- **Debit card:** Withdraws from the linked account; has a daily withdrawal limit.
- **Credit card:** Draws against a credit limit; customer pays the statement in full or in installments.

---

### 7. Loan Management Module

**Purpose:** Handle loan applications, approvals, disbursement, and repayment tracking.

**Users:** Customers (apply), Employees (process), Admins (review).

**Key operations:**
- **Customer:** Apply for a loan (select type, enter principal and term), view application status, view loan details, view repayment schedule, make payments.
- **Employee:** Review pending applications, approve (calculate EMI, set approval_date), reject, view customer's loan portfolio.
- **Admin:** View bank-wide loan portfolio, configure loan limits/rates, review defaults.

**Database tables involved:** `loans`, `loan_payments`, `transactions`, `accounts`, `audit_logs`

**Loan types:**
- Personal (unsecured, shorter term)
- Home (long-term, against property)
- Auto (for vehicle purchase)
- Education (for tuition)

**Loan lifecycle:**

1. **Application** — Customer submits application (principal, duration_months, type). Status = 'Pending'.
2. **Approval** — Employee reviews and:
   - Approves (status = 'Approved', calculates monthly_installment via EMI formula, sets approval_date).
   - Rejects (status = 'Rejected').
3. **Disbursement** — Loan funds are transferred to the customer's account (a LoanDisbursement transaction). Status = 'Active'.
4. **Repayment** — Customer makes monthly payments (via loan_payments). Each payment updates the outstanding_balance.
5. **Completion** — When outstanding_balance reaches 0, status = 'Completed'.
6. **Default** — If payments are missed (status = 'Missed' in loan_payments), loan can be marked as 'Defaulted'.

**EMI (Equated Monthly Installment) Calculation:**
```
EMI = [P * r * (1 + r)^n] / [(1 + r)^n - 1]
where:
  P = principal amount
  r = monthly interest rate (annual_rate / 12 / 100)
  n = number of months
```

**Repayment schedule:**
- When a loan is approved, `n` rows are created in `loan_payments` (one per month).
- Each row contains due_date, scheduled_principal, scheduled_interest (sums to EMI).
- As customer makes payments, paid_amount is updated and status moves to 'Paid'.

**Validation rules:**
- Principal must be > 0 and within configured limits for the loan type.
- Approval is subject to employee authorization (some employees can only approve up to $50k, for example).

---

### 8. Notification Module

**Purpose:** Deliver timely in-app alerts to users.

**Users:** Customers and Employees (receive notifications).

**Key operations:**
- Receive notifications (automatic on events)
- View notification list
- Mark as read
- (Optional) Delete old notifications

**Database tables involved:** `notifications`, `users`

**Events that trigger notifications:**
- Transaction success/failure (deposit, withdrawal, transfer)
- Loan application submitted, approved, rejected
- Card issued, activated, blocked
- Account frozen, closed
- Failed login attempts (security alert)
- Large transaction (anti-fraud threshold exceeded)

**Notification types:**
- **Transactional** (e.g., "Your transfer of $500 to Savings was successful")
- **Approval** (e.g., "Your Personal Loan application has been approved")
- **Alert** (e.g., "Your account has been frozen for security review")
- **Administrative** (e.g., "Your password was changed")

**Currently implemented:** In-app only (stored in database and displayed in dashboard). Future enhancements: email, SMS, push notifications.

---

### 9. Admin & Reporting Module

**Purpose:** System administration, user management, configuration, and bank-wide reporting.

**Users:** Admins only.

**Key operations:**
- **User management:** Create, suspend, lock, unlock, deactivate users.
- **Branch management:** Create, edit, delete branches; assign branch managers.
- **Account type management:** Create, edit, delete account products; set interest rates and minimum balances.
- **System configuration:** Set bank-wide defaults (interest rates, transaction limits, etc.).
- **Audit logs:** Search and filter the immutable audit trail.
- **Reports:** Bank-wide dashboards (total deposits, total loans, active customers, etc.).
- **Permission management:** Configure role-based authorization rules (e.g., "Employee role can approve loans up to $50k").

**Database tables involved:** `users`, `roles`, `branches`, `account_types`, `audit_logs`, `accounts`, `transactions`, `loans`, `customers`

**Key reports:**
- Daily/weekly/monthly transaction volume and totals
- Active customer count
- Loan portfolio (outstanding vs. paid)
- Asset quality (defaults, late payments)
- Card issuance and usage
- Security alerts (failed logins, locked accounts)

---

### Access Control Matrix (Role × Module)

| Module | Customer | Employee | Admin |
|---|---|---|---|
| **Authentication & User Mgmt** | Register, login, change password | Login, view own activity | Login, create/suspend/unlock users |
| **Customer Profile & KYC** | View/edit own profile (non-KYC) | View/edit any customer's profile (all fields) | View/edit any profile, configure KYC fields |
| **Account Management** | View accounts, apply to open | Approve/reject/freeze/close accounts | Manage account types, set limits |
| **Transaction Module** | Initiate deposit/withdrawal/transfer | Process on customer's behalf, view history | View all transactions, audit |
| **Transfer Module** | Own/internal transfers, manage beneficiaries | Process on customer's behalf | Monitor, audit, manage fees |
| **Card Management** | Activate, block, request replacement | Issue, block, cancel | Manage card products, configure limits |
| **Loan Management** | Apply, view status, make payments | Review, approve/reject applications | Manage loan products, configure limits |
| **Notification Module** | Read, mark as read | Read, mark as read | Create notifications, configure rules |
| **Admin & Reporting** | View own profile | View branch reports | Full access: create users, branches, reports |

---

## 🔄 Business Process Workflows

### 1. Registration & Login Workflow

```
┌─ New Customer ──┐
│                 │
▼                 ▼
Navigate to → Fill Registration Form
              ↓
              Validate locally (frontend)
              ↓
         POST /api/auth/register
              ↓
         Backend validates:
         • Email/phone not in use
         • Age ≥ 18
         • Password strength
         • ID number unique
              ↓
         Hash password with bcrypt
         Create customers row
         Create users row (role = Customer)
              ↓
         Success: Redirect to Login
              ↓
         Enter Email + Password
              ↓
         POST /api/auth/login
              ↓
         Backend looks up user by email
         Verify password with bcrypt
         Generate JWT token (expires in 24h)
              ↓
         Return token to frontend
              ↓
         Frontend stores token in memory
         Redirect to Dashboard
```

### 2. Account Opening Workflow

```
┌─ Customer ────────────────────┐
│                               │
▼                               ▼
Browse Account Types  OR  Employee Creates
    (Public site)           (Admin initiates)
        ↓
    Select "Savings Account"
    Click "Apply"
        ↓
    Form: principal amount, branch
        ↓
    POST /api/accounts
        ↓
    Backend creates accounts row:
    • Generate unique account_number
    • Set status = 'Pending'
    • Link to customer_id
    ↓
    Notify employee dashboard:
    "New account application from Alice"
    ↓
Employee Dashboard
    ↓
    Review application
    Click "Approve" or "Reject"
    ↓
    If Approve:
    • Update status = 'Active'
    • Set opening_date = today
    • Notify customer: "Account approved"
    ↓
Customer Dashboard
    ↓
    "Your Savings Account is now active!"
    Account number: ACC-2024-000001
    Balance: $0.00
```

### 3. Deposit Workflow

```
Customer Dashboard
    ↓
    Select Account
    Click "Deposit"
    ↓
    Form: Amount
    ↓
    Frontend validates:
    • Amount > 0
    • Amount ≤ daily limit (?)
    ↓
    POST /api/transactions/deposit
    ↓
    Backend validates:
    • Account exists and is Active
    • Amount > 0
    ↓
    BEGIN TRANSACTION
    ↓
    INSERT INTO transactions:
    • account_id, type='Deposit', amount
    • balance_before = current balance
    • balance_after = balance + amount
    ↓
    UPDATE accounts:
    • balance = balance + amount
    ↓
    COMMIT
    ↓
    Create notification: "Deposit of $500 successful"
    Log audit: "DEPOSIT, Success"
    ↓
    Return transaction_id to frontend
    ↓
    Frontend displays: "Deposit successful. New balance: $1,500"
```

### 4. Transfer Workflow (with Debit+Credit Atomicity)

```
Customer Dashboard
    ↓
    Select "Transfer"
    ↓
    Form: From Account, To Account, Amount
    ↓
    Frontend validates:
    • From account exists and is owned by customer
    • To account exists (or use saved beneficiary)
    • Amount > 0
    • Amount ≤ daily transfer limit
    ↓
    POST /api/transfers
    ↓
    Backend validates:
    • Both accounts exist
    • From account is Active
    • From account has sufficient balance
    ↓
    BEGIN TRANSACTION
    ↓
    Create debit transaction:
    INSERT INTO transactions (account_id=from, type='TransferOut', amount)
    ↓
    Create credit transaction:
    INSERT INTO transactions (account_id=to, type='TransferIn', amount)
    ↓
    Update both accounts:
    UPDATE accounts SET balance = balance - amount WHERE account_id = from
    UPDATE accounts SET balance = balance + amount WHERE account_id = to
    ↓
    Create transfer row:
    INSERT INTO transfers (debit_transaction_id, credit_transaction_id, ...)
    ↓
    COMMIT (if any error, ROLLBACK both debit and credit)
    ↓
    Create notifications for both customers
    Log audit: "TRANSFER, Success"
    ↓
    Return to frontend: "Transfer successful. From balance: $900, To balance: $1,100"
```

### 5. Loan Application & Approval Workflow

```
Customer Dashboard
    ↓
    Click "Apply for Loan"
    ↓
    Form: Loan Type (Personal / Home / Auto / Education)
          Principal Amount
          Desired Duration (months)
    ↓
    POST /api/loans
    ↓
    Backend validates:
    • Customer exists and is Active
    • Principal > 0 and within type limits
    • Duration > 0
    ↓
    INSERT INTO loans:
    • customer_id, loan_type, principal, interest_rate, duration_months
    • status = 'Pending'
    • application_date = today
    ↓
    Notify employee dashboard:
    "New Personal Loan application: $10,000 from Alice"
    Create audit log
    ↓
Employee Dashboard (Loan Officer)
    ↓
    View pending applications
    Click on Alice's application
    ↓
    Calculate EMI:
    EMI = [10000 * 0.06 * (1.06)^60] / [(1.06)^60 - 1]
    = approximately $193.33 per month
    ↓
    (Verify customer creditworthiness, etc.)
    ↓
    Click "Approve"
    ↓
    Backend:
    • Set status = 'Approved'
    • Set approval_date = today
    • Set monthly_installment = $193.33
    • Set approved_by_user_id = current employee
    ↓
    Create repayment schedule:
    INSERT INTO loan_payments 60 rows:
      For month 1 to 60:
      • due_date = month_end
      • scheduled_principal + scheduled_interest ≈ $193.33
    ↓
    Disburse loan to customer's checking account:
    • Create transaction (type='LoanDisbursement', amount=$10,000)
    • UPDATE accounts SET balance += $10,000
    • Update loan: status = 'Active', disbursement_account_id = checking_id
    ↓
    Notify customer: "Your $10,000 Personal Loan has been approved and disbursed."
    ↓
Customer Dashboard
    ↓
    New balance in checking: $10,000
    New loan in "My Loans": Personal Loan, Outstanding: $10,000
    Repayment schedule shows 60 monthly payments of $193.33
    ↓
    (Each month, customer can make a payment...)
```

### 6. Loan Repayment Workflow

```
Customer Dashboard → My Loans → Personal Loan → View Schedule
    ↓
    Next payment due: $193.33 on Feb 28
    Click "Make Payment"
    ↓
    Form: Amount (default: $193.33)
    ↓
    POST /api/loans/123/payment
    ↓
    Backend:
    • Find the next unpaid installment in loan_payments
    • Verify amount matches scheduled amount (or is overpayment)
    ↓
    BEGIN TRANSACTION
    ↓
    Create payment transaction:
    INSERT INTO transactions (account_id=customer's_checking, type='LoanPayment', amount=$193.33)
    UPDATE accounts SET balance -= $193.33
    ↓
    Update loan_payment row:
    UPDATE loan_payments SET paid_amount=$193.33, paid_date=now(), status='Paid'
    ↓
    Update loan's outstanding_balance:
    outstanding_balance = principal - sum(paid_amounts)
    ↓
    If outstanding_balance <= 0:
    • Update loan status = 'Completed'
    • Notify customer: "Your Personal Loan has been fully repaid."
    ↓
    COMMIT
    ↓
    Notify customer: "Payment of $193.33 received. Remaining balance: $9,806.67"
    ↓
Customer sees:
    ↓
    "Payment received! Outstanding: $9,806.67"
    Remaining payments: 59
```

---

## 📡 API Documentation (Designed)

The backend API is designed to follow REST conventions, using JSON for request/response bodies. Every endpoint returns a consistent envelope: `{ success, data, error }`.

> **Status:** Fully designed in the project master documentation. The endpoint specifications, authentication flows, and validation rules are documented below. **Implementation is pending** — no `backend/` code currently exists in this repo.

### Base URL & Authentication

- **Base URL (development):** `http://localhost:3000/api`
- **Base URL (production):** `https://api.bankease.com/api`
- **Authentication:** JWT bearer token in `Authorization` header

```
GET /accounts HTTP/1.1
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Response Envelope

All responses follow this structure:

**Success (200, 201, etc.):**
```json
{
  "success": true,
  "data": {
    "userId": 42,
    "email": "customer@example.com",
    ...
  }
}
```

**Error (4xx, 5xx):**
```json
{
  "success": false,
  "error": "Invalid email or password"
}
```

### Authentication Endpoints

#### `POST /auth/register`

Register a new customer account.

**Request:**
```json
{
  "fullName": "Alice Smith",
  "dateOfBirth": "1990-05-15",
  "gender": "Female",
  "phone": "+8801700000001",
  "email": "alice@example.com",
  "password": "SecurePass123",
  "idType": "NID",
  "idNumber": "1234567890",
  "branchId": 1
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "customerId": 7,
    "email": "alice@example.com",
    "message": "Registration successful. Please log in."
  }
}
```

**Validation:**
- Email must be unique and valid.
- Phone must be unique and match a phone regex.
- Age (calculated from DOB) must be ≥ 18.
- Password must be ≥ 8 characters, include uppercase, lowercase, and number.
- ID number must be unique.
- Branch must exist.

**Error responses:**
- 400: "Email already registered"
- 400: "Phone already in use"
- 400: "Age must be at least 18"
- 400: "Password does not meet complexity requirements"

---

#### `POST /auth/login`

Authenticate and receive a JWT token.

**Request:**
```json
{
  "email": "alice@example.com",
  "password": "SecurePass123"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "userId": 42,
      "email": "alice@example.com",
      "role": "Customer",
      "linkedCustomerId": 7
    }
  }
}
```

**Validation:**
- Email must be registered.
- Password must match (bcrypt comparison).
- Account status must be 'Active' (not 'Locked' or 'Suspended').

**Error responses:**
- 401: "Invalid email or password"
- 403: "Account is locked. Please contact support."
- 403: "Account is suspended."

---

### Account Endpoints

#### `GET /accounts`

List all accounts owned by the authenticated customer.

**Query parameters:** None

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "accountId": 15,
      "accountNumber": "ACC-2024-000001",
      "type": "Savings",
      "balance": 5000.00,
      "currency": "BDT",
      "status": "Active",
      "openingDate": "2024-01-10"
    },
    {
      "accountId": 16,
      "accountNumber": "ACC-2024-000002",
      "type": "Current",
      "balance": 10000.00,
      "currency": "BDT",
      "status": "Active",
      "openingDate": "2024-01-12"
    }
  ]
}
```

**Authorization:** Customer (sees own accounts); Employee/Admin (sees all).

---

#### `GET /accounts/:accountId`

Get detailed information for a single account, including recent transactions.

**Query parameters:**
- `limit` (default: 10) — number of recent transactions
- `offset` (default: 0) — pagination offset

**Response (200):**
```json
{
  "success": true,
  "data": {
    "accountId": 15,
    "accountNumber": "ACC-2024-000001",
    "type": "Savings",
    "balance": 5000.00,
    "currency": "BDT",
    "status": "Active",
    "openingDate": "2024-01-10",
    "minimumBalance": 1000.00,
    "interestRate": 3.50,
    "recentTransactions": [
      {
        "transactionId": 501,
        "type": "Deposit",
        "amount": 500.00,
        "balanceBefore": 4500.00,
        "balanceAfter": 5000.00,
        "createdAt": "2024-01-15T10:30:00Z"
      }
    ]
  }
}
```

**Authorization:** Customer (only if owns account); Employee/Admin.

---

#### `POST /accounts`

Create a new account application.

**Request:**
```json
{
  "accountTypeId": 1,
  "branchId": 1
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "accountId": 17,
    "accountNumber": "ACC-2024-000003",
    "status": "Pending",
    "message": "Account application submitted. An employee will review it shortly."
  }
}
```

**Authorization:** Customer (applies), Employee (creates for customer).

**Validation:**
- Account type must exist.
- Branch must exist.
- If `single_per_customer = TRUE` for this type, customer must not already have one.

---

### Transaction Endpoints

#### `POST /transactions/deposit`

Deposit funds into an account.

**Request:**
```json
{
  "accountId": 15,
  "amount": 500.00,
  "description": "Monthly savings"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "transactionId": 502,
    "referenceNumber": "TXN-20240115-000502",
    "type": "Deposit",
    "amount": 500.00,
    "newBalance": 5500.00,
    "createdAt": "2024-01-15T11:00:00Z"
  }
}
```

**Authorization:** Customer (to own account); Employee (any account).

**Validation:**
- Account must exist and be Active.
- Amount must be > 0.
- (Optional) Amount must not exceed daily deposit limit.

---

#### `POST /transactions/withdraw`

Withdraw funds from an account.

**Request:**
```json
{
  "accountId": 15,
  "amount": 200.00,
  "description": "Cash withdrawal"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "transactionId": 503,
    "referenceNumber": "TXN-20240115-000503",
    "type": "Withdrawal",
    "amount": 200.00,
    "newBalance": 5300.00,
    "createdAt": "2024-01-15T11:05:00Z"
  }
}
```

**Authorization:** Customer (own account); Employee (any account).

**Validation:**
- Account must be Active.
- Amount > 0.
- Balance after withdrawal must be ≥ 0 (no overdraft).

**Error responses:**
- 400: "Insufficient balance. Maximum withdrawal: $5,300"
- 400: "Daily withdrawal limit exceeded."

---

### Transfer Endpoints

#### `POST /transfers`

Initiate a transfer between accounts.

**Request:**
```json
{
  "fromAccountId": 15,
  "toAccountId": 16,
  "amount": 1000.00,
  "description": "Savings to checking"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "transferId": 42,
    "debitTransactionId": 504,
    "creditTransactionId": 505,
    "fromBalance": 4300.00,
    "toBalance": 11000.00,
    "fee": 0.00,
    "createdAt": "2024-01-15T11:10:00Z"
  }
}
```

**Authorization:** Customer (if from account is owned); Employee/Admin (any accounts).

**Validation:**
- Both accounts must exist and be Active.
- From account must have sufficient balance.
- Amount > 0.

**Atomic behavior:**
- If any check fails, neither transaction is created (ROLLBACK).
- Debit and credit are paired in a single `transfers` row.

---

#### `GET /transfers/:transferId`

Get transfer details.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "transferId": 42,
    "fromAccount": "ACC-2024-000001",
    "toAccount": "ACC-2024-000002",
    "amount": 1000.00,
    "fee": 0.00,
    "status": "Completed",
    "createdAt": "2024-01-15T11:10:00Z"
  }
}
```

---

### Loan Endpoints

#### `POST /loans`

Apply for a loan.

**Request:**
```json
{
  "loanType": "Personal",
  "principalAmount": 10000.00,
  "durationMonths": 60
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "loanId": 8,
    "loanType": "Personal",
    "principalAmount": 10000.00,
    "durationMonths": 60,
    "status": "Pending",
    "applicationDate": "2024-01-15",
    "message": "Loan application submitted. An employee will review it within 2-3 business days."
  }
}
```

**Authorization:** Customer (applies); Employee (creates for customer).

**Validation:**
- Loan type must be one of: Personal, Home, Auto, Education.
- Principal must be > 0 and within configured limits for the type.
- Duration must be > 0 and ≤ configured max (e.g., 84 months for Personal).

---

#### `GET /loans`

List loans for the authenticated customer (or all loans if Admin).

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "loanId": 8,
      "loanType": "Personal",
      "principalAmount": 10000.00,
      "interestRate": 6.00,
      "monthlyInstallment": 193.33,
      "outstandingBalance": 10000.00,
      "status": "Pending",
      "applicationDate": "2024-01-15",
      "approvalDate": null
    }
  ]
}
```

---

#### `POST /loans/:loanId/approve` (Employee only)

Approve a pending loan application.

**Request:**
```json
{
  "interestRate": 5.50
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "loanId": 8,
    "status": "Approved",
    "interestRate": 5.50,
    "monthlyInstallment": 187.50,
    "approvalDate": "2024-01-16",
    "message": "Loan approved. Funds will be disbursed to your account within 1 business day."
  }
}
```

**Authorization:** Employee (loan officer) at the customer's branch; Admin.

**Behavior:**
- Generate EMI based on principal, rate, and duration.
- Create repayment schedule in `loan_payments` table.
- Set status = 'Approved'.

---

#### `POST /loans/:loanId/disburse` (Employee only)

Disburse an approved loan (transfer funds to customer's account).

**Request:**
```json
{
  "toAccountId": 15
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "loanId": 8,
    "status": "Active",
    "disbursedAmount": 10000.00,
    "transactionId": 506,
    "newAccountBalance": 15000.00
  }
}
```

**Authorization:** Employee; Admin.

**Behavior:**
- Create a LoanDisbursement transaction.
- Increment account balance.
- Set loan status = 'Active'.

---

### Admin Endpoints

#### `POST /admin/users` (Admin only)

Create a new user account (Customer, Employee, or Admin).

**Request:**
```json
{
  "email": "newemployee@bank.com",
  "password": "InitialPass123",
  "role": "Employee",
  "linkedEmployeeId": 42,
  "branchId": 1
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "userId": 100,
    "email": "newemployee@bank.com",
    "role": "Employee",
    "status": "Active",
    "createdAt": "2024-01-16T09:00:00Z"
  }
}
```

**Authorization:** Admin only.

---

#### `POST /admin/branches` (Admin only)

Create a new branch.

**Request:**
```json
{
  "branchName": "Dhaka East Branch",
  "address": "123 Main Street, Dhaka",
  "phone": "+8801234567890",
  "openingDate": "2024-02-01"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "branchId": 5,
    "branchName": "Dhaka East Branch",
    "status": "Active"
  }
}
```

**Authorization:** Admin only.

---

This API documentation is extensive and covers the main operations. Additional endpoints for card management, notifications, audit logs, and reporting would follow similar patterns.

---

## 📁 Folder Structure

The project is organized into clear layers and concerns:

```
bankease/
│
├── frontend/
│   ├── index.html              ← Single-page public landing site (implemented)
│   ├── css/
│   │   ├── styles.css          ← Main stylesheet (responsive, Grid/Flexbox)
│   │   └── responsive.css      ← Mobile breakpoints
│   ├── js/
│   │   ├── main.js             ← Navigation, DOM interaction, calculators
│   │   ├── api.js              ← fetch() wrappers for API calls (when backend exists)
│   │   └── validation.js       ← Client-side form validation
│   └── assets/
│       ├── logo.png            ← Bank logo placeholder
│       ├── hero-image.jpg      ← Hero section background
│       └── branch-map.png      ← Branch locator map
│
├── backend/                    ← Node.js + Express API (designed, not yet implemented)
│   ├── src/
│   │   ├── index.js            ← Entry point, Express app setup
│   │   ├── config.js           ← Environment variables, database connection
│   │   ├── middleware/
│   │   │   ├── auth.js         ← JWT verification middleware
│   │   │   ├── authorize.js    ← Role-based authorization middleware
│   │   │   ├── errorHandler.js ← Centralized error handling
│   │   │   └── logger.js       ← Request logging (optional)
│   │   ├── routes/
│   │   │   ├── auth.js         ← POST /auth/login, /auth/register
│   │   │   ├── accounts.js     ← GET/POST /accounts, account-related endpoints
│   │   │   ├── transactions.js ← POST /deposit, /withdraw, GET transaction history
│   │   │   ├── transfers.js    ← POST /transfers, GET /transfers/:id
│   │   │   ├── loans.js        ← POST /loans, GET /loans, /approve, /disburse
│   │   │   ├── cards.js        ← GET /cards, POST /activate, /block
│   │   │   └── admin.js        ← POST /admin/users, /branches, /reports
│   │   ├── controllers/
│   │   │   ├── authController.js  ← Logic for registration/login
│   │   │   ├── accountController.js ← Logic for account operations
│   │   │   ├── transactionController.js ← Logic for deposits/withdrawals
│   │   │   ├── transferController.js ← Logic for transfers
│   │   │   ├── loanController.js ← Logic for loan operations
│   │   │   └── adminController.js ← Admin operations
│   │   ├── services/
│   │   │   ├── AuthService.js  ← Password hashing, JWT generation
│   │   │   ├── AccountService.js ← Account CRUD and validation
│   │   │   ├── TransactionService.js ← Transaction creation, atomicity
│   │   │   ├── TransferService.js ← Atomic debit+credit logic
│   │   │   ├── LoanService.js  ← EMI calculation, loan logic
│   │   │   └── NotificationService.js ← Create notifications
│   │   ├── models/
│   │   │   ├── queries.js      ← Raw SQL queries (or ORM entities)
│   │   │   └── utils.js        ← Data transformation helpers
│   │   └── utils/
│   │       ├── jwt.js          ← JWT helpers
│   │       ├── bcrypt.js       ← Password hashing helpers
│   │       ├── validation.js   ← Server-side validation functions
│   │       └── dateUtils.js    ← Date/time utilities (for EMI, interest calc)
│   ├── package.json            ← Dependencies (express, mysql2, bcrypt, jsonwebtoken, etc.)
│   ├── .env.example            ← Environment variables template
│   └── README.md               ← Backend setup instructions
│
├── database/
│   ├── schema.sql              ← SQL file to create all tables, indexes, constraints
│   ├── init-data.sql           ← (Optional) Sample data for testing
│   ├── migrations/             ← (Future) Database versioning
│   └── README.md               ← Database setup instructions
│
├── docs/
│   ├── Banking_System_Master_Documentation.docx ← Comprehensive spec
│   ├── API_Design.md           ← API specification
│   ├── Database_Design.md      ← Schema documentation
│   ├── Architecture.md         ← System architecture overview
│   └── USER_GUIDE.md           ← End-user documentation
│
├── tests/
│   ├── unit/                   ← Unit tests (functions, services)
│   │   ├── utils.test.js
│   │   └── services.test.js
│   ├── integration/            ← Integration tests (API endpoints + DB)
│   │   ├── auth.test.js
│   │   ├── accounts.test.js
│   │   └── transfers.test.js
│   └── fixtures/               ← Test data
│
├── .gitignore                  ← Git ignore rules
├── README.md                   ← This file
├── LICENSE                     ← Academic license
└── .env.example                ← Environment variable template
```

### Folder Purpose Breakdown

- **`frontend/`** — Everything the browser receives: HTML markup, CSS styling, client-side JavaScript. Currently just `index.html` (the public landing page). Will expand to include dashboard pages once the backend is built.

- **`backend/`** — Node.js application. Organized by concern (routes, controllers, services) to keep business logic separate from HTTP handling. Not yet implemented; the structure above is a proposed organization.

- **`database/`** — SQL schema and scripts. `schema.sql` will create all tables, indexes, constraints, and (optionally) stored procedures. Migration files (if using a migration tool) would track schema changes over time.

- **`docs/`** — Comprehensive documentation: master spec, API design, architecture diagrams, user guides. The source of truth for system design.

- **`tests/`** — Automated tests: unit tests (individual functions), integration tests (API endpoints against a real database). Not yet written; testing strategy is outlined in [Testing Strategy](#-testing-strategy).

---

## 🛠️ Installation Guide

This guide covers setting up the complete project (frontend, backend, and database) on a local machine for development.

### Prerequisites

Before starting, ensure you have the following installed:

- **Git** — Version control; download from https://git-scm.com/
- **Node.js** (v14 or higher) — JavaScript runtime; download from https://nodejs.org/
  - Includes `npm` (Node Package Manager) for dependency management.
- **MySQL 8** — Relational database; download from https://dev.mysql.com/downloads/mysql/
  - Or use **Docker** if available (see Docker setup below).
- **A code editor** — Visual Studio Code, WebStorm, or similar.

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/bankease.git
cd bankease
```

### Step 2: Frontend Setup

The frontend requires no build step or dependencies; it's pure HTML/CSS/JavaScript.

**To view the frontend locally:**

**Option A: Using Python's built-in server (simple)**
```bash
cd frontend
python3 -m http.server 8000
# Or on Windows: python -m http.server 8000

# Open browser: http://localhost:8000
```

**Option B: Using Node.js http-server (if preferred)**
```bash
npm install -g http-server
cd frontend
http-server -p 8000

# Open browser: http://localhost:8000
```

**Option C: Direct file opening (simplest, but no hot-reload)**
```bash
# Directly open frontend/index.html in a browser
# This works, but avoid for anything requiring API calls (will fail due to CORS)
```

### Step 3: Database Setup

**Option A: MySQL CLI (recommended)**

1. Start MySQL:
```bash
# macOS (if installed via Homebrew)
mysql.server start

# Windows (start MySQL service from Services)
# Linux: sudo systemctl start mysql
```

2. Connect to MySQL and create the database:
```bash
mysql -u root -p
# Enter password when prompted

# Inside MySQL CLI:
CREATE DATABASE banking_system;
USE banking_system;

# Import the schema:
SOURCE database/schema.sql;

# Verify tables were created:
SHOW TABLES;
```

3. Create a database user for the backend:
```sql
CREATE USER 'bankease_user'@'localhost' IDENTIFIED BY 'SecurePassword123';
GRANT ALL PRIVILEGES ON banking_system.* TO 'bankease_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Option B: Docker (if installed)**

```bash
# Run MySQL in a container
docker run --name bankease-mysql \
  -e MYSQL_ROOT_PASSWORD=rootpass \
  -e MYSQL_DATABASE=banking_system \
  -p 3306:3306 \
  -d mysql:8

# Wait for the container to start, then run the schema:
docker exec -i bankease-mysql mysql -u root -prootpass banking_system < database/schema.sql
```

### Step 4: Backend Setup (When Ready)

Once the backend code is implemented:

1. **Install dependencies:**
```bash
cd backend
npm install
```

2. **Create environment file:**
```bash
cp .env.example .env

# Edit .env with your database credentials:
# DATABASE_HOST=localhost
# DATABASE_USER=bankease_user
# DATABASE_PASSWORD=SecurePassword123
# DATABASE_NAME=banking_system
# JWT_SECRET=your_very_long_random_secret_key_here
# PORT=3000
```

3. **Start the backend server:**
```bash
npm start
# Or for development with auto-reload:
npm run dev
```

The backend will start on `http://localhost:3000`.

### Step 5: Frontend-Backend Integration (When Ready)

Once the backend is running, update the frontend's `api.js` to point to your backend:

```javascript
// frontend/js/api.js
const API_BASE_URL = 'http://localhost:3000/api';

async function login(email, password) {
  const response = await fetch(`${API_BASE_URL}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  return response.json();
}
```

### Step 6: Verify Everything

1. **Frontend:** Open `http://localhost:8000` in your browser. You should see the Meridian Trust Bank landing page.
2. **Backend:** Open `http://localhost:3000/api/health` (once implemented) or check the terminal for startup messages.
3. **Database:** From MySQL CLI:
```bash
mysql -u bankease_user -p banking_system
SHOW TABLES;
SELECT COUNT(*) FROM customers;
```

All three should be running without errors.

### Troubleshooting

**Port already in use:**
```bash
# If port 3000 or 8000 is already in use, change it in .env or http-server:
http-server -p 9000  # Use port 9000 instead
```

**Database connection failed:**
- Verify MySQL is running: `mysql -u root -p` (should connect)
- Check `database/schema.sql` syntax
- Verify credentials in `.env` match the user you created

**Module not found (backend):**
```bash
cd backend
rm -rf node_modules
npm install
```

**CORS errors (frontend calling backend):**
- Ensure the backend is running
- Check that the frontend's `API_BASE_URL` matches the backend URL
- The backend should include CORS headers (Express middleware)

---

## 🖥️ Pages & Screens

### Currently Implemented

**`frontend/index.html`** — A single, scrollable public landing page with:

- **Hero Section** — Eye-catching header with bank name (Meridian Trust Bank), tagline, and primary CTA button ("Open Account").
- **About & History** — Bank overview and establishment story.
- **Leadership** — Management team profiles (placeholder images/text).
- **Services** — Tabbed navigation:
  - Accounts (Savings, Current, Student, Salary)
  - Cards (Debit, Credit, Prepaid)
  - Loans (Personal, Home, Auto, Education)
  - Corporate (Business accounts)
- **Account Types** — Detailed product cards (interest rates, features, minimum balance).
- **Interactive Calculators** — Client-side (no server calls):
  - **Loan EMI Calculator** — Input principal, rate, term; displays monthly payment.
  - **Savings Calculator** — Input principal, rate; displays compound interest over time.
  - **Fixed Deposit Calculator** — Similar to Savings for FD products.
- **Demo Transfer Form** — A non-functional form showcasing the UI/UX for transfers. Submission shows a success message (front-end only, no data sent).
- **Branch Locator** — List and map of branch locations.
- **Security & Trust** — Messaging about security, encryption, fraud prevention, deposit insurance.
- **Rates & Offers** — Current interest rates and promotional offers (static content).
- **FAQ** — Frequently asked questions and answers.
- **Footer** — Site links, contact, copyright, social media links.

All pages are **responsive** (mobile, tablet, desktop) and use vanilla JavaScript for interactivity (no API calls in current implementation).

### Designed (Not Yet Built)

#### Public Site Pages

| Page | Purpose | Key elements |
|---|---|---|
| **Login** | Authenticate customers and employees | Email/password form, "Forgot password?" link, redirect to dashboard |
| **Register** | New customer sign-up | Full registration form (name, email, phone, DOB, ID, address, password), terms checkbox |

#### Customer Dashboard Pages

| Page | Purpose | Key elements |
|---|---|---|
| **Overview / Dashboard** | Customer home | Summary cards (total balance, recent transactions), quick action buttons (Deposit, Withdraw, Transfer), account list |
| **Profile** | Manage personal details | Editable fields (phone, email, address), read-only KYC fields, change password |
| **Accounts** | View all accounts | List of accounts with balance, type, status, links to account detail |
| **Account Details** | Single account view | Account number, balance, type, status, opening date, recent transactions, action buttons |
| **Transaction History** | View all transactions across accounts | Filterable/sortable table (amount, type, date, account, balance), export to CSV (optional) |
| **Deposit** | Add funds to an account | Select account, enter amount, confirm, show receipt |
| **Withdrawal** | Remove funds | Select account, enter amount, confirm, show receipt with new balance |
| **Transfer** | Move money between accounts | Select from/to account (or choose beneficiary), enter amount, confirm, show receipt |
| **Beneficiaries** | Manage saved transfer recipients | Add beneficiary (account number + nickname), list with edit/delete |
| **Cards** | View and manage cards | List (card type, masked number, status), action buttons (Activate, Block, Request Replacement) |
| **Loans** | Apply and manage loans | List of loans (type, principal, status, monthly payment), Apply button, link to loan detail |
| **Loan Detail** | View single loan | Principal, rate, term, monthly installment, outstanding balance, repayment schedule, Make Payment button |
| **Loan Payments** | Make and view payments | Upcoming due payments, payment form, payment history |
| **Notifications** | View in-app alerts | List (newest first), mark as read, delete (optional), filter by type |
| **Statements** | Download account statements | Date range picker, download PDF/CSV |
| **Settings** | User preferences | Change password, notification preferences, profile picture (optional), security settings |

#### Employee Dashboard Pages

| Page | Purpose | Key elements |
|---|---|---|
| **Dashboard / Branch Summary** | Overview of branch | KPIs (total customers, active accounts, pending loans, daily volume), quick access to common tasks |
| **Customer Search & Management** | Find and manage customers | Search bar (name, email, phone), list results, view customer detail, create new customer |
| **Customer Detail** | Single customer profile | All info, linked accounts, loans, cards, action buttons (update profile, freeze/close account) |
| **Account Applications** | Pending account requests | List of "Pending" status accounts, review details, Approve/Reject buttons |
| **Account Management** | Manage customer accounts | List (account number, customer, type, balance, status), action buttons (Freeze, Close) |
| **Transactions** | Process transactions on customer's behalf | Select customer/account, choose transaction type (Deposit, Withdrawal, Transfer), enter amount, confirm |
| **Loan Applications** | Review pending loan requests | List (customer, loan type, principal, term), View button → detail page → Approve/Reject buttons |
| **Loan Approval Workflow** | Detailed loan review | Loan details, customer info, EMI calculator preview, interest rate override (optional), Approve/Reject with optional note |
| **Card Management** | Issue and manage cards | Issue new card (customer, account, type), list issued cards with action buttons (Activate, Block, Cancel) |
| **Reports** | Branch-level reports | Filters (date range, type), tables/charts (transactions, loans, customers, cards) |
| **Activity Log** | Employee's own actions | List of actions performed by this employee (for audit) |

#### Admin Dashboard Pages

| Page | Purpose | Key elements |
|---|---|---|
| **System Overview** | Bank-wide KPIs | Total deposits, total loans outstanding, active customers, flagged accounts, trends (charts) |
| **User Management** | Create and manage all users | Search bar, list all users (email, role, status), Create User button, action buttons (Suspend, Lock, Unlock, Delete) |
| **Employee Management** | Manage staff | List (name, branch, position, status), Create Employee button, edit/delete actions |
| **Role Management** | Configure RBAC | List of roles (Customer, Employee, Admin), permission matrix (role × module), edit permissions |
| **Branch Management** | Manage branches | List (name, address, manager, status), Create/Edit/Delete Branch |
| **Account Type Management** | Manage account products | List (name, interest rate, minimum balance, single_per_customer), Create/Edit/Delete |
| **System Configuration** | Bank-wide settings | Forms for default interest rates, transaction limits, loan limits by type, etc. |
| **Audit Logs** | Search immutable audit trail | Advanced filters (user, action, date range, result), table of logs with details |
| **Security Monitoring** | Detect suspicious activity | Flagged logins (too many failures), locked accounts, large transactions, unusual patterns |
| **System Reports** | Bank-wide reporting | Dashboards/charts for various KPIs, exportable to PDF/Excel |

### Frontend Design Requirements

Across all pages:

- **Responsive layout** — Mobile first; works on 320px phones, tablets (768px), and desktop (1024px+).
- **Consistent navigation** — Public site has top navbar; authenticated dashboards have persistent sidebar.
- **Reusable components:**
  - **Cards** — Container for summary data (balance card, account card, etc.).
  - **Tables** — Sortable columns, pagination, filtering (for transaction history, reports, etc.).
  - **Forms** — Inline validation (color change, error message below field), submit button disabled until valid.
  - **Buttons** — Primary (blue, CTA), secondary (white/gray), danger (red, for destructive actions).
  - **Modals** — Confirmation dialogs before irreversible actions (close account, block card, reject loan).
  - **Toast alerts** — Success/error messages (appear at top-right, auto-dismiss after 3-5 seconds).
- **Loading states** — Spinner while API call is in flight; disabled buttons to prevent double-submit.
- **Empty states** — "No transactions yet" message instead of blank table, with link to take action (Deposit, Transfer, etc.).

---

## 🧪 Testing Strategy

Comprehensive testing ensures correctness, security, and reliability. Tests are organized by layer and type.

### Unit Testing

**What:** Test individual functions in isolation (no database, no HTTP).

**Examples:**
- `fn_calculate_emi(principal=10000, rate=6, months=60)` should return 193.33.
- `isValidEmail('test@example.com')` should return true; `isValidEmail('test')` should return false.
- `hashPassword('pass123')` should return a bcrypt hash, not plaintext.
- `validateTransferAmount(1000, balance=500)` should return error "Insufficient balance".

**Tools:** Jest or Mocha + Chai (Node.js testing frameworks).

**Coverage target:** ≥ 80% of utility and service functions.

### Integration Testing

**What:** Test API endpoints against a real (test) database. Verify request/response and side effects.

**Examples:**
- `POST /api/auth/login` with valid credentials should return a JWT token.
- `POST /api/transfers` should debit one account and credit another atomically.
- `POST /api/loans` should insert a loan row with status='Pending'.
- `GET /api/accounts` should return only the caller's accounts (authorization check).

**Tools:** Supertest (for HTTP request testing) + Jest + a test database (MySQL, seeded with test data).

**Process:**
1. Start a test MySQL database (via Docker or a separate MySQL instance).
2. Run migrations/schema on the test database.
3. Seed with test data (e.g., 5 test customers with accounts).
4. Run API tests against the test database.
5. Clean up (rollback) after each test.

**Coverage target:** All critical endpoints (auth, accounts, transfers, loans).

### Database Testing

**What:** Verify constraints, triggers, and referential integrity.

**Examples:**
- `INSERT INTO accounts (balance=-100)` should be rejected (CHECK constraint).
- `DELETE FROM customers` should reject (CASCADE or RESTRICT rules).
- `INSERT INTO transactions` with an invalid `account_id` should be rejected (FK constraint).
- A transfer should debit one account and credit another in a single transaction (all-or-nothing).

**Tools:** Direct SQL queries against test database; assertions on row counts, constraint errors.

### User Acceptance Testing (UAT)

**What:** End-to-end workflows tested as each role (Customer, Employee, Admin).

**Scenarios:**
1. **Customer Journey:**
   - Register → Email verification → Login → Open Savings Account → Deposit $500 → Transfer to Another Account → View Transaction History
2. **Employee Journey:**
   - Login → View pending account applications → Approve one → View branch summary → Issue a card → Approve a loan
3. **Admin Journey:**
   - Login → View bank-wide KPIs → Create new user → View audit logs → Configure interest rates

**Method:** Manual testing by a tester or peer, or automated via Selenium/Cypress for end-to-end automation (future enhancement).

### Testing Checklist

Before submission, verify:

- [ ] All unit tests pass (`npm test`)
- [ ] All integration tests pass against test database
- [ ] Database constraints are tested (overflow, FK violations, etc.)
- [ ] Authorization is tested (Customer can't access another customer's data)
- [ ] Error responses are tested (400 Bad Request, 401 Unauthorized, 403 Forbidden, 500 Server Error)
- [ ] SQL injection and XSS prevention are tested
- [ ] Account lockout after failed logins is tested
- [ ] Transfer atomicity is tested (debit and credit both succeed or both rollback)
- [ ] E2E user workflows (register → login → open account → transfer) are tested manually

---

## 🚀 Future Improvements

These are natural next steps and enhancements beyond the current academic scope. They are **not part of the current deliverable** but are documented to guide future development.

### Technology & Platform Enhancements

- **Mobile Application** — Native or cross-platform (React Native, Flutter) consuming the same REST API.
- **Progressive Web App (PWA)** — Offline capability, home screen installation, push notifications.
- **Microservices Decomposition** — Split the monolithic backend into services (Auth Service, Account Service, Loan Service) for scalability.
- **GraphQL API** — Alternative to REST, allowing clients to request exact fields needed.
- **Cloud Deployment** — AWS (RDS for MySQL, EC2 or ECS for backend, S3 for static frontend), Google Cloud, or Azure.
- **Containerization** — Docker images for backend and database; Kubernetes orchestration for scaling.

### Security & Compliance

- **Two-Factor Authentication (2FA)** — SMS or authenticator app for additional login security.
- **Encryption at Rest** — Database encryption (Transparent Data Encryption in MySQL 8).
- **API Rate Limiting** — Prevent brute-force attacks on login endpoints.
- **Account Verification** — Email/SMS verification during registration.
- **Biometric Authentication** — Fingerprint/face recognition for mobile app.
- **GDPR/Compliance** — Data export, right to be forgotten, audit trail requirements.

### Financial & Business Features

- **Credit Scoring** — Algorithm based on repayment history, transaction patterns, and credit utilization.
- **Fraud Detection** — Machine-learning-based anomaly detection (unusual transaction patterns, geographic anomalies).
- **Investment Accounts** — Stocks, bonds, mutual fund portfolios.
- **Fixed Deposit (FD) Products** — Time-locked savings with higher interest rates.
- **Interest Accrual & Credit** — Automatic monthly interest calculation and posting to accounts.
- **ATM Simulation** — ATM cash withdrawal workflows and network simulation.
- **International Transfers** — Multi-currency support and exchange rate management.
- **Overdraft Facility** — Configurable overdraft limits with interest charges.

### Reporting & Analytics

- **Advanced Analytics Dashboard** — Real-time charts, heatmaps, predictive trends.
- **PDF Report Generation** — Export account statements, loan schedules, bank reports as PDFs.
- **Excel Export** — Export transaction history, customer lists, loan portfolios to Excel.
- **Real-Time Dashboards** — WebSockets for live KPI updates.

### Developer Experience

- **API Documentation** — Swagger/OpenAPI documentation with interactive testing (Swagger UI).
- **SDK Generation** — Auto-generated client SDKs (JavaScript, Python, Java, etc.) from API spec.
- **Webhook Support** — External system integration (e.g., notify accounting system of transactions).
- **Plugin Architecture** — Allow third parties to extend functionality.

### User Experience

- **Dark Mode** — Optional dark theme for the frontend.
- **Internationalization (i18n)** — Multi-language support (Bangla, Hindi, English, etc.).
- **Accessibility (a11y)** — WCAG 2.1 AA compliance for users with disabilities.
- **Advanced Search** — Full-text search across transactions, accounts, customers.
- **Notifications** — Email, SMS, push notifications (not just in-app).
- **Mobile-First Redesign** — Optimize mobile UX (larger buttons, touch gestures, etc.).
- **Customizable Dashboard** — Users can arrange cards, choose metrics to display.

### Quality & Reliability

- **Automated Testing** — Expand coverage to 90%+ with unit, integration, and E2E tests.
- **Continuous Integration/Deployment (CI/CD)** — GitHub Actions or Jenkins for automated test + deploy.
- **Performance Optimization** — Database query optimization, caching (Redis), CDN for static assets.
- **Monitoring & Alerting** — Application Performance Monitoring (APM), error tracking (Sentry), uptime monitoring.
- **Disaster Recovery** — Automated database backups, failover replicas, disaster recovery plan.

### Data & Insights

- **Data Lake** — Centralized repository of all banking data for analytics.
- **Business Intelligence (BI)** — Tools like Tableau, Power BI for executive dashboards.
- **Predictive Analytics** — Forecast loan defaults, customer churn, revenue trends.

---

## ⚠️ Project Limitations

As an academic banking simulator, this system deliberately does **not** attempt to solve the following real-world concerns. Understanding these limitations is essential to the system's proper use and interpretation.

### No Real Money Movement

- **Fact:** Every balance is a row in this project's own MySQL database. No real funds change hands.
- **Implication:** This system must never be used to store real customer financial data or conduct actual banking transactions.

### No Real Banking Infrastructure

- **No SWIFT/ACH/RTGS Network Integration** — International and interbank transfers don't route through real banking networks; they're simulated entirely within the system.
- **No Real Card Networks** — Visa, Mastercard, and other card processing networks are not contacted. Card numbers are simulated and masked.
- **No Real Payment Gateways** — No integration with payment processors (Stripe, PayPal, etc.).

### No Real Identity Verification (KYC/AML)

- **Identity Fields Are Unverified** — ID numbers, names, and dates of birth are self-reported by customers and not validated against government databases or identity verification services.
- **No Anti-Money Laundering (AML)** — Transactions are not screened against sanctions lists or analyzed for suspicious patterns indicative of money laundering.
- **No Know Your Customer (KYC) Verification** — No checks against government ID databases or biometric verification.

### Interest Calculations Are Simplified

- **Fixed Rates Only** — Interest rates don't change; they're static at time of account opening.
- **Simplified Day-Count Conventions** — Real banking uses complex day-count rules (30/360, Actual/Actual, etc.); this system uses simple month-based accrual.
- **No Rate Resets** — Loan rates don't adjust based on market conditions or central bank rates.

### No Regulatory Infrastructure

- **No Deposit Insurance** — Real banks deposit insurance (e.g., FDIC in the US, DICGC in India, or Bangladesh Bank guarantee) protects customer funds; this system has none.
- **No Reserve Requirements** — Real banks must hold regulatory capital reserves; this system has no such constraints.
- **No Audit or Compliance Regime** — While the system includes audit logging, there's no integration with regulatory reporting or compliance frameworks.

### Limited Scalability (Academic Scope)

- **Single-Instance Backend** — The current architecture assumes one backend server. Production banking systems require load balancing, clustering, and horizontal scaling.
- **Single-Server Database** — MySQL is single-instance. Real banks use replicated, sharded databases across multiple data centers.
- **No Disaster Recovery** — No backup sites, failover replicas, or data center redundancy.

### Simplified Loan Workflows

- **No Credit Checks** — Loans are approved based on simple approval workflows, not credit scores or bureau checks.
- **No Collateral Management** — Home and auto loans don't track the underlying property/vehicle or enforce lien structures.
- **Simplified Repayment** — EMI calculation is standard; real-world loans involve prepayment penalties, rate resets, restructuring options, etc.

### No Physical Touchpoints

- **No ATM Simulation (yet)** — Customers can't withdraw cash via ATM; only in-app transfers are supported.
- **No Branch Teller Workflow** — No simulation of in-person customer service (e.g., teller assistance).
- **No Phone/Chat Banking** — No support for customer service channels beyond the web interface.

### Data Privacy & Security (Development Context)

- **Development Uses HTTP (Not HTTPS)** — Local development typically uses unencrypted HTTP. Production must use HTTPS.
- **Test Data Is Synthetic** — For testing, fake customer data is used. This system must never be populated with real customer information.
- **No Production-Grade Encryption** — Passwords are hashed (bcrypt), but this is an academic system. Production banking requires hardware security modules, encrypted key storage, etc.

### Simplified Card Processing

- **Card Numbers Are Hashed, Not Tokenized** — Real card processors tokenize card data to minimize fraud risk. This system uses hashing and masking.
- **No Real-Time Fraud Checks** — Transactions aren't checked against real-time fraud-detection networks.
- **No Chargeback Process** — Credit card chargebacks (customer disputes) aren't simulated.

**These limitations do not diminish the educational value of the project.** They are deliberate design choices to keep the scope manageable for an academic exercise while still demonstrating real banking concepts (transactions, ACID guarantees, RBAC, audit trails, etc.).

---

## 🎓 Learning Outcomes

Successfully building and deploying BankEase demonstrates practical understanding of:

### Database Concepts

- **Relational Modeling** — Designing entities, attributes, and relationships (1:1, 1:M, M:M) to represent a complex business domain (banking).
- **Normalization (1NF–3NF)** — Eliminating redundancy and anomalies; understanding trade-offs (3NF vs. denormalization for performance).
- **Primary & Foreign Keys** — Ensuring uniqueness and referential integrity across tables.
- **Constraints** — Using NOT NULL, UNIQUE, CHECK, DEFAULT to enforce business rules at the database layer.
- **Indexing** — Choosing which columns to index for read performance vs. write overhead.
- **ACID Transactions** — Guaranteeing atomicity, consistency, isolation, durability; using BEGIN...COMMIT...ROLLBACK.
- **Stored Procedures & Triggers** — Writing database-level logic (e.g., auto-updating `balance_after` on a transaction insert).
- **Views** — Creating virtual tables (e.g., a customer's account summary view).

### Backend Concepts

- **REST API Design** — Structuring endpoints, HTTP methods, request/response formats, status codes.
- **Request/Response Lifecycle** — Understanding how a browser request flows through frontend → backend → database → response → frontend.
- **Middleware** — Implementing cross-cutting concerns (authentication, logging, error handling).
- **Layered Architecture** — Separating routes, controllers, services, and models for maintainability.
- **Error Handling** — Designing consistent error responses and exception handling strategies.
- **Database Transactions in Code** — Wrapping money-movement operations in transactions to guarantee atomicity.
- **Parameterized Queries** — Preventing SQL injection using prepared statements.

### Frontend Concepts

- **Responsive Design** — Using CSS Grid/Flexbox to build layouts that adapt to mobile, tablet, and desktop.
- **Semantic HTML** — Writing accessible, well-structured markup (proper heading hierarchy, form labels, etc.).
- **Client-Side Validation** — Checking input before submission (never as the only defense; server-side validation is essential).
- **Fetch API & Async/Await** — Making asynchronous HTTP requests and handling responses.
- **Component-Style Patterns** — Building reusable UI elements (cards, tables, forms, modals) using CSS classes.
- **State Management** — Managing application state in the browser (e.g., storing the logged-in user's JWT token).
- **Accessibility** — Ensuring the UI is usable for people with disabilities (keyboard navigation, screen reader compatibility, color contrast).

### Security Concepts

- **Password Hashing** — Understanding bcrypt and why plaintext passwords are never acceptable.
- **Authentication (JWT)** — Implementing stateless, token-based authentication.
- **Authorization (RBAC)** — Controlling what actions each role can perform.
- **SQL Injection Prevention** — Using parameterized queries to prevent malicious SQL.
- **Cross-Site Scripting (XSS) Prevention** — Sanitizing and encoding user input.
- **Secure Communication (HTTPS)** — Understanding encryption in transit.
- **Audit Logging** — Recording security-relevant actions for compliance and forensics.

### Software Engineering Concepts

- **Documentation-First Design** — Writing comprehensive specs before coding, enabling others to pick up the project.
- **Separation of Concerns** — Organizing code into layers so changes in one layer don't ripple through others.
- **Modularity** — Building independent modules (auth, accounts, loans, etc.) that can be developed and tested in isolation.
- **Version Control (Git)** — Tracking changes, collaborating with others, maintaining history.
- **Testing** — Writing unit, integration, and E2E tests to verify correctness and catch regressions.
- **Deployment** — Understanding the steps to take a finished application from local development to production.

### Real-World Banking Knowledge

- **Account Lifecycle** — How accounts are opened, managed, and closed; statuses and transitions.
- **Transactional Integrity** — Why a transfer must be atomic (debit and credit both succeed, or both fail).
- **Loan Origination & Repayment** — How loans are applied for, approved, disbursed, and repaid with interest.
- **Card Management** — Issuing, activating, blocking, and expiring cards; masked numbers and security.
- **Audit Trails** — Why every sensitive action is logged for compliance and fraud investigation.
- **Customer Segmentation & Roles** — Understanding different user roles (customer, employee, admin) and their distinct permissions.

---

## 👥 Contributors

| Name | Role | Affiliation | Contact |
|---|---|---|---|
| Akash Kumar Dey | Developer, Database Designer, Project Author | Computer Science & Engineering, Khulna University | *Add contact email* |

> **Note for the author:** add a contact email above, and confirm the exact course name below, before submission or publication — these are the only two details this README cannot supply on its own.

### Contributions

This project is developed as part of a university course (Database Systems / Web Development — confirm exact course title and instructor). The developer is responsible for all aspects: specification, database design, backend API design, frontend implementation, documentation, and submission.

### Acknowledgments

- **Course Instructors:** *Add supervisor/instructor name(s)* for guidance and feedback.
- **Peer Reviewers:** *Add names, if any peers reviewed or contributed feedback.*
- **Open Source Libraries:** Thanks to the authors and maintainers of Express.js, mysql2, bcrypt, jsonwebtoken, and other open-source projects used in this system.

---

## 📄 License

This project is developed for **academic purposes** as part of university coursework. It is provided as-is for educational use, learning, and portfolio demonstration.

### Important Disclaimer

**⚠️ This is not production banking software.** It must not be deployed to handle real customer funds, real personal financial data, or real payment credentials.

### Academic License Terms

You are free to:
- Use this project for educational purposes.
- Study and learn from the code, architecture, and documentation.
- Submit this project as coursework (ensuring you adhere to your institution's academic integrity policies).
- Use this project's structure and documentation as a template for your own coursework (with proper attribution).

You must:
- Attribute the original author (Akash Kumar Dey, Khulna University) if you reuse or adapt this project.
- Comply with your institution's academic integrity policies (e.g., not submitting unmodified coursework of another as your own).
- Not deploy this project to production or use it for real banking transactions.
- Not claim this software as production-ready or suitable for handling real customer data.

### Disclaimer on Dependencies

This project depends on open-source libraries (Express.js, MySQL2, bcrypt, etc.), each with their own licenses. Ensure you comply with those licenses when deploying or distributing this project.

### Contact for Licensing Questions

For questions about licensing or reuse of this project, contact the developer.

---

## 📞 Support & Feedback

### Getting Help

- **Documentation:** Refer to the comprehensive README.md (this file) and the docs/ folder.
- **Code Comments:** Each function and module has inline comments explaining its purpose and behavior.
- **Master Spec:** See docs/Banking_System_Master_Documentation.docx for the detailed specification.

### Reporting Issues or Suggesting Improvements

As this is an academic project, the primary feedback channels are:

- **Course Instructors:** For academic feedback and clarification on requirements.
- **Peer Review:** Share the project with classmates or mentors for constructive feedback.
- **GitHub Issues (if published):** If the repository is public on GitHub, open an issue to report bugs or suggest enhancements.

### Next Steps for Developers

If you are taking over this project:

1. **Read the master documentation** (docs/Banking_System_Master_Documentation.docx) to understand the full design.
2. **Review the database schema** (Section 5, Database Design) to understand the data model.
3. **Implement the backend** (Section 11, API Documentation) using the designed API spec.
4. **Write tests** (Section 15, Testing Strategy) as you implement.
5. **Integrate the frontend** (Section 14, Pages & Screens) with the backend API.
6. **Deploy** (Section 13, Installation Guide) to a development environment.
7. **Submit** to your instructors or course platform per your institution's process.

---

## 📚 Additional Resources

### External Learning Resources

- **MySQL & Relational Databases:**
  - MySQL Official Documentation: https://dev.mysql.com/doc/
  - "SQL Performance Explained" — free online book on database indexing and optimization
  - "Database Design Best Practices" — various blogs and tutorials

- **Node.js & Express.js:**
  - Express.js Official Guide: https://expressjs.com/
  - Node.js Best Practices: https://github.com/goldbergyoni/nodebestpractices

- **Security:**
  - OWASP Top 10: https://owasp.org/www-project-top-ten/
  - Bcrypt library: https://www.npmjs.com/package/bcrypt
  - JWT.io: https://jwt.io/

- **Frontend:**
  - MDN Web Docs: https://developer.mozilla.org/
  - CSS Grid & Flexbox: CSS-Tricks guides
  - Fetch API: MDN documentation

- **Version Control:**
  - Git documentation: https://git-scm.com/doc
  - GitHub Guides: https://guides.github.com/

---

## 🔍 Frequently Asked Questions (FAQ)

### General

**Q: Can I use this project for my own banking app?**
A: This is an academic simulator, not production software. Use the architecture and patterns as learning and as a reference, but do not deploy it to handle real customer data or real money.

**Q: What happens if I find a bug?**
A: If you're a developer on this project, document the bug and fix it. If you're reviewing it, note the bug in your feedback to the author.

**Q: Can I modify the schema or add features?**
A: Yes! Modify the database schema if needed, document your changes, and update the README. Ensure any changes align with your project's specification.

### Technical

**Q: Why is there no frontend-backend integration yet?**
A: The project is in two phases: Phase 1 (completed) is the frontend landing page; Phase 2 (in progress) is implementing the backend, which will integrate with the frontend's dashboard pages.

**Q: How do I test the backend APIs?**
A: Use Postman, Insomnia, or curl to make HTTP requests to the running backend. Include a JWT token in the Authorization header for authenticated endpoints.

**Q: Why use bcrypt for passwords instead of just hashing?**
A: Bcrypt includes a salt (randomness) and a cost factor (slowness), making it resistant to rainbow-table and brute-force attacks. Simple hashing algorithms (MD5, SHA1) are not suitable for passwords.

**Q: What's the difference between 1NF, 2NF, and 3NF?**
A: See Section 7 (Database Normalization) for a detailed explanation with examples.

### Project Scope

**Q: Is internationalization (i18n) planned?**
A: It's listed under Future Improvements, but not currently implemented. The system currently uses English.

**Q: Can the system handle multi-currency accounts?**
A: The schema has a `currency` field, but conversion and multi-currency transaction logic are not implemented.

**Q: Is two-factor authentication (2FA) supported?**
A: No, it's listed as a Future Improvement. Currently, only single-factor authentication (email + password) is designed.

---

## 📝 Document History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | January 15, 2024 | Akash Kumar Dey | Initial README creation; full project documentation |
| 1.1 | January 20, 2024 | Akash Kumar Dey | Added API documentation section; refined database design |
| (Pending) | (Future) | (Next Developer) | Backend implementation, frontend integration, testing, deployment |

---

**Document Generated:** January 15, 2024  
**Last Updated:** January 15, 2024  
**Status:** Complete (Academic Specification & Documentation)

---

**End of README**
