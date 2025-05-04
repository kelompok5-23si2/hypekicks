<?php
session_start();
require_once '../orders.php';
require_once '../cart.php';
header('Content-Type: application/json');

if (isset($_SESSION['cust_id'])) {
  $cust_id = $_SESSION['cust_id'];
} else {
  echo json_encode(array('status' => 'error', 'message' => 'User not logged in'));
  exit;
}

$order_items = getCartItems($cust_id);
if (empty($order_items)) {
  echo json_encode(array('status' => 'error', 'message' => 'Cart is empty'));
  exit;
}

$total_order = isset($_POST['total_order']) ? floatval($_POST['total_order']) : 0;
$delivery = isset($_POST['delivery']) ? floatval($_POST['delivery']) : 0;
$tax = isset($_POST['tax']) ? floatval($_POST['tax']) : 0;
$final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : 0;

$result = createOrder($cust_id, $total_order, $delivery, $tax, $final_price, $order_items);

// check is result is int or string
if (is_int($result)) {
  echo json_encode(array('status' => 'success', 'order_id' => $result));
} 
else {
  echo json_encode(array('status' => 'error', 'message' => $result));
}

exit;
?>