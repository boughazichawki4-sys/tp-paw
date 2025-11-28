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
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #e9f0fa 0%, #f0e7ff 100%);
      padding: 20px;
      min-height: 100vh;
    }

    .container {
      max-width: 500px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      padding: 40px;
    }

    h1 {
      color: #333;
      margin-bottom: 30px;
      text-align: center;
      font-size: 28px;
    }

    .alert {
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid;
    }

    .alert-error {
      background-color: #fee;
      border-color: #dc2626;
      color: #991b1b;
    }

    .alert-error ul {
      list-style: none;
      margin: 0;
    }

    .alert-error li {
      padding: 5px 0;
      padding-left: 20px;
      position: relative;
    }

    .alert-error li:before {
      content: "✗ ";
      position: absolute;
      left: 0;
      color: #dc2626;
      font-weight: bold;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      font-size: 14px;
    }

    input[type="text"] {
      width: 100%;
      padding: 12px;
      border: 2px solid #d1d5db;
      border-radius: 6px;
      font-size: 15px;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    input[type="text"]:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      background-color: #f0f9ff;
    }

    input[type="text"]:invalid:not(:placeholder-shown) {
      border-color: #dc2626;
    }

    .form-actions {
      display: flex;
      gap: 10px;
      margin-top: 30px;
      justify-content: center;
    }

    button {
      padding: 12px 28px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-submit {
      background-color: #28a745;
      color: white;
    }

    .btn-submit:hover {
      background-color: #218838;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    .btn-cancel {
      background-color: #6b7280;
      color: white;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      background-color: #4b5563;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }

    .info-box {
      background-color: #f0f9ff;
      border-left: 4px solid #3b82f6;
      padding: 12px;
      border-radius: 6px;
      margin-top: 20px;
      color: #1e40af;
      font-size: 13px;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      color: #3b82f6;
      text-decoration: none;
      font-size: 14px;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 600px) {
      .container {
        padding: 25px;
        margin: 20px auto;
      }

      h1 {
        font-size: 22px;
        margin-bottom: 20px;
      }

      input[type="text"] {
        padding: 10px;
        font-size: 14px;
      }

      button {
        padding: 10px 20px;
        font-size: 14px;
      }

      .form-actions {
        flex-direction: column;
      }

      .btn-cancel {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>✏️ Modifier l'étudiant</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <strong>⚠️ Erreurs détectées :</strong>
        <ul>
          <?php foreach($errors as $e) echo '<li>' . h($e) . '</li>'; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="form-group">
        <label for="fullname">👤 Nom complet</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo h($_POST['fullname'] ?? $student['fullname']); ?>" required minlength="2" maxlength="100" placeholder="Ex: Boughazi Chawki">
      </div>

      <div class="form-group">
        <label for="matricule">🆔 Matricule</label>
        <input type="text" id="matricule" name="matricule" value="<?php echo h($_POST['matricule'] ?? $student['matricule']); ?>" required minlength="8" maxlength="20" placeholder="Ex: 20233163">
      </div>

      <div class="form-group">
        <label for="group_id">👥 Groupe</label>
        <input type="text" id="group_id" name="group_id" value="<?php echo h($_POST['group_id'] ?? $student['group_id']); ?>" required minlength="1" maxlength="50" placeholder="Ex: L3-INFO-A">
      </div>

      <div class="info-box">
        ℹ️ <strong>Rappel :</strong> Le matricule doit être unique et contenir au minimum 8 caractères.
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-submit">💾 Enregistrer les modifications</button>
        <a href="list_students.php" class="btn-cancel">❌ Annuler</a>
      </div>
    </form>

    <div class="back-link">
      <a href="list_students.php">← Retour à la liste des étudiants</a>
    </div>
  </div>
</body>
</html>
