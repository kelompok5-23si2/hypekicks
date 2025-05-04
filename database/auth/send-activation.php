<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

/**
 * Send activation email to user
 * 
 * @param string $email Recipient's email
 * @param string $fullName Recipient's full name
 * @param string $token Activation token
 * @return bool True if email sent successfully, false otherwise
 */
function sendActivationEmail($email, $fullName, $token) {
  global $email_host, $email_port, $email_username, $email_password, $email_from, $activation_url;
  
  // Create a new PHPMailer instance
  $mail = new PHPMailer();
  
  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = $email_host;
    $mail->SMTPAuth = true;
    $mail->Username = $email_username;
    $mail->Password = $email_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $email_port;
    
    // Recipients
    $mail->setFrom($email_username, 'HypeKicks');
    $mail->addAddress($email, $fullName);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Activate Your HypeKicks Account';
    
    // Build the email body
    $activationLink = $activation_url . '?token=' . $token;
    
    $mail->Body = '
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
          <h2>Welcome to HypeKicks!</h2>
          <p>Hello ' . htmlspecialchars($fullName) . ',</p>
          <p>Thank you for creating an account with us. To complete your registration and activate your account, please click the button below:</p>
          
          <div style="text-align: center; margin: 30px 0;">
            <a href="' . $activationLink . '" style="background-color: #ff6b00; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">Activate My Account</a>
          </div>
          
          <p>Or copy and paste the following link into your browser:</p>
          <p style="word-break: break-all;"><a href="' . $activationLink . '">' . $activationLink . '</a></p>
          
          <p>This link will expire in 1 hour for security reasons.</p>
          
          <p>If you did not create an account, you can ignore this email.</p>
        </div>
        
        <div style="padding: 20px; text-align: center; font-size: 12px; color: #777777;">
          <p>&copy; ' . date('Y') . ' HypeKicks. All rights reserved.</p>
        </div>
      </div>
    ';
    
    $mail->AltBody = "Hello " . $fullName . ",\n\n" .
                    "Thank you for creating an account with HypeKicks. To activate your account, please visit this link:\n" .
                    $activationLink . "\n\n" .
                    "This link will expire in 1 hour for security reasons.\n\n" .
                    "If you did not create an account, you can ignore this email.\n\n" .
                    "Regards,\nHypeKicks Team";
    
    return $mail->send();
    
  } catch (Exception $e) {
    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    return false;
  }
}
?>
