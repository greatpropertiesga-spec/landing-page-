<?php
include 'config.php';
if (empty($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
$result = $conn->query("SELECT * FROM leads ORDER BY id DESC");
while($row = $result->fetch_assoc()){
    echo $row['name'] . " - " . $row['phone'] . "<br>";
}
?>