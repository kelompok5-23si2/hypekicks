<?php
session_start();
require_once 'auth/config.php';
require_once 'auth/authenticate.php';
require_once 'auth/send-activation.php';

function setNotification($type, $message) {
  $_SESSION['notification_type'] = $type;
  $_SESSION['notification_message'] = $message;
}

// Check if email is in session
if (!isset($_SESSION['email'])) {
  setNotification('warning', 'Email address is missing. Please try signing up again.');
  header('Location: ../pages/getstarted.html');
  exit();
}

$email = $_SESSION['email'];

// Get user info from database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT cust_id, full_name, active FROM customer WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  // No user found
  setNotification('error', 'Account not found. Please sign up first.');
  header('Location: ../pages/getstarted.html?signup=1');
  exit();
}

$user = $result->fetch_assoc();

// Check if already activated
if ($user['active'] == 1) {
  setNotification('info', 'Your account is already activated. Please log in.');
  header('Location: ../pages/getstarted.html');
  exit();
}

// Generate new activation token
$token = createActivationToken($user['cust_id']);

if (!$token) {
  setNotification('error', 'Could not generate activation token. Please try again later.');
  header('Location: ../pages/waiting.html');
  exit();
}

// Send activation email
$emailSent = sendActivationEmail($email, $user['full_name'], $token);

if ($emailSent) {
  setNotification('success', 'A new activation link has been sent to your email.');
  header('Location: ../pages/waiting.html');
} else {
  setNotification('error', 'Failed to send activation email. Please try again later.');
  header('Location: ../pages/waiting.html');
}

$stmt->close();
$conn->close();
?>
