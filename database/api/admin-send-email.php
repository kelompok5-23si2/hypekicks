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

// Get email data from request
$customerId = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate data
if ($customerId <= 0 || empty($subject) || empty($message)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
    exit();
}

// Send email
$success = sendEmailToCustomer($customerId, $subject, $message);

if ($success) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}
