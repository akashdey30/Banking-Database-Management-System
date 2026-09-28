<?php
require_once __DIR__ . '/../helpers.php';
$pageTitle = 'Cards';
$base = '..';
$rows = db_select_all('SELECT cd.*, c.full_name AS customer_name, a.account_number FROM cards cd JOIN customers c ON c.customer_id=cd.customer_id JOIN accounts a ON a.account_id=cd.account_id ORDER BY cd.card_id DESC');
$columns = ['card_id', 'customer_name', 'account_number', 'card_type', 'masked_number', 'credit_limit', 'issue_date', 'expiry_date', 'status'];
include __DIR__ . '/../partials/header.php';
?>
<div class="toolbar"><h1><?= e($pageTitle) ?></h1>
<div class="actions">
<?php if (True): ?><a class="btn" href="../forms/card.php">+ Add</a><?php endif; ?>
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