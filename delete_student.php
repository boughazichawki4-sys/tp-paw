<?php
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = get_db_connection();
if ($pdo === null) {
    echo "<p>Impossible de se connecter à la base de données.</p>";
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: list_students.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Perform delete
  try {
    $del = $pdo->prepare('DELETE FROM students WHERE id = :id');
    $del->execute([':id' => $id]);
    header('Location: list_students.php?msg=' . urlencode('Étudiant supprimé.'));
    exit;
  } catch (PDOException $e) {
    @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - delete_student: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
    echo '<p style="color:red">Erreur SQL lors de la suppression : ' . h($e->getMessage()) . '</p>';
    echo '<p><a href="list_students.php">Retour</a></p>';
    exit;
  }
}

// show confirmation
try {
  $stmt = $pdo->prepare('SELECT id, fullname, matricule FROM students WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$student) {
    echo '<div style="color:red">Aucun étudiant trouvé avec cet ID.</div>';
    echo '<p><a href="list_students.php">Retour à la liste</a></p>';
    exit;
  }
} catch (PDOException $e) {
  echo '<div style="color:red">Erreur SQL : ' . h($e->getMessage()) . '</div>';
  @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - delete_student: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
  echo '<p><a href="list_students.php">Retour à la liste</a></p>';
  exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Supprimer l'étudiant</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 20px;
      background: linear-gradient(135deg, #e9f0fa 0%, #f0e7ff 100%);
      line-height: 1.6;
    }
    .container {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
      color: #333;
      text-align: left;
    }
    .info {
      background-color: #dbeafe;
      border-left: 4px solid #3b82f6;
      padding: 12px;
      border-radius: 6px;
      color: #1e40af;
      font-size: 13px;
      margin-top: 15px;
    }
    .warning {
      color: #92400e;
      background-color: #fffbeb;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      border-left: 4px solid #f59e0b;
    }
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      flex-wrap: wrap;
      justify-content: flex-start;
    }
    button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    button[type=submit] {
      background-color: #ef4444;
      color: white;
      min-width: 140px;
    }
    button[type=submit]:hover { background-color: #dc2626; transform: translateY(-2px); }
    a.btn {
      padding: 10px 20px;
      display: inline-block;
      background-color: #2563eb;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: 600;
    }
    a.cancel { background-color: transparent; color: #6b21a8; text-decoration: underline; padding: 0; font-weight: 600; }
  </style>
</head>
<body>
  <div class="container">
  <h1>Supprimer l'étudiant</h1>

  <div class="warning">
    <strong>Attention :</strong> Vous êtes sur le point de supprimer définitivement cet étudiant.
  </div>

  <p>Confirmez la suppression de : <strong><?php echo h($student['fullname']); ?> (<?php echo h($student['matricule']); ?>)</strong></p>

  <form method="post" action="">
    <div class="button-group">
      <button type="submit">Oui, supprimer</button>
      <a class="btn" href="list_students.php">Annuler</a>
    </div>
  </form>

  <div class="info" style="margin-top:18px;">
    <strong>Conseil :</strong> Assurez-vous que l'étudiant ne soit plus nécessaire avant de confirmer. Une fois supprimé, l'enregistrement est perdu.
  </div>
  </div>
</body>
</html>
