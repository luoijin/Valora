<?php
session_start();

// Check if user is properly logged in AND has user data
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user'])) {
    // Redirect to login if not authenticated
    header("Location: login.php?error=unauthorized");
    exit();
}

// Get user data from session
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Protected Page</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: black; color: white; }
        .navbar { background-color: #1e1e1e; }
    </style>
</head>
<body>
    <?php include 'navbar-user.php'; ?>

    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?>!</h2>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
    </div>
</body>
</html>