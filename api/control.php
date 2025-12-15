<?php
$data = [
  "mode" => $_POST['mode'],
  "fan"  => $_POST['fan'],
  "led"  => $_POST['led']
];
file_put_contents("control.json", json_encode($data));
echo "UPDATED";
?>
