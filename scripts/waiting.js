document.addEventListener('DOMContentLoaded', async function() {
  try {
    // Fetch user email from session
    const response = await fetch('../database/get-session-data.php');
    const sessionData = await response.json();
    
    if (sessionData.email) {
      document.getElementById('user-email').textContent = sessionData.email;
    }
    
    // Check for notifications in session
    if (sessionData.notification_type && sessionData.notification_message) {
      showNotif(sessionData.notification_type, sessionData.notification_message);
    }
  } catch (error) {
    console.error('Error fetching session data:', error);
  }

  setFirstTimer();
});

function setFirstTimer() {
  const startTime = localStorage.getItem('start_time');
  const now = Math.floor(Date.now() / 1000);
  let timeLeft;

  if (startTime) {
    const elapsedTime = now - startTime;
    timeLeft = 60 - elapsedTime;
    startResendTimer(timeLeft);
  }
  else 
    startResendTimer(60);
}

function startResendTimer(timeLeft) {
  const resendContainer = document.getElementById('resend-container');
  const originalContent = resendContainer.innerHTML;

  const timerElement = document.createElement('span');
  timerElement.className = 'resend-timer';
  timerElement.innerHTML = `please wait ${timeLeft} seconds before trying again`;
  resendContainer.innerHTML = '';
  resendContainer.appendChild(timerElement);

  if (timeLeft == 60) {
    localStorage.setItem('start_time', Math.floor(Date.now() / 1000));
  }

  const timerInterval = setInterval(() => {
    timeLeft--;
    
    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      resendContainer.innerHTML = originalContent;
      localStorage.removeItem('start_time');
    } else {
      timerElement.innerHTML = `please wait ${timeLeft} seconds before trying again`;
    }
  }, 1000);
}

function showLoadingModal() {
  document.querySelector('.loading-modal').classList.add('show');
}