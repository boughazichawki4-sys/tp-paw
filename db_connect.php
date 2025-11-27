<?php
/**
 * Retourne un objet PDO connecté à la base de données.
 * Utilise un try/catch pour gérer les erreurs et journalise en cas d'échec.
 */
function get_db_connection(): ?PDO {
    $config = require __DIR__ . '/config.php';
    $host = $config['host'] ?? '127.0.0.1';
    $user = $config['username'] ?? '';
    $pass = $config['password'] ?? '';
    $dbname = $config['dbname'] ?? '';
    $charset = $config['charset'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        $msg = date('c') . ' - DB connection failed: ' . $e->getMessage() . "\n";
        // Optional: log to file
        @file_put_contents(__DIR__ . '/db_errors.log', $msg, FILE_APPEND | LOCK_EX);
        return null;
    }
}
