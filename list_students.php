<?php
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = get_db_connection();
if ($pdo === null) {
    echo "<div style='max-width:800px;margin:20px auto;background:#fee2e2;color:#dc2626;padding:15px;border-radius:6px;'><strong>❌ Erreur :</strong> Impossible de se connecter à la base de données. Vérifiez `config.php`.</div>";
    exit;
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at DESC';

// Sanitize sort_by to prevent SQL injection
$allowed_sorts = [
    'id ASC', 'id DESC',
    'fullname ASC', 'fullname DESC',
    'matricule ASC', 'matricule DESC',
    'group_id ASC', 'group_id DESC',
    'created_at ASC', 'created_at DESC'
];
if (!in_array($sort_by, $allowed_sorts, true)) {
    $sort_by = 'created_at DESC';
}

try {
  $stmt = $pdo->query('SELECT id, fullname, matricule, group_id, created_at FROM students ORDER BY ' . $sort_by);
  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo '<div style="max-width:800px;margin:20px auto;background:#fee2e2;color:#dc2626;padding:15px;border-radius:6px;"><strong>❌ Erreur SQL :</strong> ' . h($e->getMessage()) . '</div>';
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
  <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 20px;
        background: linear-gradient(135deg, #e9f0fa 0%, #f0e7ff 100%);
        line-height: 1.6;
    }
    .container {
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 25px;
    }
    .controls {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .controls a, .controls button {
        padding: 10px 16px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    .controls a.secondary, .controls button.secondary {
        background-color: #2563eb;
    }
    .controls a:hover, .controls button:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    .message {
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid;
    }
    .message.success {
        background-color: #d1fae5;
        color: #059669;
        border-color: #059669;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    thead {
        background-color: #3b82f6;
        color: white;
    }
    th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
    }
    th:hover {
        background-color: #2563eb;
    }
    td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    tbody tr:hover {
        background-color: #f3f4f6;
    }
    .empty {
        text-align: center;
        color: #6b7280;
        font-style: italic;
        padding: 30px;
    }
    .actions {
        display: flex;
        gap: 8px;
    }
    .actions a {
        padding: 6px 12px;
        background-color: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .actions a.delete {
        background-color: #ef4444;
    }
    .actions a:hover {
        opacity: 0.8;
    }
    .sort-indicator {
        margin-left: 5px;
        font-size: 11px;
    }
    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }
        table {
            font-size: 13px;
        }
        th, td {
            padding: 8px;
        }
        .actions {
            flex-direction: column;
        }
        .actions a {
            text-align: center;
            padding: 8px 6px;
        }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>📋 Liste des étudiants (<?php echo count($students); ?>)</h1>
    
    <div class="controls">
        <a href="add_student.php">➕ Ajouter un étudiant</a>
        <a href="index.html" class="secondary">🏠 Accueil</a>
    </div>
    
    <?php if ($msg): ?>
        <div class="message success">✅ <?php echo h($msg); ?></div>
    <?php endif; ?>
    
    <?php if (empty($students)): ?>
        <div class="empty">
            📭 Aucun étudiant trouvé. <a href="add_student.php">Ajouter un premier étudiant</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
              <tr>
                <th><a href="?sort=id ASC" style="color:white;text-decoration:none;">ID</a> <?php echo ($sort_by === 'id ASC' ? '<span class="sort-indicator">↑</span>' : ($sort_by === 'id DESC' ? '<span class="sort-indicator">↓</span>' : '')); ?></th>
                <th><a href="?sort=fullname ASC" style="color:white;text-decoration:none;">Nom complet</a> <?php echo ($sort_by === 'fullname ASC' ? '<span class="sort-indicator">↑</span>' : ($sort_by === 'fullname DESC' ? '<span class="sort-indicator">↓</span>' : '')); ?></th>
                <th><a href="?sort=matricule ASC" style="color:white;text-decoration:none;">Matricule</a> <?php echo ($sort_by === 'matricule ASC' ? '<span class="sort-indicator">↑</span>' : ($sort_by === 'matricule DESC' ? '<span class="sort-indicator">↓</span>' : '')); ?></th>
                <th><a href="?sort=group_id ASC" style="color:white;text-decoration:none;">Groupe</a> <?php echo ($sort_by === 'group_id ASC' ? '<span class="sort-indicator">↑</span>' : ($sort_by === 'group_id DESC' ? '<span class="sort-indicator">↓</span>' : '')); ?></th>
                <th><a href="?sort=created_at DESC" style="color:white;text-decoration:none;">Créé le</a> <?php echo ($sort_by === 'created_at ASC' ? '<span class="sort-indicator">↑</span>' : ($sort_by === 'created_at DESC' ? '<span class="sort-indicator">↓</span>' : '')); ?></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $s): ?>
                <tr>
                  <td><strong><?php echo h($s['id']); ?></strong></td>
                  <td><?php echo h($s['fullname']); ?></td>
                  <td><?php echo h($s['matricule']); ?></td>
                  <td><?php echo h($s['group_id']); ?></td>
                  <td><?php echo h($s['created_at']); ?></td>
                  <td>
                    <div class="actions">
                      <a href="update_student.php?id=<?php echo urlencode($s['id']); ?>">✏️ Modifier</a>
                      <a href="delete_student.php?id=<?php echo urlencode($s['id']); ?>" class="delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?');">🗑️ Supprimer</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
  </div>
</body>
</html>
