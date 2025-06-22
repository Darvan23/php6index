<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students & Cijfers</title>
</head>
<body>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';

// ✅ Toevoegen
if (isset($_POST['toevoegen'])) {
    $user_id = $_POST['user_id'];
    $cijfers = $_POST['cijfers'];
    $vak = $_POST['vak'];

    if (is_numeric($cijfers)) {
        $stmt = $conn->prepare("INSERT INTO cijfers (user_id, vak, cijfer) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $vak, $cijfers]);
    } else {
        echo "<p style='color:red;'>Fout: cijfer moet een getal zijn.</p>";
    }
}

// ✅ Updaten
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nieuw_cijfer = $_POST['cijfer'];

    if (is_numeric($nieuw_cijfer)) {
        $stmt = $conn->prepare("UPDATE cijfers SET cijfer = ? WHERE id = ?");
        $stmt->execute([$nieuw_cijfer, $id]);
    } else {
        echo "<p style='color:red;'>Fout: cijfer moet een getal zijn.</p>";
    }
}

// ✅ Verwijderen
if (isset($_POST['verwijder'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM cijfers WHERE id = ?");
    $stmt->execute([$id]);
}

// ✅ Alle cijfers tonen met update/verwijder
$result = $conn->query("
    SELECT cijfers.id, users.name, cijfers.vak, cijfers.cijfer
    FROM cijfers
    JOIN users ON cijfers.user_id = users.id
");

echo "<h2>Alle cijfers</h2>";
echo "<table border='1'>
<tr><th>Naam</th><th>Vak</th><th>Cijfer</th><th>Update</th><th>Verwijder</th></tr>";

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
        <td>{$row['name']}</td>
        <td>{$row['vak']}</td>
        <td>{$row['cijfer']}</td>
        <td>
            <form method='post'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <input type='number' step='0.01' name='cijfer' required>
                <button type='submit' name='update'>Update</button>
            </form>
        </td>
        <td>
            <form method='post'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <button type='submit' name='verwijder'>Verwijder</button>
            </form>
        </td>
    </tr>";
}
echo "</table>";
?>

<!-- ✅ Formulier voor toevoegen -->
<h2>Nieuw cijfer toevoegen</h2>
<form method="post">
    <label>Student ID:</label>
    <input type="number" name="user_id" required><br>
    <label>Vak:</label>
    <input type="text" name="vak" required><br>
    <label>Cijfer:</label>
    <input type="number" step="0.01" name="cijfers" required><br>
    <button type="submit" name="toevoegen">Toevoegen</button>
</form>

<?php
// ✅ Overzicht per student met alle cijfers en gemiddelde
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

echo "<h2>Overzicht per student (cijfers + gemiddelde)</h2>";
echo "<table border='1'>
<tr><th>Naam</th><th>Cijfers</th><th>Gemiddelde</th></tr>";

foreach ($studenten as $student) {
    $naam = $student['name'];
    $vakken = implode(", ", $student['vakken']);
    $gemiddelde = $student['aantal'] > 0 ? number_format($student['totaal'] / $student['aantal'], 2) : "-";

    echo "<tr>
        <td>$naam</td>
        <td>$vakken</td>
        <td>$gemiddelde</td>
    </tr>";
}
echo "</table>";
?>

</body>
</html>
