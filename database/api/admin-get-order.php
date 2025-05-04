<?php
session_start();
require_once '../auth/admin-check.php';

// Set content type to JSON before any output
header('Content-Type: application/json');

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Prevent PHP errors from being displayed (they will be caught and returned as JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Get order ID from request
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit();
}

try {
    // Include the order-manager file inside the try block to catch any errors
    require_once '../admin/order-manager.php';
    
    // Get order details
    $order = getOrderDetails($orderId);

    if ($order === false) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit();
    }

    // Return order details
    echo json_encode(['success' => true, 'order' => $order]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
