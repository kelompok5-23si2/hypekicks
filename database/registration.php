<?php
session_start();
require_once 'auth/config.php';
require_once 'auth/authenticate.php';
require_once 'auth/send-activation.php';
require_once 'auth/session-manager.php';

/**
 * Set a notification in the session
 * 
 * @param string $type Notification type (success, error, info, warning)
 * @param string $message The notification message
 * @return void
 */
function setNotification($type, $message) {
  $_SESSION['notification_type'] = $type;
  $_SESSION['notification_message'] = $message;
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Handle logout request
if (isset($_POST['logout'])) {
  destroySession();
  setNotification('success', 'You have been successfully logged out.');
  header("Location: ../pages/landing.php");
  exit();
}

// Handle sign in requests
if (isset($_POST['signin'])) {
  $email = strtolower(sanitize_input($_POST['email']));
  $password = $_POST['password'];
  
  // Authenticate user
  $result = authenticateUser($email, $password);
  
  if ($result === false) {
    // Authentication failed
    setNotification('error', 'Invalid email or password.');
    header("Location: ../pages/getstarted.html");
    exit();
  } elseif (isset($result['activated']) && $result['activated'] === false) {
    // Account not activated
    $_SESSION['email'] = $email;
    setNotification('warning', 'Please activate your account. Check your email for the activation link.');
    header("Location: ../pages/waiting.html");
    exit();
  } elseif (isset($result['authenticated']) && $result['authenticated'] === true) {
    // Authentication successful, create user session
    createUserSession($result['cust_id'], $email, $result['full_name']);
    
    // Set success notification
    setNotification('success', 'Login successful! Welcome back, ' . $result['full_name'] . '.');
    
    header("Location: ../pages/index.php");
    exit();
  } else {
    // Unexpected result, redirect with generic error
    setNotification('error', 'An error occurred during login.');
    header("Location: ../pages/getstarted.html");
    exit();
  }
}

// Handle sign up requests
if (isset($_POST['signup'])) {
  $full_name = ucwords(strtolower(sanitize_input($_POST['full_name'])));
  $email = strtolower(sanitize_input($_POST['email']));
  $password = $_POST['password'];
  $birthDate = isset($_POST['birth_date']) ? sanitize_input($_POST['birth_date']) : '';
  
  // Create new user account
  $result = createUser($full_name, $email, $password, $birthDate);
  
  if ($result === false) {
    // User creation failed
    setNotification('error', 'Registration failed. Please try again.');
    header("Location: ../pages/getstarted.html?signup=1");
    exit();
  } elseif (isset($result['exists']) && $result['exists'] === true) {
    if ($result['active'] === true) {
      // Account already exists and is active
      setNotification('info', 'This email is already registered. Try login instead.');
      header("Location: ../pages/getstarted.html");
      exit();
    } else {
      // Account exists but not activated
      $_SESSION['email'] = $email;
      setNotification('warning', 'An account with this email already exists but is not activated. Please check your email or request a new activation link.');
      header("Location: ../pages/waiting.html");
      exit();
    }
  } elseif (isset($result['created']) && $result['created'] === true) {
    // Account created successfully
    $custId = $result['cust_id'];
    
    // Generate activation token
    $token = createActivationToken($custId);
    
    if (!$token) {
      setNotification('error', 'Failed to generate activation token. Please try again.');
      header("Location: ../pages/getstarted.html?signup=1");
      exit();
    }
    
    // Send activation email
    $emailSent = sendActivationEmail($email, $full_name, $token);
    
    if ($emailSent) {
      // Store email in session for resending activation if needed
      $_SESSION['email'] = $email;
      
      setNotification('success', 'Registration successful! Please check your email to activate your account.');
      header("Location: ../pages/waiting.html");
      exit();
    } else {
      setNotification('error', 'Failed to send activation email. Please try again.');
      header("Location: ../pages/getstarted.html?signup=1");
      exit();
    }
  } else {
    // Unexpected result, redirect with generic error
    setNotification('error', 'An error occurred during registration.');
    header("Location: ../pages/getstarted.html?signup=1");
    exit();
  }
}

// If no action was processed, redirect to home page
header("Location: ../pages/index.php");
exit();
?>
