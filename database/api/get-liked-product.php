<?php
session_start();
require_once '../customers.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'products' => []]);
    exit();
}

$cust_id = $_SESSION['cust_id'];

// Get product IDs
$likedProductIds = getCustomerLikedProducts($cust_id);

// If no products are liked, return an empty array
if ($likedProductIds === null) {
  echo json_encode(['success' => true, 'products' => []]);
}
else {
  echo json_encode(['success' => true, 'products' => $likedProductIds]);
}

exit();
?>