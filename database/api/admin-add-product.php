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

// Get product data from request
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$brand = isset($_POST['brand']) ? trim($_POST['brand']) : '';

// Validate data
if (empty($name) || empty($description) || empty($category) || $price <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

// Check if image was uploaded
$imageData = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Validate image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Only JPEG, PNG, GIF, and WEBP are allowed']);
        exit();
    }
    
    if ($_FILES['image']['size'] > $maxSize) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Image size exceeds the 5MB limit']);
        exit();
    }
    
    // Read image data
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
}

// Prepare product data
$productData = [
    'name' => $name,
    'description' => $description,
    'category' => $category,
    'price' => $price,
    'stock' => $stock,
    'brand' => $brand,
];

// Add product
$productId = addProduct($productData, $imageData);

if ($productId !== false) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Product added successfully',
        'product_id' => $productId
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to add product']);
}
