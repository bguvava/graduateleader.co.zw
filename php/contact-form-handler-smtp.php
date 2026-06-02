<?php
/* ========================================
   SGLD CONTACT FORM HANDLER (SMTP VERSION)
   Uses PHPMailer with SMTP for reliable email delivery
   
   SETUP INSTRUCTIONS:
   1. Download PHPMailer: https://github.com/PHPMailer/PHPMailer
   2. Upload PHPMailer folder to your server (e.g., /php/PHPMailer/)
   3. Update SMTP settings below with your email account details
   4. Rename this file to contact-form-handler.php (backup the old one first)
   ======================================== */

// Load PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/contact-form-errors.log');

function logDebug($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    error_log($logMessage, 3, __DIR__ . '/contact-form-debug.log');
}

logDebug("=== Form submission started (SMTP version) ===");

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// ============================================
// SMTP CONFIGURATION - UPDATE THESE SETTINGS
// ============================================
define('SMTP_HOST', 'mail.graduateleader.co.zw');  // Your SMTP server (e.g., mail.yourdomain.com)
define('SMTP_PORT', 587);                           // Usually 587 (TLS) or 465 (SSL)
define('SMTP_SECURE', 'tls');                       // 'tls' or 'ssl'
define('SMTP_USERNAME', 'info@graduateleader.co.zw'); // Your email address
define('SMTP_PASSWORD', 'your_email_password');     // Your email password
define('RECIPIENT_EMAIL', 'info@graduateleader.co.zw');
define('FROM_EMAIL', 'info@graduateleader.co.zw');
define('FROM_NAME', 'SGLD Contact Form');

// Spam keyword patterns
$spamKeywords = [
    'SEO', 'Google ranking', 'website traffic', 'SEO audit', 'GoogleSearchIndex',
    'searchregister.org', 'wealth', 'financial', 'rich', 'dollars', 'bonus', 'cash',
    'urgent', 'hurry', 'expires', 'deadline', 'immediate', 'miracle', 'unbelievable',
    'free', 'prize', 'winner', 'revolutionary', 'password', 'verify', 'security alert',
    'activate', 'unsolicited', 'bulk', 'marketing', 'congratulations', 'selected',
    'mobile apps development', 'website development'
];

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check for spam patterns
function isSpam($text, $spamKeywords) {
    $textLower = strtolower($text);
    foreach ($spamKeywords as $keyword) {
        if (stripos($textLower, strtolower($keyword)) !== false) {
            return true;
        }
    }
    return false;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logDebug("ERROR: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Get form type
$formType = isset($_POST['form_type']) ? $_POST['form_type'] : 'general';
logDebug("Form type detected: {$formType}");

// Get honeypot field based on form type
$honeypotFieldName = 'website';
if ($formType === 'enrolment') $honeypotFieldName = 'website2';
if ($formType === 'corporate') $honeypotFieldName = 'website3';

// Honeypot check - if filled, it's a bot
if (!empty($_POST[$honeypotFieldName])) {
    logDebug("SPAM BLOCKED: Honeypot field '{$honeypotFieldName}' was filled");
    $response['success'] = true;
    $response['message'] = 'Thank you for your message. We will be in touch soon.';
    echo json_encode($response);
    exit;
}

// Process based on form type
$emailSubject = '';
$emailBody = '';
$senderEmail = '';

switch ($formType) {
    case 'general':
        // Get and sanitize fields
        $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
        $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
        $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
        $subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Please provide a valid name.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (empty($subject)) {
            $errors[] = 'Please provide a subject.';
        }
        if (empty($message) || strlen($message) < 10) {
            $errors[] = 'Please provide a message (minimum 10 characters).';
        }

        if (!empty($errors)) {
            $response['message'] = implode(' ', $errors);
            echo json_encode($response);
            exit;
        }

        // Spam check
        if (isSpam($message, $spamKeywords)) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your message. We will be in touch soon.';
            echo json_encode($response);
            exit;
        }

        $senderEmail = $email;
        $emailSubject = "SGLD General Enquiry: " . $subject;
        $emailBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f9fc; }
        .header { background: linear-gradient(135deg, #1B2A4A 0%, #F5A623 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
        .field { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #1B2A4A; margin-bottom: 5px; }
        .value { color: #4A4A4A; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px solid #F5A623; color: #7A8A99; font-size: 14px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin: 0;'>New General Enquiry</h1>
            <p style='margin: 10px 0 0;'>School of Graduate Leadership Development</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Name:</div>
                <div class='value'>{$name}</div>
            </div>
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'>{$email}</div>
            </div>
            <div class='field'>
                <div class='label'>Phone:</div>
                <div class='value'>" . ($phone ?: 'Not provided') . "</div>
            </div>
            <div class='field'>
                <div class='label'>Subject:</div>
                <div class='value'><strong>{$subject}</strong></div>
            </div>
            <div class='field' style='border-bottom: none;'>
                <div class='label'>Message:</div>
                <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
            <div class='footer'>
                <p>This email was sent from the SGLD website contact form.</p>
                <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p>Website: <a href='https://graduateleader.co.zw'>graduateleader.co.zw</a></p>
            </div>
        </div>
    </div>
</body>
</html>
";
        break;

    // Add other form types (enrolment, corporate) here with the same pattern
    // ... [truncated for brevity - copy from original handler]

    default:
        $response['message'] = 'Invalid form type.';
        echo json_encode($response);
        exit;
}

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    logDebug("Configuring PHPMailer with SMTP...");
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Optional: Enable verbose debug output (comment out in production)
    // $mail->SMTPDebug = 2;
    
    // Recipients
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress(RECIPIENT_EMAIL);
    $mail->addReplyTo($senderEmail);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $emailSubject;
    $mail->Body    = $emailBody;
    
    logDebug("Sending email via SMTP to " . RECIPIENT_EMAIL);
    $mail->send();
    
    logDebug("SUCCESS: Email sent successfully via SMTP");
    $response['success'] = true;
    $response['message'] = 'Thank you for contacting SGLD. We will respond within one business day.';
    
} catch (Exception $e) {
    logDebug("ERROR: PHPMailer exception: " . $mail->ErrorInfo);
    $response['message'] = 'Sorry, there was an error sending your message. Please try again or contact us directly via phone or WhatsApp.';
}

logDebug("=== Form submission ended ===\n");

echo json_encode($response);
exit;
?>
