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
    if(isset($_POST['toevoegen'])){
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        $cijfer = $_POST['cijfer'];
        if(is_numeric($cijfer)){
            $stmt = $conn -> prepare ("INSERT INTO cijfers (user_id,vak,cijfer) VALUES (?,?,?)");
            $stmt->execute([$user_id,$vak,$cijfer]);
            echo "Cijfer toegevoegd";
        } else {
            echo "cijfer moet een getal zijn";
        }
    }

    //update
    if(isset($_POST['update'])){
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        $cijfer = $_POST['cijfer'];
        if(is_numeric($cijfer)){
            $stmt = $conn -> prepare ("UPDATE cijfers SET cijfer = ? WHERE user_id =? AND vak =?");
            $stmt -> execute ([$cijfer,$user_id,$vak]);
            echo "Cijfer toegevoegd";
        } else {
            echo "cijfer moet een getal zijn";
        }

    }

    //delete
    if(isset($_POST['delete'])){
        $user_id = $_POST['user_id'];
        $vak = $_POST['vak'];
        if(is_numeric($user_id)){
            $stmt = $conn -> prepare ("DELETE FROM cijfers WHERE user_id =? AND vak =?");
            $stmt -> execute ([$user_id,$vak]);
            echo "cijfer verwijderd";
        } else {
            echo "student id moet een getal zijn";
        }
    }
    $result = $conn -> query ("SELECT users.name , cijfers.vak, cijfers.cijfer , cijfers.user_id FROM cijfers JOIN users ON cijfers.user_id = users.id ORDER BY users.id");
    echo "<h2>Alle cijfers </h2>";
    echo "<table border = '1' >
    <tr><th>naam</th><th>Vak</th><th>Cijfers</th></tr>";
    While ($row = $result -> fetch(PDO::FETCH_ASSOC)){
        echo "<tr><td>{$row['name']}</td><td>{$row['vak']}</td><td>{$row['cijfer']}</td></tr>";
    } 
    echo "</table>";

    //gemiddlede cijfer 
    $sql = "SELECT users.id, users.name, cijfers.vak , cijfers.cijfer
    FROM users LEFT JOIN cijfers ON users.id = cijfers.user_id
    ORDER BY users.name";
    $studenten = [];
    foreach ($conn -> query ($sql) as $rij){
        $id = $rij['id'];
    if(!isset($studenten[$id])){
        $studenten[$id] = [
            'naam' =>$rij['name'],
            'vakken' =>[],
            'total' =>0,
            'aantaal' => 0
        ];
    }
    if(!empty($rij['vak'])&& is_numeric($rij['cijfer'])){
        $studenten[$id]['vakken'][]="{$rij['vak']}:{$rij['cijfer']}";
        $studenten[$id]['total']+=$rij['cijfer'];
        $studenten[$id]['aantaal']++;

    }
}
    
    ?>
    <table border = '1' >
        <tr><th>Naam</th><th>vakken + cijfers</th><th>gemiddlede</th></tr>
        <?php foreach($studenten as $student):?>
            <tr><td><?=$student['naam']?></td>
        <td><?=implode(',',$student['vakken'])?></td>
    <td><?=$student['aantaal']>0? number_format($student['total']/$student['aantaal'],2):'_'?>
</td>
</td>
<?php endforeach; ?>






    </table>
    <br>
    <form action="index6.php" method="post">
    <label>student id</label>
    <input name="user_id" type="number">
    <label>vak</label>
    <input name="vak" type="text">
    <label>cijfers</label>
    <input name="cijfer" type="number">
    <button type="submit" name="toevoegen">Toevoegen</button>
    <button type="submit" name="delete">verwijderen</button>
    <button type="submit" name="update">Update</button>



    </form>
</body>
</html>