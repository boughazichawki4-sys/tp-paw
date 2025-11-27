<?php
$students_file = __DIR__ . '/students.json';
$message = '';
$errors = [];

function h($s){ return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// Load students
$students = [];
if (file_exists($students_file)) {
    $json = file_get_contents($students_file);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) $students = $decoded;
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = date('Y-m-d');
    $attendance_file = __DIR__ . "/attendance_{$date}.json";

    if (file_exists($attendance_file)) {
        $message = 'Attendance for today has already been taken.';
    } else {
        $attendance = [];
        foreach ($students as $s) {
            $id = $s['student_id'] ?? null;
            if ($id === null) continue;
            $field = 'status_' . $id;
            $val = $_POST[$field] ?? 'absent';
            $status = ($val === 'present') ? 'present' : 'absent';
            $attendance[] = ['student_id' => $id, 'status' => $status];
        }

        $encoded = json_encode($attendance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            $errors[] = 'Erreur lors de l\'encodage des données JSON.';
        } else {
            $written = @file_put_contents($attendance_file, $encoded, LOCK_EX);
            if ($written === false) $errors[] = 'Impossible d\'écrire le fichier d\'attendance.';
            else $message = "Attendance saved to attendance_{$date}.json";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Take Attendance</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:20px}
        table{border-collapse:collapse;width:80%}
        th,td{border:1px solid #ccc;padding:8px;text-align:left}
        .present{color:green}
        .absent{color:red}
    </style>
</head>
<body>
    <h1>Prise de présence — <?php echo date('Y-m-d'); ?></h1>

    <?php if ($message): ?>
        <div><strong><?php echo h($message); ?></strong></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color:#a00"><ul><?php foreach($errors as $e) echo '<li>'.h($e).'</li>';?></ul></div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <p>Aucun étudiant trouvé dans <code>students.json</code>.</p>
    <?php else: ?>
        <form method="post" action="">
            <table>
                <thead><tr><th>Student ID</th><th>Name</th><th>Group</th><th>Present?</th></tr></thead>
                <tbody>
                <?php foreach($students as $s):
                    $id = $s['student_id'] ?? '';
                    $name = $s['name'] ?? '';
                    $group = $s['group'] ?? '';
                ?>
                    <tr>
                        <td><?php echo h($id); ?></td>
                        <td><?php echo h($name); ?></td>
                        <td><?php echo h($group); ?></td>
                        <td>
                            <label><input type="radio" name="status_<?php echo h($id); ?>" value="present" checked> Present</label>
                            <label style="margin-left:8px"><input type="radio" name="status_<?php echo h($id); ?>" value="absent"> Absent</label>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="submit">Enregistrer la présence</button> <a href="index.html">Retour</a></p>
        </form>
    <?php endif; ?>
</body>
</html>
