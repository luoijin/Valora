<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/dao/crudDAO.php';

$username = 'admin';
$password = 'admin123'; // change if you used a different admin password

$crud = new crudDAO();
$user = $crud->login($username, $password);

if ($user) {
    echo "LOGIN OK\n";
    echo "User ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Role: " . ($user['role'] ?? 'n/a') . "\n";
    echo "Stored hash: " . $user['password'] . "\n";
    echo "password_verify result: ";
    var_export(password_verify($password, $user['password']));
    echo "\n";
} else {
    echo "LOGIN FAILED\n";
}

?>