<?php
session_start();
require_once '../customers.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$cust_id = $_SESSION['cust_id'];

// Get product ID from request
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($productId <= 0) {
  header('Content-Type: application/json');
  echo json_encode(['success' => false, 'message' => 'Invalid product ID:' . $productId]);
  exit();
}

// Add product to wishlist
$result = addToLiked($cust_id, $productId);

if ($result) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Product added to wishlist']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to add product to wishlist']);
}
?>
