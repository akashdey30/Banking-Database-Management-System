<?php
/**
 * footer.php — bottom of every page
 *
 * Closes the <main>, row and container opened in header.php, then loads the scripts.
 * A page can load extra scripts by setting BEFORE including this file:
 *     $extraScripts = ['transactions.js'];      (files inside assets/js/)
 */
$base         = $base ?? '..';
$extraScripts = $extraScripts ?? [];
?>
        </main>
    </div>
</div>

<footer class="text-center text-muted small py-3 border-top">
    <?= e(APP_NAME) ?> &mdash; Database Systems course project (Group 19)
</footer>

<script src="<?= e($base) ?>/assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= e($base) ?>/assets/js/app.js"></script>
<?php foreach ($extraScripts as $script): ?>
<script src="<?= e($base) ?>/assets/js/<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
