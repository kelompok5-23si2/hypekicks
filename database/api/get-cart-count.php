<?php
// Ensure this runs before any output
header('Content-Type: application/json');
session_start();

// Prevent PHP errors from being output directly (would break JSON)
ini_set('display_errors', 0);

require_once '../cart.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not authenticated']);
  exit();
}

$cust_id = $_SESSION['cust_id'];

$cartCount = getCartCount($cust_id);
if ($cartCount !== false) {
  echo json_encode(['success' => true, 'cart_count' => $cartCount]);
} else {
  echo json_encode(['success' => false, 'message' => 'Error retrieving cart count']);
}

exit();
?>