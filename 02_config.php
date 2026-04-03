<?php
// ══════════════════════════════════════════════════
//  SUPABASE REST API — No direct DB port needed
// ══════════════════════════════════════════════════
define('SB_URL', 'https://ejwlfxrdzwmtejosuyvi.supabase.co');
define('SB_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImVqd2xmeHJkendtdGVqb3N1eXZpIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTIzNTI4NiwiZXhwIjoyMDkwODExMjg2fQ.e06rWSO82xmQ6b8xOxAW6S4wg9G3XqLxRW0J7fS_3-A); // Supabase → Settings → API → service_role key

// ══════════════════════════════════════════════════
//  GMAIL NOTIFICATION
//  1. Enable 2FA on your Google account
//  2. Go to: myaccount.google.com/apppasswords
//  3. Create an App Password for "Mail"
//  4. Paste the 16-char password below
// ══════════════════════════════════════════════════
define('GMAIL_FROM',    'info@greatpropertiesga.com'); // your Gmail
define('GMAIL_PASS',    'YOUR_GMAIL_APP_PASSWORD');    // 16-char App Password
define('NOTIFY_EMAIL',  'info@greatpropertiesga.com'); // where to receive alerts

// ══ Admin credentials ══
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'ChangeThis123!');

// ══ Session ══
if (!is_dir('/tmp/sessions')) mkdir('/tmp/sessions', 0777, true);
session_save_path('/tmp/sessions');
if (session_status() === PHP_SESSION_NONE) session_start();

// ══════════════════════════════════════════════════
//  Supabase REST API helper
// ══════════════════════════════════════════════════
function sb($method, $table, $data = null, $query = '') {
    $url = SB_URL . '/rest/v1/' . $table . ($query ? '?' . $query : '');
    $headers = [
        'apikey: '        . SB_KEY,
        'Authorization: Bearer ' . SB_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation',
    ];
    if ($method === 'GET') {
        $headers[] = 'Prefer: count=exact';
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($body, true)];
}

// ══════════════════════════════════════════════════
//  Gmail SMTP notification
// ══════════════════════════════════════════════════
function sendGmailAlert($name, $phone, $email, $address) {
    if (GMAIL_PASS === 'YOUR_GMAIL_APP_PASSWORD') return; // skip if not configured
    $to      = NOTIFY_EMAIL;
    $from    = GMAIL_FROM;
    $pass    = GMAIL_PASS;
    $subject = 'New Lead - Great Properties GA';
    $html    = "
    <div style='font-family:Arial;padding:24px;background:#f5f5f5;'>
      <div style='background:#fff;border-radius:10px;padding:24px;max-width:480px;'>
        <h2 style='color:#cc0000;margin:0 0 16px'>&#128313; New Lead Received!</h2>
        <table style='width:100%;border-collapse:collapse;font-size:15px;'>
          <tr><td style='padding:8px 0;color:#888;width:100px'>Name</td><td style='font-weight:bold'>$name</td></tr>
          <tr><td style='padding:8px 0;color:#888'>Phone</td><td style='font-weight:bold;color:#cc0000'>$phone</td></tr>
          <tr><td style='padding:8px 0;color:#888'>Email</td><td>$email</td></tr>
          <tr><td style='padding:8px 0;color:#888'>Address</td><td>$address</td></tr>
        </table>
        <a href='https://greatpropertiesga.com/admin.php' style='display:inline-block;margin-top:20px;background:#cc0000;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;'>View in Dashboard</a>
      </div>
    </div>";

    $socket = @stream_socket_client('tls://smtp.gmail.com:465', $errno, $errstr, 10);
    if (!$socket) return;

    $r = function() use ($socket) {
        $out = '';
        while ($line = fgets($socket, 512)) {
            $out .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $out;
    };
    $w = function($s) use ($socket) { fwrite($socket, $s . "\r\n"); };

    $r();
    $w('EHLO greatpropertiesga.com'); $r();
    $w('AUTH LOGIN');                  $r();
    $w(base64_encode($from));          $r();
    $w(base64_encode($pass));          $r();
    $w("MAIL FROM:<$from>");            $r();
    $w("RCPT TO:<$to>");               $r();
    $w('DATA');                        $r();
    $msg  = "From: Great Properties GA <$from>\r\n";
    $msg .= "To: $to\r\n";
    $msg .= "Subject: $subject\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $msg .= $html . "\r\n.";
    $w($msg); $r();
    $w('QUIT');
    fclose($socket);
}
?>
