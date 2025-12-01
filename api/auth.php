<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        if (isset($pdo)) {
            // 1. ดึงข้อมูล User จากฐานข้อมูล โดยอ้างอิง Username
            // Join กับตาราง roles เพื่อเอาชื่อตำแหน่งมาด้วย (เช่น Admin, User)
            $stmt = $pdo->prepare("
                SELECT u.*, r.role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.role_id 
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. ตรวจสอบว่าพบ User หรือไม่ และรหัสผ่านถูกต้องไหม
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // --- Login สำเร็จ ---
                
                // เก็บข้อมูลลง Session เพื่อนำไปใช้ในหน้าอื่นๆ
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role_name'] ?? 'User'; // ถ้าไม่มี role ให้เป็น User
                $_SESSION['department'] = $user['department'];

                // ส่งไปหน้า Dashboard
                header("Location: ../index.php");
                exit;

            } else {
                // --- Login ไม่สำเร็จ ---
                echo "<script>
                    alert('❌ ชื่อผู้ใช้งาน หรือ รหัสผ่าน ไม่ถูกต้อง'); 
                    window.location.href='../login.php';
                </script>";
            }
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    // ถ้าเข้าไฟล์นี้โดยตรงให้ดีดกลับไปหน้า Login
    header("Location: ../login.php");
    exit;
}
?>