<?php
/**
 * dashboard/index.php — entry point of BankEase
 *
 * Shows:
 *   1. Four summary numbers read from the database (read-only queries)
 *   2. One card for every module listed in includes/menu.php
 *
 * Tables used: customers, accounts, transactions
 */
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/menu.php';

$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
$base       = '..';

// ---- Summary numbers (each query returns one value) ----
$stats = [
    'customers'    => (int) db_scalar('SELECT COUNT(*) FROM customers'),
    'accounts'     => (int) db_scalar("SELECT COUNT(*) FROM accounts WHERE status = 'active'"),
    'total_money'  => (float) db_scalar("SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE status = 'active'"),
    'transactions' => (int) db_scalar('SELECT COUNT(*) FROM transactions'),
];

include __DIR__ . '/../includes/header.php';
?>

<h1 class="h3 mb-1">Dashboard</h1>
<p class="text-muted mb-4">Welcome to <?= e(APP_NAME) ?> &mdash; <?= e(APP_SUBTITLE) ?></p>

<!-- ============ Summary numbers ============ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-box shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Customers</div>
            <div class="stat-number"><?= number_format($stats['customers']) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-box shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Active accounts</div>
            <div class="stat-number"><?= number_format($stats['accounts']) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-box shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Total balance (active accounts)</div>
            <div class="stat-number balance-text"><?= e(format_money($stats['total_money'])) ?></div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-box shadow-sm h-100"><div class="card-body">
            <div class="text-muted small">Transactions recorded</div>
            <div class="stat-number"><?= number_format($stats['transactions']) ?></div>
        </div></div>
    </div>
</div>

<!-- ============ Module cards (from includes/menu.php) ============ -->
<h2 class="h5 mb-3">Modules</h2>
<div class="row g-3">
    <?php foreach (menu_items() as $item): ?>
        <?php if ($item['key'] === 'dashboard') { continue; } // no card that links to the page we are on ?>
        <div class="col-sm-6 col-lg-4">
            <a class="card module-card h-100 shadow-sm <?= $item['implemented'] ? '' : 'disabled-look' ?>"
               href="<?= e($base . '/' . $item['url']) ?>">
                <div class="card-body">
                    <h3 class="card-title h6 mb-1">
                        <?= e($item['label']) ?>
                        <?php if (!$item['implemented']): ?>
                            <span class="badge text-bg-secondary ms-1">Not yet implemented</span>
                        <?php endif; ?>
                    </h3>
                    <p class="card-text small text-muted mb-0"><?= e($item['description']) ?></p>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
