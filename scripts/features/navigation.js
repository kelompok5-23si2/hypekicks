class PageNavigator {
  constructor(contentContainer, defaultPage) {
    this.contentContainer = contentContainer;
    this.defaultPage = defaultPage;
    this.pageCache = {};
    this.currentPage = null;
    this.isLoading = false;
    this.loadingIndicator = document.querySelector('.loading-indicator');
    this.likedProductId = [];
    this.cartCount = document.getElementById('cart-count');
    
    // Initialize the navigation
    this.init();
  }
  
  /**
   * Initialize the navigation system
   */
  init() {
    const hash = window.location.hash.substring(1);
    const initialPage = hash || this.defaultPage;
    this.loadPage(initialPage);
    this.updateActiveLink(initialPage);
    this.likedProductId = this.getLikedProductId();
    this.updateCartCount();

    this.setupNavLinks();
    this.setupHistoryHandling();
  }

  updateCartCount() {
    const url = '../database/api/get-cart-count.php';
    return fetch(url)
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          this.cartCount.textContent = data.cart_count;
          if (data.cart_count == 0) {
            this.cartCount.classList.add('hidden');
          } else {
            this.cartCount.classList.remove('hidden');
          }
        }
      })
      .catch((error) => {
        console.error('Error fetching cart count:', error);
        throw error;
      });
  }

  getLikedProductId() {
    const url = '../database/api/get-liked-product.php';
  
    return fetch(url)
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          return data.products;
        } else {
          showNotif('error', data.message);
          return [];
        }
      })
      .catch((error) => {
        console.error('Error fetching liked product IDs:', error);
        throw error;
      });
  }
  
  /**
   * Set up navigation click handlers
   */
  setupNavLinks() {
    const navLinks = document.querySelectorAll('.nav-link-js');
    
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const pageName = link.getAttribute('data-page');
        
        if (this.currentPage === pageName) {
          window.scrollTo(0, 0);
          return;
        };
        
        this.loadPage(pageName);
        this.updateActiveLink(pageName);
        
        // Update URL and browser history
        history.pushState(
          { page: pageName },
          document.title,
          `#${pageName}`
        );
      });
    });
  }
  
  /**
   * Set up browser history handling for back/forward
   */
  setupHistoryHandling() {
    window.addEventListener('popstate', (event) => {
      const pageName = event.state?.page || this.defaultPage;
      this.loadPage(pageName);
      this.updateActiveLink(pageName);
    });
  }
  
  /**
   * Update the active state of navigation links
   */
  updateActiveLink(pageName) {
    const navLinks = document.querySelectorAll('.nav-link-js');
    
    navLinks.forEach(link => {
      const linkPage = link.getAttribute('data-page');
      link.classList.toggle('active', linkPage === pageName);
    });
  }
  
  /**
   * Load a page by name
   */
  async loadPage(pageName) {
    if (this.isLoading || this.currentPage === pageName) return;
    
    this.currentPage = pageName;
    this.isLoading = true;
    this.showLoading();
    
    try {
      if (!this.pageCache[pageName]) {
        const response = await fetch(`../pages/components/${pageName}.php`);
        if (!response.ok) {
          throw new Error(`Page not found: ${pageName}`);
        }
        
        this.pageCache[pageName] = await response.text();
      }
      
      // Update DOM with page content
      this.contentContainer.innerHTML = this.pageCache[pageName];
      
      // Initialize page scripts based on page name
      this.initPageScripts(pageName);
      
      this.setupNavLinks();
      this.setupHistoryHandling();
      
      window.scrollTo(0, 0);
      document.dispatchEvent(new CustomEvent('pageLoaded', { 
        detail: { page: pageName }
      }));
      
    } catch (error) {
      console.error('Error loading page:', error);
      
      // Show error page
      this.contentContainer.innerHTML = `
        <div class="error-page text-center p-4">
          <h1 class="mb-2">Page Not Found</h1>
          <p class="mb-3">Sorry, the page "${pageName}" could not be loaded.</p>
          <button id="return-home" class="btn btn-primary">Back to Home</button>
        </div>
      `;
      
      // Add event listener for return home button
      document.getElementById('return-home').addEventListener('click', () => {
        this.loadPage(this.defaultPage);
        this.updateActiveLink(this.defaultPage);
        history.pushState(
          { page: this.defaultPage },
          document.title,
          `#${this.defaultPage}`
        );
      });
    } finally {
      this.isLoading = false;
      this.hideLoading();
    }
  }
  
  /**
   * Initialize page-specific scripts
   */
  initPageScripts(pageName) {
    switch(pageName) {
      case 'home-page':
        this.initHomePageScripts();
        break;
      case 'product-page':
        this.initProductPageScripts(true, false);
        break;
      case 'cart-page':
        this.initCartPageScripts();
        break;
      case 'wishlist-page':
        this.initProductPageScripts(false, true);
        break;
      case 'contact-page':
        this.initContactPageScripts();
        this.initAccordion();
        break;
      case 'help-page':
        this.initAccordion();
        break;
      case 'order-page':
        this.initOrderPageScripts();
        break;
    }
  }
  
  /**
   * Initialize home page scripts
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

  async initProductPageScripts(productPage, wishlistPage) {
    let search = document.getElementById('search-product-input').value;
    let category = '';
    let brand = '';
    let price = '';
    const limit = 8;
    const likedProductId = await this.likedProductId;

    if (productPage)
      initEventListener();

    showProductCards(1);
    
    function initEventListener() {
      document.querySelectorAll('.filter').forEach((filter) => {
        filter.addEventListener('change', (event) => {
          const { name, value } = event.target;
          if (name === 'search') {
            search = value;
          } else if (name === 'category') {
            category = value;
          } else if (name === 'brand') {
            brand = value;
          } else if (name === 'price') {
            price = value;
          }
          showProductCards(1);
        });
      });

      document.querySelectorAll('.reset-filter-js').forEach((btn) => {
        btn.addEventListener('click', () => {
          resetFilter();
          showProductCards(1);
        });
      }) ;

      document.getElementById('search-product-input').addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
          event.preventDefault();
          search = event.target.value;
          const hash = window.location.hash.substring(1);
          if (hash === 'product-page') {
            showProductCards(1);
          }
          else {
            document.getElementById('product-page-link-js').click();
          }
        }
      });

      document.getElementById('search-product-button').addEventListener('click', () => {
        search = document.getElementById('search-product-input').value;
        const hash = window.location.hash.substring(1);
        if (hash === 'product-page') {
          showProductCards(1);
        }
        else {
          document.getElementById('product-page-link-js').click();
        }
      });
    };
    
    async function showProductCards(page) {
      const productGrid = document.querySelector('.product-grid');
      productGrid.innerHTML = '';
      const paginationContainer = document.querySelector('.pagination');
      let pagination = '';
      paginationContainer.innerHTML = '';

      let url, params;

      if (productPage) {
        url = '../database/api/get-product.php';
        params = new URLSearchParams({
          search: search,
          category: category,
          brand: brand,
          price: price,
          limit: limit,
          page: page,
        });
      }
      else if (wishlistPage) {
        url = '../database/api/get-wishlist.php';
        params = '';
      }

      try {
        const response = await fetch(`${url}${params ? '?' + params : ''}`);
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        const data = await response.json();
        
        let products = data.products;
        if (products.length === 0) {
          let paramsStr = ``;
          if (productPage) {
            if (search) {
              paramsStr += `for "${search}"`;
            }
            if (category || brand || price) {
              paramsStr += ` with the selected filter`;
            }
          } else if (wishlistPage) {
            paramsStr = ''; // No filters for wishlist
          }

          if (wishlistPage) {
            productGrid.style.display = "block";
            productGrid.style.textAlign = "center";
            productGrid.style.marginTop = "2em";
          }
          
          productGrid.innerHTML = 
          `<div>
            <p class="no-results">No products found${paramsStr ? ' ' + paramsStr : ''}.</p>
            ${productPage ? '<button class="btn btn-primary filter-apply reset-filter-js">Reset</button>' : ''}
            </div>
          `;
          
          if (productPage) {
            document.querySelectorAll('.reset-filter-js').forEach((btn) => {
              btn.addEventListener('click', () => {
                resetFilter();
                showProductCards(1);
              });
            });
          }
          return;
        }
        
        products.forEach((product) => {
          let rating = product.rating * 10;
          rating = rating - rating % 5;
          const liked = Array.isArray(likedProductId) ? likedProductId.some((id) => id == product.id) : false;
        
          const productCard = document.createElement('div');
          productCard.classList.add('product-card');
          productCard.innerHTML = `
            <div class="product-image">
              <img src="${product.image}" alt="${product.name}">
              <button class='like-btn-js' data-id=${product.id}><img id='like-btn-img-${product.id}' src="../assets/icon/${liked || wishlistPage ? 'heart-fill-red' : 'heart-line'}.png"></button>
            </div>
            <div class="product-info">
              <h2>${product.name}</h2>
              <div class="product-rating">
                <img src="../assets/icon/star-${rating}.png">
                <span class="rating">
                  <span>${product.rating}</span>
                  <span>${product.sold ? product.sold + ' sold' : ''}</span>
                </span>
              </div>
              <span>${product.brand}, ${product.category == 'Limited' ? 'Limited Edition' : product.category + ' shoes'}.</span>
              <span>Available: ${product.stock} left.</span>
              <span class="price">${product.price}</span>
            </div>
            <button class='add-to-cart-btn' data-id=${product.id}><img src="../assets/icon/add-to-cart.png"> Add To Cart</button>
          `;
          productGrid.appendChild(productCard);
        });

        document.querySelectorAll('.like-btn-js').forEach((likeBtn) => {
          likeBtn.addEventListener('click', (event) => {
            const productId = likeBtn.getAttribute('data-id');
            const likeIcon = document.getElementById(`like-btn-img-${productId}`);
            const isLiked = likeIcon.src.includes('heart-fill-red.png');
            const newSrc = isLiked ? '../assets/icon/heart-line.png' : '../assets/icon/heart-fill-red.png';
            const url = isLiked ? '../database/api/remove-from-wishlist.php' : '../database/api/add-to-wishlist.php';
            
            // Create form data instead of URLSearchParams
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch(url, {
              method: 'POST',
              body: formData,
            })
            .then(response => response.json())
            .then((data) => {
              if (data.success) {
                showNotif('success', data.message);
                likeIcon.src = newSrc;
                
                // Reload wishlist page if on wishlist and item was removed
                if (wishlistPage && isLiked) {
                  setTimeout(() => showProductCards(1), 500);
                }
              }
              else {
                showNotif('error', data.message);
              }
            })
            .catch((error) => {
              console.error('Error updating wishlist:', error);
              showNotif('error', 'Failed to update wishlist');
            });
          });
        });

        document.querySelectorAll('.add-to-cart-btn').forEach((addToCartBtn) => {
          addToCartBtn.addEventListener('click', () => {
            const productId = addToCartBtn.getAttribute('data-id');
            const url = '../database/api/add-to-cart.php';
            
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            
            fetch(url, {
              method: 'POST',
              body: formData,
            })
            .then(response => response.json())
            .then((data) => {
              if (data.success) {
                showNotif('success', data.message);
              } else {
                showNotif('error', data.message);
              }
            })
            .catch((error) => {
              console.error('Error adding product to cart:', error);
              showNotif('error', 'Failed to add product to cart');
            });
          });
        });

        // Only show pagination for product page, not for wishlist
        if (productPage) {
          const totalPages = data.total_pages;
          const currentPage = data.current_page;
          const totalResults = data.total_results;

          if (totalPages == 1 && totalResults > 0) {
            pagination = `<p class='pages-info'>Page 1 of 1</p>`;
          }
          else if (totalPages > 1) {
            if (currentPage != 1) {
              pagination += "<button class='pagination-prev'>&larr; Prev</button>";            
            }
            // ... Rest of pagination code
            if (totalPages >= 3 && currentPage >= 3) {
              pagination += "<button class='pagination-number'>1</button>";
              if (currentPage != 3) {
                pagination += "<span class='pagination-ellipsis'>...</span>";
              }
            }
            for (let i = currentPage - 1; i <= currentPage + 1; i++) {
              if (i > 0 && i <= totalPages) {
                if (i == currentPage) {
                  pagination += `<button class='pagination-number active'>${i}</button>`;
                }
                else {
                  pagination += `<button class='pagination-number'>${i}</button>`;
                }
              }
            }
            if (totalPages >= 3 && currentPage <= totalPages - 2) {
              if (currentPage != totalPages -2) {
                pagination += `<span class='pagination-ellipsis'>...</span>`;
              }
              pagination += `<button class='pagination-number'>${totalPages}</button>`;
            }
            if (currentPage != totalPages) {
              pagination += "<button class='pagination-next'>Next &rarr;</button>";
            }
          }
          paginationContainer.innerHTML = pagination;

          document.querySelectorAll('.pagination-number').forEach((number) => {
            number.addEventListener('click', (event) => {
              const pageNumber = parseInt(event.target.innerText);
              showProductCards(pageNumber);
            });
          });
          if (currentPage != 1) {
            document.querySelector('.pagination-prev').addEventListener('click', () => {
              showProductCards(currentPage - 1);
            });
          }
          if (currentPage != totalPages) {
            document.querySelector('.pagination-next').addEventListener('click', () => {
              showProductCards(currentPage + 1);
            });
          }
        }
      } catch (error) {
        console.error('Error fetching product data:', error);
        productGrid.innerHTML = '<p class="no-results">Failed to load products. Please try again later.</p>';
      }
    }

    function resetFilter() {
      search = '';
      category = '';
      brand = '';
      price = '';
      document.querySelectorAll('.filter').forEach((filter) => {
        filter.checked = false;
      });
      document.getElementById('search-product-input').value = '';
    }
  }

  /**
   * Show loading indicator
   */
  showLoading() {
    if (this.loadingIndicator) {
      this.loadingIndicator.style.display = 'flex';
    }
  }
  
  /**
   * Hide loading indicator
   */
  hideLoading() {
    if (this.loadingIndicator) {
      this.loadingIndicator.style.display = 'none';
    }
  }

  initCartPageScripts() {
    setTimeout(() => {
      const cartContainer = document.querySelector('.cart-container');
      this.updateCartCount();
      const checkoutModal = document.getElementById("checkout-modal");
      
      if (cartContainer && checkoutModal) {
        let cartItems = getCart();
        let subtotal = 0;
        let delivery = 0;
        let tax = 0;

        cartItems.then((items) => {
          cartContainer.innerHTML = ''; 
          
          if (items && items.length > 0) {
            items.forEach((item) => {
              const cartItem = document.createElement('div');
              cartItem.classList.add('cart-item');
                cartItem.innerHTML = `
                <img src="${item.image_src}" alt="${item.product_name}">
                <div class="cart-item-info">
                  <div>
                  <h3>${item.product_name}</h3>
                  <span><b>Brand:</b> ${item.brand || 'N/A'}</span><br>
                  <span><b>Category:</b> ${item.category == 'Limited' ? 'Limited Edition' : item.category + ' shoes'}</span><br>
                  <span><b>Stock:</b> ${item.stock || '0'} left.</span><br>
                  </div>
                  <span class="price">Rp. ${Number(item.price).toLocaleString('de-DE', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span>
                </div>
                <div class="cart-buttons">
                  <img src="../assets/icon/delete-bin-6-fill.png" class="delete-button modify-cart-js" data-id="${item.product_id}" data-action="remove">
                  <div class="input-quantity">
                  <div class="operator-btn modify-cart-js" data-id="${item.product_id}" data-action="add">+</div>
                  <input type="text" name="input-quantity-${item.product_id}" id="input-quantity-${item.product_id}" value="${item.quantity}" readonly>
                  <div class="operator-btn modify-cart-js" data-id="${item.product_id}" data-action="substract">-</div>
                  </div>
                </div>
                `;
              cartContainer.appendChild(cartItem);
              subtotal += item.price * item.quantity;
              delivery += 15000 + 6000 * (item.quantity - 1);
              tax = subtotal * 0.1;
            });
          } else {
            cartContainer.innerHTML = '<p>No items in the cart.</p>';
            cartContainer.style.textAlign = 'center';
            cartContainer.style.padding = '20px';
          }
          
          document.querySelectorAll('.price-subtotal-js').forEach((btn) => {
            btn.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/\./g, ',').replace(/,/g, '.');
          });
          document.querySelectorAll('.price-tax-js').forEach((btn) => {
            btn.textContent = 'Rp ' + tax.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/\./g, ',').replace(/,/g, '.');
          });
          document.querySelectorAll('.price-delivery-js').forEach((btn) => {
            btn.textContent = 'Rp ' + delivery.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/\./g, ',').replace(/,/g, '.');
          });
          document.querySelectorAll('.price-total-js').forEach((btn) => {
            btn.textContent = 'Rp ' + (subtotal + tax + delivery).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).replace(/\./g, ',').replace(/,/g, '.');
          });
          // Set up event listeners for cart item buttons
          this.setupCartEventListeners(subtotal, delivery, tax);

        }).catch(error => {
          console.error('Error loading cart:', error);
          cartContainer.innerHTML = '<p>Failed to load cart items. Please try again later.</p>';
        });
      }
      else {
        this.initCartPageScripts();
      }      
    }, 200);

    function getCart() {
      const url = '../database/api/get-cart-items.php';
      return fetch(url)
        .then((response) => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Don't show notification on initial load
            return data.cart_items;
          } else {
            // Don't show error notification for empty cart
            console.log(data.message);
            return [];
          }
        })
        .catch((error) => {
          console.error('Error fetching cart data:', error);
          throw error;
        });
    }
  }
  
  // Add a new method to handle cart event listeners
  setupCartEventListeners(subtotal, delivery, tax) {
    // Add event listeners for remove buttons
    document.querySelectorAll('.modify-cart-js').forEach(button => {
      button.addEventListener('click', () => {
        const productId = button.getAttribute('data-id');
        let url = '';
        const dataAction = button.getAttribute('data-action');

        if (!dataAction) return;
        switch (dataAction) {
          case 'add':
            url = '../database/api/add-to-cart.php';
            break;
          case 'substract':
            url = '../database/api/substract-from-cart.php';
            break;
          case 'remove':
            url = '../database/api/remove-from-cart.php';
            break;
        }
        if (!url) return;

        const formData = new FormData();
        formData.append('product_id', productId);
        fetch(url, {
          method: 'POST',
          body: formData,
        })
        .then(response => response.json())
        .then((data) => {
          if (data.success) {
            this.initCartPageScripts();
          } else {
            showNotif('error', data.message);
          }
        })
        .catch((error) => {
          console.error('Error removing product from cart:', error);
          showNotif('error', 'Failed to remove product from cart');
        });
      });
    });
    
    const checkoutModal = document.getElementById("checkout-modal");
    const orderSummaryModal = document.getElementById("order-summary-modal");
    const paymentModal = document.getElementById("payment-modal");
    
    // Get button elements
    const checkoutButton = document.getElementById("checkout-button");
    const closeModalBtn = document.getElementById("close-modal");
    const confirmOrderButton = document.getElementById("confirm-order-button");
    const closePaymentModalBtn = document.getElementById("close-payment-modal");
    const confirmPaymentButton = document.getElementById("confirm-payment-button");
    
    checkoutButton.addEventListener("click", function() {
      const cartContainer = document.querySelector('.cart-container');
      if (cartContainer && cartContainer.innerHTML.includes('No items in the cart.')) {
        showNotif('warning', 'Please add items to your cart before checkout!');
        return;
      }
      checkoutModal.style.display = "flex";
      orderSummaryModal.style.display = "block";
    });
    
    closeModalBtn.addEventListener("click", function() {
      checkoutModal.style.display = "none";
      orderSummaryModal.style.display = "none";
    });
    
    confirmOrderButton.addEventListener("click", function() {
      orderSummaryModal.style.display = "none";
      paymentModal.style.display = "block";
    });
    
    closePaymentModalBtn.addEventListener("click", function() {
      checkoutModal.style.display = "none";
      paymentModal.style.display = "none";
    });
    
    confirmPaymentButton.addEventListener("click", function() {

      const parent = this;

      const formData = new FormData();
      const url = '../database/api/generate-order.php';

      formData.append('total_order', subtotal);
      formData.append('delivery', delivery);
      formData.append('tax', tax);
      formData.append('final_price', subtotal + delivery + tax);
      
      fetch(url, {
        method: 'POST',
        body: formData,
      })
      .then(response => response.json())
      .then((data) => {
        if (data.status == 'success') {
          showNotif('success', "Your order has been placed successfully with order ID: " + data.order_id);
          setTimeout(() => {
            checkoutModal.style.display = "none";
            paymentModal.style.display = "none";
            console.log("Resetting cart...");;
            window.location.reload();
          }, 2500);
        }
        else {
          showNotif('error', data.message);
        }
      })
      .catch((error) => {
        console.error('Error fetching product data:', error);
        throw error;
      });
    });
    
    window.addEventListener("click", function(event) {
      if (event.target == checkoutModal) {
        checkoutModal.style.display = "none";
        orderSummaryModal.style.display = "none";
        paymentModal.style.display = "none";
      }
    });
  }

  initContactPageScripts() {
    const contactForm = document.getElementById('contact-form');

    contactForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(contactForm);
      const url = 'mailto:hypekicks.indonesia@gmail.com';
      const name = contactForm.querySelector('input[name="name"]').value;
      const email = contactForm.querySelector('input[name="email"]').value;
      const subject = contactForm.querySelector('input[name="subject"]').value;
      const message = contactForm.querySelector('textarea[name="message"]').value;

      const body = `Name: ${name}\nEmail: ${email}\n\nMessage:\n${message}`;

      const mailtoLink = `${url}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
      window.location.href = mailtoLink;
    });
  }

  initAccordion() {
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
      const header = item.querySelector('.accordion-header');
      
      header.addEventListener('click', () => {
        item.classList.toggle('active');
        accordionItems.forEach(otherItem => {
          if (otherItem !== item) {
            otherItem.classList.remove('active');
          }
        })
      });
    });
  }

  initOrderPageScripts() {
    const tabs = document.querySelectorAll('.tab-button');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const status = tab.getAttribute('data-status');

        // Update active tab
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        // Show corresponding panel
        panels.forEach(panel => panel.classList.add('hidden'));
        document.getElementById(status).classList.remove('hidden');

        // Fetch and display orders
        this.fetchOrdersByStatus(status);
      });
    });

    // Load default tab
    tabs[0].click();
  }

  fetchOrdersByStatus(tabStatus) {
    const url = `../database/api/get-orders.php?status=${encodeURIComponent(tabStatus)}`;
    const panel = document.getElementById(tabStatus);
    
    // Show loading state
    panel.innerHTML = '<div class="loading">Loading orders...</div>';
    
    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.orders.length > 0) {
          panel.innerHTML = data.orders.map(order => {
            const date = new Date(order.created_at).toLocaleDateString('en-US', {
              year: 'numeric', 
              month: 'long', 
              day: 'numeric'
            });
            
            const finalPrice = parseFloat(order.final_price).toLocaleString('id-ID', { 
              style: 'currency', 
              currency: 'IDR',
              minimumFractionDigits: 0
            });

            let orderDetailsHTML = '';
            order.items.forEach(item => {
              orderDetailsHTML += `
                <div class="order-details-item">
                  <div class="order-details-item-image">
                    <img src="${item.image}" alt="${item.name}">
                  </div>
                  <div class="order-details-item-info">
                    <h4>${item.name}</h4>
                    <span class="order-details-item-price">Rp ${parseFloat(item.price).toLocaleString('id-ID')}</span>
                    <span class="order-details-item-quantity">Quantity: ${item.quantity}</span>
                  </div>
                </div>
              `;
            });

            let statusHistoryHTML = '';
            order.status_history.forEach(status => {
                statusHistoryHTML += `
                <div class="status-history-item">
                  <span class="status-history-item-date">
                    ${new Date(status.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric'})}
                    <br>
                    ${new Date(status.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit' })}
                  </span>
                  <div class="status-history-item-dot"></div>
                  <span>${status.notes}</span>
                </div>
                `;
            });
            statusHistoryHTML += `
            <div class="status-history-item">
              <span class="status-history-item-date">
                ${new Date(order.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric'})}
                <br>
                ${new Date(order.created_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit' })}
              </span>
              <div class="status-history-item-dot"></div>
              <span>Pesanan dibuat. Menunggu verifikasi pembayaran.</span>
            </div>
            `;
            
            return `
              <div class="order-item">
                <div class="order-header">
                  <div class="order-header-info">
                    <h3>Order #${order.order_id}</h3>
                    <span class="order-status status-${tabStatus}">${order.status}</span>
                  </div>
                  <span class="order-date">${date}</span>
                </div>
                <div class="order-status-history">
                  <h4>Order Progress</h4>
                  ${statusHistoryHTML}
                </div>
                <div class="order-details" id="order-details-${order.order_id}">
                  <h4>Order Details</h4>
                  ${orderDetailsHTML}
                </div>
                <div class="order-info">
                  <div class="order-info-item">
                    <span class="order-info-label">Subtotal</span>
                    <span class="order-info-value">Rp ${parseFloat(order.total_order).toLocaleString('id-ID')}</span>
                  </div>
                  <div class="order-info-item">
                    <span class="order-info-label">Delivery Fee</span>
                    <span class="order-info-value">Rp ${parseFloat(order.delivery).toLocaleString('id-ID')}</span>
                  </div>
                  <div class="order-info-item">
                    <span class="order-info-label">Tax</span>
                    <span class="order-info-value">Rp ${parseFloat(order.tax).toLocaleString('id-ID')}</span>
                  </div>
                </div>
                <div class="order-total">
                  <span class="order-total-label">Total:</span>
                  <span class="order-total-value">Rp ${parseFloat(order.final_price).toLocaleString('id-ID')}</span>
                </div>
              </div>
            `;
          }).join('');
        } else {
          panel.innerHTML = `<div class="no-orders">No ${tabStatus} orders found</div>`;
        }
      })
      .catch(error => {
        console.error('Error fetching orders:', error);
        panel.innerHTML = '<div class="no-orders">Failed to load orders. Please try again later.</div>';
      });
  }
}