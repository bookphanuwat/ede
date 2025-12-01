<?php
// api/save_document.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. สร้างรหัสเอกสาร (Custom Logic) เช่น EDE-YYYYMMDD-RUNNING
        // ในที่นี้ใช้ timestamp + random แบบง่ายไปก่อน
        $doc_code = "EDE-" . date("Ymd") . "-" . rand(1000, 9999);

        // 2. บันทึกลงฐานข้อมูล (Prepared Statement)
        $stmt = $pdo->prepare("INSERT INTO documents (document_code, title, type_id, reference_no, sender_name, receiver_name, created_by, current_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Registered')");
        $stmt->execute([
            $doc_code,
            $_POST['title'],
            $_POST['type_id'],
            $_POST['reference_no'],
            $_POST['sender_name'],
            $_POST['receiver_name'],
            $_POST['created_by']
        ]);
        
        $document_id = $pdo->lastInsertId();

        // 3. บันทึก Log แรก
        $stmtLog = $pdo->prepare("INSERT INTO document_status_log (document_id, status, action_by) VALUES (?, 'Registered', ?)");
        $stmtLog->execute([$document_id, $_POST['created_by']]);

        // 4. Redirect ไปหน้าพิมพ์ใบปะหน้าพร้อมรหัสเอกสาร
        header("Location: ../print_cover.php?code=" . $doc_code);
        exit;

    } catch (Exception $e) {
        // Handle Error (ควรบันทึก log ไฟล์ จริงๆ)
        echo "Error: " . $e->getMessage();
    }
}
?>