<?php
include "../config/database.php";
$q = $conn->query("SELECT * FROM monitoring ORDER BY id DESC LIMIT 1");
echo json_encode($q->fetch_assoc());
?>
