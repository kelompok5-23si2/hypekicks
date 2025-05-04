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

try {
  $result = getCartItems($cust_id);
  
  if ($result !== false) {
    echo json_encode(['success' => true, 'message' => 'Cart items retrieved successfully', 'cart_items' => $result]);
  } else {
    echo json_encode(['success' => false, 'message' => 'No items in cart']);
  }
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error retrieving cart items: ' . $e->getMessage()]);
}

exit();
?>
