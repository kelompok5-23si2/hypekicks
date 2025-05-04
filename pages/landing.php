<?php
session_start();
require_once '../database/auth/session-check.php';
if (isLoggedIn()) {
  header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="../assets/logo/logo.png" type="image/x-icon">
<title>HypeKicks - Welcome</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../styles/theme.css">
<link rel="stylesheet" href="../styles/base/reset.css">
<link rel="stylesheet" href="../styles/base/typography.css">
<link rel="stylesheet" href="../styles/base/utilities.css">
<link rel="stylesheet" href="../styles/base/layout.css">

<link rel="stylesheet" href="../styles/components/buttons.css">
<link rel="stylesheet" href="../styles/components/loading.css">
<link rel="stylesheet" href="../styles/components/notification.css">

<link rel="stylesheet" href="../styles/layouts/header.css">
<link rel="stylesheet" href="../styles/layouts/footer.css">

<link rel="stylesheet" href="../styles/pages/home-page.css">
</head>
<body>
  
<?php include 'includes/header.php'; ?>

<main id="content-container">
  <section class="loading-indicator">
    <div class="loading-dots-container">
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
    </div>
  </section>
</main>


<?php include 'includes/footer.php';?>


<div class="notification-container"></div>
<div class="notification-templates">
    <template id="notif-success-template">
      <div class="notification success">
        <div class="notification-icon">
          <img src="../assets/icon/checkbox-circle-line.png" alt="Success">
        </div>
        <div class="notification-content">
          <span class="notification-text"></span>
        </div>
        <div class="notification-close">
          <img src="../assets/icon/close-line.png" alt="Close">
        </div>
        <div class="notification-progress-bar"></div>
      </div>
    </template>
    <template id="notif-error-template">
      <div class="notification error">
          <div class="notification-icon">
              <img src="../assets/icon/close-circle-line.png" alt="Error">
          </div>
          <div class="notification-content">
              <span class="notification-text"></span>
          </div>
          <div class="notification-close">
              <img src="../assets/icon/close-line.png" alt="Close">
          </div>
          <div class="notification-progress-bar"></div>
      </div>
  </template>
  <template id="notif-warning-template">
      <div class="notification warning">
          <div class="notification-icon">
              <img src="../assets/icon/error-warning-line.png" alt="Warning">
          </div>
          <div class="notification-content">
              <span class="notification-text"></span>
          </div>
          <div class="notification-close">
              <img src="../assets/icon/close-line.png" alt="Close">
          </div>
          <div class="notification-progress-bar"></div>
      </div>
  </template>
  <template id="notif-info-template">
      <div class="notification info">
          <div class="notification-icon">
              <img src="../assets/icon/information-line.png" alt="Info">
          </div>
          <div class="notification-content">
              <span class="notification-text"></span>
          </div>
          <div class="notification-close">
              <img src="../assets/icon/close-line.png" alt="Close">
          </div>
          <div class="notification-progress-bar"></div>
      </div>
  </template>
</div>

<script src="../scripts/core/notification.js"></script>
<script src="../scripts/landing-page.js"></script>
</body>
</html>
