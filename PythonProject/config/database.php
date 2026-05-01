<?php 
define ('DATABASE_HOST', 'localhost');
define ('DATABASE_NAME', 'project_python');
define ('DATABASE_USER', 'root');
define ('DATABASE_PASSWORD', '0835072866');

function getDatabaseConnection() {
    $dsn = 'mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DATABASE_USER, DATABASE_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        return null;
    }
}

?>