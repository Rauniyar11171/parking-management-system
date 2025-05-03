<?php
require_once 'config/db.php';

try {
    // Read the SQL file
    $sql = file_get_contents('database.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
