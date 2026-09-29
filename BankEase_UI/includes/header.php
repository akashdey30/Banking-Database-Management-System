<?php
/**
 * header.php — top of every page (navbar + sidebar)
 *
 * Every page sets these variables BEFORE including this file:
 *   $pageTitle   text for the browser tab and the page heading
 *   $activeMenu  the 'key' of the current item in includes/menu.php
 *   $base        path back to the project root:  '..' inside a folder, '.' at the root
 *
 * Every page also ends with:  include __DIR__ . '/../includes/footer.php';
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/menu.php';

$pageTitle  = $pageTitle  ?? APP_NAME;
$activeMenu = $activeMenu ?? '';
$base       = $base       ?? '..';

// Group the menu items so the sidebar can show a small title above each group.
$menuGroups = [];
foreach (menu_items() as $item) {
    $menuGroups[$item['group']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>

    <!-- Bootstrap 5 is stored inside assets/ so the project works offline -->
    <link rel="stylesheet" href="<?= e($base) ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= e($base) ?>/assets/css/app.css">
</head>
<body>

<!-- ============ Top navbar ============ -->
<nav class="navbar navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="<?= e($base) ?>/dashboard/index.php">
            <?= e(APP_NAME) ?> <small class="fw-normal opacity-75 d-none d-sm-inline">| <?= e(APP_SUBTITLE) ?></small>
        </a>
        <!-- Button that opens the sidebar on small screens -->
        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <!-- ============ Sidebar ============ -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="pt-3">
                <?php foreach ($menuGroups as $groupName => $items): ?>
                    <h6 class="sidebar-heading text-muted text-uppercase px-3 mt-3 mb-1"><?= e($groupName) ?></h6>
                    <ul class="nav flex-column">
                        <?php foreach ($items as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeMenu === $item['key'] ? 'active' : '' ?>"
                                   href="<?= e($base . '/' . $item['url']) ?>">
                                    <?= e($item['label']) ?>
                                    <?php if (!$item['implemented']): ?>
                                        <span class="badge text-bg-secondary ms-1">Soon</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>
        </nav>

        <!-- ============ Page content starts here ============ -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            <?php flash_show(); ?>
