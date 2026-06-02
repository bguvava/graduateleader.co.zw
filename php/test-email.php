<?php
/**
 * Simple Email Test Script
 * Upload this file and access it via browser to test if mail() works
 */

// Test email address
$test_to = 'info@graduateleader.co.zw';
$test_subject = 'Test Email from SGLD Server';
$test_message = 'This is a test email sent at ' . date('Y-m-d H:i:s');
$test_headers = 'From: noreply@graduateleader.co.zw';

echo '<h1>Email Test Results</h1>';
echo '<p><strong>Testing server mail() function...</strong></p>';

// Test 1: Basic mail() test
$result1 = mail($test_to, $test_subject, $test_message, $test_headers);
echo '<p>Test 1 - Basic mail(): ' . ($result1 ? '<span style="color:green">SUCCESS</span>' : '<span style="color:red">FAILED</span>') . '</p>';

// Test 2: Check if mail() function exists
echo '<p>Test 2 - mail() function exists: ' . (function_exists('mail') ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . '</p>';

// Test 3: Check PHP mail configuration
echo '<h2>PHP Mail Configuration:</h2>';
echo '<pre>';
echo 'sendmail_path: ' . ini_get('sendmail_path') . "\n";
echo 'SMTP: ' . ini_get('SMTP') . "\n";
echo 'smtp_port: ' . ini_get('smtp_port') . "\n";
echo '</pre>';

// Test 4: Server information
echo '<h2>Server Information:</h2>';
echo '<pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'Operating System: ' . PHP_OS . "\n";
echo 'Server Software: ' . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo '</pre>';

echo '<hr>';
echo '<p><strong>Instructions:</strong></p>';
echo '<ol>';
echo '<li>Check if Test 1 shows SUCCESS</li>';
echo '<li>Check your inbox at info@graduateleader.co.zw</li>';
echo '<li>Check spam/junk folder</li>';
echo '<li>If mail() shows SUCCESS but no email arrives, the server mail is configured incorrectly</li>';
echo '<li>If mail() shows FAILED, mail() function is disabled on server</li>';
echo '</ol>';
?>
