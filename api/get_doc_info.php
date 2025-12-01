<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$doc_code = $_GET['code'] ?? '';

if (empty($doc_code)) {
    echo json_encode(['error' => 'No code provided']);
    exit;
}

try {
    // 1. ดึงข้อมูลเอกสาร
    $stmt = $pdo->prepare("
        SELECT d.*, dt.type_name 
        FROM documents d
        LEFT JOIN document_type dt ON d.type_id = dt.type_id
        WHERE d.document_code = ?
    ");
    $stmt->execute([$doc_code]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        echo json_encode(['error' => 'Document not found']);
        exit;
    }

    // 2. ดึงประวัติ 3 รายการล่าสุด (Timeline)
    $stmt_log = $pdo->prepare("
        SELECT l.*, u.fullname 
        FROM document_status_log l
        LEFT JOIN users u ON l.action_by = u.user_id
        WHERE l.document_id = ?
        ORDER BY l.action_time DESC LIMIT 3
    ");
    $stmt_log->execute([$doc['document_id']]);
    $logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['doc' => $doc, 'logs' => $logs]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>