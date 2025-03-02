<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CanteenGo! - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .brand-logo {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .canteen-green { color: rgb(0, 87, 20);}
        .go-white { color: white; }
    </style>
      <!-- Add registration success message -->
      <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success mt-3">Registration successful! Please login.</div>
      <?php endif; ?>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-4">
                <h1 class="brand-logo">
                    <span class="canteen-green">Canteen</span><span class="go-white bg-dark rounded px-2">Go!</span>
                </h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Login</button>
                        </form>
                        <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <!-- Add registration link below login form -->
     <div class="text-center mt-3">
        Don't have an account? <a href="register.php" class="text-success">Register here</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>