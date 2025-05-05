document.addEventListener('DOMContentLoaded', async function() {
  const contentArea = document.getElementById('content-container');
  const pageLoader = new PageLoader(contentArea);
  pageLoader.loadPage('home-page');
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

class PageLoader {
  constructor(contentContainer) {
    this.contentContainer = contentContainer;
    this.loadingIndicator = document.querySelector('.loading-indicator');
  }
  
  /**
   * Load a page by name
   * @param {string} pageName 
   * @returns {Promise}
   */
  async loadPage(pageName) {
    this.showLoading();
    
    try {
      const response = await fetch(`../pages/components/${pageName}.php`);
      
      if (!response.ok) {
        throw new Error(`Page not found: ${pageName}`);
      }
      
      this.contentContainer.innerHTML = await response.text();
      this.initPageScripts(pageName);
      
      window.scrollTo(0, 0);
      
      const event = new CustomEvent('pageLoaded', { detail: { page: pageName } });
      document.dispatchEvent(event);
      
      this.setupNavLinks();

    } catch (error) {
      console.error('Error loading page:', error);
      this.handlePageLoadError(error);
    } finally {
      this.hideLoading();
    }
  }

  setupNavLinks() {
    const navLinks = document.querySelectorAll('.nav-link-js');
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const pageName = link.getAttribute('data-page');
        if (pageName) {
          window.location.href = `index.php#${pageName}`;
        }
      });
    });
  }
  
  /**
   * Show the loading indicator
   */
  showLoading() {
    if (this.loadingIndicator) {
      this.loadingIndicator.style.display = 'flex';
    }
  }
  
  /**
   * Hide the loading indicator
   */
  hideLoading() {
    if (this.loadingIndicator) {
      this.loadingIndicator.style.display = 'none';
    }
  }
  
  /**
   * Handle page loading errors
   */
  handlePageLoadError(error) {
    this.contentContainer.innerHTML = `
      <div class="error-container">
        <h2>Page Not Found</h2>
        <p>We couldn't find the page you were looking for.</p>
        <p class="error-details">${error.message}</p>
        <button class="btn btn-primary" onclick="window.location.href='index.php'">Return to Home</button>
      </div>
    `;
  }
  
  /**
   * Initialize any scripts needed for the specific page
   * @param {string} pageName 
   */
  initPageScripts(pageName) {
    switch (pageName) {
      case 'home-page':
        this.initHomePageScripts();
        break;
    }
  }

  /**
   * Initialize scripts specific to the home page
   */
  initHomePageScripts() {
    const expandButton = document.querySelector('.expand-button');
  
    if (expandButton) {
      expandButton.addEventListener('click', function() {
        const servicePolicySection = document.querySelector('.service-policy');
        
        if (servicePolicySection) {
          const servicePolicyPosition = servicePolicySection.getBoundingClientRect().top + window.pageYOffset;
          
          window.scrollTo({
            top: servicePolicyPosition,
            behavior: 'smooth'
          });
        }
      });
    }
  }
}

