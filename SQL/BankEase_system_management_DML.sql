-- ============================================================
-- BankEase -- Banking Management System
-- Data Manipulation Language (DML) -- Sample Data
-- Group 19: Akash Kumer Dey (240230), Joyee Mitra Roti (240237)
-- Run after BankEase_system_management_DDL.sql
-- ============================================================
USE bankease;
SET NAMES utf8mb4;

-- 1. roles
INSERT INTO roles (role_name, description) VALUES
('Customer', 'Self-service banking customer'),
('Employee', 'Bank staff handling day-to-day branch operations (teller, customer service, loan officer)'),
('Administrator', 'Branch manager or head-office staff with reporting, approval and access-control rights');

-- 2. branches (manager_employee_id left NULL for now, set after employees are inserted)
INSERT INTO branches (branch_name, address, phone, opening_date, status) VALUES
('Khulna Sonadanga Branch', 'Sonadanga Bus Terminal Road, Khulna 9100', '01711223344', '2010-03-14', 'active'),
('Dhaka Motijheel Branch', '12 Dilkusha C/A, Motijheel, Dhaka 1000', '01912558877', '2005-07-01', 'active'),
('Chattogram Agrabad Branch', 'Agrabad C/A, Chattogram 4100', '01611004477', '2013-11-22', 'active');

-- 3. employees (works_at)
INSERT INTO employees (full_name, phone, email, position, branch_id, hire_date, employment_status) VALUES
('Md. Rafiqul Islam', '01715623948', 'rafiqul.islam@bankease.com.bd', 'Branch Manager', 1, '2010-03-01', 'active'),
('Farzana Akhter', '01827749215', 'farzana.akhter@bankease.com.bd', 'Senior Teller', 1, '2017-02-14', 'active'),
('Shahriar Kabir', '01911207765', 'shahriar.kabir@bankease.com.bd', 'Branch Manager', 2, '2008-09-19', 'active'),
('Nusrat Sultana', '01614458820', 'nusrat.sultana@bankease.com.bd', 'Loan Officer', 2, '2018-05-03', 'active'),
('Imran Hossain', '01516730294', 'imran.hossain@bankease.com.bd', 'Branch Manager', 3, '2012-01-27', 'active'),
('Taslima Begum', '01720094413', 'taslima.begum@bankease.com.bd', 'Customer Service Officer', 3, '2020-08-16', 'active'),
('Md. Sohel Rana', '01755218364', 'sohel.rana@bankease.com.bd', 'Teller', 2, '2019-11-04', 'active'),
('Mahmuda Khanom', '01640719285', 'mahmuda.khanom@bankease.com.bd', 'Teller', 3, '2021-03-22', 'active');

-- Close the circular reference now that employees exist
UPDATE branches SET manager_employee_id = 1 WHERE branch_id = 1;
UPDATE branches SET manager_employee_id = 3 WHERE branch_id = 2;
UPDATE branches SET manager_employee_id = 5 WHERE branch_id = 3;

-- 4. customers (registers_at, kyc_verified_by)
-- Personal / KYC fields follow the BRAC Bank "KYC Form for Beneficial Owner".
-- Spouse, TIN and income are optional on the form, so some rows leave them empty.
INSERT INTO customers (cif_number, full_name, full_name_bn, father_name, mother_name, spouse_name, date_of_birth, gender, nationality, profession, monthly_income, source_of_fund, tin_number, phone, email, id_type, id_number, branch_id, status, kyc_status, kyc_verified_by_employee_id, kyc_verified_at, registration_date) VALUES
('10047821', 'Abdul Karim Mollah', 'আব্দুল করিম মোল্লা', 'Abdus Salam Mollah', 'Rokeya Begum', 'Shirin Akter', '1985-04-12', 'male', 'Bangladeshi', 'Assistant Teacher', 32000.00, 'Salary', '478215936402', '01711098234', 'akarim.mollah@gmail.com', 'nid', '1985123456789', 1, 'active', 'verified', 1, '2016-05-19 11:05:00', '2016-05-19 10:00:00'),
('10052390', 'Salma Khatun', 'সালমা খাতুন', 'Md. Abul Kalam', 'Nurjahan Begum', 'Md. Jahangir Alam', '1992-11-03', 'female', 'Bangladeshi', 'Boutique owner', 18500.00, 'Boutique sales income', NULL, '01812345098', NULL, 'nid', '9211003456712', 1, 'active', 'verified', 2, '2018-02-27 10:20:00', '2018-02-27 09:30:00'),
('20011642', 'Mohammad Yusuf Ali', NULL, 'Late Md. Nurul Islam', 'Hasina Banu', 'Rahima Yusuf', '1978-01-22', 'male', 'Bangladeshi', 'Garments exporter', 285000.00, 'Business income', '619034872155', '01634509821', 'yusuf.ali78@yahoo.com', 'passport', 'BN0847213', 2, 'active', 'verified', 3, '2012-08-14 13:10:00', '2012-08-14 12:15:00'),
('20098817', 'Rehana Parvin', NULL, 'Md. Shahidul Haque', 'Morjina Begum', NULL, '2001-06-30', 'female', 'Bangladeshi', 'Student', NULL, 'Family support', NULL, '01911872245', 'rehana.parvin01@gmail.com', 'birth_certificate', '20016301234567891', 2, 'inactive', 'pending', NULL, NULL, '2022-01-05 11:00:00'),
('30006254', 'Kazi Nazrul Hasan', 'কাজী নজরুল হাসান', 'Kazi Mahbubul Hasan', 'Rahela Khatun', 'Sadia Afrin', '1990-09-09', 'male', 'Bangladeshi', 'Civil engineer', 74500.00, 'Salary', '340872619083', '01523098761', 'kazi.hasan90@outlook.com', 'nid', '9009091238845', 3, 'active', 'verified', 5, '2015-12-01 14:50:00', '2015-12-01 14:00:00'),
('30017733', 'Ayesha Siddika', NULL, 'Md. Abu Bakar Siddique', 'Lutfun Nahar', 'Md. Tanvir Ahmed', '1988-03-17', 'female', 'Bangladeshi', 'Pharmacist', 46800.00, 'Salary', '712305498216', '01711567823', NULL, 'driving_license', 'DL-CTG-0223491', 3, 'active', 'verified', 5, '2019-06-23 12:00:00', '2019-06-23 11:20:00'),
('10000936', 'Habibur Rahman Khan', 'হাবিবুর রহমান খান', 'Late Abdur Rahman Khan', 'Late Jobeda Khatun', 'Monowara Begum', '1965-12-25', 'male', 'Bangladeshi', 'Retired government officer', 41200.00, 'Pension', '298170453362', '01812098345', 'habib.rk65@gmail.com', 'nid', '6512253345671', 1, 'active', 'verified', 1, '2010-03-15 10:05:00', '2010-03-15 09:10:00'),
('20074508', 'Fatema Tuz Zohra', NULL, 'Md. Golam Mostafa', 'Shamima Nasrin', NULL, '1997-08-14', 'female', 'Bangladeshi', 'Freelance graphic designer', 27500.00, 'Freelance income received from abroad', NULL, '01633021198', NULL, 'nid', '9708143321567', 2, 'blocked', 'expired', 3, '2020-10-30 14:40:00', '2020-10-30 13:50:00'),
('30012185', 'Shafiqul Islam Bhuiyan', NULL, 'Md. Anwar Hossain Bhuiyan', 'Halima Khatun', 'Nazma Akter', '1983-05-05', 'male', 'Bangladeshi', 'Electronics shop owner', 96000.00, 'Business income', '583920174467', '01911345267', NULL, 'passport', 'BN1122938', 3, 'active', 'verified', 5, '2017-04-11 10:50:00', '2017-04-11 10:10:00'),
('10088459', 'Nasrin Jahan Mitu', 'নাসরিন জাহান মিতু', 'Md. Mizanur Rahman', 'Fatema Begum', 'Md. Rakibul Hasan', '1995-02-28', 'female', 'Bangladeshi', 'Staff nurse', 29800.00, 'Salary', NULL, '01716789021', 'nasrin.mitu95@gmail.com', 'nid', '9502281145678', 1, 'active', 'verified', 2, '2021-09-17 09:45:00', '2021-09-17 09:05:00');

-- 5. addresses (belongs_to) -- present and permanent addresses as asked on the KYC form.
-- Not every customer has both on file yet, and customers 4, 6 and 8 have none recorded.
INSERT INTO addresses (customer_id, address_type, address_line, post_office, thana, city, district, postal_code, country, is_primary) VALUES
(1, 'present', 'House 12, Road 4, Sonadanga', 'Sonadanga', 'Sonadanga', 'Khulna', 'Khulna', '9100', 'Bangladesh', TRUE),
(1, 'permanent', 'Vill: Kashipur', 'Kashipur Bazar', 'Dumuria', 'Dumuria', 'Khulna', '9220', 'Bangladesh', FALSE),
(2, 'present', 'Village Rupsha, Rupsha Ghat Road', 'Rupsha', 'Rupsha', 'Khulna', 'Khulna', '9203', 'Bangladesh', TRUE),
(3, 'present', 'Flat 3B, Eskaton Garden Road', 'Eskaton', 'Ramna', 'Dhaka', 'Dhaka', '1000', 'Bangladesh', TRUE),
(3, 'permanent', 'Vill: Bahadurpur', 'Kalihati', 'Kalihati', 'Kalihati', 'Tangail', '1970', 'Bangladesh', FALSE),
(5, 'present', 'Housing Estate Road 2, Agrabad', 'Agrabad', 'Double Mooring', 'Chattogram', 'Chattogram', '4100', 'Bangladesh', TRUE),
(7, 'present', 'Boyra Main Road', 'Boyra', 'Khulna Sadar', 'Khulna', 'Khulna', '9000', 'Bangladesh', TRUE),
(7, 'permanent', 'Vill: Batiaghata Bazar', 'Batiaghata', 'Batiaghata', 'Batiaghata', 'Khulna', '9260', 'Bangladesh', FALSE),
(9, 'present', 'GEC Circle, Sholoshahar', 'Chattogram Sadar', 'Panchlaish', 'Chattogram', 'Chattogram', '4000', 'Bangladesh', TRUE),
(10, 'present', 'Doulatpur, Jashore Road', 'Doulatpur', 'Daulatpur', 'Khulna', 'Khulna', '9202', 'Bangladesh', TRUE),
(10, 'permanent', 'Vill: Sachiadaha', 'Terokhada', 'Terokhada', 'Terokhada', 'Khulna', '9270', 'Bangladesh', FALSE);

-- 6. users (login_for customer, login_for employee, has_role)
-- Customer logins (Rehana and Fatema have no login; Habibur's is locked after 3 failed attempts)
INSERT INTO users (email, password_hash, role_id, linked_customer_id, failed_login_attempts, status, last_login_at, created_at) VALUES
('akarim.mollah@gmail.com', '$2y$10$N9qo8uLOickgx2ZMRZoMy.Mrq4tFN3S1Kd8kUwLBEZ0oW1z9m1fS.', 1, 1, 0, 'active', '2026-09-25 21:14:02', '2016-05-19 10:00:00'),
('salma.khatun92@gmail.com', '$2y$10$7EqJtq98hPqEX7fNZaFWoOe6L3Y8t4mHkVQpB1zc9YnLxWq2dR3.a', 1, 2, 1, 'active', '2026-09-18 08:03:44', '2018-02-27 09:30:00'),
('yusuf.ali78@yahoo.com', '$2y$10$Kb1xPz6vR8mWq3sHtYc4NeF9jL2oV5aQdE7nB0uZgX1yTsMhCw6Oi', 1, 3, 0, 'active', '2026-09-26 19:47:21', '2012-08-14 12:15:00'),
('kazi.hasan90@outlook.com', '$2y$10$aX3mLp9QwE2rT6yU8iO1se7sD5fH0gJ4kZ2vN6bM1cX9lWq3rP7Fa', 1, 5, 0, 'active', '2026-09-20 07:22:10', '2015-12-01 14:00:00'),
('ayesha.siddika88@gmail.com', '$2y$10$D8fGh2Jk5Lm9Np1Qr4St7uVwXyZa3Bc6De9Fg2Hj5Km8Nq1Pr4Su7', 1, 6, 0, 'active', '2026-09-24 13:56:39', '2019-06-23 11:20:00'),
('habib.rk65@gmail.com', '$2y$10$Vc4Wd7Xe0Yf3Zg6Ah9Bi2Cj5Dk8El1Fm4Gn7Ho0Ip3Jq6Kr9Ls2Mt5', 1, 7, 3, 'locked', '2026-07-30 16:11:58', '2010-03-15 09:10:00'),
('shafiqul.bhuiyan83@gmail.com', '$2y$10$Nu8Ov1Pw4Qx7Ry0Sz3Ta6Ub9Vc2Wd5Xe8Yf1Zg4Ah7Bi0Cj3Dk6El9', 1, 9, 0, 'active', '2026-09-22 15:29:52', '2017-04-11 10:10:00'),
('nasrin.mitu95@gmail.com', '$2y$10$Fm2Gn5Ho8Ip1Jq4Kr7Ls0Mt3Nu6Ov9Pw2Qx5Ry8Sz1Ta4Ub7Vc0Wd3', 1, 10, 0, 'active', '2026-09-10 19:40:55', '2021-09-17 09:05:00');

-- Staff logins (branch managers get Administrator access, tellers / officers get Employee access)
INSERT INTO users (email, password_hash, role_id, linked_employee_id, failed_login_attempts, status, last_login_at, created_at) VALUES
('rafiqul.islam@bankease.com.bd', '$2y$10$Xe6Yf9Zg2Ah5Bi8Cj1Dk4El7Fm0Gn3Ho6Ip9Jq2Kr5Ls8Mt1Nu4Ov7', 3, 1, 0, 'active', '2026-09-27 09:01:15', '2010-03-01 08:00:00'),
('farzana.akhter@bankease.com.bd', '$2y$10$Qw3Er6Ty9Ui2Op5As8Df1Gh4Jk7Lz0Xc3Vb6Nm9Qw2Er5Ty8Ui1Op4', 2, 2, 0, 'active', '2026-09-27 08:47:30', '2017-02-14 08:30:00'),
('shahriar.kabir@bankease.com.bd', '$2y$10$As7Df0Gh3Jk6Lz9Xc2Vb5Nm8Qw1Er4Ty7Ui0Op3As6Df9Gh2Jk5Lz8', 3, 3, 0, 'active', '2026-09-26 17:52:04', '2008-09-19 08:00:00'),
('nusrat.sultana@bankease.com.bd', '$2y$10$Ui1Op4As7Df0Gh3Jk6Lz9Xc2Vb5Nm8Qw1Er4Ty7Ui0Op3As6Df9Gh2', 2, 4, 1, 'active', '2026-09-25 12:18:47', '2018-05-03 08:30:00'),
('imran.hossain@bankease.com.bd', '$2y$10$Xc2Vb5Nm8Qw1Er4Ty7Ui0Op3As6Df9Gh2Jk5Lz8Xc1Vb4Nm7Qw0Er3', 3, 5, 0, 'active', '2026-09-27 08:59:41', '2012-01-27 08:00:00'),
('taslima.begum@bankease.com.bd', '$2y$10$Nm8Qw1Er4Ty7Ui0Op3As6Df9Gh2Jk5Lz8Xc1Vb4Nm7Qw0Er3Ty6Ui9', 2, 6, 0, 'active', '2026-09-24 09:33:12', '2020-08-16 08:30:00'),
('sohel.rana@bankease.com.bd', '$2y$10$Zk4Lm7No0Pq3Rs6Tu9Vw2Xy5Za8Bc1De4Fg7Hi0Jk3Lm6No9Pq2Rs5', 2, 7, 0, 'active', '2026-09-27 08:52:18', '2019-11-04 08:30:00'),
('mahmuda.khanom@bankease.com.bd', '$2y$10$Hj3Kl6Mn9Op2Qr5St8Uv1Wx4Yz7Ab0Cd3Ef6Gh9Ij2Kl5Mn8Op1Qr4', 2, 8, 2, 'active', '2026-09-26 08:44:07', '2021-03-22 08:30:00');

-- 7. account_types (of_type)
INSERT INTO account_types (type_name, minimum_balance, interest_rate, single_per_customer) VALUES
('Savings Account', 500.00, 3.50, FALSE),
('Current Account', 1000.00, 0.00, FALSE);

-- 8. accounts (owned_by, of_type, managed_by)
-- balance is the latest ledger position (see the last completed transaction of each account)
INSERT INTO accounts (account_number, account_title, customer_id, account_type_id, branch_id, balance, status, opening_date, closing_date) VALUES
('KHU0110000472591', 'Abdul Karim Mollah', 1, 1, 1, 77730.50, 'active', '2016-05-20', NULL),
('KHU0210000472688', 'Abdul Karim Mollah', 1, 2, 1, 9230.00, 'active', '2019-01-15', NULL),
('KHU0110000589214', 'Salma Khatun', 2, 1, 1, 5646.00, 'active', '2018-03-01', NULL),
('DHK0210000116620', 'Mohammad Yusuf Ali', 3, 2, 2, 231725.60, 'active', '2012-08-20', NULL),
('DHK0110000225103', 'Rehana Parvin', 4, 1, 2, 900.00, 'frozen', '2022-01-10', NULL),
('CTG0110000937482', 'Kazi Nazrul Hasan', 5, 1, 3, 47120.15, 'active', '2015-12-05', NULL),
('CTG0210000148857', 'Ayesha Siddika', 6, 2, 3, 67340.90, 'active', '2019-07-01', NULL),
('KHU0110000803317', 'Habibur Rahman Khan', 7, 1, 1, 149760.25, 'active', '2010-03-15', NULL),
('DHK0210000390246', 'Fatema Tuz Zohra', 8, 2, 2, 0.00, 'closed', '2020-11-02', '2024-06-30'),
('CTG0110000601739', 'Shafiqul Islam Bhuiyan', 9, 1, 3, 33050.00, 'active', '2017-04-20', NULL),
('CTG0210000601740', 'Shafiqul Islam Bhuiyan', 9, 2, 3, 0.00, 'pending', '2026-08-02', NULL),
('KHU0110000955028', 'Nasrin Jahan Mitu', 10, 1, 1, 10215.60, 'active', '2021-09-20', NULL);

-- 9. transactions (posted_to, counterparty, performed_by)
-- Pending / failed rows do not move the balance (balance_after = balance_before).
INSERT INTO transactions (reference_number, account_id, transaction_type, amount, balance_before, balance_after, related_account_id, description, status, performed_by_user_id, created_at) VALUES
('TXN842913', 1, 'deposit', 15000.00, 69250.75, 84250.75, NULL, 'Cash deposit at counter', 'completed', 10, '2026-05-14 10:22:00'),
('TXN117682', 1, 'withdrawal', 4500.25, 84250.75, 79750.50, NULL, 'ATM withdrawal', 'completed', 1, '2026-06-02 18:47:11'),
('TXN293841', 2, 'deposit', 8000.00, 4430.00, 12430.00, NULL, 'Cheque deposit', 'completed', 10, '2026-04-10 09:15:32'),
('TXN560213', 3, 'withdrawal', 1200.00, 6800.40, 5600.40, NULL, 'Utility bill payment', 'completed', 2, '2026-07-19 14:02:47'),
('TXN904471', 4, 'deposit', 50000.00, 181875.60, 231875.60, NULL, 'Business receipts deposit', 'completed', 15, '2026-03-05 11:40:09'),
('TXN338290', 5, 'withdrawal', 600.00, 1500.00, 900.00, NULL, 'Cash withdrawal', 'completed', 15, '2026-01-22 16:05:00'),
('TXN671059', 6, 'deposit', 3200.15, 41920.00, 45120.15, NULL, 'Cash deposit', 'completed', 16, '2026-08-01 08:30:21'),
('TXN455812', 7, 'withdrawal', 2200.00, 69540.90, 67340.90, NULL, 'Cheque withdrawal', 'completed', 5, '2026-06-27 13:12:55'),
('TXN782364', 8, 'deposit', 12500.25, 140260.00, 152760.25, NULL, 'Cash deposit at counter', 'completed', 10, '2026-02-14 10:50:00'),
('TXN109938', 9, 'withdrawal', 500.00, 500.00, 0.00, NULL, 'Final withdrawal before account closure', 'completed', 11, '2024-06-30 09:00:00'),
('TXN221076', 10, 'deposit', 5000.00, 28050.00, 33050.00, NULL, 'Cash deposit', 'completed', 16, '2026-05-30 17:22:40'),
('TXN560771', 11, 'deposit', 18900.45, 0.00, 0.00, NULL, 'Initial funding deposit -- awaiting account activation', 'pending', 16, '2026-08-02 09:05:00'),
('TXN904225', 12, 'withdrawal', 784.40, 8000.00, 7215.60, NULL, 'Mobile top-up and bill payment', 'completed', 8, '2026-09-10 19:41:03'),
('TXN117359', 2, 'withdrawal', 250.00, 9230.00, 9230.00, NULL, 'Declined POS payment -- card authorization failed', 'failed', 1, '2026-09-20 12:00:00'),
('TXN338847', 1, 'transfer_out', 2000.00, 79750.50, 77750.50, 6, 'Transfer to Kazi Nazrul Hasan', 'completed', 1, '2026-09-05 20:10:15'),
('TXN338848', 6, 'transfer_in', 2000.00, 45120.15, 47120.15, 1, 'Transfer from Abdul Karim Mollah', 'completed', 1, '2026-09-05 20:10:20'),
('TXN550012', 3, 'interest', 45.60, 5600.40, 5646.00, NULL, 'Quarterly savings interest credit', 'completed', 9, '2026-09-01 00:05:00'),
('TXN660234', 4, 'fee', 150.00, 231875.60, 231725.60, NULL, 'Account maintenance fee', 'completed', 11, '2026-09-15 09:00:00'),
('TXN771102', 8, 'transfer_out', 3000.00, 152760.25, 149760.25, 12, 'Transfer to Nasrin Jahan Mitu', 'completed', 10, '2026-09-22 15:30:00'),
('TXN771103', 12, 'transfer_in', 3000.00, 7215.60, 10215.60, 8, 'Transfer from Habibur Rahman Khan', 'completed', 10, '2026-09-22 15:30:05'),
('TXN338850', 1, 'fee', 20.00, 77750.50, 77730.50, NULL, 'Cross-branch transfer fee', 'completed', 1, '2026-09-05 20:10:16'),
('TXN482016', 2, 'withdrawal', 3200.00, 12430.00, 9230.00, NULL, 'Cheque withdrawal', 'completed', 1, '2026-08-11 15:26:40');

-- 10. transfers (debit_side, credit_side)
-- Both transfers are between BankEase accounts (a 20.00 fee applies only across branches).
INSERT INTO transfers (debit_transaction_id, credit_transaction_id, transfer_type, fee, status) VALUES
(15, 16, 'internal', 20.00, 'completed'),
(19, 20, 'internal', 0.00, 'completed');

-- 11. beneficiaries (saved_by, points_to)
INSERT INTO beneficiaries (customer_id, nickname, target_account_id, added_at) VALUES
(1, 'Kazi Nazrul', 6, '2026-02-11 10:00:00'),
(3, 'Landlord', 8, '2026-05-06 09:30:00'),
(6, NULL, 1, '2026-07-14 16:45:00'),
(7, 'Nasrin', 12, '2026-09-22 15:25:00'),
(9, 'Office Rent', 4, '2026-08-29 11:20:00');

-- 12. cards (held_by, linked_to)
INSERT INTO cards (customer_id, account_id, card_type, masked_number, card_number_hash, credit_limit, issue_date, expiry_date, status) VALUES
(1, 1, 'debit', '5211 XXXX XXXX 4471', 'a91cf8e6b3d0714f2c59a8b1e6d3f0c7a2b9e4d1f8c5a0b7e3d9f2c6a1b8e4d0', NULL, '2020-01-15', '2025-01-15', 'expired'),
(1, 2, 'credit', '4532 XXXX XXXX 8890', 'b73de5a1f9c2708b4e6a3d0f7c1b8e5a2d9f6c3b0e7a4d1f8c5b2e9a6d3f0c7', 100000.00, '2023-06-01', '2027-06-01', 'active'),
(3, 4, 'debit', '5211 XXXX XXXX 2214', 'f10ab4c8e2d97530a6b1f4e8c2d5a9b3e7f0c4a8d1b5e9f2c6a0d3b7e4f1c8a5', NULL, '2022-03-10', '2027-03-10', 'active'),
(5, 6, 'debit', '5211 XXXX XXXX 7783', 'd820e6b4a1f8c3705e9a2d6b0f4c8e1a5d9b3f7c0a4e8d2b6f1a5c9e3d7b0f4', NULL, '2024-11-20', '2029-11-20', 'active'),
(7, 8, 'credit', '4532 XXXX XXXX 3305', 'c904f7a3b6d1e8502c7a4d9b1f5e8c2a6d0b4f8e2c5a9d3b7f1e4c8a2d6b0f5', 50000.00, '2021-08-09', '2026-08-09', 'blocked'),
(9, 10, 'debit', '5211 XXXX XXXX 9027', 'e771a5c9f2d84603b8e1a6d4c0f7b3e9a2d5c8b1f6e0a4d7c3b9f5e2a8d1c6b0', NULL, '2025-02-18', '2030-02-18', 'active');

-- 13. loans (borrowed_by, disbursed_to, approved_by)
-- monthly_installment is the reducing-balance EMI; outstanding_balance = principal minus
-- the principal part of every installment paid so far (see loan_payments below).
INSERT INTO loans (customer_id, loan_type, principal_amount, interest_rate, duration_months, application_date, approval_date, disbursement_account_id, monthly_installment, outstanding_balance, status, approved_by_user_id) VALUES
(1, 'Personal Loan', 200000.00, 12.50, 24, '2025-11-01', '2025-11-10', 1, 9461.46, 130761.75, 'active', 9),
(5, 'Home Renovation Loan', 500000.00, 10.75, 60, '2024-06-15', '2024-06-25', 6, 10808.98, 307622.62, 'active', 13),
(8, 'Auto Loan', 800000.00, 11.00, 48, '2026-02-01', NULL, NULL, 20676.42, 0.00, 'rejected', NULL),
(9, 'Business Expansion Loan', 300000.00, 13.00, 36, '2026-09-14', NULL, NULL, 10108.19, 0.00, 'pending', NULL);

-- 14. loan_payments (installment_of, paid_via)
-- Loan 1: installments 1-9 paid (9th late), 10th overdue, 11th not yet due.
-- Loan 2: installments 1-27 paid, 28th not yet due. Paid in cash at the branch, so no ledger link.
INSERT INTO loan_payments (loan_id, installment_number, due_date, scheduled_principal, scheduled_interest, paid_amount, paid_date, status, related_transaction_id) VALUES
(1, 1, '2025-12-10', 7378.13, 2083.33, 9461.46, '2025-12-10', 'paid', NULL),
(1, 2, '2026-01-10', 7454.98, 2006.48, 9461.46, '2026-01-09', 'paid', NULL),
(1, 3, '2026-02-10', 7532.64, 1928.82, 9461.46, '2026-02-12', 'paid', NULL),
(1, 4, '2026-03-10', 7611.10, 1850.36, 9461.46, '2026-03-11', 'paid', NULL),
(1, 5, '2026-04-10', 7690.39, 1771.07, 9461.46, '2026-04-10', 'paid', NULL),
(1, 6, '2026-05-10', 7770.49, 1690.97, 9461.46, '2026-05-10', 'paid', NULL),
(1, 7, '2026-06-10', 7851.44, 1610.02, 9461.46, '2026-06-10', 'paid', NULL),
(1, 8, '2026-07-10', 7933.22, 1528.24, 9461.46, '2026-07-09', 'paid', NULL),
(1, 9, '2026-08-10', 8015.86, 1445.60, 9461.46, '2026-08-16', 'paid', NULL),
(1, 10, '2026-09-10', 8099.36, 1362.10, 0.00, NULL, 'overdue', NULL),
(1, 11, '2026-10-10', 8183.73, 1277.73, 0.00, NULL, 'due', NULL),
(2, 1, '2024-07-25', 6329.81, 4479.17, 10808.98, '2024-07-25', 'paid', NULL),
(2, 2, '2024-08-25', 6386.52, 4422.46, 10808.98, '2024-08-25', 'paid', NULL),
(2, 3, '2024-09-25', 6443.73, 4365.25, 10808.98, '2024-09-25', 'paid', NULL),
(2, 4, '2024-10-25', 6501.46, 4307.52, 10808.98, '2024-10-26', 'paid', NULL),
(2, 5, '2024-11-25', 6559.70, 4249.28, 10808.98, '2024-11-26', 'paid', NULL),
(2, 6, '2024-12-25', 6618.46, 4190.52, 10808.98, '2024-12-25', 'paid', NULL),
(2, 7, '2025-01-25', 6677.75, 4131.23, 10808.98, '2025-01-22', 'paid', NULL),
(2, 8, '2025-02-25', 6737.57, 4071.41, 10808.98, '2025-02-25', 'paid', NULL),
(2, 9, '2025-03-25', 6797.93, 4011.05, 10808.98, '2025-03-23', 'paid', NULL),
(2, 10, '2025-04-25', 6858.83, 3950.15, 10808.98, '2025-04-25', 'paid', NULL),
(2, 11, '2025-05-25', 6920.27, 3888.71, 10808.98, '2025-06-01', 'paid', NULL),
(2, 12, '2025-06-25', 6982.27, 3826.71, 10808.98, '2025-06-26', 'paid', NULL),
(2, 13, '2025-07-25', 7044.82, 3764.16, 10808.98, '2025-07-25', 'paid', NULL),
(2, 14, '2025-08-25', 7107.93, 3701.05, 10808.98, '2025-08-23', 'paid', NULL),
(2, 15, '2025-09-25', 7171.60, 3637.38, 10808.98, '2025-09-24', 'paid', NULL),
(2, 16, '2025-10-25', 7235.85, 3573.13, 10808.98, '2025-10-23', 'paid', NULL),
(2, 17, '2025-11-25', 7300.67, 3508.31, 10808.98, '2025-11-24', 'paid', NULL),
(2, 18, '2025-12-25', 7366.07, 3442.91, 10808.98, '2025-12-26', 'paid', NULL),
(2, 19, '2026-01-25', 7432.06, 3376.92, 10808.98, '2026-02-01', 'paid', NULL),
(2, 20, '2026-02-25', 7498.64, 3310.34, 10808.98, '2026-02-25', 'paid', NULL),
(2, 21, '2026-03-25', 7565.81, 3243.17, 10808.98, '2026-03-24', 'paid', NULL),
(2, 22, '2026-04-25', 7633.59, 3175.39, 10808.98, '2026-04-24', 'paid', NULL),
(2, 23, '2026-05-25', 7701.97, 3107.01, 10808.98, '2026-05-25', 'paid', NULL),
(2, 24, '2026-06-25', 7770.97, 3038.01, 10808.98, '2026-06-25', 'paid', NULL),
(2, 25, '2026-07-25', 7840.59, 2968.39, 10808.98, '2026-07-23', 'paid', NULL),
(2, 26, '2026-08-25', 7910.82, 2898.16, 10808.98, '2026-08-25', 'paid', NULL),
(2, 27, '2026-09-25', 7981.69, 2827.29, 10808.98, '2026-09-25', 'paid', NULL),
(2, 28, '2026-10-25', 8053.19, 2755.79, 0.00, NULL, 'due', NULL);
-- 15. notifications (sent_to)
INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES
(1, 'transaction_alert', 'Deposit of BDT 15,000.00 received in account ...2591', TRUE, '2026-05-14 10:23:00'),
(1, 'loan_reminder', 'Installment of BDT 9,461.46 for your Personal Loan was due on 10 Sep 2026 and is overdue', FALSE, '2026-09-11 09:00:00'),
(4, 'promotion', 'New fixed deposit scheme now available at 8.5% interest', FALSE, '2026-09-01 07:30:00'),
(6, 'security_alert', 'Your online banking login was locked after 3 failed attempts', TRUE, '2026-07-30 16:21:00'),
(6, 'kyc_reminder', 'Your KYC information has expired. Please visit your branch with a valid ID to update it', FALSE, '2026-09-01 09:00:00'),
(8, 'transaction_alert', 'BDT 3,000.00 received in account ...5028', TRUE, '2026-09-22 15:30:10'),
(10, 'system_alert', 'End-of-day reconciliation report is ready for review', FALSE, '2026-09-26 18:00:00');

-- 16. audit_logs (logged_by)
INSERT INTO audit_logs (user_id, action, target_table, target_id, description, ip_address, result, created_at) VALUES
(9, 'APPROVE_LOAN', 'loans', 1, 'Approved Personal Loan application', '10.20.4.11', 'success', '2025-11-10 11:00:00'),
(13, 'APPROVE_LOAN', 'loans', 2, 'Approved Home Renovation Loan application', '10.20.7.3', 'success', '2024-06-25 10:40:00'),
(11, 'REJECT_LOAN', 'loans', 3, 'Rejected Auto Loan application due to insufficient income documentation', '10.20.5.9', 'success', '2026-02-20 14:15:00'),
(10, 'BLOCK_CARD', 'cards', 5, 'Card blocked after customer reported it lost', '10.20.4.15', 'success', '2026-08-05 12:00:00'),
(NULL, 'LOGIN_FAILED', 'users', 2, 'Failed login attempt: incorrect password', '103.87.44.12', 'failure', '2026-09-18 22:47:00'),
(11, 'CLOSE_ACCOUNT', 'accounts', 9, 'Finalized account closure after zero balance confirmed', '10.20.5.9', 'success', '2024-06-30 09:10:00'),
(10, 'VERIFY_KYC', 'customers', 10, 'NID and application form checked and countersigned at account opening', '10.20.4.15', 'success', '2021-09-17 09:45:00'),
(NULL, 'LOCK_USER', 'users', 6, 'Login locked after 3 consecutive failed attempts', '103.87.51.204', 'success', '2026-07-30 16:20:00'),
(9, 'FLAG_KYC_EXPIRED', 'customers', 7, 'KYC review date passed; customer asked to update documents at the branch', '10.20.4.11', 'success', '2026-09-01 08:50:00');
