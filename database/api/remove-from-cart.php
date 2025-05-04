<?php
require_once '../cart.php';
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not authenticated']);
  exit();
}

$cust_id = $_SESSION['cust_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;

$success = removeFromCart($cust_id, $product_id);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
}
exit();
?>
