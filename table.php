<?php
require 'config.php';

$sql = "SELECT * FROM history ORDER BY id DESC LIMIT 15";
$result = $db->query($sql);
if (!$result) {
  echo json_encode(array('error' => 'Error: ' . $sql . "<br>" . $db->error));
  exit;
}

$row = mysqli_fetch_assoc($result);
$data = array(
    'id' => $row['id'],
    'timeAndDate' => date("h:i A, Y-m-d", strtotime($row['timeAndDate'])),
    'temperature' => $row['temperature'],
    'humidity' => $row['humidity'],
    'soilMoistureLevel' => $row['soilMoistureLevel']
);

echo json_encode($data);

$result->close();
$db->close();
?>
