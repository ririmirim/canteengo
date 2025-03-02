<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Debug output
        echo "<pre>";
        echo "Database Hash: " . $user['password'] . "\n";
        echo "Input Password: " . $password . "\n";
        echo "Password Match: " . (password_verify($password, $user['password']) ? 'Yes' : 'No');
        echo "</pre>";

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $session_id = session_id();
            $user_id = $user['id'];

            $stmt = $conn->prepare("REPLACE INTO active_sessions (user_id, username, last_activity, session_id) 
                                  VALUES (?, ?, NOW(), ?)");
            $stmt->bind_param("iss", $user_id, $_SESSION['username'], $session_id);

            if (!$stmt->execute()) {
                error_log("Session tracking failed: " . $stmt->error);
            }
            $stmt->close();
            
            header("Location: " . ($user['role'] === 'student' ? 'student.php' : 'staff.php'));
            exit();
        }
    }
    
    header("Location: index.php?error=Invalid credentials");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - CanteenGo!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($_GET['registered'])): ?>
                            <div class="alert alert-success">Registration successful! Please login.</div>
                        <?php endif; ?>
                        
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Login</button>
                        </form>
                        <div class="mt-3 text-center">
                            Don't have an account? <a href="register.php" class="text=success">Register here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>