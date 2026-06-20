<?php
/**
 * Verify Payment — AJAX Endpoint
 * Verifies Razorpay payment signature & updates database
 */
define('AARAMBH_INIT', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

if (!RateLimiter::checkLimit('payments', 15, 3600)) {
    jsonResponse(['success' => false, 'message' => 'Too many payment requests. Please try again later.'], 429);
}

$input = json_decode(file_get_contents('php://input'), true);

$razorpayOrderId = InputValidator::validateRazorpayId($input['razorpay_order_id'] ?? '');
$razorpayPaymentId = InputValidator::validateRazorpayId($input['razorpay_payment_id'] ?? '');
$razorpaySignature = InputValidator::validateString($input['razorpay_signature'] ?? '', 255);
$studentId = InputValidator::validateInt($input['student_id'] ?? 0);

if (!$razorpayOrderId || !$razorpayPaymentId || !$razorpaySignature || !$studentId) {
    jsonResponse(['success' => false, 'message' => 'Invalid payment parameters.'], 400);
}

try {
    // Step 1: Verify Razorpay Signature
    $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);

    if (!hash_equals($expectedSignature, $razorpaySignature)) {
        error_log("Payment signature mismatch for order: $razorpayOrderId");
        jsonResponse(['success' => false, 'message' => 'Payment verification failed. If amount was deducted, contact academics@heyyguru.in'], 400);
    }

    $db = getDB();

    // Step 2: Update payment record
    $stmt = $db->prepare("UPDATE payments SET razorpay_payment_id = ?, razorpay_signature = ?, status = 'captured', updated_at = NOW() WHERE razorpay_order_id = ? AND student_id = ?");
    $stmt->execute([$razorpayPaymentId, $razorpaySignature, $razorpayOrderId, $studentId]);

    // Step 3: Update student status
    $stmt = $db->prepare("UPDATE students SET razorpay_payment_id = ?, status = 'paid', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$razorpayPaymentId, $studentId]);

    // Step 4: Get student details for email
    $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    // Step 5: Send confirmation email
    if ($student) {
        require_once __DIR__ . '/send_email.php';
        $emailSent = sendConfirmationEmail($student, $razorpayPaymentId);

        if ($emailSent) {
            $stmt = $db->prepare("UPDATE students SET email_sent = 1 WHERE id = ?");
            $stmt->execute([$studentId]);
        }
    }

    jsonResponse([
        'success' => true,
        'message' => 'Payment verified successfully! Welcome to AARAMBH.',
        'payment_id' => $razorpayPaymentId
    ]);

} catch (Exception $e) {
    error_log("Payment verification error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Verification error. Contact academics@heyyguru.in'], 500);
}
