<?php
require_once __DIR__ . '/config.php';

// Edit these if you want a different admin username/password
$adminUsername = 'admin';
$newPassword = 'admin123';

try {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Check if user exists
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $adminUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $db->prepare('UPDATE users SET password = :password, role = :role WHERE id = :id');
        $update->execute(['password' => $hash, 'role' => 'admin', 'id' => $user['id']]);
        echo "Updated password for existing user '{$adminUsername}' (id={$user['id']}).\n";
    } else {
        $insert = $db->prepare('INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (:username, :email, :password, :first_name, :last_name, :role)');
        $insert->execute([
            'username' => $adminUsername,
            'email' => 'admin@valora.local',
            'password' => $hash,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin'
        ]);
        echo "Created new admin user '{$adminUsername}'.\n";
    }

    echo "New password is: {$newPassword}\n";
    echo "Bcrypt hash stored in DB: {$hash}\n";
    echo "You can now test login via CLI or browser.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>