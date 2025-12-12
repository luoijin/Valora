<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Valora - Sign Up</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
  <style>
      :root {
      /**
      * colors
      */

      --hoockers-green_20: hsla(148, 20%, 20%, 0.2);        
      --pale-spring-bud: #e5e7eb;                           
      --hoockers-green: #064e3b;                           
      --spanish-gray: #6b7280;                           
      --light-gray: #d1d5db;                                
      --cultured-1: #f3f4f6;                            
      --cultured-2: #e5e7eb;                               
      --gray-web: #374151;                                  
      --white_30: rgba(255, 255, 255, 0.3);
      --black_70: rgba(0, 0, 0, 0.7);
      --black_50: rgba(0, 0, 0, 0.5);
      --black_15: rgba(0, 0, 0, 0.15);
      --black_10: rgba(0, 0, 0, 0.1);
      --black_5: rgba(0, 0, 0, 0.05);
      --white: #ffffff;
      --black: #000000;
      }

      .auth-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--cultured-1);
      }
      .auth-card {
      background: var(--white);
      border: 2px solid var(--light-gray);
      border-radius: 14px;
      box-shadow: 0 4px 20px var(--hoockers-green_20);
      padding: 2.5rem 2.5rem 1.7rem;
      max-width: 420px;
      width: 95%;
      margin: 1.3rem 0;
      }
      .auth-title {
      color: var(--hoockers-green);
      font-weight: var(--fw-800);
      font-size: var(--fs-4);
      text-align: center;
      font-family: var(--ff-urbanist);
      }
      .auth-subtitle {
      color: var(--spanish-gray);
      text-align: center;
      margin-bottom: 1rem;
      font-family: var(--ff-urbanist);
      }
      .btn-auth {
      background: var(--hoockers-green);
      color: var(--white);
      border: 2px solid var(--hoockers-green_20);
      border-radius: 25px;
      font-weight: var(--fw-700);
      transition: all 0.2s;
      width: 100%;
      font-size: var(--fs-7);
      }
      .btn-auth:hover {
      background: var(--black);
      color: var(--pale-spring-bud);
      border-color: var(--black);
      }
      .auth-link {
      margin-top: 1.2rem;
      text-align: center;
      color: var(--hoockers-green);
      font-family: var(--ff-urbanist);
      }
      .auth-link a {
      color: var(--hoockers-green);
      font-weight: bold;
      text-decoration: none;
      }
      .auth-link a:hover {
      color: var(--black);
      }
      .btn-back {
      margin: 20px 0 0 20px;
      background: transparent;
      border: 2px solid var(--hoockers-green);
      color: var(--hoockers-green);
      padding: 0.3rem 1.1rem;
      border-radius: 25px;
      font-weight: var(--fw-600);
      font-size: var(--fs-8);
      transition: all 0.3s;
      display: inline-block;
      text-decoration: none;
      }
      .btn-back:hover {
      background: var(--hoockers-green);
      color: var(--white);
      border-color: var(--black);
      }
      .input-wrapper label {
      color: var(--spanish-gray);
      font-size: var(--fs-8);
      font-family: var(--ff-urbanist);
      }
      .input-wrapper input {
      width: 100%;
      padding: .8rem 1rem;
      border: 1px solid var(--light-gray);
      border-radius: var(--radius-3);
      background: var(--cultured-2);
      font-size: var(--fs-7);
      margin-bottom: 2px;
      transition: border .2s;
      font-family: var(--ff-urbanist);
      }
      .input-wrapper input:focus {
      border-color: var(--hoockers-green);
      outline: none;
      }

      
      .alert-auth {
      background: var(--pale-spring-bud);
      color: var(--black);
      border-radius: var(--radius-3);
      padding: 10px 18px;
      margin-bottom: 15px;
      font-weight: var(--fw-600);
      font-size: var(--fs-8);
      border-left: 4px solid var(--hoockers-green);
      }
      
      .password-rules {
      background: var(--cultured-2);
      border: 1px solid var(--light-gray);
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      }
      
      .password-rules div {
      margin: 8px 0;
      font-size: 14px;
      font-weight: 500;
      transition: color 0.3s ease;
      }
      
      .password-rules .invalid {
      color: #dc3545;
      }
      
      .password-rules .valid {
      color: #28a745;
      }
      
      .password-rules div::before {
      /* content: "● "; */
      margin-right: 5px;
      }

      /* Remove Bootstrap validation icons */
      .was-validated .form-control:valid,
      .form-control.is-valid {
        border-color: #28a745;
        background-image: none;
        padding-right: 0.75rem;
      }

      .was-validated .form-control:invalid,
      .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
        padding-right: 0.75rem;
      }

      /* Keep padding-right for password fields with eye toggle */
      #password,
      #confirmpassword {
        padding-right: 45px !important;
      }

      @media (max-width: 500px) {
      .auth-card { padding: 10% 5%; }
      }

  </style>
</head>
<body>
  <div class="back-home">
    <a href="home.php" class="btn btn-outline-success m-3 rounded-pill px-3">Back to Home</a>
  </div>


  <div class="auth-container">
    <div class="auth-card">
      
        <?php
        if (isset($_GET['error']) && !empty($_GET['error'])): ?>
          <div class="container">
            <div class="alert alert-danger text-center rounded-pill py-2 mb-3" role="alert">
              <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
          </div>
        <?php endif; ?>
      <h2 class="auth-title mb-3">Create Account</h2><br>
      <!-- <p class="text-muted text-center mb-4">Join us today</p> -->

      <form action="../process/signup-process.php" method="POST" class="needs-validation" novalidate>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="firstname" placeholder=""
              pattern="^[A-Za-z\s]+$" required>
            <div class="invalid-feedback">Letters and spaces only.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="lastname" placeholder=""
              pattern="^[A-Za-z\s]+$" required>
            <div class="invalid-feedback">Letters and spaces only.</div>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label">Username</label>
          <input type="text" class="form-control" name="username" required>
          <div class="invalid-feedback">Enter a valid username.</div>
        </div>

        <div class="mt-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email"
            pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
            placeholder="example@gmail.com" required>
          <div class="invalid-feedback">Enter a valid email address.</div>
        </div>

        <div class="mt-3">
          <label class="form-label">Password Requirements:</label>
          <div class="password-rules">
            <div id="length" class="invalid">• Minimum 6 characters</div>
            <div id="upper" class="invalid">• At least one uppercase letter</div>
            <div id="lower" class="invalid">• At least one lowercase letter</div>
            <div id="number" class="invalid">• At least one number</div>
          </div>
        <div>
          
        <label class="form-label">Password</label>
        <div style="position: relative;">
          <input type="password" class="form-control" id="password" name="password"
            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{6,12}$"
            placeholder="Enter password" required style="padding-right: 45px;">
          <span onclick="togglePassword('password', 'eyeIcon1')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--spanish-gray); user-select: none;">
            <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <!-- Default: Eye-slash (password hidden) -->
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
          </span>
        </div>
        <div class="invalid-feedback">Follow password rules above.</div>
        </div>

        <div class="mt-3">
          <label class="form-label">Confirm Password</label>
          <div style="position: relative;">
            <input type="password" class="form-control" id="confirmpassword" name="confirm_password"
              placeholder="Confirm password" required style="padding-right: 45px;">
            <span onclick="togglePassword('confirmpassword', 'eyeIcon2')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--spanish-gray); user-select: none;">
              <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <!-- Default: Eye-slash (password hidden) -->
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </span>
          </div>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>


        </div>

        <button type="submit" class="btn btn-auth mt-4">Sign Up</button>
      </form>

      <p class="auth-link">Already have an account? <a href="login.php">Login</a></p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmpassword");

    const rules = {
      length: document.getElementById("length"),
      upper: document.getElementById("upper"),
      lower: document.getElementById("lower"),
      number: document.getElementById("number")
    };

    password.addEventListener("input", () => {
      const val = password.value;
      validateRule(rules.length, val.length >= 6);
      validateRule(rules.upper, /[A-Z]/.test(val));
      validateRule(rules.lower, /[a-z]/.test(val));
      validateRule(rules.number, /\d/.test(val));
    });

    function validateRule(el, valid) {
      el.className = valid ? "valid" : "invalid";
    }

    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          const pwd = password.value;
          const confirm = confirmPassword.value;
          if (!form.checkValidity() || pwd !== confirm) {
            event.preventDefault();
            event.stopPropagation();
            confirmPassword.setCustomValidity(pwd !== confirm ? "Passwords do not match" : "");
          } else {
            confirmPassword.setCustomValidity("");
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>

  <script>
    function togglePassword(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(iconId);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            // Eye-off icon (with slash)
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        } else {
            passwordField.type = 'password';
            // Eye icon (normal)
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        }
    }
  </script>
</body>
</html>