document.addEventListener('DOMContentLoaded', async function() {
  // Load the signin content by default
  loadContent('signin');
  
  // Check URL parameters for redirect to signup
  const urlParams = new URLSearchParams(window.location.search);
  const signupParams = urlParams.get('signup');

  if (signupParams == 1) {
    loadContent('signup');
  }

  // Fetch session data to pre-populate fields and show notifications
  try {
    const response = await fetch('../database/get-session-data.php');
    const sessionData = await response.json();
    
    if (sessionData.email) {
      const emailInput = document.getElementById('email');
      if (emailInput) {
        emailInput.value = sessionData.email;
      }
    }

    if (sessionData.notification_type && sessionData.notification_message) {
      showNotif(sessionData.notification_type, sessionData.notification_message);
    }
  } catch (error) {
    console.error('Error fetching session data:', error);
  }
});

/**
 * Loads the specified content template into the dynamic container
 * @param {string} type - 'signin' or 'signup'
 */
function loadContent(type) {
  const container = document.getElementById('dynamic-container');
  const template = document.getElementById(`${type}-template`);
  
  // Clone the template content
  const content = template.content.cloneNode(true);
  
  // Clear and update the container
  container.innerHTML = '';
  container.appendChild(content);
  
  // Update the active button state
  document.getElementById(`choose-signin`).classList.toggle('active', type === 'signin');
  document.getElementById(`choose-signup`).classList.toggle('active', type === 'signup');
  
  // Set redirect field value to current URL
  const redirectField = document.getElementById('redirect');
  if (redirectField) {
    const currentUrl = window.location.href.split('?')[0];
    redirectField.value = currentUrl;
  }
}

/**
 * Toggle password visibility between text and password
 * @param {string} inputId - ID of the password field
 * @param {string} iconId - ID of the eye icon
 */
function togglePasswordVisibility(inputId, iconId) {
  const passwordInput = document.getElementById(inputId);
  const passwordIcon = document.getElementById(iconId);

  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    passwordIcon.src = '../assets/icon/eye-off-fill.png';
  } else {
    passwordInput.type = 'password';
    passwordIcon.src = '../assets/icon/eye-fill.png';
  }
}

/**
 * Validate the signin form
 * @returns {boolean} - True if form is valid, false otherwise
 */
function validateSignin() {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('signin-password').value;
  const loadingModal = document.querySelector('.loading-modal');

  if (!email || !password) {
    showNotif('warning', 'Please fill in all fields.');
    return false;
  }

  // Basic email validation
  if (!isValidEmail(email)) {
    showNotif('warning', 'Please enter a valid email address.');
    return false;
  }

  // If all validations pass, show loading modal
  loadingModal.classList.add('show');
  return true;
}

/**
 * Validate the signup form
 * @returns {boolean} - True if form is valid, false otherwise
 */
function validateSignup() {
  let fullname = document.getElementById('fullname').value.trim();
  let birthdate = new Date(document.getElementById('birthdate').value);
  let email = document.getElementById('email').value.trim();
  let password = document.getElementById('signup-password').value;
  let confirmPassword = document.getElementById('signup-confirm-password').value;
  const loadingModal = document.querySelector('.loading-modal');

  if (!fullname || !birthdate || !email || !password || !confirmPassword) {
    showNotif('warning', 'All fields must be filled.');
    return false;
  }

  // Age validation (at least 13 years old)
  let today = new Date();
  let minDOB = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
  let maxDOB = new Date(today.getFullYear() - 120, today.getMonth(), today.getDate());
  
  if (birthdate > minDOB) {
    showNotif('warning', 'You must be at least 13 years old to continue.');
    return false;
  }

  if (birthdate < maxDOB) {
    showNotif('warning', 'Please enter a valid birth date.');
    return false;
  }

  // Email validation
  if (!isValidEmail(email)) {
    showNotif('warning', 'Please enter a valid email address.');
    return false;
  }

  // Password validation (minimum 8 characters with at least 1 letter and 1 number)
  if (password.length < 8) {
    showNotif('warning', 'Password must be at least 8 characters long.');
    return false;
  }

  if (!/[A-Za-z]/.test(password) || !/[0-9]/.test(password)) {
    showNotif('warning', 'Password must contain at least one letter and one number.');
    return false;
  }

  // Password confirmation
  if (password !== confirmPassword) {
    showNotif('warning', 'Passwords do not match.');
    return false;
  }

  // If all validations pass, show loading modal
  loadingModal.classList.add('show');
  return true;
}

/**
 * Validate email format
 * @param {string} email - Email address to validate
 * @returns {boolean} - True if valid, false otherwise
 */
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

/**
 * Show loading modal
 */
function showLoadingModal() {
  document.querySelector('.loading-modal').classList.add('show');
}
