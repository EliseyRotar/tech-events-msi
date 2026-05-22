<?php
/**
 * Contact form endpoint — accepts POST with JSON body.
 * Validates, stores to log, and attempts email via PHP mail().
 */

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$name    = trim($body['name']    ?? '');
$email   = trim($body['email']   ?? '');
$org     = trim($body['organization'] ?? '');
$role    = trim($body['role']    ?? '');
$message = trim($body['message'] ?? '');

// Validate
if ($name === '' || $email === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Name, email, and message are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Store message in log file (writable in Docker and local setups)
$logDir  = sys_get_temp_dir() . '/techdragon_contacts';
$logFile = $logDir . '/contacts.json';

if (!is_dir($logDir)) {
    @mkdir($logDir, 0700, true);
}

$entry = [
    'id'           => uniqid('msg_', true),
    'timestamp'    => date('c'),
    'name'         => $name,
    'email'        => $email,
    'organization' => $org,
    'role'         => $role,
    'message'      => $message,
    'ip'           => $_SERVER['REMOTE_ADDR'] ?? '',
];

$existing = [];
if (is_file($logFile)) {
    $raw = file_get_contents($logFile);
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $existing = $decoded;
}

$existing[] = $entry;
file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

// Attempt email delivery (works when PHP mail() / sendmail is configured)
require_once __DIR__ . '/../config.php';

$contactEmail = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : (getenv('CONTACT_EMAIL') ?: '');

if ($contactEmail !== '') {
    $subject = "[Tech Dragons] New contact from {$name}";
    $body = <<<TXT
New contact form submission — Tech Dragons Events
=================================================
Name         : {$name}
Email        : {$email}
Organisation : {$org}
Role         : {$role}
Timestamp    : {$entry['timestamp']}

Message:
{$message}
TXT;
    $headers  = "From: noreply@techdragonevents.com\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($contactEmail, $subject, $body, $headers);

    // Auto-reply to sender
    $replySubject = "We received your message — Tech Dragons Events";
    $replyBody = <<<TXT
Hi {$name},

Thank you for reaching out to Tech Dragons Events.

We've received your message and will get back to you within 24 hours.

— The Tech Dragons Events Team
TXT;
    $replyHeaders  = "From: noreply@techdragonevents.com\r\n";
    $replyHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    @mail($email, $replySubject, $replyBody, $replyHeaders);
}

echo json_encode(['success' => true, 'message' => 'Message received.']);
