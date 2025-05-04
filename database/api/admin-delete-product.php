<?php
session_start();
require_once '../auth/admin-check.php';
require_once '../admin/product-manager.php';

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

// Get product ID from request
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if ($productId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

// Delete product
$success = deleteProduct($productId);

if ($success) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete product. It may be used in existing orders.'
    ]);
}
