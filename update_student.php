<?php
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = get_db_connection();
if ($pdo === null) {
    echo "<p>Impossible de se connecter à la base de données.</p>";
    exit;
}

$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: list_students.php'); exit;
}

// Load existing

try {
  $stmt = $pdo->prepare('SELECT id, fullname, matricule, group_id FROM students WHERE id = :id');
  $stmt->execute([':id' => $id]);
  $student = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$student) {
    echo '<div style="color:red">Aucun étudiant trouvé avec cet ID.</div>';
    echo '<p><a href="list_students.php">Retour à la liste</a></p>';
    exit;
  }
} catch (PDOException $e) {
  echo '<div style="color:red">Erreur SQL : ' . h($e->getMessage()) . '</div>';
  @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - update_student: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
  echo '<p><a href="list_students.php">Retour à la liste</a></p>';
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');

    if ($fullname === '') $errors[] = 'Le nom complet est requis.';
    if ($matricule === '') $errors[] = 'Le matricule est requis.';
    if ($group_id === '') $errors[] = 'Le groupe est requis.';

    if (empty($errors)) {
        // ensure matricule unique for other rows
        try {
          $chk = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matricule = :m AND id <> :id');
          $chk->execute([':m' => $matricule, ':id' => $id]);
          if ($chk->fetchColumn() > 0) {
            $errors[] = 'Ce matricule est déjà utilisé par un autre étudiant.';
          } else {
            $upd = $pdo->prepare('UPDATE students SET fullname = :fn, matricule = :m, group_id = :g WHERE id = :id');
            $upd->execute([':fn' => $fullname, ':m' => $matricule, ':g' => $group_id, ':id' => $id]);
            header('Location: list_students.php?msg=' . urlencode('Étudiant mis à jour.'));
            exit;
          }
        } catch (PDOException $e) {
          $errors[] = 'Erreur SQL lors de la mise à jour : ' . h($e->getMessage());
          @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - update_student: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Modifier l'étudiant</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}label{display:block;margin-top:8px}input[type=text]{width:300px;padding:6px}.error{color:#a00}</style>
</head>
<body>
  <h1>Modifier l'étudiant</h1>
  <?php if (!empty($errors)): ?><div class="error"><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" action="">
    <label>Nom complet:
      <input type="text" name="fullname" value="<?php echo h($_POST['fullname'] ?? $student['fullname']); ?>" required>
    </label>
    <label>Matricule:
      <input type="text" name="matricule" value="<?php echo h($_POST['matricule'] ?? $student['matricule']); ?>" required>
    </label>
    <label>Groupe:
      <input type="text" name="group_id" value="<?php echo h($_POST['group_id'] ?? $student['group_id']); ?>" required>
    </label>
    <p>
      <button type="submit">Enregistrer</button>
      <a href="list_students.php" style="margin-left:12px">Annuler</a>
    </p>
  </form>
</body>
</html>
