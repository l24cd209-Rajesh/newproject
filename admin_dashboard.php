<?php
require 'db_config.php';
session_start();

// Validate admin login with prepared statements
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify password (support both old MD5 and new password_hash)
        $password_valid = false;
        if (strlen($admin['password']) === 32) {
            // Old MD5 hash (for backward compatibility)
            $password_valid = (md5($password) === $admin['password']);
        } else {
            // New password_hash format
            $password_valid = password_verify($password, $admin['password']);
        }
        
        if ($password_valid) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            
            // Get participants data
            $participants = $conn->query("SELECT * FROM participants ORDER BY registered_at DESC");
        } else {
            echo "<script>alert('Invalid credentials'); window.location.href='admin_login.html';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Invalid credentials'); window.location.href='admin_login.html';</script>";
        exit;
    }
    $stmt->close();
} else {
    // Check if already logged in
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header("Location: admin_login.html");
        exit;
    }
    // Get participants data for logged-in admin
    $participants = $conn->query("SELECT * FROM participants ORDER BY registered_at DESC");
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Dashboard - Registered Users</title>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f0f4f8;
                padding: 20px;
            }
            h2 {
                text-align: center;
                color: #0d47a1;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 30px;
            }
            table th, table td {
                padding: 12px;
                border: 1px solid #ccc;
                text-align: center;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            }
            .back-link, .logout-link {
                display: inline-block;
                text-align: center;
                margin: 10px;
                color: #0d47a1;
                text-decoration: none;
                font-weight: bold;
                padding: 10px 20px;
                border: 1px solid #0d47a1;
                border-radius: 5px;
            }
            .back-link:hover, .logout-link:hover {
                background-color: #0d47a1;
                color: white;
            }
            .logout-link {
                background-color: #d32f2f;
                border-color: #d32f2f;
                color: white;
            }
            .logout-link:hover {
                background-color: #b71c1c;
            }
            .header-actions {
                text-align: center;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="header-actions">
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
        
        <h2>📊 Admin Dashboard - Registered Participants</h2>

        <table id="participantsTable" class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Username</th>
                    <th>Referral</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($participants && $participants->num_rows > 0): ?>
                <?php while ($row = $participants->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['referral']) ?></td>
                        <td><?= htmlspecialchars($row['registered_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No participants registered yet.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="header-actions">
            <a href="project.html" class="back-link">← Back to Home</a>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#participantsTable').DataTable({
                "order": [[ 0, "desc" ]], // Sort by ID descending (newest first)
                "pageLength": 25
            });
        });
    </script>
    </body>
    </html>
<?php
$conn->close();
?>
