<?php
session_start();
require_once 'config.php';
require_once 'authenticate.php';
require_once 'session-manager.php';

function setNotification($type, $message) {
  $_SESSION['notification_type'] = $type;
  $_SESSION['notification_message'] = $message;
}

// Check if token exists in the URL
if (isset($_GET['token'])) {
  $token = $_GET['token'];
  $userId = verifyActivationToken($token);
  
  if ($userId) {
    // Fetch user info to create session
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("SELECT email, full_name FROM customer WHERE cust_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      
      // Create authenticated session
      createUserSession($userId, $user['email'], $user['full_name']);
      
      // Show success message
      setNotification('success', 'Your account has been successfully activated! Welcome to HypeKicks.');
      
      $stmt->close();
      $conn->close();
      
      // Redirect to appropriate page
      header("Location: ../../pages/index.php");
      exit();
    }
    
    $stmt->close();
    $conn->close();
  } else {
    // Token invalid or expired
    setNotification('error', 'Your activation link is invalid or has expired. Please request a new one.');
    header("Location: ../../pages/waiting.html");
    exit();
  }
} else {
  // No token provided
  setNotification('error', 'No activation token provided.');
  header("Location: ../../pages/landing.html");
  exit();
}
?>
