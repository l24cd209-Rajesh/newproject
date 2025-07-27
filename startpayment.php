<?php
session_start();
require('vendor/autoload.php');
use Razorpay\Api\Api;

// Save form data in session to use after payment
$_SESSION['fullName'] = $_POST['fullName'];
$_SESSION['email'] = $_POST['email'];
$_SESSION['phone'] = $_POST['phone'];

$key = "";
$secret = "";

$api = new Api($key, $secret);

$orderData = [
    'receipt' => 'I4C_' . rand(1000, 9999),
    'amount' => 100,
    'currency' => 'INR',
    'payment_capture' => 1
];

$order = $api->order->create($orderData);
$_SESSION['order_id'] = $order['id'];
?>

<!DOCTYPE html>
<html>
<head><title>Pay ₹1 - I4C 2025</title></head>
<body>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script>
    var options = {
      "key": "<?= $key ?>",
      "amount": "100",
      "currency": "INR",
      "name": "IEEE I4C 2025",
      "description": "Event Registration ₹1",
      "order_id": "<?= $order['id'] ?>",
      "handler": function (response) {
        // Send to server
        fetch("paymentSuccess.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            payment_id: response.razorpay_payment_id
          })
        }).then(res => res.json())
          .then(data => {
            if (data.success) {
              window.location.href = "thankyou.html";
            } else {
              alert("Error: " + data.message);
            }
          });
      },
      "theme": { "color": "#3399cc" }
    };
    var rzp = new Razorpay(options);
    rzp.open();
  </script>
</body>
</html>
