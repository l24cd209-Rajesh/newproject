

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require('PHPMailer/src/PHPMailer.php');
require('PHPMailer/src/SMTP.php');
require('PHPMailer/src/Exception.php');
use PHPMailer\PHPMailer\PHPMailer;

$data = json_decode(file_get_contents('php://input'), true);
$payment_id = $data['payment_id'];

$name = $_SESSION['fullName'];
$email = $_SESSION['email'];
$phone = $_SESSION['phone'];
$order_id = $_SESSION['order_id'];

$con = new mysqli("localhost", "root", "", "db1");

if ($con->connect_error) {
  echo json_encode(['success' => true, 'message' => 'DB Error']);
  exit;
}

$stmt = $con->prepare("INSERT INTO i4c (name, email, phone, payment_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $phone, $payment_id);
$stmt->execute();
$stmt->close();
$con->close();

// Send confirmation mail
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yaswanthvardhan216@gmail.com';
    $mail->Password = '';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yaswanthvardhan216@gmail.com', 'IEEE I4C 2025');
    $mail->addAddress($email, $name);
    $mail->isHTML(true);
    $mail->Subject = "I4C 2025 Registration Confirmation";
    $mail->Body = "
    Dear $name,<br><br>

    Thank you for registering for the <strong>IEEE IES Industrial Innovation Conclave (I4C) 2025</strong>!<br><br>

    <strong>Your Registration ID:</strong> I4C2025-<?= $Id ?>$Id<br><br>

    Please keep this ID for future reference. Below are the event details:<br><br>

    📅 <strong>Dates:</strong> 06–07 August 2025<br>
    📍 <strong>Venue:</strong> Vivanta by Taj, Begumpet, Hyderabad, India<br>
    🎯 <strong>Theme:</strong> AI Frontiers – Pioneering Intelligent Solutions in Industry<br><br>

    <strong>Event Highlights:</strong><br>
    • Keynote Sessions by Global Leaders<br>
    • Expert Talks on AI Applications across Industries<br>
    • Hands-on Workshops (MATLAB & EDGE Devices)<br>
    • AI Design Contest (priority for student participants)<br>
    • Speed Mentoring – 1-on-1 expert guidance<br>
    • Networking with Industry Leaders<br>
    • Industry Exhibitions & Visits<br><br>

    <strong>Featured Speakers:</strong><br>
    • Prof. Milos Manic – President, IEEE IES | Fellow IEEE<br>
    • Anuradha Vattem – Smart City Architect, IIIT-H<br>
    • Dr. Balakrishna Pamulaparthy – GE, Hyderabad<br>
    • Sharat Manikonda – Director, Innodatatics<br>
    • Guruprasad Padmanabhan – CEO, Asthra AI<br>
    • Kavinga Upul Ekanayaka – ACCELR, Sri Lanka<br><br>

    <strong>Workshops:</strong><br>
    • MATLAB Hands-on by MathWorks Team<br>
    • Product Demo by Divya Chilukoti, Silicon Labs<br><br>

    <strong>AI Design Contest:</strong><br>
    Showcase your innovative AI solutions!<br><br>

    For any queries, reply to this email.<br><br>

    Looking forward to seeing you at I4C 2025!<br><br>

    Warm regards,<br>
    IEEE I4C 2025 Team
";

    $mail->send();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Mail Error: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true]);
exit;
