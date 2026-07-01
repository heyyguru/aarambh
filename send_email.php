<?php
/**
 * Send Email — Confirmation Email Helper
 * Sends beautiful HTML confirmation email from academics@heyyguru.in
 */
if (!defined('AARAMBH_INIT')) {
    define('AARAMBH_INIT', true);
    require_once __DIR__ . '/config.php';
}

/**
 * Send enrollment confirmation email
 */
function sendConfirmationEmail($student, $paymentId) {
    if (strtolower($student['email']) === 'noemail@example.com') {
        return false; // Skip sending email
    }

    $to = $student['email'];
    $studentName = $student['name'];
    $studentClass = $student['student_class'];
    $phone = $student['phone'];

    $subject = "🎉 Welcome to AARAMBH, {$studentName}! Your Journey Begins Now";

    $htmlBody = getEmailTemplate($studentName, $studentClass, $phone, $paymentId);

    // Try SMTP first (most reliable)
    $sent = sendViaSMTP($to, $subject, $htmlBody);

    // Fallback to PHP mail() if SMTP fails
    if (!$sent) {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: HeyyGuru <' . SMTP_FROM_EMAIL . '>';
        $headers[] = 'Reply-To: ' . SUPPORT_EMAIL;
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        $sent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    return $sent;
}

/**
 * Send via SMTP using native PHP sockets (No dependencies required)
 */
function sendViaSMTP($to, $subject, $htmlBody) {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $user = SMTP_USERNAME;
    $pass = SMTP_PASSWORD;
    $fromEmail = SMTP_FROM_EMAIL;
    $fromName = SMTP_FROM_NAME;

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ]
    ]);

    $socket = stream_socket_client("tcp://$host:$port", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        error_log("SMTP Error: $errstr ($errno)");
        return false;
    }

    function read_resp($socket) {
        $resp = "";
        while ($str = fgets($socket, 515)) {
            $resp .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $resp;
    }
    function send_cmd($socket, $cmd) {
        fwrite($socket, $cmd . "\r\n");
        return read_resp($socket);
    }

    read_resp($socket); // read banner
    send_cmd($socket, "EHLO localhost");
    send_cmd($socket, "STARTTLS");
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    send_cmd($socket, "EHLO localhost");
    send_cmd($socket, "AUTH LOGIN");
    send_cmd($socket, base64_encode($user));
    $authResp = send_cmd($socket, base64_encode($pass));
    
    if (strpos($authResp, "235") === false) {
        error_log("SMTP Auth Failed: " . $authResp);
        fclose($socket);
        return false;
    }

    send_cmd($socket, "MAIL FROM:<$fromEmail>");
    send_cmd($socket, "RCPT TO:<$to>");
    send_cmd($socket, "DATA");

    $headers = [
        "From: $fromName <$fromEmail>",
        "To: $to",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8"
    ];

    $msg = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
    $sendResp = send_cmd($socket, $msg);
    send_cmd($socket, "QUIT");
    fclose($socket);

    return strpos($sendResp, "250") !== false;
}

/**
 * Beautiful HTML email template
 */
function getEmailTemplate($name, $class, $phone, $paymentId) {
    $year = date('Y');
    $date = date('d M Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to AARAMBH!</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#F0F2FF;color:#1A1D2E;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F0F2FF;padding:40px 15px;">
        <tr>
            <td align="center">
                <!-- Certificate Wrapper -->
                <table width="650" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF;border:8px solid #4A6CF7;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);position:relative;">
                    <tr>
                        <td align="center" style="padding:60px 40px;">
                            
                            <h1 style="color:#1A1D2E;margin:0;font-size:36px;font-weight:800;letter-spacing:1px;">Welcome to HeyyGuru</h1>
                            
                            <p style="color:#6C7293;margin:30px 0 20px;font-size:18px;text-transform:uppercase;letter-spacing:2px;font-weight:600;">
                                This is to certify that
                            </p>
                            
                            <h2 style="color:#4A6CF7;margin:0;font-size:48px;font-weight:700;font-style:italic;border-bottom:3px solid #E5E7EB;display:inline-block;padding-bottom:10px;text-transform:capitalize;">
                                {$name}
                            </h2>
                            
                            <p style="color:#4A4F6A;margin:40px 0 40px;font-size:22px;line-height:1.5;">
                                is successfully enrolled in <strong style="color:#FF3CAC;font-weight:800;">AARAMBH</strong><br>on <strong>{$date}</strong>.
                            </p>
                            
                            <div style="background:linear-gradient(135deg,#4A6CF7,#6C3CE1);color:#FFFFFF;padding:15px 35px;border-radius:50px;display:inline-block;font-size:18px;font-weight:700;box-shadow:0 4px 15px rgba(108,60,225,0.3);">
                                Let's begin the journey with HeyyGuru!
                            </div>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:60px;border-top:1px solid #E5E7EB;padding-top:30px;">
                                <tr>
                                    <td width="50%" align="left" style="color:#8890B0;font-size:14px;line-height:1.8;">
                                        <strong>Class:</strong> {$class}<br>
                                        <strong>Phone:</strong> +91 {$phone}
                                    </td>
                                    <td width="50%" align="right" style="color:#8890B0;font-size:14px;line-height:1.8;">
                                        <strong>Payment ID:</strong> {$paymentId}<br>
                                        <strong>Date:</strong> {$date}
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                </table>
                
                <!-- Extra Information -->
                <table width="650" cellpadding="0" cellspacing="0" style="margin-top:30px;">
                    <tr>
                        <td align="center" style="color:#6C7293;font-size:14px;line-height:1.6;">
                            <p style="margin-bottom:10px;">Our mentor will call you within 24 hours. Your class schedule will be sent via WhatsApp.</p>
                            <p style="margin-bottom:20px;">Need Help? <a href="mailto:academics@heyyguru.in" style="color:#4A6CF7;text-decoration:none;">academics@heyyguru.in</a> | <a href="tel:+917676798650" style="color:#4A6CF7;text-decoration:none;">+91 7676798650</a></p>
                            <p style="font-size:12px;color:#8890B0;">© {$year} HeyyGuru. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}
