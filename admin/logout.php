<?php
session_start();

// Clear admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Redirect to login page
header("Location: login.php");
exit();
