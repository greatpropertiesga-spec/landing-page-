<?php
// ══ SESSION ══
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp/sessions');
    session_start();
}

// ══ SUPABASE CREDENTIALS ══
$db_host = 'db.ejwlfxrdzwmtejosuyvi.supabase.co';
$db_name = 'postgres';
$db_user = 'postgres';
$db_pass = '4078shadydrive';
$db_port = '5432';

// ══ PDO CONNECTION ══
try {
    $pdo = new PDO(
        "pgsql:host={$db_host};port={$db_port};dbname={$db_name}",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 5,
        ]
    );
} catch (PDOException $e) {
    // Only show error on admin pages, not on landing
    $pdo = null;
    $db_error = $e->getMessage();
}

// ══ ADMIN CREDENTIALS ══
$admin_user = 'admin';
$admin_pass = 'ChangeThis123!';

// ══ EMAIL ══
$lead_notification_email = 'info@greatpropertiesga.com';
$from_email              = 'info@greatpropertiesga.com';
$from_name               = 'Great Properties GA';
?>
