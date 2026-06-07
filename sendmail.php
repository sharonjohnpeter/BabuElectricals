<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to client
ini_set('log_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// CORS headers for security
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate Indian phone number
function validate_phone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Please use POST.'
    ]);
    exit;
}

// Get and sanitize form data
$name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
$phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$service = isset($_POST['service']) ? sanitize_input($_POST['service']) : '';
$message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (!validate_phone($phone)) {
    $errors[] = 'Please enter a valid 10-digit Indian mobile number';
}

if (empty($email)) {
    $errors[] = 'Email address is required';
} elseif (!validate_email($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($service)) {
    $errors[] = 'Please select a service';
}

if (empty($message)) {
    $errors[] = 'Project details are required';
} elseif (strlen($message) < 10) {
    $errors[] = 'Please provide more details about your project (minimum 10 characters)';
}

// If validation errors exist, return them
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fix the following errors:',
        'errors' => $errors
    ]);
    exit;
}

// ========== SMTP CONFIGURATION ==========
// Replace these with your actual SMTP credentials
// For Gmail:
// - Enable 2-Step Verification in your Google Account
// - Generate an App Password (Google Account > Security > App Passwords)
// - Use the 16-character app password below

$smtp_config = [
    'host' => 'smtp.gmail.com',        // SMTP server (smtp.gmail.com for Gmail)
    'port' => 587,                      // 587 for TLS, 465 for SSL
    'auth' => true,                     // Enable authentication
    'username' => 'bhabuelectricals@gmail.com',  // Your email address
    'password' => 'YOUR_APP_PASSWORD',  // Your Gmail App Password (NOT your regular password)
    'encryption' => PHPMailer::ENCRYPTION_STARTTLS  // TLS encryption
];

// For other email providers, use these settings:
// Outlook/Hotmail:
// 'host' => 'smtp-mail.outlook.com', 'port' => 587, 'encryption' => PHPMailer::ENCRYPTION_STARTTLS
// Yahoo:
// 'host' => 'smtp.mail.yahoo.com', 'port' => 587, 'encryption' => PHPMailer::ENCRYPTION_STARTTLS
// Zoho:
// 'host' => 'smtp.zoho.com', 'port' => 587, 'encryption' => PHPMailer::ENCRYPTION_STARTTLS

// Create mailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtp_config['host'];
    $mail->SMTPAuth   = $smtp_config['auth'];
    $mail->Username   = $smtp_config['username'];
    $mail->Password   = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['encryption'];
    $mail->Port       = $smtp_config['port'];
    
    // Enable debug logging (disable in production)
    $mail->SMTPDebug = 0; // Set to 2 for debugging
    
    // Set timeout
    $mail->Timeout = 30;
    
    // Recipients - Main email to company
    $mail->setFrom($smtp_config['username'], 'Bhabu Electricals Website');
    $mail->addAddress('bhabuelectricals@gmail.com', 'Bhabu Electricals'); // Main recipient
    $mail->addAddress('sharonpeter313@gmail.com', 'Sharon Peter'); // Additional recipient
    $mail->addReplyTo($email, $name);
    
    // Optional: Add CC to another email
    // $mail->addCC('manager@bhabuelectricals.com', 'Manager');
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Enquiry from {$name} - Bhabu Electricals";
    
    // HTML Email Template
    $html_content = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>New Enquiry - Bhabu Electricals</title>
        <style>
            body {
                font-family: "DM Sans", Arial, sans-serif;
                line-height: 1.6;
                color: #1a1a2e;
                background-color: #f5f5f5;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #F26522, #2196F3);
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                color: white;
                margin: 0;
                font-size: 24px;
                font-family: "Playfair Display", serif;
            }
            .header p {
                color: rgba(255,255,255,0.9);
                margin: 10px 0 0;
            }
            .content {
                padding: 30px;
            }
            .section {
                margin-bottom: 25px;
                border-bottom: 1px solid #e0e0e0;
                padding-bottom: 15px;
            }
            .section-title {
                font-size: 16px;
                font-weight: 700;
                color: #F26522;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .section-content {
                font-size: 15px;
                color: #4a5568;
                margin: 0;
            }
            .badge {
                display: inline-block;
                background: #f0f4f8;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 14px;
                color: #2196F3;
                margin-top: 5px;
            }
            .footer {
                background: #1a4a3a;
                padding: 20px;
                text-align: center;
                color: rgba(255,255,255,0.7);
                font-size: 12px;
            }
            .footer a {
                color: #F26522;
                text-decoration: none;
            }
            .highlight {
                color: #F26522;l
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⚡ New Enquiry Received</h1>
                <p>Bhabu Electricals - Contact Form Submission</p>
            </div>
            <div class="content">
                <div class="section">
                    <div class="section-title">Customer Information</div>
                    <p class="section-content">
                        <strong>Name:</strong> ' . htmlspecialchars($name) . '<br>
                        <strong>Phone:</strong> <a href="tel:+91' . htmlspecialchars($phone) . '">+91 ' . htmlspecialchars($phone) . '</a><br>
                        <strong>Email:</strong> <a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>
                    </p>
                </div>
                
                <div class="section">
                    <div class="section-title">Service Required</div>
                    <p class="section-content">
                        <span class="badge">' . htmlspecialchars($service) . '</span>
                    </p>
                </div>
                
                <div class="section">
                    <div class="section-title">Project Details</div>
                    <p class="section-content">' . nl2br(htmlspecialchars($message)) . '</p>
                </div>
                
                <div class="section">
                    <div class="section-title">Submission Details</div>
                    <p class="section-content">
                        <strong>Date & Time:</strong> ' . date('F j, Y, g:i a') . '<br>
                        <strong>IP Address:</strong> ' . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown') . '
                    </p>
                </div>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' Bhabu Electricals. All rights reserved.</p>
                <p>This is an automated message from your website contact form. Please respond within 24 hours.</p>
            </div>
        </div>
    </body>
    </html>';
    
    $mail->Body = $html_content;
    $mail->AltBody = "New Enquiry from Bhabu Electricals Website\n\n"
        . "Customer Details:\n"
        . "Name: {$name}\n"
        . "Phone: +91 {$phone}\n"
        . "Email: {$email}\n\n"
        . "Service Required: {$service}\n\n"
        . "Project Details:\n{$message}\n\n"
        . "Submitted on: " . date('F j, Y, g:i a');
    
    // Send email
    $mail->send();
    
    // Send auto-response to customer
    $auto_mail = new PHPMailer(true);
    $auto_mail->isSMTP();
    $auto_mail->Host       = $smtp_config['host'];
    $auto_mail->SMTPAuth   = $smtp_config['auth'];
    $auto_mail->Username   = $smtp_config['username'];
    $auto_mail->Password   = $smtp_config['password'];
    $auto_mail->SMTPSecure = $smtp_config['encryption'];
    $auto_mail->Port       = $smtp_config['port'];
    
    $auto_mail->setFrom($smtp_config['username'], 'Bhabu Electricals');
    $auto_mail->addAddress($email, $name);
    $auto_mail->addReplyTo($smtp_config['username'], 'Bhabu Electricals');
    
    $auto_mail->isHTML(true);
    $auto_mail->Subject = "Thank you for contacting Bhabu Electricals";
    
    $auto_content = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Thank You - Bhabu Electricals</title>
        <style>
            body {
                font-family: "DM Sans", Arial, sans-serif;
                line-height: 1.6;
                color: #1a1a2e;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 500px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .header {
                background: linear-gradient(135deg, #F26522, #2196F3);
                padding: 30px;
                text-align: center;
            }
            .header h2 {
                color: white;
                margin: 0;
            }
            .content {
                padding: 30px;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #F26522, #2196F3);
                color: white;
                padding: 12px 25px;
                border-radius: 8px;
                text-decoration: none;
                margin-top: 20px;
            }
            .footer {
                background: #f5f5f5;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>⚡ Thank You, ' . htmlspecialchars($name) . '!</h2>
            </div>
            <div class="content">
                <p>We have received your enquiry regarding <strong>' . htmlspecialchars($service) . '</strong>.</p>
                <p>Our team will review your requirements and get back to you within <strong>one business day</strong>.</p>
                <p>For urgent inquiries, please call us at:</p>
                <p style="text-align: center; font-size: 24px; font-weight: bold; color: #F26522;">
                    <a href="tel:+919003434422" style="color: #F26522; text-decoration: none;">+91 90034 34422</a>
                </p>
                <center>
                    <a href="https://wa.me/919003434422" class="btn">📱 Chat on WhatsApp</a>
                </center>
            </div>
            <div class="footer">
                <p>Bhabu Electricals - Trusted Since 1990</p>
                <p>Kumbakonam | Chennai</p>
            </div>
        </div>
    </body>
    </html>';
    
    $auto_mail->Body = $auto_content;
    $auto_mail->AltBody = "Thank you for contacting Bhabu Electricals!\n\n"
        . "We have received your enquiry regarding {$service}.\n\n"
        . "Our team will get back to you within one business day.\n\n"
        . "For urgent inquiries, call us at +91 90034 34422\n\n"
        . "Thank you for choosing Bhabu Electricals.";
    
    $auto_mail->send();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Your enquiry has been sent successfully! We will contact you within one business day.'
    ]);
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Mailer Error: {$mail->ErrorInfo}");
    
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send your enquiry at this time. Please call us directly at +91 90034 34422.',
        'debug' => ($_SERVER['HTTP_HOST'] === 'localhost') ? $mail->ErrorInfo : null
    ]);
}
?>