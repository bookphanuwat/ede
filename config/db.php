<?php
// config/db.php
$host = 'localhost';
$dbname = 'ede_system';
$username = 'root'; // เปลี่ยนตามการตั้งค่าจริง
$password = '';     // เปลี่ยนตามการตั้งค่าจริง

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>