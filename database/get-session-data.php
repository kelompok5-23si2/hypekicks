<?php
// filepath: c:\xampp\htdocs\HypeKicks\new-files-here\database\get-session-data.php
session_start();

// Initialize response array
$response = [
  'email' => null,
  'notification_type' => null,
  'notification_message' => null,
  'user_id' => null,
  'is_logged_in' => false
];

// Add email from session if available
if (isset($_SESSION['email'])) {
  $response['email'] = $_SESSION['email'];
}

// Add notification information if available
if (isset($_SESSION['notification_type']) && isset($_SESSION['notification_message'])) {
  $response['notification_type'] = $_SESSION['notification_type'];
  $response['notification_message'] = $_SESSION['notification_message'];
  
  // Clear notification from session once retrieved
  unset($_SESSION['notification_type']);
  unset($_SESSION['notification_message']);
}

// Add user ID if available
if (isset($_SESSION['user_id'])) {
  $response['user_id'] = $_SESSION['user_id'];
  $response['is_logged_in'] = true;
}

// Set JSON content type header
header('Content-Type: application/json');

// Return session data
echo json_encode($response);
exit;
?>