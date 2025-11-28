<?php
/**
 * Unified page for managing students
 * - Add form with AJAX submission
 * - List loaded from database
 * - Real-time updates
 */
require_once __DIR__ . '/db_connect.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = get_db_connection();
if ($pdo === null) {
    echo "<div style='max-width:1200px;margin:20px auto;background:#fee2e2;color:#dc2626;padding:15px;border-radius:6px;'><strong>❌ Erreur :</strong> Impossible de se connecter à la base de données. Vérifiez `config.php`.</div>";
    exit;
}

// Load students from database
$students = [];
try {
    $stmt = $pdo->query('SELECT id, fullname, matricule, group_id, created_at FROM students ORDER BY created_at DESC');
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    @file_put_contents(__DIR__ . '/db_errors.log', date('c') . ' - manage_students: ' . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestion des Étudiants</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e9f0fa 0%, #f0e7ff 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        /* Form Section */
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #f0f9ff;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
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

        .btn-submit:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-reset {
            background-color: #6b7280;
            color: white;
        }

        .btn-reset:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid;
            font-size: 13px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #dc2626;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #059669;
            border-color: #059669;
        }

        /* Table Section */
        .table-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .table-section h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: space-between;
        }

        .student-count {
            background-color: #3b82f6;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .sync-indicator {
            background-color: #10b981;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            animation: pulse 2s infinite;
            margin-left: 10px;
        }

        .sync-indicator.syncing {
            background-color: #f59e0b;
            animation: spin 1s linear infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #3b82f6;
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        tbody tr {
            transition: background-color 0.2s;
        }

        tbody tr:hover {
            background-color: #f0f9ff;
        }

        tbody tr.new-row {
            background-color: #fef9c3;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state p {
            font-style: italic;
            margin-bottom: 15px;
        }

        .actions {
            display: flex;
            gap: 6px;
        }

        .actions a {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            transition: all 0.2s;
            font-weight: 600;
        }

        .actions .edit {
            background-color: #3b82f6;
        }

        .actions .edit:hover {
            background-color: #2563eb;
        }

        .actions .delete {
            background-color: #ef4444;
        }

        .actions .delete:hover {
            background-color: #dc2626;
        }

        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .controls a {
            padding: 10px 16px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
        }

        .controls a:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }

        /* Loading indicator */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #3b82f6;
            font-weight: 600;
        }

        @media (max-width: 968px) {
            .content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .form-section, .table-section {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            th, td {
                padding: 8px;
                font-size: 12px;
            }

            .actions a {
                padding: 4px 8px;
                font-size: 11px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Gestion des Étudiants</h1>

        <div class="controls">
            <a href="index.html">🏠 Accueil</a>
        </div>

        <div class="content">
            <!-- Form Section -->
            <div class="form-section">
                <h2>➕ Ajouter un Étudiant</h2>
                
                <div id="formAlert" class="alert"></div>

                <form id="addStudentForm" name="addStudentForm">
                    <div class="form-group">
                        <label for="fullname">👤 Nom complet *</label>
                        <input type="text" id="fullname" name="fullname" required placeholder="Ex: Jean Dupont" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="matricule">🆔 Matricule *</label>
                        <input type="text" id="matricule" name="matricule" required placeholder="Ex: 20233163" maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="group_id">👥 Groupe *</label>
                        <input type="text" id="group_id" name="group_id" required placeholder="Ex: A1" maxlength="50">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">💾 Ajouter</button>
                        <button type="reset" class="btn-reset">🔄 Réinitialiser</button>
                    </div>
                </form>

                <div class="info-box" style="background-color:#dbeafe;border-left:4px solid #3b82f6;padding:12px;border-radius:6px;color:#1e40af;font-size:12px;margin-top:15px;">
                    <strong>ℹ️ Info :</strong><br>
                    • Min. 3 caractères pour le nom<br>
                    • Min. 8 chiffres pour le matricule<br>
                    • Matricule unique requis
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <h2>
                    📋 Liste des Étudiants
                    <span class="student-count" id="studentCount"><?php echo count($students); ?> étudiant(s)</span>
                    <span class="sync-indicator" id="syncIndicator" title="Synchronisation avec la base de données">🔄 En sync</span>
                </h2>

                <div class="loading" id="tableLoading">⏳ Chargement...</div>

                <div id="studentTableContainer">
                    <?php if (!empty($students)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Matricule</th>
                                    <th>Groupe</th>
                                    <th>Créé</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <?php foreach ($students as $s): ?>
                                    <tr data-id="<?php echo h($s['id']); ?>">
                                        <td><strong><?php echo h($s['id']); ?></strong></td>
                                        <td><?php echo h($s['fullname']); ?></td>
                                        <td><?php echo h($s['matricule']); ?></td>
                                        <td><?php echo h($s['group_id']); ?></td>
                                        <td><?php echo h(substr($s['created_at'], 0, 10)); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="update_student.php?id=<?php echo urlencode($s['id']); ?>" class="edit">✏️ Modifier</a>
                                                <a href="delete_student.php?id=<?php echo urlencode($s['id']); ?>" class="delete" onclick="return confirm('Êtes-vous sûr ?');">🗑️ Supprimer</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>📭 Aucun étudiant trouvé.</p>
                            <p style="font-size:12px;color:#9ca3af;">Utilisez le formulaire à gauche pour ajouter un étudiant.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addStudentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formAlert = document.getElementById('formAlert');
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            // Reset alert
            formAlert.classList.remove('show', 'alert-error', 'alert-success');
            formAlert.textContent = '';

            // Disable submit button
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '⏳ Ajout en cours...';

            try {
                const formData = new FormData(e.target);
                const response = await fetch('api_add_student.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success && response.ok) {
                    // Show success message
                    formAlert.textContent = data.message;
                    formAlert.classList.add('show', 'alert-success');

                    // Reset form
                    e.target.reset();
                    e.target.querySelector('#fullname').focus();

                    // Add new student to table
                    if (data.student) {
                        addStudentToTable(data.student);
                        updateStudentCount();
                    }

                    // Clear message after 3 seconds
                    setTimeout(() => {
                        formAlert.classList.remove('show');
                    }, 3000);
                } else {
                    formAlert.textContent = data.message || 'Erreur lors de l\'ajout.';
                    formAlert.classList.add('show', 'alert-error');
                }
            } catch (error) {
                formAlert.textContent = '❌ Erreur réseau: ' + error.message;
                formAlert.classList.add('show', 'alert-error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });

        function addStudentToTable(student) {
            const tbody = document.getElementById('studentTableBody');
            
            // Remove empty state if exists
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) emptyState.remove();

            // Create or get table
            let table = document.querySelector('table');
            if (!table) {
                // Create table if it doesn't exist
                const container = document.getElementById('studentTableContainer');
                table = document.createElement('table');
                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Matricule</th>
                        <th>Groupe</th>
                        <th>Créé</th>
                        <th>Actions</th>
                    </tr>
                `;
                table.appendChild(thead);
                const newTbody = document.createElement('tbody');
                newTbody.id = 'studentTableBody';
                table.appendChild(newTbody);
                container.innerHTML = '';
                container.appendChild(table);
            }

            const tbody2 = document.getElementById('studentTableBody') || table.querySelector('tbody');
            
            const row = document.createElement('tr');
            row.className = 'new-row';
            row.dataset.id = student.id;
            row.innerHTML = `
                <td><strong>${escapeHtml(student.id)}</strong></td>
                <td>${escapeHtml(student.fullname)}</td>
                <td>${escapeHtml(student.matricule)}</td>
                <td>${escapeHtml(student.group_id)}</td>
                <td>${escapeHtml(student.created_at.substring(0, 10))}</td>
                <td>
                    <div class="actions">
                        <a href="update_student.php?id=${encodeURIComponent(student.id)}" class="edit">✏️ Modifier</a>
                        <a href="delete_student.php?id=${encodeURIComponent(student.id)}" class="delete" onclick="return confirm('Êtes-vous sûr ?');">🗑️ Supprimer</a>
                    </div>
                </td>
            `;
            
            tbody2.insertBefore(row, tbody2.firstChild);
            
            // Remove highlight after animation
            setTimeout(() => row.classList.remove('new-row'), 500);
        }

        function updateStudentCount() {
            const tbody = document.getElementById('studentTableBody');
            const count = tbody ? tbody.querySelectorAll('tr').length : 0;
            document.getElementById('studentCount').textContent = count + ' étudiant(s)';
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // === SYNCHRONISATION EN TEMPS RÉEL ===
        // Recharger les étudiants depuis la BD toutes les 2 secondes
        function reloadStudentsFromDatabase() {
            const syncIndicator = document.getElementById('syncIndicator');
            
            // Marquer comme en cours de synchronisation
            if (syncIndicator) {
                syncIndicator.classList.add('syncing');
                syncIndicator.textContent = '⏳ Syncing...';
            }

            fetch('api_sync_students.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.students && data.students.length > 0) {
                        // Vérifier s'il y a de nouveaux étudiants
                        const currentCount = document.querySelectorAll('#studentTableBody tr').length;
                        
                        if (data.count > currentCount) {
                            // Nouvelle donnée détectée - recharger
                            if (syncIndicator) {
                                syncIndicator.classList.add('syncing');
                                syncIndicator.textContent = '🔄 Actualisation...';
                            }
                            setTimeout(() => {
                                location.reload();
                            }, 500);
                        } else {
                            // Pas de changement - rétablir l'état normal
                            if (syncIndicator) {
                                syncIndicator.classList.remove('syncing');
                                syncIndicator.textContent = '🔄 En sync';
                            }
                        }
                    }
                })
                .catch(error => {
                    console.warn('Erreur de synchronisation:', error);
                    if (syncIndicator) {
                        syncIndicator.classList.remove('syncing');
                        syncIndicator.textContent = '⚠️ Erreur sync';
                        syncIndicator.style.backgroundColor = '#ef4444';
                    }
                });
        }

        // Vérifier les mises à jour toutes les 2 secondes
        setInterval(reloadStudentsFromDatabase, 2000);

        // Aussi écouter les changements de localStorage (from index.html)
        window.addEventListener('storage', function(e) {
            if (e.key === 'syncTrigger' || e.key === 'newStudentAdded') {
                // Un nouvel étudiant a été ajouté depuis une autre page
                console.log('📡 Synchronisation détectée depuis localStorage...');
                const syncIndicator = document.getElementById('syncIndicator');
                if (syncIndicator) {
                    syncIndicator.classList.add('syncing');
                    syncIndicator.textContent = '🔄 Synchronisation...';
                }
                setTimeout(() => {
                    reloadStudentsFromDatabase();
                }, 500);
            }
        });
    </script>
</body>
</html>
