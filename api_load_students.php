<?php
/**
 * API endpoint for loading students from database
 * Returns JSON array of students
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$response = ['success' => false, 'students' => [], 'message' => ''];

$pdo = get_db_connection();
if ($pdo === null) {
    http_response_code(500);
    $response['message'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->query('SELECT id, fullname, matricule, group_id, created_at FROM students ORDER BY created_at DESC');
    $response['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;
    http_response_code(200);
} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erreur lors de la récupération des étudiants.';
    @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - api_load_students: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
}

echo json_encode($response);
exit;
