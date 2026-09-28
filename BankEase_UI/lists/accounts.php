<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Accounts';
$base = '..';
$rows = db_select_all('SELECT a.*, c.full_name AS customer_name, at.type_name, b.branch_name FROM accounts a JOIN customers c ON c.customer_id=a.customer_id JOIN account_types at ON at.account_type_id=a.account_type_id JOIN branches b ON b.branch_id=a.branch_id ORDER BY a.account_id DESC');
$columns = ['account_id', 'account_number', 'account_title', 'customer_name', 'type_name', 'branch_name', 'balance', 'currency', 'status', 'opening_date'];
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1>
<div class="actions">
<?php if (True): ?><a class="btn" href="../forms/account.php">+ Add</a><?php endif; ?>
<a class="btn secondary" href="../index.php">Dashboard</a>
</div></div>
<div class="toolbar"><input data-table-search type="search" placeholder="Search this table..." style="max-width:340px"></div>
<div class="table-wrap"><table class="data-table"><thead><tr>
<?php foreach ($columns as $column): ?><th><?= e(ucwords(str_replace('_',' ',$column))) ?></th><?php endforeach; ?>
</tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
<?php foreach ($columns as $column): ?><td><?= e($row[$column] ?? '-') ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="<?= count($columns) ?>" class="muted">No records found.</td></tr><?php endif; ?>
</tbody></table></div>
<?php include __DIR__ . '/../partials/footer.php'; ?>