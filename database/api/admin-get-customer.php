<?php
session_start();
require_once '../auth/admin-check.php';
require_once '../admin/customer-manager.php';

// Ensure all output is JSON
header('Content-Type: application/json');

// Disable error display in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Function to return JSON error
function returnError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    returnError('Not authenticated');
}

// Get customer ID from request
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    returnError('Invalid customer ID');
}

try {
    // Get customer details
    $customer = getCustomerDetails($customerId);
    
    if ($customer === false) {
        returnError('Customer not found');
    }

    // Return customer details
    echo json_encode(['success' => true, 'customer' => $customer]);
    
} catch (Exception $e) {
    // Log error to server log
    error_log("Error in admin-get-customer.php: " . $e->getMessage());
    returnError($e->getMessage());
}
