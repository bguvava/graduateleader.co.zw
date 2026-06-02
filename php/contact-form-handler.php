<?php
/* ========================================
   SGLD CONTACT FORM HANDLER
   Handles General, Enrolment, and Corporate enquiry forms
   ======================================== */

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/contact-form-errors.log');

// Log function
function logDebug($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    error_log($logMessage, 3, __DIR__ . '/contact-form-debug.log');
}

logDebug("=== Form submission started ===");

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Configuration
define('RECIPIENT_EMAIL', 'info@graduateleader.co.zw');
define('FROM_EMAIL', 'info@graduateleader.co.zw');  // Changed from noreply@ to info@ (must use existing email account)
define('FROM_NAME', 'SGLD Contact Form');

// Spam keyword patterns (case-insensitive)
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
logDebug("POST data: " . json_encode($_POST));

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

// Initialize variables
$name = $email = $phone = $subject = $message = '';
$emailSubject = $emailBody = '';

// Process based on form type
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
            $errors[] = 'Please provide a detailed message (minimum 10 characters).';
        }

        if (!empty($errors)) {
            $response['message'] = implode(' ', $errors);
            echo json_encode($response);
            exit;
        }

        // Spam check
        if (isSpam($subject . ' ' . $message, $spamKeywords)) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your message. We will be in touch soon.';
            echo json_encode($response);
            exit;
        }

        // Build email
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
                <div class='value'>{$subject}</div>
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

    case 'enrolment':
        // Get and sanitize fields
        $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
        $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
        $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
        $qualification = isset($_POST['qualification']) ? trim(strip_tags($_POST['qualification'])) : '';
        $programme = isset($_POST['programme']) ? trim(strip_tags($_POST['programme'])) : '';
        $delivery_mode = isset($_POST['delivery_mode']) ? trim(strip_tags($_POST['delivery_mode'])) : '';
        $start_date = isset($_POST['start_date']) ? trim(strip_tags($_POST['start_date'])) : '';
        $comments = isset($_POST['comments']) ? trim(strip_tags($_POST['comments'])) : '';

        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Please provide a valid name.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (empty($phone)) {
            $errors[] = 'Please provide a phone number.';
        }
        if (empty($programme)) {
            $errors[] = 'Please select a programme.';
        }

        if (!empty($errors)) {
            $response['message'] = implode(' ', $errors);
            echo json_encode($response);
            exit;
        }

        // Spam check
        if (isSpam($comments, $spamKeywords)) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your enrolment enquiry. We will be in touch soon.';
            echo json_encode($response);
            exit;
        }

        // Build email
        $emailSubject = "SGLD Enrolment Enquiry: " . $programme;
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
            <h1 style='margin: 0;'>New Programme Enrolment Enquiry</h1>
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
                <div class='value'>{$phone}</div>
            </div>
            <div class='field'>
                <div class='label'>Highest Qualification:</div>
                <div class='value'>" . ($qualification ?: 'Not provided') . "</div>
            </div>
            <div class='field'>
                <div class='label'>Programme of Interest:</div>
                <div class='value'><strong>{$programme}</strong></div>
            </div>
            <div class='field'>
                <div class='label'>Preferred Delivery Mode:</div>
                <div class='value'>" . ($delivery_mode ?: 'Not specified') . "</div>
            </div>
            <div class='field'>
                <div class='label'>Preferred Start Date:</div>
                <div class='value'>" . ($start_date ?: 'Not specified') . "</div>
            </div>
            <div class='field' style='border-bottom: none;'>
                <div class='label'>Additional Comments:</div>
                <div class='value'>" . ($comments ? nl2br(htmlspecialchars($comments)) : 'None') . "</div>
            </div>
            <div class='footer'>
                <p>This email was sent from the SGLD website enrolment form.</p>
                <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p>Website: <a href='https://graduateleader.co.zw'>graduateleader.co.zw</a></p>
            </div>
        </div>
    </div>
</body>
</html>
";
        break;

    case 'corporate':
        // Get and sanitize fields
        $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
        $organisation = isset($_POST['organisation']) ? trim(strip_tags($_POST['organisation'])) : '';
        $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
        $phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
        $team_size = isset($_POST['team_size']) ? trim(strip_tags($_POST['team_size'])) : '';
        $enquiry_type = isset($_POST['enquiry_type']) ? trim(strip_tags($_POST['enquiry_type'])) : '';
        $details = isset($_POST['details']) ? trim(strip_tags($_POST['details'])) : '';

        // Validation
        $errors = [];
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Please provide a valid name.';
        }
        if (empty($organisation)) {
            $errors[] = 'Please provide your organisation name.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        if (empty($enquiry_type)) {
            $errors[] = 'Please select an enquiry type.';
        }
        if (empty($details) || strlen($details) < 10) {
            $errors[] = 'Please provide details about your enquiry (minimum 10 characters).';
        }

        if (!empty($errors)) {
            $response['message'] = implode(' ', $errors);
            echo json_encode($response);
            exit;
        }

        // Spam check
        if (isSpam($details, $spamKeywords)) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your corporate enquiry. We will be in touch soon.';
            echo json_encode($response);
            exit;
        }

        // Build email
        $emailSubject = "SGLD Corporate Enquiry: " . $enquiry_type;
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
            <h1 style='margin: 0;'>New Corporate Enquiry</h1>
            <p style='margin: 10px 0 0;'>School of Graduate Leadership Development</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>Name:</div>
                <div class='value'>{$name}</div>
            </div>
            <div class='field'>
                <div class='label'>Organisation:</div>
                <div class='value'><strong>{$organisation}</strong></div>
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
                <div class='label'>Team Size:</div>
                <div class='value'>" . ($team_size ?: 'Not specified') . "</div>
            </div>
            <div class='field'>
                <div class='label'>Nature of Enquiry:</div>
                <div class='value'><strong>{$enquiry_type}</strong></div>
            </div>
            <div class='field' style='border-bottom: none;'>
                <div class='label'>Details:</div>
                <div class='value'>" . nl2br(htmlspecialchars($details)) . "</div>
            </div>
            <div class='footer'>
                <p>This email was sent from the SGLD website corporate enquiry form.</p>
                <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p>Website: <a href='https://graduateleader.co.zw'>graduateleader.co.zw</a></p>
            </div>
        </div>
    </div>
</body>
</html>
";
        break;

    default:
        $response['message'] = 'Invalid form type.';
        echo json_encode($response);
        exit;
}

// Email headers for HTML email
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=utf-8',
    'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

// Log email details before sending
logDebug("Preparing to send email:");
logDebug("  To: " . RECIPIENT_EMAIL);
logDebug("  Subject: {$emailSubject}");
logDebug("  From: " . FROM_EMAIL);
logDebug("  Reply-To: {$email}");
logDebug("  Headers: " . implode(" | ", $headers));

// Send email
$mail_sent = mail(RECIPIENT_EMAIL, $emailSubject, $emailBody, implode("\r\n", $headers));

// Log the result
logDebug("mail() function returned: " . ($mail_sent ? 'TRUE (success)' : 'FALSE (failed)'));
logDebug("PHP mail function exists: " . (function_exists('mail') ? 'YES' : 'NO'));

if ($mail_sent) {
    logDebug("SUCCESS: Email sent successfully");
    $response['success'] = true;
    $response['message'] = 'Thank you for contacting SGLD. We will respond within one business day.';
} else {
    logDebug("ERROR: mail() returned false - email not sent");
    $response['message'] = 'Sorry, there was an error sending your message. Please try again or contact us directly via phone or WhatsApp.';
}

logDebug("=== Form submission ended ===\n");

echo json_encode($response);
exit;
?>