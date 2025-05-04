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

// Get product ID from request
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

// Get product details
$product = getProductDetails($productId);

if ($product === false) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Return product details
header('Content-Type: application/json');
echo json_encode(['success' => true, 'product' => $product]);
