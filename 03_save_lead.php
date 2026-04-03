<?php
include 'config.php';
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}
$address = trim($_POST['address'] ?? '');
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
if ($address === '' || $name === '' || $phone === '' || $email === '') {
    http_response_code(400);
    echo "All fields are required.";
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Invalid email address.";
    exit;
}
$stmt = $conn->prepare("INSERT INTO leads (address, name, phone, email) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $address, $name, $phone, $email);
if (!$stmt->execute()) {
    http_response_code(500);
    echo "Database error.";
    exit;
}
$subject = "New Lead - Great Properties GA";
$message = "New Lead:\n\nAddress: $address\nName: $name\nPhone: $phone\nEmail: $email";
$headers = "From: info@greatpropertiesga.com";
mail("info@greatpropertiesga.com", $subject, $message, $headers);
$conn->close();
echo "success";
?>