<?php
function isAdminLoggedIn() {
    // Check if session variables exist
    if (!isset($_SESSION['admin_logged_in']) || 
        !isset($_SESSION['admin_id']) || 
        !isset($_SESSION['admin_role'])) {
        return false;
    }
    
    // Check if logged in flag is true
    if ($_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    return true;
}

/**
 * Get the name of the currently logged in admin
 * 
 * @return string Admin name or empty string if not logged in
 */
function getAdminName() {
    return isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
}