<?php
// Session configuration and lifetime
$session_lifetime = 3600; // 1 hour

/**
 * Create a user session after successful authentication
 * 
 * @param int $cust_id User's customer ID
 * @param string $email User's email
 * @param string $full_name User's full name
 * @return void
 */
function createUserSession($cust_id, $email, $full_name) {
  global $session_lifetime;
  $expires_at = date('Y-m-d H:i:s', time() + $session_lifetime);
  
  // Set session variables
  $_SESSION['cust_id'] = $cust_id;
  $_SESSION['email'] = $email;
  $_SESSION['full_name'] = $full_name;
  $_SESSION['logged_in'] = true;
  $_SESSION['expires_at'] = $expires_at;
}

/**
 * Destroy the current user session
 * 
 * @return void
 */
function destroySession() {
  // Unset all session variables
  unset($_SESSION['cust_id']);
  unset($_SESSION['email']);
  unset($_SESSION['full_name']);
  unset($_SESSION['logged_in']);
  unset($_SESSION['expires_at']);
}
?>
