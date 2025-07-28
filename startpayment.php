<?php
session_start();
require_once 'config.php';
require_once 'db_config.php';

// Check if we have required data from form
if (!isset($_POST['fullName']) || !isset($_POST['email']) || !isset($_POST['phone'])) {
    header("Location: userform.html");
    exit;
}

// Validate Razorpay configuration
if (empty(RAZORPAY_KEY_ID) || empty(RAZORPAY_KEY_SECRET)) {
    die("Payment system is not configured properly. Please contact administrator.");
}

// Save form data in session to use after payment
$_SESSION['fullName'] = trim($_POST['fullName']);
$_SESSION['email'] = trim($_POST['email']);
$_SESSION['phone'] = trim($_POST['phone']);

// Validate input
if (empty($_SESSION['fullName']) || empty($_SESSION['email']) || empty($_SESSION['phone'])) {
    echo "<script>alert('Please fill all required fields.'); window.location.href='userform.html';</script>";
    exit;
}

if (!filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Please enter a valid email address.'); window.location.href='userform.html';</script>";
    exit;
}

try {
    require_once 'vendor/autoload.php';
    use Razorpay\Api\Api;

    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $orderData = [
        'receipt' => 'YAICESS_' . date('YmdHis') . '_' . rand(1000, 9999),
        'amount' => REGISTRATION_FEE * 100, // Amount in paise
        'currency' => 'INR',
        'payment_capture' => 1
    ];

    $order = $api->order->create($orderData);
    $_SESSION['order_id'] = $order['id'];
} catch (Exception $e) {
    error_log("Payment initialization error: " . $e->getMessage());
    echo "<script>alert('Payment system error. Please try again later.'); window.location.href='userform.html';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay ₹<?= REGISTRATION_FEE ?> - <?= APP_NAME ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d47a1;
            margin-bottom: 20px;
        }
        .amount {
            font-size: 36px;
            color: #4CAF50;
            font-weight: bold;
            margin: 20px 0;
        }
        .details {
            color: #666;
            margin-bottom: 30px;
        }
        .loading {
            color: #0d47a1;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="logo"><?= APP_NAME ?></div>
        <h2>Registration Payment</h2>
        <div class="amount">₹<?= REGISTRATION_FEE ?></div>
        <div class="details">
            <p><strong>Name:</strong> <?= htmlspecialchars($_SESSION['fullName']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($_SESSION['phone']) ?></p>
        </div>
        <div class="loading">Initializing payment...</div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "<?= RAZORPAY_KEY_ID ?>",
            "amount": "<?= REGISTRATION_FEE * 100 ?>",
            "currency": "INR",
            "name": "<?= APP_NAME ?>",
            "description": "Event Registration Fee",
            "order_id": "<?= htmlspecialchars($order['id']) ?>",
            "handler": function (response) {
                // Send payment details to server
                fetch("paymentsuccess.php", {
                    method: "POST",
                    headers: { 
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({
                        payment_id: response.razorpay_payment_id,
                        order_id: response.razorpay_order_id,
                        signature: response.razorpay_signature
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = "thankyou.html";
                    } else {
                        alert("Payment verification failed: " + (data.message || 'Unknown error'));
                        window.location.href = "userform.html";
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Payment processing error. Please contact support.");
                    window.location.href = "userform.html";
                });
            },
            "modal": {
                "ondismiss": function() {
                    window.location.href = "userform.html";
                }
            },
            "prefill": {
                "name": "<?= htmlspecialchars($_SESSION['fullName']) ?>",
                "email": "<?= htmlspecialchars($_SESSION['email']) ?>",
                "contact": "<?= htmlspecialchars($_SESSION['phone']) ?>"
            },
            "theme": { 
                "color": "#0d47a1" 
            }
        };
        
        var rzp = new Razorpay(options);
        
        // Auto-open payment modal
        setTimeout(function() {
            rzp.open();
        }, 1000);
        
        // Fallback if Razorpay fails to load
        rzp.on('payment.failed', function (response) {
            alert('Payment failed: ' + response.error.description);
            window.location.href = "userform.html";
        });
    </script>
</body>
</html>
