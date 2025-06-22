<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studenten Cijfers</title>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';

// ✅ Toevoegen / Update / Verwijder logica
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actie'])) {
        $actie = $_POST['actie'];

        if ($actie == "toevoegen") {
            $user_id = $_POST['user_id'];
            $vak = $_POST['vak'];
            $cijfer = $_POST['cijfer'];
            if (is_numeric($cijfer)) {
                $stmt = $conn->prepare("INSERT INTO cijfers (user_id, vak, cijfer) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $vak, $cijfer]);
            } else {
                echo "<p style='color:red;'>Fout: cijfer moet een getal zijn.</p>";
            }

        } elseif ($actie == "update") {
            $id = $_POST['id'];
            $nieuw_cijfer = $_POST['cijfer'];
            if (is_numeric($nieuw_cijfer)) {
                $stmt = $conn->prepare("UPDATE cijfers SET cijfer = ? WHERE id = ?");
                $stmt->execute([$nieuw_cijfer, $id]);
            }

        } elseif ($actie == "verwijder") {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM cijfers WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
}

// ✅ Haal alle cijfers op
$result = $conn->query("
    SELECT cijfers.id, users.name, users.id as user_id, cijfers.vak, cijfers.cijfer
    FROM cijfers
    JOIN users ON cijfers.user_id = users.id
");
$cijfers = $result->fetchAll(PDO::FETCH_ASSOC);

// ✅ Haal alle studenten op voor dropdown
$students = $conn->query("SELECT * FROM users ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Nieuw cijfer toevoegen</h2>
<form method="post">
    <input type="hidden" name="actie" value="toevoegen">
    <label>Student:</label>
    <select name="user_id" required>
        <?php foreach ($students as $student): ?>
            <option value="<?= $student['id'] ?>"><?= $student['name'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Vak:</label>
    <input type="text" name="vak" required><br>

    <label>Cijfer:</label>
    <input type="number" step="0.01" name="cijfer" required><br>

    <button type="submit">Toevoegen</button>
</form>

<hr>

<h2>Alle cijfers (bewerken/verwijderen)</h2>
<table border="1">
    <tr><th>Naam</th><th>Vak</th><th>Cijfer</th><th>Acties</th></tr>
    <?php foreach ($cijfers as $row): ?>
    <tr>
        <form method="post">
            <td><?= $row['name'] ?></td>
            <td><?= $row['vak'] ?></td>
            <td>
                <input type="number" step="0.01" name="cijfer" value="<?= $row['cijfer'] ?>" required>
            </td>
            <td>
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="actie" value="update">Update</button>
                <button type="submit" name="actie" value="verwijder" onclick="return confirm('Weet je het zeker?')">Verwijder</button>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>

<hr>

<h2>Overzicht per student met gemiddelde</h2>
<?php
// ✅ Bouw overzicht
$sql = "
    SELECT users.id, users.name, cijfers.vak, cijfers.cijfer
    FROM users
    LEFT JOIN cijfers ON users.id = cijfers.user_id
    ORDER BY users.name
";
$result = $conn->query($sql);
$studenten = [];

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id'];
    if (!isset($studenten[$id])) {
        $studenten[$id] = [
            'name' => $row['name'],
            'vakken' => [],
            'totaal' => 0,
            'aantal' => 0
        ];
    }

    if ($row['vak'] && is_numeric($row['cijfer'])) {
        $studenten[$id]['vakken'][] = "{$row['vak']}: {$row['cijfer']}";
        $studenten[$id]['totaal'] += $row['cijfer'];
        $studenten[$id]['aantal']++;
    }
}
?>
<table border="1">
    <tr><th>Naam</th><th>Cijfers</th><th>Gemiddelde</th></tr>
    <?php foreach ($studenten as $student): ?>
    <tr>
        <td><?= $student['name'] ?></td>
        <td><?= implode(', ', $student['vakken']) ?></td>
        <td><?= $student['aantal'] > 0 ? number_format($student['totaal'] / $student['aantal'], 2) : '-' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
