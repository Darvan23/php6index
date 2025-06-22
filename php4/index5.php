<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Overzicht</title>
</head>
<body>

<h2>Overzicht van studenten en hun gemiddelde cijfers</h2>

<?php
include 'database.php';

// Haal alle data op
$sql = "SELECT users.id, users.name, cijfers.vak, cijfers.cijfer
        FROM users 
        LEFT JOIN cijfers ON users.id = cijfers.user_id 
        ORDER BY users.name";

$studenten = [];
foreach ($conn->query($sql) as $rij) {
    $id = $rij['id'];

    if (!isset($studenten[$id])) {
        $studenten[$id] = [
            'naam' => $rij['name'],
            'vakken' => [],
            'totaal' => 0,
            'aantal' => 0
        ];
    }

    if (!empty($rij['vak']) && is_numeric($rij['cijfer'])) {
        $studenten[$id]['vakken'][] = "{$rij['vak']}: {$rij['cijfer']}";
        $studenten[$id]['totaal'] += $rij['cijfer'];
        $studenten[$id]['aantal']++;
    }
}
?>

<table border="1">
    <tr>
        <th>Naam</th>
        <th>Vakken + cijfers</th>
        <th>Gemiddelde</th>
    </tr>
    <?php foreach ($studenten as $student): ?>
    <tr>
        <td><?= $student['naam'] ?></td>
        <td><?= implode(', ', $student['vakken']) ?></td>
        <td>
            <?= $student['aantal'] > 0
                ? number_format($student['totaal'] / $student['aantal'], 2)
                : '-' ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
