<?php
// SGLD Contact Form Handler
// Handles General, Enrolment, and Corporate enquiry forms
// Includes spam protection and email delivery

// Set headers for JSON response
header('Content-Type: application/json');

// Configuration
define('RECIPIENT_EMAIL', 'info@graduateleader.co.zw');
define('FROM_EMAIL', 'noreply@graduateleader.co.zw');
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

// Response helper function
function sendResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Validate required field
function validateRequired($field, $label) {
    if (empty($field)) {
        sendResponse(false, "$label is required.");
    }
}

// Validate email format
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email address.');
    }
}

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

// Honeypot check
function checkHoneypot($honeypotField) {
    if (!empty($honeypotField)) {
        // Bot filled honeypot field - reject silently
        sendResponse(true, 'Thank you for your message. We will be in touch soon.');
    }
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Get form type
$formType = isset($_POST['form_type']) ? $_POST['form_type'] : 'general';

// Get honeypot field based on form type
$honeypotFieldName = 'website';
if ($formType === 'enrolment') $honeypotFieldName = 'website2';
if ($formType === 'corporate') $honeypotFieldName = 'website3';

$honeypot = isset($_POST[$honeypotFieldName]) ? $_POST[$honeypotFieldName] : '';
checkHoneypot($honeypot);

// Process based on form type
switch ($formType) {
    case 'general':
        // Get and sanitize fields
        $name = trim(strip_tags($_POST['name'] ?? ''));
        $email = trim(strip_tags($_POST['email'] ?? ''));
        $phone = trim(strip_tags($_POST['phone'] ?? ''));
        $subject = trim(strip_tags($_POST['subject'] ?? ''));
        $message = trim(strip_tags($_POST['message'] ?? ''));

        // Validation
        validateRequired($name, 'Name');
        validateRequired($email, 'Email');
        validateRequired($subject, 'Subject');
        validateRequired($message, 'Message');
        validateEmail($email);

        // Spam check
        if (isSpam($subject . ' ' . $message, $spamKeywords)) {
            // Silently reject spam but pretend success
            sendResponse(true, 'Thank you for your message. We will be in touch soon.');
        }

        // Build email
        $emailSubject = "SGLD General Enquiry: $subject";
        $emailBody = "New General Enquiry from SGLD Website\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Phone: " . ($phone ?: 'Not provided') . "\n\n";
        $emailBody .= "Subject: $subject\n\n";
        $emailBody .= "Message:\n$message\n\n";
        $emailBody .= "---\n";
        $emailBody .= "Sent from: graduateleader.co.zw contact form\n";
        $emailBody .= "Time: " . date('Y-m-d H:i:s') . "\n";
        break;

    case 'enrolment':
        // Get and sanitize fields
        $name = trim(strip_tags($_POST['name'] ?? ''));
        $email = trim(strip_tags($_POST['email'] ?? ''));
        $phone = trim(strip_tags($_POST['phone'] ?? ''));
        $qualification = trim(strip_tags($_POST['qualification'] ?? ''));
        $programme = trim(strip_tags($_POST['programme'] ?? ''));
        $delivery_mode = trim(strip_tags($_POST['delivery_mode'] ?? ''));
        $start_date = trim(strip_tags($_POST['start_date'] ?? ''));
        $comments = trim(strip_tags($_POST['comments'] ?? ''));

        // Validation
        validateRequired($name, 'Name');
        validateRequired($email, 'Email');
        validateRequired($phone, 'Phone');
        validateRequired($programme, 'Programme');
        validateEmail($email);

        // Spam check
        if (isSpam($comments, $spamKeywords)) {
            sendResponse(true, 'Thank you for your enrolment enquiry. We will be in touch soon.');
        }

        // Build email
        $emailSubject = "SGLD Enrolment Enquiry: $programme";
        $emailBody = "New Programme Enrolment Enquiry from SGLD Website\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Phone: $phone\n";
        $emailBody .= "Highest Qualification: " . ($qualification ?: 'Not provided') . "\n";
        $emailBody .= "Programme of Interest: $programme\n";
        $emailBody .= "Preferred Delivery Mode: " . ($delivery_mode ?: 'Not specified') . "\n";
        $emailBody .= "Preferred Start Date: " . ($start_date ?: 'Not specified') . "\n\n";
        $emailBody .= "Additional Comments:\n" . ($comments ?: 'None') . "\n\n";
        $emailBody .= "---\n";
        $emailBody .= "Sent from: graduateleader.co.zw enrolment form\n";
        $emailBody .= "Time: " . date('Y-m-d H:i:s') . "\n";
        break;

    case 'corporate':
        // Get and sanitize fields
        $name = trim(strip_tags($_POST['name'] ?? ''));
        $organisation = trim(strip_tags($_POST['organisation'] ?? ''));
        $email = trim(strip_tags($_POST['email'] ?? ''));
        $phone = trim(strip_tags($_POST['phone'] ?? ''));
        $team_size = trim(strip_tags($_POST['team_size'] ?? ''));
        $enquiry_type = trim(strip_tags($_POST['enquiry_type'] ?? ''));
        $details = trim(strip_tags($_POST['details'] ?? ''));

        // Validation
        validateRequired($name, 'Name');
        validateRequired($organisation, 'Organisation');
        validateRequired($email, 'Email');
        validateRequired($enquiry_type, 'Enquiry type');
        validateRequired($details, 'Details');
        validateEmail($email);

        // Spam check
        if (isSpam($details, $spamKeywords)) {
            sendResponse(true, 'Thank you for your corporate enquiry. We will be in touch soon.');
        }

        // Build email
        $emailSubject = "SGLD Corporate Enquiry: $enquiry_type";
        $emailBody = "New Corporate Enquiry from SGLD Website\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Organisation: $organisation\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Phone: " . ($phone ?: 'Not provided') . "\n";
        $emailBody .= "Team Size: " . ($team_size ?: 'Not specified') . "\n";
        $emailBody .= "Nature of Enquiry: $enquiry_type\n\n";
        $emailBody .= "Details:\n$details\n\n";
        $emailBody .= "---\n";
        $emailBody .= "Sent from: graduateleader.co.zw corporate form\n";
        $emailBody .= "Time: " . date('Y-m-d H:i:s') . "\n";
        break;

    default:
        sendResponse(false, 'Invalid form type.');
}

// Send email
$headers = [
    'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

$mailSent = mail(RECIPIENT_EMAIL, $emailSubject, $emailBody, implode("\r\n", $headers));

if ($mailSent) {
    sendResponse(true, 'Thank you for contacting SGLD. We will respond within one business day.');
} else {
    sendResponse(false, 'Sorry, there was an error sending your message. Please try again or contact us directly.');
}
