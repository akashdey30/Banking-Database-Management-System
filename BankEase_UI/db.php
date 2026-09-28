<?php
/**
 * db.php — BankEase database access layer
 *
 * All SQL in the project goes through the functions in this file.
 * Every query uses PDO prepared statements: the SQL text contains
 * placeholders (:name) and the values are passed separately, which
 * prevents SQL injection.
 *
 * Errors: if a query fails (for example a foreign key or CHECK
 * constraint is violated), PDO throws a PDOException. These helper
 * functions do NOT catch it, so the calling form can catch it and
 * show a friendly message.
 */

require_once __DIR__ . '/config.php';

/* ------------------------------------------------------------------
 * Connection
 * ------------------------------------------------------------------ */

/**
 * Return the shared PDO connection.
 * The connection is opened the first time db() is called and then reused.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST
         . ';port=' . DB_PORT
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    // Make MySQL use the same time zone as PHP (e.g. "+06:00" for Asia/Dhaka),
    // so columns that default to CURRENT_TIMESTAMP match the PHP dates.
    $offset = (new DateTime('now', new DateTimeZone(APP_TIMEZONE)))->format('P');

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw errors as exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // rows as ['column' => value]
            PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . $offset . "'",
        ]);
    } catch (PDOException $e) {
        db_connection_failed($e);
    }

    return $pdo;
}

/**
 * Stop the request with a clear message when MySQL cannot be reached.
 * API endpoints (files inside /api/) receive JSON, other pages receive HTML.
 */
function db_connection_failed(PDOException $e): void
{
    $message = 'Could not connect to the database. '
             . 'Check that MySQL is running and that bankease.sql has been imported.';

    if (APP_DEBUG) {
        $message .= ' Details: ' . $e->getMessage();
    }

    http_response_code(500);

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($script, '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        echo '<p style="font-family: monospace; color: #b00020;">'
           . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
           . '</p>';
    }
    exit;
}

/* ------------------------------------------------------------------
 * Reading data (SELECT)
 * ------------------------------------------------------------------ */

/**
 * Run a SELECT and return ALL rows as an array of associative arrays.
 * Returns an empty array when nothing matches.
 *
 * Example:
 *   $rows = db_select_all('SELECT * FROM accounts WHERE customer_id = :id',
 *                         [':id' => 5]);
 */
function db_select_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Run a SELECT and return only the FIRST row, or null if there is none.
 * Useful for "load one record by primary key".
 */
function db_select_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/**
 * Run a SELECT and return the value of the first column of the first row
 * (or null if there is no row). Useful for COUNT(), SUM(), balances, etc.
 *
 * Example:
 *   $count = db_scalar('SELECT COUNT(*) FROM cif_check WHERE cif_number = :c', [...]);
 */
function db_scalar(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $value = $stmt->fetchColumn();
    return $value === false ? null : $value;
}

/* ------------------------------------------------------------------
 * Changing data (INSERT / UPDATE / DELETE)
 * ------------------------------------------------------------------ */

/**
 * Run an INSERT, UPDATE or DELETE.
 * Returns the number of rows that were affected.
 */
function db_execute(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Run an INSERT and return the new AUTO_INCREMENT primary key
 * (for example the new transaction_id).
 */
function db_insert(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}

/* ------------------------------------------------------------------
 * SQL transactions
 * ------------------------------------------------------------------
 * A database transaction groups several SQL statements so that they
 * either ALL succeed or ALL are undone. Deposits, withdrawals,
 * transfers and loan disbursement each change more than one table
 * (transactions + accounts, ...), so they use this pattern:
 *
 *   db_begin();
 *   try {
 *       ... several INSERT / UPDATE statements ...
 *       db_commit();      // make all changes permanent
 *   } catch (Exception $e) {
 *       db_rollback();    // undo everything
 *       ... show error ...
 *   }
 */

/** Start a database transaction. */
function db_begin(): void
{
    if (!db()->inTransaction()) {
        db()->beginTransaction();
    }
}

/** Save all changes made since db_begin(). */
function db_commit(): void
{
    if (db()->inTransaction()) {
        db()->commit();
    }
}

/** Undo all changes made since db_begin(). */
function db_rollback(): void
{
    if (db()->inTransaction()) {
        db()->rollBack();
    }
}