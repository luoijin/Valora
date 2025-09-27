<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname  = htmlspecialchars($_POST['lastname']);
    $username  = htmlspecialchars($_POST['username']);
    $email     = htmlspecialchars($_POST['email']);
    $password  = htmlspecialchars($_POST['password']);

    $_SESSION['user'] = [
        'firstname' => $firstname,
        'lastname'  => $lastname,
        'username'  => $username,
        'email'     => $email,
        'password'  => $password
    ];

    $_SESSION['logged_in'] = false;

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Registration Successful</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { background-color: black; color: white; }
            .success-card { background-color: #1e1e1e; border-radius: 12px; }
        </style>
    </head>
    <body>
        <div class='d-flex justify-content-center align-items-center' style='min-height: 100vh;'>
            <div class='success-card p-5 text-center shadow-lg' style='width: 400px;'>
                <div class='text-success mb-3'>
                    <i class='bi bi-check-circle' style='font-size: 3rem;'>✓</i>
                </div>
                <h2 class='text-success mb-3'>Registration Successful!</h2>
                <p class='mb-4'>You have successfully registered. You will be redirected to the login page in <span id='countdown'>3</span> seconds.</p>
                <a href='../pages/login.php' class='btn btn-primary'>Go to Login Now</a>
            </div>
        </div>
        
        <script>
            let countdown = 3;
            const countdownElement = document.getElementById('countdown');
            
            const timer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = '../pages/login.php?success=registered';
                }
            }, 1000);
        </script>
    </body>
    </html>";
    exit();
} else {
    header("Location: ../pages/signup.php?error=invalidrequest");
    exit();
}
?>