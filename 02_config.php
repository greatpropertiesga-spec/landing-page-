<?php
// ============================================================
//  config.php  -  Great Properties GA
// ============================================================

// --- Session (must be first) ---
if (!is_dir('/tmp/php_sessions')) @mkdir('/tmp/php_sessions', 0777, true);
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp/php_sessions');
    session_start();
}

// --- Supabase REST API (no direct DB port needed) ---
define('SB_URL', 'https://ejwlfxrdzwmtejosuyvi.supabase.co');
define('SB_KEY', 'YOUR_SUPABASE_SERVICE_ROLE_KEY'); // Settings > API > service_role

// --- Admin login ---
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'ChangeThis123!');

// --- Gmail notification (optional) ---
// 1. Enable 2FA on Google  2. Create App Password at myaccount.google.com/apppasswords
define('GMAIL_FROM',   'info@greatpropertiesga.com');
define('GMAIL_PASS',   'YOUR_GMAIL_APP_PASSWORD');   // 16-char App Password
define('NOTIFY_TO',    'info@greatpropertiesga.com');

// ============================================================
//  Supabase REST helper
// ============================================================
function sb(string $method, string $table, array $data = null, string $query = ''): array {
    $url = SB_URL . '/rest/v1/' . $table . ($query ? '?' . $query : '');
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'apikey: '          . SB_KEY,
            'Authorization: Bearer ' . SB_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation',
        ],
    ]);
    if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($body, true), 'error' => $err];
}

// ============================================================
//  Gmail SMTP notification
// ============================================================
function sendGmailAlert(string $name, string $phone, string $email, string $address): void {
    if (GMAIL_PASS === 'YOUR_GMAIL_APP_PASSWORD') return;
    $from = GMAIL_FROM;
    $to   = NOTIFY_TO;
    $subj = 'New Lead - Great Properties GA';
    $html = "<div style='font-family:Arial;padding:20px;background:#f5f5f5'>
      <div style='background:#fff;border-radius:10px;padding:24px;max-width:480px'>
        <h2 style='color:#cc0000;margin:0 0 16px'>&#128313; New Lead Received!</h2>
        <table style='width:100%;font-size:15px'>
          <tr><td style='color:#888;padding:6px 0;width:90px'>Name</td><td><strong>$name</strong></td></tr>
          <tr><td style='color:#888;padding:6px 0'>Phone</td><td><strong style='color:#cc0000'>$phone</strong></td></tr>
          <tr><td style='color:#888;padding:6px 0'>Email</td><td>$email</td></tr>
          <tr><td style='color:#888;padding:6px 0'>Address</td><td>$address</td></tr>
        </table>
        <a href='https://greatpropertiesga.com/admin.php'
           style='display:inline-block;margin-top:18px;background:#cc0000;color:#fff;
                  padding:12px 22px;border-radius:6px;text-decoration:none;font-weight:bold'>
           View in Dashboard &rarr;
        </a>
      </div></div>";

    $sock = @stream_socket_client('tls://smtp.gmail.com:465', $errno, $errstr, 10);
    if (!$sock) return;
    $r = function() use ($sock) {
        $o = '';
        while ($l = fgets($sock, 515)) { $o .= $l; if ($l[3] === ' ') break; }
        return $o;
    };
    $w = fn($s) => fwrite($sock, $s . "\r\n");
    $r(); $w('EHLO greatpropertiesga.com'); $r();
    $w('AUTH LOGIN'); $r();
    $w(base64_encode($from)); $r();
    $w(base64_encode(GMAIL_PASS)); $r();
    $w("MAIL FROM:<$from>"); $r();
    $w("RCPT TO:<$to>"); $r();
    $w('DATA'); $r();
    $w("From: Great Properties GA <$from>\r\nTo: $to\r\nSubject: $subj\r\n"
     . "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$html\r\n.");
    $r(); $w('QUIT');
    fclose($sock);
}
