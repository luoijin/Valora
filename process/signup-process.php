<?php
require_once '../config.php';
require_once '../dao/crudDAO.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$first_name = $_POST['firstname'] ?? '';
$last_name = $_POST['lastname'] ?? '';
$username = $_POST['username'] ?? '';
$email = trim($_POST['email']) ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

// Validate empty fields (use the correct variable names)
if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
    $errors[] = "All fields are required.";
}

// Validate first and last names
if (preg_match('/[0-9]/', $first_name)) {
    $errors[] = "First Name should not contain numbers.";
}
if (preg_match('/[0-9]/', $last_name)) {
    $errors[] = "Last Name should not contain numbers.";
}

// Validate email format
if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
    $errors[] = "Email must be a valid @gmail.com address.";
}

// Validate password
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{6,12}$/', $password)) {
    $errors[] = "Password must be 6–12 characters long, include uppercase, lowercase, and a number. No special characters allowed.";
}

// Confirm password check
if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

// If errors exist
if (!empty($errors)) {
    $errorMsg = implode(' ', $errors);
    header('Location: ../pages/signup.php?error=' . urlencode($errorMsg));
    exit();
}

// Insert into database
$crud = new crudDAO();
$result = $crud->create($first_name, $last_name, $username, $password, $email);

if ($result) {
    header('Location: ../pages/login.php');
    exit();
} else {
    header('Location: ../pages/signup.php?error=' . urlencode('Registration failed. Email or username may already exist.'));
    exit();
}
?>