<?php
try {
    $conn= new PDO("mysql:host=localhost;dbname=student","root","");
}
catch (PDOException){
    die ("er is een databasefout opgetreden");
}
?>