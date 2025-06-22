<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    include "database.php";
    //toevoegen
    if (isset($_POST['toevoegen'])) {
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        $cijfers = $_POST['cijfers'];
        if (is_numeric($cijfers)) {
            $stmt = $conn->prepare("INSERT INTO cijfers (user_id , vak, cijfer) VALUES (?,?,?)");
            $stmt->execute([$user_id, $vak, $cijfers]);
        } else {
            echo "<p style=red>cijfers moet een getal zijn</p>";
        }
    }
    //updaten
    if (isset($_POST['update'])) {
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        $cijfer = $_POST['cijfers']; // this is the new value
    
        if (is_numeric($cijfer)) {
            $stmt = $conn->prepare("UPDATE cijfers SET cijfer = ? WHERE user_id = ? AND vak = ?");
            $stmt->execute([$cijfer, $user_id, $vak]);
    
            echo "<p style='color:green;'>Cijfer is bijgewerkt.</p>";
        } else {
            echo "<p style='color:red;'>Cijfer moet een getal zijn.</p>";
        }
    }
    
    //verwijderen
    if (isset($_POST['delete'])) {
        $vak = $_POST['vak'];
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM cijfers WHERE user_id =? AND vak = ?");
        $stmt->execute([$user_id, $vak]);
    }
    //toon bestande cijfers 
    $result = $conn->query("SELECT users.name,
     cijfers.vak, cijfers.cijfer,cijfers.user_id
      FROM cijfers 
      JOIN users ON cijfers.user_id = 
      users.id ORDER BY users.name");
     echo "<h2>Alle cijfers</h2>";
     echo "<table border='1'>
     <tr><th>Naam</th><th>Vak</th><th>Cijfers</th></tr>";
     while ($row = $result->fetch(PDO::FETCH_ASSOC)){
        echo "<tr>
        <td>{$row['name']}</td>
        <td>{$row['vak']}</td>
        <td>{$row['cijfer']}</td>
        </tr>";
        
     }
    echo "</table>";
  // ✅ Nieuwe gebruiker + cijfer toevoegen
if (isset($_POST['nieuwe_student'])) {
    $student_name = $_POST['student_naam'];
    $vak = $_POST['vak'];
    $cijfer = $_POST['cijfers'];

    if (!empty($student_name) && is_numeric($cijfer)) {
        // Stap 1: Voeg student toe
        $stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
        $stmt->execute([$student_name]);

        // Stap 2: Haal het ID van de nieuwe student op
        $user_id = $conn->lastInsertId();

        // Stap 3: Voeg het cijfer toe
        $stmt = $conn->prepare("INSERT INTO cijfers (user_id, vak, cijfer) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $vak, $cijfer]);

        echo "<p style='color:green;'>Nieuwe student '$student_name' toegevoegd met cijfer $cijfer voor $vak.</p>";
    } else {
        echo "<p style='color:red;'>Vul alle velden correct in. Cijfer moet een getal zijn.</p>";
    }
}

    ?>
    <form action="index3.php" method="post">
        <label>student id</label>
        <input type="number" name="user_id" required><br>
        <label>vak</label>
        <input type="text" name="vak" required><br>
        <label>cijfers</label>
        <input type="number" name="cijfers" required><br>
        <button type="submit" name="toevoegen">toevoegen</button>
        <button type="submit" name="update">update</button>
        <button type="submit" name="delete">verwijderen</button>
    </form>
    <h2>Nieuwe student toevoegen met eerste cijfer</h2>
<form method="post">
    <label>Student naam:</label>
    <input type="text" name="student_naam" required><br>

    <label>Vak:</label>
    <input type="text" name="vak" required><br>

    <label>Cijfer:</label>
    <input type="number" step="0.01" name="cijfers" required><br>

    <button type="submit" name="nieuwe_student">Toevoegen</button>
</form>

</body>

</html>