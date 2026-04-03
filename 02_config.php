<?php
$host = "localhost";
$dbname = "leads_db";
$username = "leads_user";
$password = "YOUR_DB_PASSWORD";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
session_start();
$admin_user = "admin";
$admin_pass = "ChangeThis123!";
$smtp_host = "smtp.hostinger.com";
$smtp_port = 465;
$smtp_username = "info@greatpropertiesga.com";
$smtp_password = "YOUR_EMAIL_PASSWORD";
$smtp_secure = "ssl";
$lead_notification_email = "info@greatpropertiesga.com";
$from_email = "info@greatpropertiesga.com";
$from_name = "Great Properties GA";
$enable_sms = false;
$twilio_sid = "YOUR_TWILIO_SID";
$twilio_token = "YOUR_TWILIO_AUTH_TOKEN";
$twilio_from = "+1XXXXXXXXXX";
$twilio_to = "+14045901613";
?>