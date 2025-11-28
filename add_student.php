<?php
/**
 * Add student using the database `students` table.
 * Falls back to a friendly message if DB connection is not available.
 */
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function sanitizeInput($input) {
    // FILTER_SANITIZE_STRING est déprécié en PHP 8.1+
    // On supprime les balises HTML et on retire les espaces en début/fin.
    // L'échappement pour l'affichage est géré par la fonction `h()`.
    return trim(strip_tags($input));
}

$errors = [];
$success = '';

$pdo = get_db_connection();
if ($pdo === null) {
    $errors[] = 'Impossible de se connecter à la base de données. Vérifiez `config.php` et le serveur MySQL.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $fullname = sanitizeInput($_POST['fullname'] ?? '');
    $matricule = sanitizeInput($_POST['matricule'] ?? '');
    $group_id = sanitizeInput($_POST['group_id'] ?? '');

    if ($fullname === '') $errors[] = 'Le nom complet est requis.';
    elseif (strlen($fullname) < 3) $errors[] = 'Le nom complet doit contenir au moins 3 caractères.';
    elseif (strlen($fullname) > 100) $errors[] = 'Le nom complet ne peut pas dépasser 100 caractères.';
    
    if ($matricule === '') $errors[] = 'Le matricule est requis.';
    elseif (!preg_match('/^\d{8,}$/', $matricule)) $errors[] = 'Le matricule doit contenir au moins 8 chiffres.';
    
    if ($group_id === '') $errors[] = 'Le groupe est requis.';
    elseif (strlen($group_id) > 50) $errors[] = 'Le groupe ne peut pas dépasser 50 caractères.';

    if (empty($errors)) {
        // Check for duplicate matricule
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matricule = :m');
        if ($stmt === false) {
            $errors[] = 'Erreur de requête préparée.';
        } else {
            $stmt->execute([':m' => $matricule]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Un étudiant avec ce matricule existe déjà.';
            } else {
                $ins = $pdo->prepare('INSERT INTO students (fullname, matricule, group_id, created_at) VALUES (:fn, :m, :g, NOW())');
                if ($ins === false) {
                    $errors[] = 'Erreur de requête préparée lors de l\'insertion.';
                } else {
                    try {
                        $ins->execute([':fn' => $fullname, ':m' => $matricule, ':g' => $group_id]);
                        $success = '✅ Étudiant ajouté avec succès (ID: ' . $pdo->lastInsertId() . ').';
                        // Clear form values
                        $fullname = $matricule = $group_id = '';
                    } catch (PDOException $e) {
                        $errors[] = 'Erreur lors de l\'enregistrement en base de données.';
                        @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - insert failed: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
                    }
                }
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: linear-gradient(135deg, #e9f0fa 0%, #f0e7ff 100%);
            line-height: 1.6;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .error {
            color: #dc2626;
            background-color: #fee2e2;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #dc2626;
        }
        .error ul {
            margin: 8px 0;
            padding-left: 20px;
        }
        .error li {
            margin: 5px 0;
        }
        .success {
            color: #059669;
            background-color: #d1fae5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #059669;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: #333;
        }
        input[type=text] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 2px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type=text]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
            justify-content: center;
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
            background-color: #28a745;
            color: white;
            flex: 1;
            min-width: 120px;
        }
        button[type=submit]:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        a {
            padding: 10px 20px;
            display: inline-block;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s;
        }
        a:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <div class="container">
        <h1>➕ Ajouter un étudiant</h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>Erreurs détectées :</strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo h($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="success"><strong><?php echo h($success); ?></strong></div>
        <?php endif; ?>

        <?php if (!($pdo instanceof PDO)): ?>
            <div class="info">
                ⚠️ La fonctionnalité d'ajout d'étudiant nécessite une base de données opérationnelle.
            </div>
            <div class="button-group" style="margin-top: 25px;">
                <a href="index.html">Retour à l'accueil</a>
            </div>
        <?php else: ?>
        <form method="post" action="">
            <label>Nom complet: <span style="color: red;">*</span>
                <input type="text" name="fullname" value="<?php echo isset($fullname) ? h($fullname) : ''; ?>" required placeholder="Ex: Jean Dupont" maxlength="100">
            </label>

            <label>Matricule: <span style="color: red;">*</span>
                <input type="text" name="matricule" value="<?php echo isset($matricule) ? h($matricule) : ''; ?>" required placeholder="Ex: 20233163" maxlength="20">
            </label>

            <label>Groupe: <span style="color: red;">*</span>
                <input type="text" name="group_id" value="<?php echo isset($group_id) ? h($group_id) : ''; ?>" required placeholder="Ex: A1" maxlength="50">
            </label>

            <div class="button-group">
                <button type="submit">Ajouter l'étudiant</button>
            </div>
        </form>
        
        <div class="button-group" style="margin-top: 15px;">
            <a href="list_students.php">Voir la liste</a>
            <a href="index.html">Retour</a>
        </div>
        
        <div class="info" style="margin-top: 20px;">
            <strong>Conseils :</strong><br>
            • Le nom doit avoir au moins 3 caractères<br>
            • Le matricule doit avoir au moins 8 chiffres<br>
            • Les doublons sont refusés
        </div>
        <?php endif; ?>
    </div>
</body>
</html>