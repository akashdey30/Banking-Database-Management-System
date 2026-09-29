<?php
/**
 * placeholder.php — shared "not yet implemented" page
 *
 * Used by branches/, employees/, loans/, reports/, settings/ and transactions/history.php.
 * A placeholder page only needs these four lines:
 *
 *     <?php
 *     $pageTitle = 'Loan Management';  $activeMenu = 'loans';  $base = '..';
 *     include __DIR__ . '/../includes/placeholder.php';
 *
 * No database access happens here, so this page can never show a PHP or SQL error.
 */

$base = $base ?? '..';
include __DIR__ . '/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm text-center mt-4">
            <div class="card-body p-5">
                <span class="badge text-bg-secondary mb-3">Not Yet Implemented</span>
                <h1 class="h3 mb-3"><?= e($pageTitle) ?></h1>
                <p class="lead text-muted mb-2">
                    This module is not yet implemented in the current project version.
                </p>
                <p class="text-muted mb-4">
                    The page and its folder are already part of the project structure, so the module
                    can be added later without changing the navigation.
                </p>
                <a href="<?= e($base) ?>/dashboard/index.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
