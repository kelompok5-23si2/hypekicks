<?php
session_start();
require_once '../auth/admin-check.php';
require_once '../admin/customer-manager.php';

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

// Get customer ID and action from request
$customerId = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($customerId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit();
}

// Determine active status based on action
$active = 0;
if ($action === 'activate') {
    $active = 1;
} elseif ($action !== 'deactivate') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Update customer status
$success = updateCustomerStatus($customerId, $active);

if ($success) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $active ? 'Customer activated successfully' : 'Customer deactivated successfully'
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update customer status']);
}
