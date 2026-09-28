<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Customers';
$base = '..';
$rows = db_select_all('SELECT c.*, b.branch_name FROM customers c JOIN branches b ON b.branch_id=c.branch_id ORDER BY c.customer_id DESC');
$columns = ['customer_id', 'cif_number', 'full_name', 'phone', 'email', 'id_type', 'id_number', 'branch_name', 'status', 'kyc_status', 'registration_date'];
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1>
<div class="actions">
<?php if (True): ?><a class="btn" href="../forms/customer.php">+ Add</a><?php endif; ?>
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