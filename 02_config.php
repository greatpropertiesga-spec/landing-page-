<?php
// ══════════════════════════════════════════
//  SUPABASE / PostgreSQL CONNECTION
// ══════════════════════════════════════════

$db_host = 'db.ejwlfxrdzwmtejosuyvi.supabase.co';
$db_name = 'postgres';
$db_user = 'postgres';
$db_pass = '4078shadydrive';
$db_port = '5432';

try {
    $pdo = new PDO(
        "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

session_start();

// ── Admin credentials ──
$admin_user = 'admin';
$admin_pass = 'ChangeThis123!';

// ── Email notifications ──
$lead_notification_email = 'info@greatpropertiesga.com';
$from_email              = 'info@greatpropertiesga.com';
$from_name               = 'Great Properties GA';
?>
