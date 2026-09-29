<?php
/**
 * accounts/index.php — Account List
 *
 * Search (?q=) and status filter (?status=) are sent as GET parameters, so the
 * URL can be bookmarked and pagination keeps them. Ten rows per page (RECORDS_PER_PAGE).
 *
 * Tables used: accounts, customers, account_types, branches (SELECT with JOINs)
 */
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle  = 'Account List';
$activeMenu = 'account_list';
$base       = '..';

$q      = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');

// ---------- build the WHERE part (values always go through placeholders) ----------
$where  = [];
$params = [];

if ($q !== '') {
    $where[] = '(a.account_number LIKE :q1 OR a.account_title LIKE :q2 OR c.full_name LIKE :q3)';
    $like = '%' . $q . '%';
    $params[':q1'] = $like;
    $params[':q2'] = $like;
    $params[':q3'] = $like;
}
if (in_array($status, enum_values('account_status'), true)) {      // only known ENUM values are accepted
    $where[] = 'a.status = :status';
    $params[':status'] = $status;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$from = ' FROM accounts a
          JOIN customers c     ON c.customer_id = a.customer_id
          JOIN account_types t ON t.account_type_id = a.account_type_id
          JOIN branches b      ON b.branch_id = a.branch_id';

// ---------- count + one page of rows ----------
$total  = (int) db_scalar('SELECT COUNT(*)' . $from . $whereSql, $params);
$limit  = (int) RECORDS_PER_PAGE;
$offset = (get_page() - 1) * $limit;

$rows = db_select_all(
    'SELECT a.account_id, a.account_number, a.account_title, a.balance, a.status, a.opening_date,
            c.full_name AS customer_name, t.type_name, b.branch_name'
    . $from . $whereSql . ' ORDER BY a.account_id DESC LIMIT ' . $limit . ' OFFSET ' . $offset,
    $params
);

$badge = ['active' => 'success', 'pending' => 'warning', 'frozen' => 'info', 'closed' => 'secondary'];

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Account List</h1>
    <a class="btn btn-primary btn-sm" href="create.php">+ New Account</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <form method="get" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="search" name="q" class="form-control" value="<?= e($q) ?>"
                       placeholder="Search by account number, title or customer name...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <?= enum_options_html(enum_values('account_status'), $status, 'All statuses') ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-secondary" type="submit">Search</button>
                <a class="btn btn-outline-secondary" href="index.php">Reset</a>
            </div>
        </form>

        <div class="small text-muted mb-2"><?= $total ?> account(s) found</div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Account number</th>
                        <th>Title / customer</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th>Opened</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['account_number']) ?></td>
                        <td>
                            <?= e($row['account_title']) ?>
                            <?php if ($row['account_title'] !== $row['customer_name']): ?>
                                <div class="small text-muted"><?= e($row['customer_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($row['type_name']) ?></td>
                        <td><?= e($row['branch_name']) ?></td>
                        <td class="text-end balance-text"><?= e(format_money($row['balance'])) ?></td>
                        <td><span class="badge text-bg-<?= e($badge[$row['status']] ?? 'secondary') ?>"><?= e(pretty_enum($row['status'])) ?></span></td>
                        <td><?= e(format_date($row['opening_date'])) ?></td>
                        <td class="text-end table-actions">
                            <?php if ($row['status'] !== 'closed'): ?>
                                <a class="btn btn-outline-primary btn-sm" href="edit.php?id=<?= e($row['account_id']) ?>">Edit</a>
                            <?php endif; ?>
                            <?php if (in_array($row['status'], ['active', 'pending'], true)): ?>
                                <form method="post" action="deactivate.php">
                                    <input type="hidden" name="account_id" value="<?= e($row['account_id']) ?>">
                                    <button type="submit" class="btn btn-outline-warning btn-sm"
                                            data-confirm="Deactivate (freeze) account <?= e($row['account_number']) ?>? No transactions will be possible until it is activated again.">
                                        Deactivate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No accounts found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= pagination_html($total) ?></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
