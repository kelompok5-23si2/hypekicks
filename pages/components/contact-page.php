<?php

session_start();
$name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

?>

<div class="page-container contact-page">
  <header class="page-header">
    <h1>Contact Us</h1>
    <p class="page-tagline">We're Here to Help</p>
  </header>
  
  <div class="contact-container">
    <section class="contact-form-section">
      <h2>Send Us a Message</h2>
      <form class="contact-form" id="contact-form">
        <div class="form-group">
          <label for="name">Your Name</label>
          <input type="text" id="name" name="name" required class="form-input disabled" value="<?php echo $name ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required class="form-input disabled" value="<?php echo $email ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required class="form-input">
        </div>
        
        <div class="form-group">
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="5" required class="form-input"></textarea>
        </div>
        
        <button style="width: 100%;" type="submit" class="btn btn-primary" id="send-message-btn">Send Message</button>
      </form>
    </section>
  </div>
  
  <section class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <div class="accordion">
      <div class="accordion-item">
        <button class="accordion-header">How can I track my order?</button>
        <div class="accordion-content">
          <p>Once your order ships, you'll receive a shipping confirmation email with tracking information. You can also track your order by logging into your account and viewing your order history.</p>
        </div>
      </div>
      
      <div class="accordion-item">
        <button class="accordion-header">What is your return policy?</button>
        <div class="accordion-content">
          <p>We offer a 45-day return policy for unworn shoes in their original packaging. Please visit our Returns page for more detailed information on the return process.</p>
        </div>
      </div>
    </div>
  </section>
</div>
