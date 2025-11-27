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
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}</style>
</head>
<body>
  <h1>Supprimer l'étudiant</h1>
  <p>Confirmez la suppression de : <strong><?php echo h($student['fullname']); ?> (<?php echo h($student['matricule']); ?>)</strong></p>
  <form method="post" action="">
    <button type="submit">Oui, supprimer</button>
    <a href="list_students.php" style="margin-left:12px">Annuler</a>
  </form>
</body>
</html>
