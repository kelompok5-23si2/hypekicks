<?php

session_start();
require_once '../auth/config.php';
require_once '../orders.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$cust_id = $_SESSION['cust_id'];
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Status is required']);
    exit();
}

try {
    $orders = getOrdersByStatus($cust_id, $status);
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching orders: ' . $e->getMessage()]);
}

exit();