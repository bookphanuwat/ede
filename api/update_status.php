<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_code = $_POST['doc_code'] ?? '';
    $new_status = $_POST['status'] ?? 'Received';
    $next_receiver = $_POST['receiver_name'] ?? ''; // รับชื่อผู้รับคนต่อไป
    $line_user_id = $_POST['line_user_id'] ?? '';

    if (empty($doc_code)) die("Error: No Code");

    try {
        $pdo->beginTransaction();

        // 1. หา ID เอกสาร
        $stmt = $pdo->prepare("SELECT document_id FROM documents WHERE document_code = ?");
        $stmt->execute([$doc_code]);
        $doc = $stmt->fetch();
        if (!$doc) throw new Exception("ไม่พบเอกสาร");
        $doc_id = $doc['document_id'];

        // 2. อัปเดตสถานะหลัก และ เปลี่ยนชื่อผู้รับ (ถ้ามีการกรอกมา)
        $sql = "UPDATE documents SET current_status = ?";
        $params = [$new_status];

        if (!empty($next_receiver)) {
            $sql .= ", receiver_name = ?";
            $params[] = $next_receiver;
        }
        
        $sql .= " WHERE document_id = ?";
        $params[] = $doc_id;

        $stmtUpdate = $pdo->prepare($sql);
        $stmtUpdate->execute($params);

        // 3. บันทึก Log
        // หา User ID จาก LINE ID (ถ้ามี) หรือใส่ NULL
        $u_stmt = $pdo->prepare("SELECT user_id FROM users WHERE line_user_id = ?");
        $u_stmt->execute([$line_user_id]);
        $user = $u_stmt->fetch();
        $action_by = $user ? $user['user_id'] : NULL;

        $log_note = !empty($next_receiver) ? "ส่งต่อให้: $next_receiver" : "อัปเดตสถานะ";

        $stmtLog = $pdo->prepare("INSERT INTO document_status_log (document_id, status, action_by, line_user_id_action, location_note) VALUES (?, ?, ?, ?, ?)");
        $stmtLog->execute([$doc_id, $new_status, $action_by, $line_user_id, $log_note]);

        $pdo->commit();
        echo "Success";

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>