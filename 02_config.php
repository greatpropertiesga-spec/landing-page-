<?php
// ============================================================
//  config.php - Great Properties GA
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', '1');

// --- Session ---
if (!is_dir('/tmp/php_sessions')) @mkdir('/tmp/php_sessions', 0777, true);
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('/tmp/php_sessions');
    session_start();
}

// --- Supabase ---
define('SB_URL', 'https://ejwlfxrdzwmtejosuyvi.supabase.co');
define('SB_KEY', 'sbp_1c6afa5dd1664f1bca7947f13bac1de107df5c40');

// --- Admin ---
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'ChangeThis123!');

// --- Gmail (opcional) ---
define('GMAIL_FROM', 'info@greatpropertiesga.com');
define('GMAIL_PASS', 'YOUR_GMAIL_APP_PASSWORD');
define('NOTIFY_TO',  'info@greatpropertiesga.com');

// ============================================================
//  Supabase REST helper
// ============================================================
function sb(string $method, string $table, ?array $data = null, string $query = ''): array {
    $url = SB_URL . '/rest/v1/' . $table . ($query ? '?' . $query : '');

    $opts = [
        'http' => [
            'method'        => $method,
            'header'        => implode("\r\n", [
                'apikey: '               . SB_KEY,
                'Authorization: Bearer ' . SB_KEY,
                'Content-Type: application/json',
                'Prefer: return=representation',
            ]),
            'ignore_errors' => true,
            'timeout'       => 10,
        ],
        'ssl' => ['verify_peer' => false],
    ];

    if ($data !== null) {
        $opts['http']['content'] = json_encode($data);
    }

    $body = @file_get_contents($url, false, stream_context_create($opts));
    $code = 0;
    if (isset($http_response_header)) {
        preg_match('/HTTP\/\d\.?\d?\s+(\d+)/', $http_response_header[0], $m);
        $code = (int)($m[1] ?? 0);
    }

    return [
        'code' => $code,
        'data' => $body ? json_decode($body, true) : null,
        'raw'  => $body,
    ];
}

// ============================================================
//  Gmail SMTP (opcional)
// ============================================================
function sendGmailAlert(string $name, string $phone, string $email, string $address): void {
    if (GMAIL_PASS === 'YOUR_GMAIL_APP_PASSWORD') return;
    $from = GMAIL_FROM; $to = NOTIFY_TO;
    $html = "<div style='font-family:Arial;padding:20px'>
      <h2 style='color:#cc0000'>New Lead!</h2>
      <p><b>Name:</b> $name</p><p><b>Phone:</b> $phone</p>
      <p><b>Email:</b> $email</p><p><b>Address:</b> $address</p>
      <a href='https://greatpropertiesga.com/admin.php'
         style='background:#cc0000;color:#fff;padding:10px 20px;
                border-radius:6px;text-decoration:none;display:inline-block;margin-top:12px'>
         View Dashboard
      </a></div>";
    $sock = @stream_socket_client('tls://smtp.gmail.com:465', $e, $s, 10);
    if (!$sock) return;
    $r = function() use ($sock) { $o=''; while($l=fgets($sock,515)){$o.=$l;if($l[3]===' ')break;} return $o; };
    $w = fn($s) => fwrite($sock, $s."\r\n");
    $r(); $w('EHLO greatpropertiesga.com'); $r();
    $w('AUTH LOGIN'); $r();
    $w(base64_encode($from)); $r();
    $w(base64_encode(GMAIL_PASS)); $r();
    $w("MAIL FROM:<$from>"); $r();
    $w("RCPT TO:<$to>"); $r();
    $w('DATA'); $r();
    $w("From: Great Properties GA <$from>\r\nTo: $to\r\nSubject: New Lead - Great Properties GA\r\n"
      ."MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$html\r\n.");
    $r(); $w('QUIT'); fclose($sock);
}
