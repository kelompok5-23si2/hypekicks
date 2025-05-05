/**
 * Notification system for user feedback
 */
class NotificationSystem {
  constructor() {
    this.container = document.querySelector('.notification-container');
    this.isAccepting = true;
    this.isClosing = false;
    this.timeout = null;
    
    // Set up event delegation for close buttons
    document.addEventListener('click', (event) => {
      if (event.target.closest('.notification-close')) {
        this.close();
      }
    });
  }
  
  /**
   * Show a notification
   */
  show(type, message) {
    // Don't accept new notifications while one is being processed
    if (!this.isAccepting) return;
    this.isAccepting = false;
    
    // Get the template for this notification type
    const template = document.getElementById(`notif-${type}-template`);
    if (!template) {
      console.error(`Notification template for type "${type}" not found`);
      this.isAccepting = true;
      return;
    }
    
    // Clone the template content
    const content = template.content.cloneNode(true);
    
    // Update the message text
    const messageElement = content.querySelector('.notification-text');
    messageElement.textContent = message;
    
    // Add to the container
    this.container.appendChild(content);
    
    // Show the notification
    this.container.style.display = 'block';
    setTimeout(() => {
      this.container.classList.add('show');
    }, 10); // Small delay to ensure the display change takes effect first
    
    // Don't set auto-close if one is already closing
    if (this.isClosing) return;
    
    // Auto-close after delay
    this.timeout = setTimeout(() => {
      this.close();
    }, 3500);
  }
  
  /**
   * Close the notification
   */
  close() {
    // Don't try to close if already closing
    if (this.isClosing) return;
    this.isClosing = true;
    
    // Remove the show class to trigger animation
    this.container.classList.remove('show');
    
    // Wait for animation to complete
    setTimeout(() => {
      this.container.style.display = 'none';
      this.container.innerHTML = '';
      this.isAccepting = true;
      this.isClosing = false;
      clearTimeout(this.timeout);
    }, 500);
  }
}

// Create global instance
const Notification = new NotificationSystem();

// For backward compatibility
function showNotif(type, message) {
  Notification.show(type, message);
}

function closeNotif() {
  Notification.close();
}
