<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    if (isset($_SESSION['user']) &&
        $_SESSION['user']['username'] === $username &&
        $_SESSION['user']['password'] === $password) {

        $_SESSION['logged_in'] = true;

        header("Location: ../pages/protected-page.php");
        exit();
    } else {
        header("Location: ../pages/login.php?error=invalid");
        exit();
    }
} else {
    header("Location: ../pages/login.php?error=request");
    exit();
}
?>