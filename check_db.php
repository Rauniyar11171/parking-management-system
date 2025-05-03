<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';

try {
    // Check if database exists
    $stmt = $pdo->query("SELECT DATABASE()");
    $db = $stmt->fetchColumn();
    echo "Current database: " . $db . "<br>";

    // Check users table
    $stmt = $pdo->query("DESCRIBE users");
    echo "Users table structure:<br>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
