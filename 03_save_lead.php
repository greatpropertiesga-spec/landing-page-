<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo 'Method not allowed'; exit;
}

$address = trim($_POST['address'] ?? '');
$name    = trim($_POST['name']    ?? '');
$phone   = trim($_POST['phone']   ?? '');
$email   = trim($_POST['email']   ?? '');

if (!$address || !$name || !$phone || !$email) {
    http_response_code(400); echo 'All fields are required.'; exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo 'Invalid email.'; exit;
}

// ── Save to Supabase via REST API ──
$res = sb('POST', 'leads', [
    'address' => $address,
    'name'    => $name,
    'phone'   => $phone,
    'email'   => $email,
    'status'  => 'New',
]);

if ($res['code'] >= 400) {
    http_response_code(500);
    echo 'Error saving lead: ' . json_encode($res['data']);
    exit;
}

// ── Send Gmail alert ──
sendGmailAlert($name, $phone, $email, $address);

echo 'success';
?>
