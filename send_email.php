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
    $to = $student['email'];
    $studentName = $student['name'];
    $studentClass = $student['student_class'];
    $phone = $student['phone'];

    $subject = "🎉 Welcome to AARAMBH, {$studentName}! Your Journey Begins Now";

    $htmlBody = getEmailTemplate($studentName, $studentClass, $phone, $paymentId);

    // Try PHP mail() first (works on most shared hosting)
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: HeyyGuru <' . SMTP_FROM_EMAIL . '>';
    $headers[] = 'Reply-To: ' . SUPPORT_EMAIL;
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    $sent = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));

    if (!$sent) {
        // Try SMTP if mail() fails (requires PHPMailer)
        $sent = sendViaSMTP($to, $subject, $htmlBody);
    }

    return $sent;
}

/**
 * Try sending via SMTP (PHPMailer)
 */
function sendViaSMTP($to, $subject, $htmlBody) {
    // Check if PHPMailer is available
    $phpmailerPath = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($phpmailerPath)) {
        error_log("PHPMailer not installed. Run: composer require phpmailer/phpmailer");
        return false;
    }

    require_once $phpmailerPath;

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(SUPPORT_EMAIL, 'HeyyGuru Support');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("SMTP Email error: " . $e->getMessage());
        return false;
    }
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
