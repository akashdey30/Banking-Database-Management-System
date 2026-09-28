<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Transactions';
$base = '..';
$rows = db_select_all('SELECT t.*, a.account_number, ra.account_number AS related_account_number FROM transactions t JOIN accounts a ON a.account_id=t.account_id LEFT JOIN accounts ra ON ra.account_id=t.related_account_id ORDER BY t.transaction_id DESC');
$columns = ['transaction_id', 'reference_number', 'account_number', 'transaction_type', 'amount', 'balance_before', 'balance_after', 'related_account_number', 'status', 'created_at'];
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1>
<div class="actions">
<?php if (False): ?><a class="btn" href="../">+ Add</a><?php endif; ?>
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