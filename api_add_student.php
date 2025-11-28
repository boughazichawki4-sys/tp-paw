<?php
/**
 * API endpoint for adding a student via AJAX
 * Returns JSON response with success/error status
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function sanitizeInput($input) {
    return trim(strip_tags($input));
}

$response = ['success' => false, 'message' => '', 'student' => null];

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Méthode non autorisée. Utilisez POST.';
    echo json_encode($response);
    exit;
}

$pdo = get_db_connection();
if ($pdo === null) {
    http_response_code(500);
    $response['message'] = 'Erreur de connexion à la base de données.';
    echo json_encode($response);
    exit;
}

$fullname = sanitizeInput($_POST['fullname'] ?? '');
$matricule = sanitizeInput($_POST['matricule'] ?? '');
$group_id = sanitizeInput($_POST['group_id'] ?? '');

// Validation
$errors = [];

if ($fullname === '') {
    $errors[] = 'Le nom complet est requis.';
} elseif (strlen($fullname) < 3) {
    $errors[] = 'Le nom complet doit contenir au moins 3 caractères.';
} elseif (strlen($fullname) > 100) {
    $errors[] = 'Le nom complet ne peut pas dépasser 100 caractères.';
}

if ($matricule === '') {
    $errors[] = 'Le matricule est requis.';
} elseif (!preg_match('/^\d{8,}$/', $matricule)) {
    $errors[] = 'Le matricule doit contenir au moins 8 chiffres.';
}

if ($group_id === '') {
    $errors[] = 'Le groupe est requis.';
} elseif (strlen($group_id) > 50) {
    $errors[] = 'Le groupe ne peut pas dépasser 50 caractères.';
}

if (!empty($errors)) {
    http_response_code(400);
    $response['message'] = implode(' | ', $errors);
    echo json_encode($response);
    exit;
}

// Check for duplicate matricule
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matricule = :m');
    $stmt->execute([':m' => $matricule]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        $response['message'] = 'Un étudiant avec ce matricule existe déjà.';
        echo json_encode($response);
        exit;
    }

    // Insert new student
    $ins = $pdo->prepare('INSERT INTO students (fullname, matricule, group_id, created_at) VALUES (:fn, :m, :g, NOW())');
    $ins->execute([':fn' => $fullname, ':m' => $matricule, ':g' => $group_id]);
    
    $studentId = $pdo->lastInsertId();
    
    // Fetch the newly inserted student
    $selectStmt = $pdo->prepare('SELECT id, fullname, matricule, group_id, created_at FROM students WHERE id = :id');
    $selectStmt->execute([':id' => $studentId]);
    $student = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['message'] = '✅ Étudiant ajouté avec succès!';
    $response['student'] = $student;
    http_response_code(201);

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erreur lors de l\'enregistrement en base de données.';
    @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - api_add_student: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
}

echo json_encode($response);
exit;
