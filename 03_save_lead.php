<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$address = trim($_POST['address'] ?? '');
$name    = trim($_POST['name']    ?? '');
$phone   = trim($_POST['phone']   ?? '');
$email   = trim($_POST['email']   ?? '');

if ($address === '' || $name === '' || $phone === '' || $email === '') {
    http_response_code(400);
    echo 'All fields are required.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email address.';
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO leads (address, name, phone, email, status) VALUES (?, ?, ?, ?, 'New')"
    );
    $stmt->execute([$address, $name, $phone, $email]);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Database error.';
    exit;
}

// Email notification
$subject = 'New Lead - Great Properties GA';
$message = "New Lead:\n\nName: $name\nPhone: $phone\nEmail: $email\nAddress: $address";
$headers = "From: $from_email";
mail($lead_notification_email, $subject, $message, $headers);

echo 'success';
?>
