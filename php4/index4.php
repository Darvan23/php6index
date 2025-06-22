<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cijfers</title>
</head>
<body>
    <?php 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include "database.php";
    //toevoegen
    if(isset($_POST['toevoegen'])){
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        $cijfers = $_POST['cijfer'];
        if(is_numeric($cijfers)){
            $stmt = $conn->prepare ("INSERT INTO cijfers(user_id , vak , cijfer)VALUES (?,?,?)");
            $stmt->execute([$user_id,$vak,$cijfers]);
        } else {
            echo "<p style=red>cijfers moet getaal zijn</p>";
        }
    }

    //update
        
        if(isset($_POST['update'])){
            $user_id = $_POST['user_id'];
            $vak = $_POST['vak'];
            $cijfers = $_POST['cijfer'];
            if(is_numeric($cijfers)){
                $stmt = $conn->prepare ("UPDATE cijfers SET cijfer = ? WHERE user_id = ? AND vak =?");
                $stmt->execute([$user_id,$vak,$cijfers]);
            
        } else {
            echo "<p style=red>cijfers moet getaal zijn</p>";
        }
        }
        //Delete
        if(isset($_POST['delete'])){
            $user_id = $_POST['user_id'];
            $vak = $_POST['vak'];
                $stmt = $conn->prepare ("DELETE FROM cijfers WHERE user_id = ? AND vak = ?");
                $stmt->execute([$user_id,$vak]);
            
        }
        //toon bestande cijfers
        $result = $conn->query("SELECT users.name , cijfers.vak , cijfers.cijfer , cijfers.user_id FROM cijfers JOIN users ON cijfers.user_id = users.id ORDER BY users.name");
        echo "<h2>Alle cijfers</h2>";
        echo "<table border='1'>
        <tr><th>Naam</th><th>Vak</th><th>Cijfers</th></tr>";
        while($row=$result->fetch(PDO::FETCH_ASSOC)){
            echo "<tr>
            <td>{$row['name']}</td>";
            echo "<td>{$row['vak']}</td>";
            echo "<td>{$row['cijfer']}</td>
            </tr>";
        } echo "</table>";
        
    
    ?>
    <h2>Overzicht van studenten en hun gemiddelde cijfers</h2>
<?php
// 📊 Haal alle gegevens op
$sql = "
    SELECT users.id, users.name, cijfers.vak, cijfers.cijfer
    FROM users
    LEFT JOIN cijfers ON users.id = cijfers.user_id
    ORDER BY users.name
";
$result = $conn->query($sql);
$rijen = $result->fetchAll(PDO::FETCH_ASSOC);

$studenten = [];

// 🧠 Groepeer per student met for-loop
for ($i = 0; $i < count($rijen); $i++) {
    $id = $rijen[$i]['id'];
    
    if (!isset($studenten[$id])) {
        $studenten[$id] = [
            'naam' => $rijen[$i]['name'],
            'vakken' => [],
            'totaal' => 0,
            'aantal' => 0
        ];
    }

    if (!empty($rijen[$i]['vak']) && is_numeric($rijen[$i]['cijfer'])) {
        $studenten[$id]['vakken'][] = "{$rijen[$i]['vak']}: {$rijen[$i]['cijfer']}";
        $studenten[$id]['totaal'] += $rijen[$i]['cijfer'];
        $studenten[$id]['aantal']++;
    }
}
?>

<!-- 🧾 Tabel laten zien -->
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

    <form action="index4.php" method="post">
     <label>student id</label>
     <input type="text" name="user_id"><br>
     <label>Vak</label><br>
     <input type="text" name="vak"><br>
     <label>Cijfers</label>
     <input type="number" name="cijfer"><br>
     <Button type="submit" name="toevoegen">toevoegen</Button>
     <Button type="submit" name="update">update</Button>
     <Button type="submit" name="delete">Verwijderen</Button>
    </form>
</body>
</html>