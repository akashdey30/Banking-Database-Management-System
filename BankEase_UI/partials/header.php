<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../helpers.php';
$pageTitle = $pageTitle ?? ($title ?? APP_NAME);
$base = $base ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= e($base) ?>/style.css">
</head>
<body>
<header class="topbar"><div class="topbar-inner">
<a class="brand" href="<?= e($base) ?>/index.php">BankEase</a>
<nav class="nav">
<a href="<?= e($base) ?>/index.php">Dashboard</a>
<a href="<?= e($base) ?>/lists/branches.php">Branches</a>
<a href="<?= e($base) ?>/lists/employees.php">Employees</a>
<a href="<?= e($base) ?>/lists/customers.php">Customers</a>
<a href="<?= e($base) ?>/lists/accounts.php">Accounts</a>
<a href="<?= e($base) ?>/lists/transactions.php">Transactions</a>
<a href="<?= e($base) ?>/lists/loans.php">Loans</a>
</nav></div></header>
<main class="container"><?php flash_show(); ?>
