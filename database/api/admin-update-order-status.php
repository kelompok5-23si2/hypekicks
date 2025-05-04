<?php
session_start();
require_once '../auth/admin-check.php';
require_once '../admin/order-manager.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get order data from request
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate data
if ($orderId <= 0 || empty($status)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please provide all required information']);
    exit();
}

// Update order status
$success = updateOrderStatus($orderId, $status, $notes);

if ($success) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
}
