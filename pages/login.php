<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user'])) {
    header("Location: protected-page.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: black;
      color: white;
    }
    .login-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(70vh - 1px); 
    }
    .card {
        background-color: #1e1e1e;
        color: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
    .btn-custom {
      background-color: #1E90FF;
      color: white;
      transition: 0.3s;
    }
    .btn-custom:hover {
      background-color: #1058c5ff;
    }
  </style>
</head>
<body>

  <?php include 'navbar.php'; ?>

  <div class="login-wrapper">
    <div class="card p-4 shadow-lg" style="width: 350px;">
        <h2 class="text-center mb-3">Login</h2>
        
        <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
            <div class="alert alert-info">You have been logged out successfully.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'invalid'): ?>
                <div class="alert alert-danger">Invalid username or password.</div>
            <?php elseif ($_GET['error'] === 'unauthorized'): ?>
                <div class="alert alert-warning">Please login to access that page.</div>
            <?php elseif ($_GET['error'] === 'request'): ?>
                <div class="alert alert-danger">Invalid request.</div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form action="../process/login-process.php" method="POST">
            <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" 
                 class="form-control" 
                 required 
                 pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{6,12}$" 
                 title="Password must contain 1 uppercase, 1 lowercase, 1 number, no special characters, and be 6-12 characters long.">
        </div>
        <button type="submit" class="btn btn-custom w-100">Login</button>
        <div class="text-center mt-3">
            <small><a href="signup.php" style="color: #1E90FF;">Don't have an account? Sign up</a></small>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>