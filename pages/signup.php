<?php
session_start();

// Only redirect if user is actually logged in AND authenticated
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user'])) {
    header("Location: protected-page.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: black;
      color: white;
    }
    .signup-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(90vh - 1px); 
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
    .form-text {
      color: #bbb !important;
    }
  </style>
</head>
<body>

  <?php include 'navbar.php'; ?>

  <div class="signup-wrapper">
    <div class="card p-4 shadow-lg" style="width: 400px;">
        <h2 class="text-center mb-3">Sign Up</h2>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalidrequest'): ?>
            <div class="alert alert-danger">Invalid request.</div>
        <?php endif; ?>
        
        <form action="../process/signup-process.php" method="post" onsubmit="return validatePasswords()">        
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="firstname" class="form-control" 
                   pattern="^[A-Za-z]+$" required
                   oninvalid="this.setCustomValidity('First name must contain letters only')" 
                   oninput="this.setCustomValidity('')">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="lastname" class="form-control" 
                   pattern="^[A-Za-z]+$" required
                   oninvalid="this.setCustomValidity('Last name must contain letters only')" 
                   oninput="this.setCustomValidity('')">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required
                 oninvalid="this.setCustomValidity('Invalid email address')" 
                 oninput="this.setCustomValidity('')">
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" id="password" name="password" 
                 class="form-control" 
                 required
                 pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{6,12}$" 
                 oninvalid="this.setCustomValidity('Password must be 6–12 characters, include at least 1 uppercase, 1 lowercase, and 1 number. No special characters allowed.')" 
                 oninput="this.setCustomValidity('')">
          <div class="form-text">
            Must be 6–12 chars, at least one uppercase, one lowercase, and one number.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" id="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-custom w-100">Sign Up</button>
        <div class="text-center mt-3">
            <small><a href="login.php" style="color: #1E90FF;">Already have an account? Login</a></small>
        </div>
      </form>
    </div>
  </div>

  <script>
    function validatePasswords() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
            return false;
        }
        
        document.getElementById('confirm_password').setCustomValidity('');
        return true;
    }
    
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>