<header class="main-header">
  <div class="header-container">
    <!-- Logo -->
    <a href="index.php" class="logo">
        <img src="../assets/logo/logo.png" alt="HypeKicks Logo">
    </a>
    
    <!-- Main Navigation -->
    <nav class="main-nav">
      <ul class="nav-list">
        <li class="nav-item">
            <a href="index.php#home-page" class="nav-link nav-link-js active" data-page="home-page">Home</a>
        </li>
        <li class="nav-item">
            <a href="index.php#product-page" class="nav-link nav-link-js" data-page="product-page" id="product-page-link-js">Products</a>
        </li>
        <li class="nav-item">
            <a href="index.php#cart-page" class="nav-link nav-link-js" data-page="cart-page">Cart</a>
        </li>
        <li class="nav-item">
            <a href="index.php#order-page" class="nav-link nav-link-js" data-page="order-page">My Order</a>
        </li>
        <li class="nav-item">
            <a href="index.php#wishlist-page" class="nav-link nav-link-js" data-page="wishlist-page">Wishlist</a>
        </li>
        <li class="nav-item">
            <a href="index.php#about-page" class="nav-link nav-link-js" data-page="about-page">About Us</a>
        </li>
        <li class="nav-item">
            <a href="index.php#contact-page" class="nav-link nav-link-js" data-page="contact-page">Contact</a>
        </li>
        <li class="nav-item">
            <a href="index.php#help-page" class="nav-link nav-link-js" data-page="help-page">Help</a>
        </li>
      </ul>
    </nav>
    
    <?php if (isLoggedIn()): ?>
    <!-- User Actions -->
    <div class="user-actions">
      <div class="search-container">
        <input id="search-product-input" type="text" class="search-input" placeholder="Search products...">
        <button class="search-button" id="search-product-button">
            <img src="../assets/icon/search-line.png" alt="Search">
        </button>
      </div>
      
      <!-- Cart Icon -->
      <a href="#cart-page" class="action-icon nav-link-js" data-page="cart-page">
          <img src="../assets/icon/shopping-cart-2-fill-black.png" alt="Cart">
          <span id="cart-count" class="cart-count hidden"></span>
      </a>
        <!-- User Menu (Logged in) -->
      <div class="dropdown user-menu">
        <button class="dropdown-toggle action-icon">
            <img src="../assets/icon/user-3-line.png" alt="Account">
        </button>
        <ul class="dropdown-menu">
          <li>
              <span class="user-greeting">Hello, <?php echo getUserName(); ?></span>
          </li>
          <li class="dropdown-divider"></li>
          <li>
              <form action="../database/registration.php" method="post">
                  <button type="submit" name="logout" class="btn-link">Sign Out</button>
              </form>
          </li>
        </ul>
      </div>
    </div>
    <?php else: ?>
    <div class="user-actions">
      <button class="btn btn-primary" onclick="window.location.href = 'getstarted.html'">Sign In</button>
      <button class="btn btn-primary" onclick="window.location.href = 'getstarted.html?signup=1'">Sign Up</button>
    </div>
    <?php endif; ?>
  </div>
</header>
