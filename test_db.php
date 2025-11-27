<?php
require_once __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if ($pdo instanceof PDO) {
    echo "Connection successful";
} else {
    echo "Connection failed";
}
