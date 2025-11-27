<?php
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = get_db_connection();
if ($pdo === null) {
    echo "<p>Impossible de se connecter à la base de données. Vérifiez `config.php`.</p>";
    exit;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

try {
  $stmt = $pdo->query('SELECT id, fullname, matricule, group_id, created_at FROM students ORDER BY id DESC');
  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo '<div style="color:red">Erreur SQL : ' . h($e->getMessage()) . '</div>';
  @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - list_students: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
  $students = [];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Liste des étudiants</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;margin:20px}table{border-collapse:collapse;width:90%}th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#eee}</style>
</head>
<body>
  <h1>Liste des étudiants</h1>
  <?php if ($msg): ?><div style="color:green"><?php echo h($msg); ?></div><?php endif; ?>
  <p><a href="add_student.php">Ajouter un étudiant</a> | <a href="index.html">Accueil</a></p>
  <table>
    <thead>
      <tr><th>ID</th><th>Nom complet</th><th>Matricule</th><th>Groupe</th><th>Créé</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if (empty($students)): ?>
        <tr><td colspan="6">Aucun étudiant trouvé.</td></tr>
      <?php else: ?>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?php echo h($s['id']); ?></td>
            <td><?php echo h($s['fullname']); ?></td>
            <td><?php echo h($s['matricule']); ?></td>
            <td><?php echo h($s['group_id']); ?></td>
            <td><?php echo h($s['created_at']); ?></td>
            <td>
              <a href="update_student.php?id=<?php echo urlencode($s['id']); ?>">Modifier</a>
              | <a href="delete_student.php?id=<?php echo urlencode($s['id']); ?>">Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
