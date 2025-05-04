<?php
require_once '../../database/auth/config.php';
session_start();
header('Content-Type: application/json');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT image FROM product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();

$result = $stmt->get_result();
$image = $result->fetch_assoc()['image'];
$stmt->close();
$conn->close();

if ($image) {
  header('Content-Type: image/jpeg');
  echo $image;
} else {
  http_response_code(404);
  echo json_encode(['error' => 'Image not found']);
}

?>