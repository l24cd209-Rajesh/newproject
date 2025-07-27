<?php
require 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);  // Use hash in real apps
    $referral = $_POST['referral'];

    $stmt = $conn->prepare("INSERT INTO participants (fullname, email, phone, username, password, referral) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullname, $email, $phone, $username, $password, $referral);

    if ($stmt->execute()) {
        echo "<script>alert('Registered Successfully!'); window.location.href='project.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
