<?php
/**
 * Add student using the database `students` table.
 * Falls back to a friendly message if DB connection is not available.
 */
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$errors = [];
$success = '';

$pdo = get_db_connection();
if ($pdo === null) {
    $errors[] = 'Impossible de se connecter à la base de données. Vérifiez `config.php` et le serveur MySQL.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id = trim($_POST['group_id'] ?? '');

    if ($fullname === '') $errors[] = 'Le nom complet est requis.';
    if ($matricule === '') $errors[] = 'Le matricule est requis.';
    if ($group_id === '') $errors[] = 'Le groupe est requis.';

    if (empty($errors)) {
        // Check for duplicate matricule
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matricule = :m');
        $stmt->execute([':m' => $matricule]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Un étudiant avec ce matricule existe déjà.';
        } else {
            $ins = $pdo->prepare('INSERT INTO students (fullname, matricule, group_id) VALUES (:fn, :m, :g)');
            try {
                $ins->execute([':fn' => $fullname, ':m' => $matricule, ':g' => $group_id]);
                $success = 'Étudiant ajouté avec succès.';
                // Clear form values
                $fullname = $matricule = $group_id = '';
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de l\'enregistrement en base de données.';
                @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - insert failed: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ajouter un étudiant</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:20px}
        .error{color:#a00}
        .success{color:#080}
        label{display:block;margin-top:8px}
        input[type=text]{width:300px;padding:6px}
    </style>
</head>
<body>
    <h1>Ajouter un étudiant</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo h($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?php echo h($success); ?></div>
    <?php endif; ?>

    <?php if (!($pdo instanceof PDO)): ?>
        <p>La fonctionnalité d'ajout d'étudiant nécessite une base de données opérationnelle.</p>
        <p><a href="index.html">Retour</a></p>
    <?php else: ?>
    <form method="post" action="">
        <label>Nom complet:
            <input type="text" name="fullname" value="<?php echo isset($fullname) ? h($fullname) : ''; ?>" required>
        </label>

        <label>Matricule:
            <input type="text" name="matricule" value="<?php echo isset($matricule) ? h($matricule) : ''; ?>" required>
        </label>

        <label>Groupe:
            <input type="text" name="group_id" value="<?php echo isset($group_id) ? h($group_id) : ''; ?>" required>
        </label>

        <p>
            <button type="submit">Ajouter</button>
            <a href="list_students.php" style="margin-left:12px">Voir la liste</a>
            <a href="index.html" style="margin-left:12px">Retour</a>
        </p>
    </form>
    <?php endif; ?>

</body>
</html>
cd "c:\Users\Mi-Computer N°08\Desktop\tp"
php -S localhost:8000