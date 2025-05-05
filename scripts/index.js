/**
 * Main application entry point
 */
document.addEventListener('DOMContentLoaded', () => {
  // Initialize page navigation
  const contentContainer = document.getElementById('dynamic-content');
  const navigator = new PageNavigator(contentContainer, 'home-page');
  
  // Check for notifications in session data
  checkSessionNotifications();
});

/**
 * Check for notifications that might be stored in the session
 */
async function checkSessionNotifications() {
  try {
    const response = await fetch('../database/get-session-data.php');
    const sessionData = await response.json();
    
    // Show notification if present in session
    if (sessionData.notification_type && sessionData.notification_message) {
      showNotif(sessionData.notification_type, sessionData.notification_message);
    }
    
    // Populate fields with session data if available
    if (sessionData.email) {
      const emailInputs = document.querySelectorAll('input[name="email"]');
      emailInputs.forEach(input => {
        input.value = sessionData.email;
      });
    }
  } catch (error) {
    console.error('Error fetching session data:', error);
  }
}

/**
 * Handle global application events
 */
document.addEventListener('click', (event) => {
  // Close dropdowns when clicking outside
  if (!event.target.closest('.dropdown')) {
    document.querySelectorAll('.dropdown.show').forEach(dropdown => {
      dropdown.classList.remove('show');
    });
  }
});
