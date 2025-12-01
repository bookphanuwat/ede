<?php
session_start();
require_once '../config/db.php';

// ตรวจสอบสิทธิ์ (ถ้ามีระบบ Login แล้ว ควรเปิดบรรทัดนี้)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { die("Access Denied"); }

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // 1. ห้ามลบตัวเอง
        if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
            echo "<script>alert('❌ ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้'); window.location.href='../settings.php';</script>";
            exit;
        }

        // เริ่มต้น Transaction (เพื่อให้ทำงานต่อเนื่องกัน ถ้าพลาดให้ยกเลิกหมด)
        $pdo->beginTransaction();

        // 2. ปลดชื่อออกจากประวัติการสแกน (document_status_log)
        // เปลี่ยน action_by ให้เป็น NULL แทนการลบแถวประวัติทิ้ง
        $stmt = $pdo->prepare("UPDATE document_status_log SET action_by = NULL WHERE action_by = ?");
        $stmt->execute([$id]);

        // 3. ปลดชื่อออกจากเอกสารที่เคยสร้าง (documents) 
        // เปิดใช้งานส่วนนี้ เพื่อป้องกัน Error ในตาราง documents ด้วย
        $stmt = $pdo->prepare("UPDATE documents SET created_by = NULL WHERE created_by = ?");
        $stmt->execute([$id]);

        // 4. ทำการลบ User
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);

        // ยืนยันการทำงาน
        $pdo->commit();
        
        echo "<script>alert('✅ ลบข้อมูลเรียบร้อยแล้ว'); window.location.href='../settings.php';</script>";

    } catch (PDOException $e) {
        // ถ้ายกเลิกกลางคัน ให้ย้อนกลับค่าเดิม
        $pdo->rollBack();

        // เช็ค Error เฉพาะกรณี (เช่น ติด Foreign Key ของตารางอื่นอีก)
        if ($e->getCode() == '23000') {
            echo "<script>
                alert('⚠️ ไม่สามารถลบได้เนื่องจากติดข้อกำหนดของฐานข้อมูล\\n(กรุณาตั้งค่า Database ให้ action_by/created_by รองรับค่า NULL ก่อน)'); 
                window.location.href='../settings.php';
            </script>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
} else {
    header("Location: ../settings.php");
}
?>