<?php
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $referral = trim($_POST['referral'] ?? '');

    // Basic validation
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($phone) || !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
        $errors[] = "Valid phone number is required";
    }
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }

    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT id FROM participants WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username or email already exists. Please choose different ones.'); window.history.back();</script>";
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new participant using prepared statement
    $stmt = $conn->prepare("INSERT INTO participants (fullname, email, phone, username, password, referral) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullname, $email, $phone, $username, $password_hash, $referral);

    if ($stmt->execute()) {
        echo "<script>alert('Registered Successfully! You can now proceed to payment.'); window.location.href='userform.html';</script>";
    } else {
        error_log("Registration error: " . $conn->error);
        echo "<script>alert('Registration failed. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect to registration form if accessed directly
    header("Location: userform.html");
    exit;
}
?>
