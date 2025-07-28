

<?php
session_start();
require_once 'config.php';
require_once 'db_config.php';

header('Content-Type: application/json');

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (!isset($data['payment_id']) || !isset($data['order_id'])) {
        throw new Exception('Missing required payment data');
    }

    $payment_id = $data['payment_id'];
    $order_id = $data['order_id'];
    $signature = $data['signature'] ?? '';

    // Validate session data
    if (!isset($_SESSION['fullName']) || !isset($_SESSION['email']) || !isset($_SESSION['phone'])) {
        throw new Exception('Session data missing');
    }

    $name = $_SESSION['fullName'];
    $email = $_SESSION['email'];
    $phone = $_SESSION['phone'];
    $session_order_id = $_SESSION['order_id'] ?? '';

    // Verify order ID matches session
    if ($order_id !== $session_order_id) {
        throw new Exception('Order ID mismatch');
    }

    // Verify payment signature (recommended for security)
    if (!empty(RAZORPAY_KEY_SECRET) && !empty($signature)) {
        $expected_signature = hash_hmac('sha256', $order_id . "|" . $payment_id, RAZORPAY_KEY_SECRET);
        if ($signature !== $expected_signature) {
            throw new Exception('Payment signature verification failed');
        }
    }

    // Find or create participant
    $participant_id = null;
    
    // Check if participant exists by email
    $check_stmt = $conn->prepare("SELECT id FROM participants WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $participant = $result->fetch_assoc();
        $participant_id = $participant['id'];
    } else {
        // Create new participant record
        $insert_stmt = $conn->prepare("INSERT INTO participants (fullname, email, phone, username, password) VALUES (?, ?, ?, ?, ?)");
        $username = strtolower(str_replace(' ', '_', $name)) . '_' . rand(1000, 9999);
        $temp_password = password_hash('temp_' . rand(100000, 999999), PASSWORD_DEFAULT);
        $insert_stmt->bind_param("sssss", $name, $email, $phone, $username, $temp_password);
        
        if ($insert_stmt->execute()) {
            $participant_id = $conn->insert_id;
        } else {
            throw new Exception('Failed to create participant record');
        }
        $insert_stmt->close();
    }
    $check_stmt->close();

    // Check if payment already recorded
    $payment_check = $conn->prepare("SELECT id FROM payments WHERE payment_id = ?");
    $payment_check->bind_param("s", $payment_id);
    $payment_check->execute();
    $payment_result = $payment_check->get_result();
    
    if ($payment_result->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Payment already recorded']);
        exit;
    }
    $payment_check->close();

    // Record payment
    $payment_stmt = $conn->prepare("INSERT INTO payments (participant_id, payment_id, order_id, amount, status) VALUES (?, ?, ?, ?, 'completed')");
    $payment_stmt->bind_param("issd", $participant_id, $payment_id, $order_id, REGISTRATION_FEE);
    
    if (!$payment_stmt->execute()) {
        throw new Exception('Failed to record payment');
    }
    $payment_stmt->close();

    // Send confirmation email
    if (!empty(SMTP_USERNAME) && !empty(SMTP_FROM_EMAIL)) {
        try {
            require_once 'vendor/autoload.php';
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\SMTP;
            use PHPMailer\PHPMailer\Exception as PHPMailerException;

            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = APP_NAME . " - Registration Confirmation";
            
            $registration_id = "YAICESS2025-" . str_pad($participant_id, 6, '0', STR_PAD_LEFT);
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #0d47a1;'>" . APP_NAME . "</h2>
                    <p>Dear " . htmlspecialchars($name) . ",</p>
                    
                    <p>Thank you for registering for " . APP_NAME . "!</p>
                    
                    <div style='background: #f5f5f5; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                        <h3 style='color: #0d47a1; margin-top: 0;'>Registration Details</h3>
                        <p><strong>Registration ID:</strong> {$registration_id}</p>
                        <p><strong>Payment ID:</strong> " . htmlspecialchars($payment_id) . "</p>
                        <p><strong>Amount Paid:</strong> ₹" . REGISTRATION_FEE . "</p>
                        <p><strong>Payment Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    
                    <div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                        <h3 style='color: #0d47a1; margin-top: 0;'>Event Details</h3>
                        <p><strong>Date:</strong> July 30, 2025</p>
                        <p><strong>Venue:</strong> Hyderabad, India</p>
                        <p><strong>Theme:</strong> Innovation and Technology</p>
                    </div>
                    
                    <p>Please keep this email and your Registration ID for future reference.</p>
                    
                    <p>For any queries, please contact us at " . SMTP_FROM_EMAIL . "</p>
                    
                    <p>We look forward to seeing you at the event!</p>
                    
                    <hr style='margin: 30px 0;'>
                    <p style='color: #666; font-size: 12px;'>
                        This is an automated email. Please do not reply to this email.
                    </p>
                </div>
            ";

            $mail->send();
        } catch (PHPMailerException $e) {
            error_log("Email sending failed: " . $e->getMessage());
            // Don't fail the payment process if email fails
        }
    }

    // Clear session data
    unset($_SESSION['fullName'], $_SESSION['email'], $_SESSION['phone'], $_SESSION['order_id']);

    echo json_encode([
        'success' => true, 
        'message' => 'Payment successful and confirmation email sent',
        'registration_id' => $registration_id ?? null
    ]);

} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Payment processing failed: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
