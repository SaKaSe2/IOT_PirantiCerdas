<?php
include "../config/database.php";

$suhu = $_POST['suhu'];
$hum  = $_POST['kelembaban'];
$kipas = $_POST['kipas'];
$led = $_POST['led'];
$mode = $_POST['mode'];

$conn->query("INSERT INTO monitoring 
(suhu, kelembaban, kipas, led, mode) 
VALUES ('$suhu','$hum','$kipas','$led','$mode')");
echo "OK";
?>
