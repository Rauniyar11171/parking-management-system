<?php
require_once 'config/db.php';

$stmt = $pdo->query("SELECT id, name, email FROM users");
$users = $stmt->fetchAll();

echo "Number of users: " . count($users) . "\n";
if (count($users) > 0) {
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}\n";
    }
}
?>
