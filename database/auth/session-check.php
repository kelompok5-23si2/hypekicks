<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function isLoggedIn() {
  return isset($_SESSION['cust_id']) && $_SESSION['cust_id'] > 0;
}

function requireLogin() {
  if (!isLoggedIn()) {
    // Set notification
    $_SESSION['notification_type'] = 'info';
    $_SESSION['notification_message'] = 'Please log in to access this page.';
    
    // Redirect to login page
    header("Location: ../pages/getstarted.html");
    exit;
  }
}

function getUserName() {
  return $_SESSION['full_name'] ?? 'User';
}

?>
