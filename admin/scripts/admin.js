/**
 * HypeKicks Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize admin functionality
  initAdminPanel();
});

/**
 * Initialize admin panel functionality
 */
function initAdminPanel() {
  // Navigation
  setupNavigation();
  
  // Toggle sidebar
  setupSidebarToggle();
  
  // Load initial content
  loadInitialContent();
}

/**
 * Setup sidebar navigation
 */
function setupNavigation() {
  const navItems = document.querySelectorAll('.nav-item');
  
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      // Update active state
      navItems.forEach(nav => nav.classList.remove('active'));
      this.classList.add('active');
      
      // Update page title
      const pageName = this.dataset.page;
      document.getElementById('page-title').textContent = pageName.charAt(0).toUpperCase() + pageName.slice(1);
      
      // Load the corresponding content
      loadContent(pageName);
    });
  });
}

/**
 * Setup sidebar toggle functionality
 */
function setupSidebarToggle() {
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.querySelector('.admin-sidebar');
  
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('expanded', true);
    });
  }
  
  // Handle responsive layout
  handleResponsiveLayout();
  window.addEventListener('resize', handleResponsiveLayout);
}

/**
 * Handle responsive layout changes
 */
function handleResponsiveLayout() {
  const windowWidth = window.innerWidth;
  const body = document.body;
  
  if (windowWidth < 992) {
    body.classList.add('sidebar-collapsed');
  } else {
    body.classList.remove('sidebar-collapsed');
  }
}

/**
 * Load initial content based on URL or default to dashboard
 */
function loadInitialContent() {
  // Get page from URL query parameter
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('page') || 'dashboard';
  
  // Set active navigation
  const navItems = document.querySelectorAll('.nav-item');
  navItems.forEach(nav => nav.classList.remove('active'));
  
  const activeNav = document.querySelector(`.nav-item[data-page="${page}"]`);
  if (activeNav) {
    activeNav.classList.add('active');
  }
  
  // Update page title
  document.getElementById('page-title').textContent = page.charAt(0).toUpperCase() + page.slice(1);
  
  // Load content
  loadContent(page);
}

/**
 * Load content into the dynamic content area
 * 
 * @param {string} page - The page to load
 */
function loadContent(page) {
  const contentArea = document.getElementById('dynamic-content');
  
  // Show loading
  contentArea.innerHTML = `
    <div class="loading-indicator visible">
      <div class="loading-dots-container">
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
      </div>
    </div>
  `;
  
  // Update URL without reloading
  const url = new URL(window.location.href);
  url.searchParams.set('page', page);
  window.history.pushState({}, '', url);
  
  // Fetch and load content
  fetch(`components/${page}.php`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Page not found');
      }
      return response.text();
    })
    .then(html => {
      contentArea.innerHTML = html;
      // Call initPageScripts after content is loaded
      initPageScripts(page);
    })
    .catch(error => {
      console.error('Error loading content:', error);
      contentArea.innerHTML = `
        <div class="error-container">
          <h2>Content Not Available</h2>
          <p>The requested page could not be loaded. Please try again or contact the administrator.</p>
          <p class="error-details">${error.message}</p>
        </div>
      `;
    });
}

/**
 * @param {number} amount - Amount to format
 * @return {string} Formatted currency string
 */
function formatCurrency(amount) {
  return 'Rp ' + parseFloat(amount).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * @param {string|Date} date - Date to format
 * @return {string} Formatted date string
 */
function formatDate(date) {
  if (!(date instanceof Date)) {
    date = new Date(date);
  }
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
}

// Page-specific functions

function initPageScripts(page) {
  switch (page) {
    case 'dashboard':
      initChartData();
      break;
    case 'orders':
      initOrdersPage();
      break;
    case 'products':
      initProductsPage();
      break;
    case 'customers':
      initCustomersPage();
      break;
    case 'reports':
      initChartData();
      break;
    default:
      break;
  }
}

function initChartData() {
  // Remove debug logs and simplify
  setTimeout(() => {
    getChartData()
      .then(data => {
        window.dashboardChartData = data;
        
        if (document.getElementById('salesChart') && 
            document.getElementById('categoryChart')) {
          initChartsDirectly(data);
        }
        else {
          initDashboardPage();
        }
      })
      .catch(error => {
        console.error('Failed to load chart data:', error);
      });    
  }, 200);
}

function getChartData() {
  return fetch('../database/api/get-chart-data.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to fetch chart data: ' + response.status);
      }
      return response.json();
    })
    .catch(error => {
      console.error('Error in getChartData:', error);
      // Return default data to prevent errors
      return {
        monthly_sales: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        category_distribution: [
          { category: 'Running', count: 5 },
          { category: 'Basketball', count: 3 },
          { category: 'Casual', count: 2 }
        ]
      };
    });
}

function initChartsDirectly(chartData) {
  const salesCtx = document.getElementById('salesChart');
  const categoryCtx = document.getElementById('categoryChart');
  
  if (!salesCtx || !categoryCtx) {
    console.error('Chart canvases not found!');
    return;
  }
  
  // Double check Chart.js is available
  if (typeof Chart === 'undefined') {
    console.error('Chart.js library not loaded!');
    return;
  }
  
  createCharts(chartData, salesCtx, categoryCtx);
}

// Separate function to create charts
function createCharts(chartData, salesCtx, categoryCtx) {
  // Sales Chart
  const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [{
        label: 'Sales',
        data: chartData.weekly_sales || [],
        backgroundColor: 'rgba(255, 107, 0, 0.1)',
        borderColor: 'rgba(255, 107, 0, 1)',
        borderWidth: 2,
        tension: 0.4,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
  
  // Category Chart
  new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
      labels: (chartData.category_distribution || []).map(item => item.category),
      datasets: [{
        data: (chartData.category_distribution || []).map(item => item.count),
        backgroundColor: [
          'rgba(255, 107, 0, 0.8)',
          'rgba(54, 162, 235, 0.8)',
          'rgba(255, 206, 86, 0.8)',
          'rgba(75, 192, 192, 0.8)',
          'rgba(153, 102, 255, 0.8)'
        ],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
  
  // Add event listeners for chart period filters
  const chartFilters = document.querySelectorAll('.chart-filter');
  if (chartFilters.length) {
    chartFilters.forEach(filter => {
      filter.addEventListener('click', function() {
        chartFilters.forEach(f => f.classList.remove('active'));
        this.classList.add('active');
        
        const period = this.dataset.period;
        if (period === 'weekly') {
          salesChart.data.labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
          salesChart.data.datasets[0].data = chartData.weekly_sales || [];
        } else if (period === 'monthly') {
          salesChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
          salesChart.data.datasets[0].data = chartData.monthly_sales || [];
        } else if (period === 'yearly') {
          salesChart.data.labels = chartData.yearly_labels || ['2021', '2022', '2023', '2024', '2025'];
          salesChart.data.datasets[0].data = chartData.yearly_sales || [];
        }
        
        salesChart.update();
      });
    });
  }
}

function initProductsPage() {
  setTimeout(() => {
    
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const addProductBtn = document.getElementById('add-product-btn');
    
    // Modal functionality
    const productModal = document.getElementById('product-modal');
    const deleteModal = document.getElementById('delete-modal');

    if (paginationLinks && addProductBtn && productModal && deleteModal) {
      
      // Pagination functionality
      paginationLinks.forEach(link => {
          link.addEventListener('click', function(e) {
              e.preventDefault();
              const page = this.dataset.page;
              
              let url = 'index.php?page=products&p=' + page;
              
              
              window.location.href = url;
          });
      });
      
      
      // Open add product modal
      addProductBtn.addEventListener('click', function() {
        document.getElementById('modal-title').textContent = 'Add New Product';
        document.getElementById('product-id').value = '';
        document.getElementById('product-form').reset();
        document.getElementById('image-preview').src = '../assets/icon/image-fill.png';
        
        showModal(productModal);
      });
      
      // Close modals with X button
      document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
          closeModals();
        });
      });
      
      // Close modals with cancel button
      document.getElementById('cancel-form-btn').addEventListener('click', function() {
        closeModals();
      });
      
      document.getElementById('cancel-delete-btn').addEventListener('click', function() {
        closeModals();
      });
      
      // Image upload preview
      document.getElementById('product-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
          };
          reader.readAsDataURL(file);
          document.getElementById('image-changed').value = 1;
        }
      });
      
      // Remove image button
      document.getElementById('remove-image-btn').addEventListener('click', function() {
          document.getElementById('product-image').value = '';
          document.getElementById('image-preview').src = '../assets/icon/image-fill.png';
          document.getElementById('image-changed').value = 1;
      });
      
      // Edit product functionality
      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const productId = this.dataset.id;
          
          document.getElementById('modal-title').textContent = 'Edit Product';
          document.getElementById('product-id').value = productId;
          
          // Show modal while loading data
          showModal(productModal);
          
          fetch(`../database/api/admin-get-product.php?id=${productId}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const product = data.product;
                document.getElementById('product-name').value = product.name;
                document.getElementById('product-category').value = product.category;
                document.getElementById('product-price').value = parseInt(product.price) || product.price;
                document.getElementById('product-stock').value = product.stock;
                document.getElementById('product-description').value = product.description;
                document.getElementById('product-brand').value = product.brand;
                
                if (product.image_url  && product.image_url !== '../assets/product_image/placeholder.png') {
                    document.getElementById('image-preview').src = product.image_url;
                } else {
                    document.getElementById('image-preview').src = '../assets/icon/image-fill.png';
                }
              } else {
                showNotif('error', 'Failed to load product data');
                closeModals();
              }
            })
            .catch(error => {
              console.error('Error loading product:', error);
              showNotif('error', 'Failed to load product data');
              closeModals();
            });
        });
      });

      document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const productId = this.dataset.id;
          
          document.getElementById('modal-title').textContent = 'Product Information';
          document.getElementById('product-id').value = productId;
          
          // Show modal while loading data
          showModal(productModal);
          
          fetch(`../database/api/admin-get-product.php?id=${productId}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const product = data.product;
                document.getElementById('product-name').value = product.name;
                document.getElementById('product-category').value = product.category;
                document.getElementById('product-price').value = parseInt(product.price) || product.price;
                document.getElementById('product-stock').value = product.stock;
                document.getElementById('product-description').value = product.description;
                document.getElementById('product-brand').value = product.brand;

                document.getElementById('product-name').disabled = true;
                document.getElementById('product-category').disabled = true;
                document.getElementById('product-price').disabled = true;
                document.getElementById('product-stock').disabled = true;
                document.getElementById('product-description').disabled = true;
                document.querySelector('.image-upload-actions').style.display = 'none';
                document.getElementById('submit-form-btn').style.display = 'none';
                document.getElementById('cancel-form-btn').innerHTML = 'Close';
                
                if (product.image_url  && product.image_url !== '../assets/product_image/placeholder.png') {
                    document.getElementById('image-preview').src = product.image_url;
                } else {
                    document.getElementById('image-preview').src = '../assets/icon/image-fill.png';
                }
              } else {
                showNotif('error', 'Failed to load product data');
                closeModals();
              }
            })
            .catch(error => {
              console.error('Error loading product:', error);
              showNotif('error', 'Failed to load product data');
              closeModals();
            });
        });
      });
      
      // Delete product functionality
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const productId = this.dataset.id;
          document.getElementById('confirm-delete-btn').dataset.id = productId;
          showModal(deleteModal);
        });
      });
      
      document.getElementById('confirm-delete-btn').addEventListener('click', function() {
          const productId = this.dataset.id;
          
          fetch('../database/api/admin-delete-product.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `product_id=${productId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showNotif('success', 'Product deleted successfully');
              
              // Remove the product row from the table
              const productRow = document.querySelector(`tr[data-id="${productId}"]`);
              if (productRow) {
                productRow.remove();
              }
              
              // Reload the page after a delay
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            } else {
              showNotif('error', data.message || 'Failed to delete product');
            }
          })
          .catch(error => {
              console.error('Error deleting product:', error);
              showNotif('error', 'Failed to delete product');
          })
          .finally(() => {
              closeModals();
          });
      });
      
      // Form submission
      document.getElementById('product-form').addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const productId = document.getElementById('product-id').value;
          
          // Determine adding or updating a product
          const isUpdate = productId !== '';
          const endpoint = isUpdate ? '../database/api/admin-update-product.php' : '../database/api/admin-add-product.php';
          
          fetch(endpoint, {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
            console.log(data);
              if (data.success) {
                  showNotif('success', isUpdate ? 'Product updated successfully' : 'Product added successfully');
                  
                  // Reload the page after a delay
                  setTimeout(() => {
                      window.location.reload();
                  }, 1000);
              } else {
                  showNotif('error', data.message || 'Failed to save product');
              }
          })
          .catch(error => {
              console.error('Error saving product:', error);
              showNotif('error', 'Failed to save product');
          })
          .finally(() => {
              closeModals();
          });
      });
      
      // Helper functions
      function showModal(modal) {
          modal.classList.add('show');
          document.body.classList.add('modal-open');
      }
      
      function closeModals() {
          document.querySelectorAll('.modal').forEach(modal => {
              modal.classList.remove('show');
          });
          document.body.classList.remove('modal-open');
          document.getElementById('product-name').disabled = false;
          document.getElementById('product-category').disabled = false;
          document.getElementById('product-price').disabled = false;
          document.getElementById('product-stock').disabled = false;
          document.getElementById('product-brand').disabled = false;
          document.getElementById('product-description').disabled = false;
          document.querySelector('.image-upload-actions').style.display = 'flex';
          document.getElementById('submit-form-btn').style.display = 'block';
          document.getElementById('cancel-form-btn').innerHTML = 'Cancel';
          document.getElementById('product-image').value = '';
          document.getElementById('image-changed').value = 0;
      }
      
      // Close modal when clicking outside
      window.addEventListener('click', function(e) {
          if (e.target.classList.contains('modal')) {
              closeModals();
          }
      });
    }
    else {
      initProductsPage();
      return;
    }
  }, 200);
}

function initCustomersPage() {
  setTimeout(() => {    
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const customerModal = document.getElementById('customer-modal');
    const emailModal = document.getElementById('email-modal');
    const statusModal = document.getElementById('status-modal');
    const emailButtons = document.querySelectorAll('.email-btn');
    const viewButtons = document.querySelectorAll('.view-btn');
    const blockButtons = document.querySelectorAll('.block-btn');
    const emailForm = document.getElementById('email-form');

    if (paginationLinks && customerModal && emailModal && statusModal && emailButtons && viewButtons && blockButtons && emailForm) {
      paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const page = this.dataset.page;
          
          let url = 'index.php?page=customers&p=' + page;
          
          window.location.href = url;
        });
      });
      
      // View customer details
      viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          const customerId = this.dataset.id;
          
          // Show modal with loading indicator
          showModal(customerModal);
          
          // Fetch customer details
          fetch(`../database/api/admin-get-customer.php?id=${customerId}`)
            .then(response => {
              if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
              }
              return response.text().then(text => {
                try {
                  // Try to parse as JSON
                  return JSON.parse(text);
                } catch(e) {
                  // If parsing fails, throw with the response text
                  console.error("Invalid JSON response:", text);
                  throw new Error('Invalid response format from server');
                }
              });
            })
            .then(data => {
                if (data.success) {
                    displayCustomerDetails(data.customer);
                } else {
                    document.getElementById('customer-details').innerHTML = `
                        <div class="error-message">
                            <p>${data.message || 'Failed to load customer data'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading customer:', error);
                document.getElementById('customer-details').innerHTML = `
                    <div class="error-message">
                        <p>Failed to load customer data. Please try again.</p>
                        <p class="error-details">${error.message}</p>
                    </div>
                `;
            });
        });
      });
      
      // Send email to customer
      emailButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          const customerId = this.dataset.id;
          const customerEmail = this.closest('tr').querySelector('td:nth-child(3)').textContent;
          const customerName = this.closest('tr').querySelector('td:nth-child(2) span').textContent;
          
          document.getElementById('customer-id').value = customerId;
          document.getElementById('recipient').value = `${customerName} <${customerEmail}>`;
          
          showModal(emailModal);
        });
      });
      
      // Handle email form submission
      emailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending...';
        
        fetch('../database/api/admin-send-email.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
              showNotif('success', 'Email sent successfully');
              closeModals();
          } else {
              showNotif('error', data.message || 'Failed to send email');
          }
        })
        .catch(error => {
          console.error('Error sending email:', error);
          showNotif('error', 'Failed to send email. Please try again.');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Send Email';
        });
      });
      
      // Cancel email button
      document.getElementById('cancel-email-btn').addEventListener('click', function() {
        closeModals();
      });
      
      // Deactivate account button
      blockButtons.forEach(btn => {
        btn.addEventListener('click', function() {
          const customerId = this.dataset.id;
          const customerName = this.closest('tr').querySelector('td:nth-child(2) span').textContent;
          
          document.getElementById('status-modal-title').textContent = 'Deactivate Account';
          document.getElementById('status-modal-message').textContent = `Are you sure you want to deactivate ${customerName}'s account? They will no longer be able to log in.`;
          
          const confirmBtn = document.getElementById('confirm-status-btn');
          confirmBtn.dataset.id = customerId;
          confirmBtn.dataset.action = 'deactivate';
          confirmBtn.textContent = 'Deactivate';
          confirmBtn.className = 'btn btn-danger';
          
          showModal(statusModal);
        });
      });
      
      // Activate account button
      document.querySelectorAll('.activate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const customerId = this.dataset.id;
          const customerName = this.closest('tr').querySelector('td:nth-child(2) span').textContent;
          
          document.getElementById('status-modal-title').textContent = 'Activate Account';
          document.getElementById('status-modal-message').textContent = `Are you sure you want to activate ${customerName}'s account?`;
          
          const confirmBtn = document.getElementById('confirm-status-btn');
          confirmBtn.dataset.id = customerId;
          confirmBtn.dataset.action = 'activate';
          confirmBtn.textContent = 'Activate';
          confirmBtn.className = 'btn btn-primary';
          
          showModal(statusModal);
        });
      });
      
      // Handle account status change
      document.getElementById('confirm-status-btn').addEventListener('click', function() {
        const customerId = this.dataset.id;
        const action = this.dataset.action;
        
        fetch('../database/api/admin-update-customer-status.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `customer_id=${customerId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotif('success', `Account ${action === 'activate' ? 'activated' : 'deactivated'} successfully`);
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
          } else {
              showNotif('error', data.message || `Failed to ${action} account`);
          }
        })
        .catch(error => {
            console.error('Error updating account status:', error);
            showNotif('error', `Failed to ${action} account. Please try again.`);
        })
        .finally(() => {
            closeModals();
        });
      });
      
      // Cancel status change button
      document.getElementById('cancel-status-btn').addEventListener('click', function() {
          closeModals();
      });
      
      // Close modals with X button
      document.querySelectorAll('.modal-close').forEach(closeBtn => {
          closeBtn.addEventListener('click', function() {
              closeModals();
          });
      });
      
      // Helper functions
      function showModal(modal) {
          modal.classList.add('show');
          document.body.classList.add('modal-open');
      }
      
      function closeModals() {
          document.querySelectorAll('.modal').forEach(modal => {
              modal.classList.remove('show');
          });
          document.body.classList.remove('modal-open');
      }
      
      function displayCustomerDetails(customer) {
          // Create customer details HTML
        let html = `
          <div class="customer-profile">
            <div class="customer-avatar">
                <img src="../assets/icon/user-3-line.png" alt="${customer.full_name}">
            </div>
            <div class="customer-info">
              <h3>${customer.full_name}</h3>
              <p>${customer.email}</p>
              <div class="customer-stats">
                <div class="stat">
                  <span class="stat-label">Member Since</span>
                  <span class="stat-value">${formatDate(customer.created_at)}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Status</span>
                  <span class="stat-value status-badge ${customer.active ? 'completed' : 'cancelled'}">
                      ${customer.active ? 'Active' : 'Inactive'}
                  </span>
                </div>
                <div class="stat">
                  <span class="stat-label">Orders</span>
                  <span class="stat-value">${customer.order_count}</span>
                </div>
                <div class="stat">
                  <span class="stat-label">Total Spent</span>
                  <span class="stat-value">${formatCurrency(customer.total_spent || 0)}</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="detail-section">
            <h4>Personal Information</h4>
            <div class="detail-grid">
              <div class="detail-item">
                <div class="detail-label">Full Name</div>
                <div class="detail-value">${customer.full_name}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value">${customer.email}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Birth Date</div>
                <div class="detail-value">${customer.birth_date ? formatDate(customer.birth_date) : 'Not provided'}</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Account Status</div>
                <div class="detail-value">${customer.active ? 'Active' : 'Inactive'}</div>
              </div>
            </div>
          </div>`;
            
        // Add recent orders if available
        if (customer.recent_orders && customer.recent_orders.length > 0) {
          html += `
            <div class="detail-section">
              <h4>Recent Orders</h4>
              <div class="table-responsive">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Date</th>
                      <th>Total</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>`;
                          
          customer.recent_orders.forEach(order => {
              html += `
            <tr>
              <td>#${order.order_id}</td>
              <td>${formatDate(order.created_at)}</td>
              <td>${formatCurrency(order.final_price)}</td>
              <td><span class="status-badge ${getOrderStatusClass(order.status)}">${order.status}</span></td>
            </tr>`;
          });
          
          html += `
                  </tbody>
                </table>
              </div>
            </div>`;
        }
        
        // Update the modal content
        document.getElementById('customer-details').innerHTML = html;
      }
      
      function getOrderStatusClass(status) {
        status = status.toLowerCase();

        if (status.includes('waiting')) {
            return 'waiting';
        } else if (status.includes('processing')) {
            return 'processing';
        } else if (status.includes('shipping')) {
            return 'shipping';
        } else if (status.includes('arrived')) {
            return 'arrived';
        } else if (status.includes('cancelled')) {
            return 'cancelled';
        } else {
            return '';
        }
    }
      
      // Close modal when clicking outside
      window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
          closeModals();
        }
      });
    }
    else {
      initCustomersPage();
      return;
    }
  }, 200);
}

function initOrdersPage() {
  setTimeout(() => {
    const paginationLinks = document.querySelectorAll('.pagination-link');
    const orderDetails = document.getElementById('order-details');
    
    // Modal functionality
    const orderModal = document.getElementById('order-modal');
    const statusModal = document.getElementById('status-modal');
    const statusBadge = document.querySelectorAll('.status-badge');

    if (paginationLinks && orderDetails && orderModal && statusModal && statusBadge) {

      statusBadge.forEach(badge => {
        const status = badge.textContent.trim().toLowerCase();
        badge.classList.add(getOrderStatusClass(status));
      });
      
      // Pagination functionality
      paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const page = this.dataset.page;
          
          let url = 'index.php?page=orders&p=' + page;
          
          const status = document.getElementById('status-filter').value;
          const dateRange = document.getElementById('date-range-filter').value;
          const search = orderSearch.value;
          
          if (status) url += `&status=${encodeURIComponent(status)}`;
          if (dateRange) url += `&date_range=${encodeURIComponent(dateRange)}`;
          if (search) url += `&search=${encodeURIComponent(search)}`;
          
          window.location.href = url;
        });
      });      
      
      // View order details
      document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.id;
            
            // Show modal with loading indicator
            showModal(orderModal);
            
            // Fetch order details
            fetch(`../database/api/admin-get-order.php?id=${orderId}`)
              .then(response => {
                if (!response.ok) {
                  throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                }
                return response.text().then(text => {
                  try {
                    // Try to parse as JSON
                    return JSON.parse(text);
                  } catch(e) {
                    // If parsing fails, throw with the response text
                    console.error("Invalid JSON response:", text);
                    throw new Error('Invalid response format from server');
                  }
                });
              })
              .then(data => {
                if (data.success) {
                  displayOrderDetails(data.order);
                } else {
                    orderDetails.innerHTML = `
                      <div class="error-message">
                          <p>${data.message || 'Failed to load order data'}</p>
                      </div>
                  `;
                }
              })
              .catch(error => {
                console.error('Error loading order:', error);
                orderDetails.innerHTML = `
                    <div class="error-message">
                        <p>Failed to load order data. Please try again.</p>
                        <p class="error-details">${error.message}</p>
                    </div>
                `;
              });
        });
      });
      
      // Edit order status
      document.querySelectorAll('.edit-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              const orderId = this.dataset.id;
              const currentStatus = this.closest('tr').querySelector('.status-badge').textContent.trim();
              
              document.getElementById('edit-order-id').value = orderId;
              document.getElementById('order-status').value = currentStatus;
              document.getElementById('status-notes').value = '';
              
              showModal(statusModal);
          });
      });
      
      // Handle status form submission
      document.getElementById('update-status-form').addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const submitBtn = this.querySelector('button[type="submit"]');
          
          submitBtn.disabled = true;
          submitBtn.innerHTML = 'Updating...';
          
          fetch('../database/api/admin-update-order-status.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  showNotif('success', 'Order status updated successfully');
                  
                  // Update the status in the table
                  const orderId = document.getElementById('edit-order-id').value;
                  const newStatus = document.getElementById('order-status').value;
                  const statusBadge = document.querySelector(`tr[data-id="${orderId}"] .status-badge`);
                  
                  if (statusBadge) {
                      statusBadge.textContent = newStatus;
                      statusBadge.className = `status-badge ${getOrderStatusClass(newStatus)}`;
                  }
                  
                  closeModals();
              } else {
                  showNotif('error', data.message || 'Failed to update order status');
              }
          })
          .catch(error => {
              console.error('Error updating order status:', error);
              showNotif('error', 'Failed to update order status. Please try again.');
          })
          .finally(() => {
              submitBtn.disabled = false;
              submitBtn.innerHTML = 'Update Status';
          });
      });
      
      // Cancel status update
      document.getElementById('cancel-status-btn').addEventListener('click', function() {
          closeModals();
      });
      
      // Close modals with X button
      document.querySelectorAll('.modal-close').forEach(closeBtn => {
          closeBtn.addEventListener('click', function() {
              closeModals();
          });
      });
      
      // Helper functions
      function showModal(modal) {
          modal.classList.add('show');
          document.body.classList.add('modal-open');
      }
      
      function closeModals() {
          document.querySelectorAll('.modal').forEach(modal => {
              modal.classList.remove('show');
          });
          document.body.classList.remove('modal-open');
      }
      
      function displayOrderDetails(order) {
          // Create order details HTML
          let html = `
              <div class="order-summary">
                  <div class="order-header">
                      <h3>Order #${order.order_id}</h3>
                      <span class="status-badge ${getOrderStatusClass(order.status)}">${order.status}</span>
                  </div>
                  
                  <div class="order-info-grid">
                      <div class="order-info-item">
                          <span class="info-label">Date:</span>
                          <span class="info-value">${formatDate(order.created_at)}</span>
                      </div>
                      <div class="order-info-item">
                          <span class="info-label">Customer:</span>
                          <span class="info-value">${order.customer_name}</span>
                      </div>
                      <div class="order-info-item">
                          <span class="info-label">Email:</span>
                          <span class="info-value">${order.email}</span>
                      </div>
                      <div class="order-info-item">
                          <span class="info-label">Phone:</span>
                          <span class="info-value">${order.phone_number}</span>
                      </div>
                  </div>
              </div>
              
              <div class="order-sections">                  
                  <div class="order-section">
                      <h4>Payment Information</h4>
                      <p><strong>Method:</strong> ${order.payment_method || 'Standard Payment'}</p>
                      <p><strong>Status:</strong> ${order.payment_status || 'Paid'}</p>
                      <p><strong>Transaction ID:</strong> ${order.transaction_id || 'N/A'}</p>
                  </div>
              </div>
              
              <div class="order-items">
                  <h4>Order Items</h4>
                  <div class="table-responsive">
                      <table class="data-table">
                          <thead>
                              <tr>
                                  <th>Product</th>
                                  <th>Price</th>
                                  <th>Quantity</th>
                                  <th>Total</th>
                              </tr>
                          </thead>
                          <tbody>`;
          
          order.items.forEach(item => {
              html += `
                  <tr>
                      <td>
                          <div class="product-cell">
                              <img src="${item.image_url}" alt="${item.name}" class="product-thumbnail">
                              <span>${item.name}</span>
                          </div>
                      </td>
                      <td>${formatCurrency(item.price)}</td>
                      <td>${item.quantity}</td>
                      <td>${formatCurrency(parseFloat(item.price) * parseInt(item.quantity))}</td>
                  </tr>`;
          });
          
          html += `
                          </tbody>
                      </table>
                  </div>
              </div>
              
              <div class="order-totals">
                  <div class="totals-table">
                      <div class="total-row">
                          <span>Subtotal:</span>
                          <span>${formatCurrency(order.total_order)}</span>
                      </div>
                      <div class="total-row">
                          <span>Shipping:</span>
                          <span>${order.delivery ? formatCurrency(order.delivery) : 'Free'}</span>
                      </div>
                      <div class="total-row">
                          <span>Tax:</span>
                          <span>${formatCurrency(order.tax)}</span>
                      </div>
                      <div class="total-row">
                          <span>Discount:</span>
                          <span>-${formatCurrency(order.discount || 0)}</span>
                      </div>
                      <div class="total-row grand-total">
                          <span>Total:</span>
                          <span>${formatCurrency(order.final_price)}</span>
                      </div>
                  </div>
              </div>
              
              <div class="order-actions-container">
                  <div class="order-status-history">
                      <h4>Status History</h4>
                      <ul class="status-timeline">
                          ${order.status_history ? renderStatusHistory(order.status_history) : '<li>No status history available</li>'}
                      </ul>
                  </div>
                  
                  <div class="order-action-buttons">
                      <button class="btn btn-primary update-status-btn" data-id="${order.order_id}">
                          Update Status
                      </button>
                      <button class="btn btn-outline close-status-btn">
                          Close
                      </button>
                  </div>
              </div>`;
          
          // Update the modal content
          orderDetails.innerHTML = html;
          
          // Add event listeners to the new buttons AFTER the HTML has been inserted
          const updateStatusBtn = document.querySelector('.update-status-btn');
          if (updateStatusBtn) {
              updateStatusBtn.addEventListener('click', function() {
                  closeModals();
                  const btnId = this.dataset.id;
                  const editBtn = document.querySelector(`.edit-btn[data-id="${btnId}"]`);
                  if (editBtn) {
                      editBtn.click();
                  }
              });
          }

          const closeStatusBtn = document.querySelector('.close-status-btn');
          if (closeStatusBtn) {
              closeStatusBtn.addEventListener('click', function() {
                  closeModals();
              });
          }
      }
      
      function renderStatusHistory(history) {
          if (!history || history.length === 0) return '<li>No status updates recorded</li>';
          
          let html = '';
          history.forEach(item => {
              html += `
                  <li class="status-item">
                      <div class="status-marker ${getOrderStatusClass(item.status)}"></div>
                      <div class="status-content">
                          <div class="status-header">
                              <span class="status-name">${item.status}</span>
                              &nbsp;
                              <span class="status-date">${formatDate(item.date)}</span>
                          </div>
                          ${item.notes ? `<p class="status-notes">${item.notes}</p>` : ''}
                      </div>
                  </li>`;
          });
          
          return html;
      }
      
      function getOrderStatusClass(status) {
          status = status.toLowerCase();

          if (status.includes('waiting')) {
              return 'waiting';
          } else if (status.includes('processing')) {
              return 'processing';
          } else if (status.includes('shipping')) {
              return 'shipping';
          } else if (status.includes('arrived')) {
              return 'arrived';
          } else if (status.includes('cancelled')) {
              return 'cancelled';
          } else {
              return '';
          }
      }
      
      // Close modal when clicking outside
      window.addEventListener('click', function(e) {
          if (e.target.classList.contains('modal')) {
              closeModals();
          }
      });

    }
    else {
      initOrdersPage();
    }
  }, 200);
}