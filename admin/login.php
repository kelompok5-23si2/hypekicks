<?php
session_start();
require_once '../database/auth/admin-auth.php';
require_once '../database/auth/admin-check.php';

// Check if admin is already logged in
if (isAdminLoggedIn()) {
  header("Location: index.php");
  exit();
}

$error = '';

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  $result = authenticateAdmin($username, $password);
  
  if ($result['authenticated']) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_id'] = $result['admin_id'];
      $_SESSION['admin_name'] = $result['admin_name'];
      $_SESSION['admin_role'] = $result['admin_role'];
      
      header("Location: index.php");
      exit();
  } else {
      $error = 'Invalid username or password';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - HypeKicks</title>
  <link rel="shortcut icon" href="../assets/logo/logo.png" type="image/x-icon">
  
  <!-- Styles -->
  <link rel="stylesheet" href="../styles/theme.css">
  <link rel="stylesheet" href="../styles/base/reset.css">
  <link rel="stylesheet" href="../styles/base/typography.css">
  <link rel="stylesheet" href="../styles/components/forms.css">
  <link rel="stylesheet" href="../styles/components/buttons.css">
  <link rel="stylesheet" href="styles/admin-login.css">
</head>
<body>
  <div class="admin-login-container">
    <div class="login-form-container">
      <div class="login-logo">
        <img src="../assets/logo/logo.png" alt="HypeKicks Logo">
        <h1>Admin Panel</h1>
      </div>
      
      <?php if ($error && $error != ""): ?>
        <div class="login-error">
          <?php 
            echo $error;
            $error = '';
          ?>
        </div>
      <?php endif; ?>
      
      <form method="post" class="admin-login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-input" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-input-container">
            <input type="password" id="password" name="password" class="form-input" required>
            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
              <img src="../assets/icon/eye-fill.png" alt="Show">
            </button>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Login</button>
      </form>
      
      <div class="login-footer">
          <a href="../pages/index.php">Back to Website</a>
      </div>
    </div>
  </div>
  
  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const toggleBtn = document.querySelector('.toggle-password img');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.src = '../assets/icon/eye-off-fill.png';
        toggleBtn.alt = 'Hide';
      } else {
        passwordInput.type = 'password';
        toggleBtn.src = '../assets/icon/eye-fill.png';
        toggleBtn.alt = 'Show';
      }
    }
  </script>
</body>
</html>
