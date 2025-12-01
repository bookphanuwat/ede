<?php 
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$search_query = $_GET['search'] ?? '';
$doc_data = null;
$logs = [];

// --- ส่วนค้นหาข้อมูล ---
if (!empty($search_query)) {
    try {
        if (isset($pdo)) {
            // 1. ค้นหาเอกสาร (จากเลขทะเบียน หรือ ชื่อเรื่อง)
            $sql = "SELECT d.*, dt.type_name, u.fullname as creator_name 
                    FROM documents d 
                    LEFT JOIN document_type dt ON d.type_id = dt.type_id 
                    LEFT JOIN users u ON d.created_by = u.user_id 
                    WHERE d.document_code = ? OR d.title LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$search_query, "%$search_query%"]);
            $doc_data = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. ถ้าเจอเอกสาร ให้ดึงประวัติ (Logs) มาทำ Timeline
            if ($doc_data) {
                $sql_log = "SELECT l.*, u.fullname as actor_name 
                            FROM document_status_log l 
                            LEFT JOIN users u ON l.action_by = u.user_id 
                            WHERE l.document_id = ? 
                            ORDER BY l.action_time DESC"; // เรียงล่าสุดขึ้นก่อน
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([$doc_data['document_id']]);
                $logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) { /* Error Handling */ }
}

// Helper: แปลงสีสถานะ
function getStatusColor($status) {
    return match ($status) {
        'Received' => 'success',
        'Sent' => 'warning',
        'Registered' => 'info',
        'Late' => 'danger',
        default => 'secondary'
    };
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ติดตามเอกสาร - EDE System</title>
    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <?php 
            $page_title = "ติดตามเอกสาร"; 
            $header_class = "header-tracking"; 
            include 'includes/topbar.php'; 
        ?>

        <div class="page-content">
            <h5 class="mb-4 fw-bold text-secondary text-center">**🔍 ค้นหาและติดตามเอกสาร**</h5>

            <!-- 1. Form ค้นหา -->
            <form method="GET" action="tracking.php" class="row justify-content-center mb-5">
                <div class="col-md-8">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border p-1">
                        <span class="input-group-text border-0 bg-white ps-3 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 shadow-none" 
                               placeholder="ระบุเลขที่ทะเบียน หรือ ชื่อเรื่อง..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" 
                                style="background-color: var(--color-tracking); border:none;">
                            ค้นหา
                        </button>
                    </div>
                    <!-- ตัวเลือกเพิ่มเติม (Mockup) -->
                    <div class="text-center mt-2 text-muted small">
                        <span class="cursor-pointer me-2"><i class="fas fa-filter"></i> ตัวกรองขั้นสูง</span>
                        <span class="cursor-pointer"><i class="far fa-calendar-alt"></i> ช่วงเวลา</span>
                    </div>
                </div>
            </form>

            <!-- 2. ส่วนแสดงผลลัพธ์ -->
            <?php if ($doc_data): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto animate-fade-in" style="max-width: 900px;">
                    <!-- หัว Card: สถานะและชื่อเรื่อง -->
                    <div class="card-header border-0 p-4 d-flex justify-content-between align-items-center" 
                         style="background-color: rgba(102, 187, 106, 0.1);">
                        <div>
                            <h5 class="mb-1 text-success fw-bold">
                                <i class="far fa-file-alt me-2"></i><?php echo htmlspecialchars($doc_data['title']); ?>
                            </h5>
                            <small class="text-muted">
                                เลขทะเบียน: <strong><?php echo htmlspecialchars($doc_data['document_code']); ?></strong>
                                | ประเภท: <?php echo htmlspecialchars($doc_data['type_name'] ?? 'ทั่วไป'); ?>
                            </small>
                        </div>
                        <span class="badge rounded-pill bg-<?php echo getStatusColor($doc_data['current_status']); ?> text-uppercase px-3 py-2">
                            <?php echo htmlspecialchars($doc_data['current_status']); ?>
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <!-- รายละเอียดเอกสาร -->
                        <div class="row mb-4 bg-light rounded-3 p-3 mx-1">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">ผู้ส่ง:</small><br>
                                <strong><?php echo htmlspecialchars($doc_data['sender_name']); ?></strong>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted">ผู้รับ:</small><br>
                                <strong><?php echo htmlspecialchars($doc_data['receiver_name']); ?></strong>
                            </div>
                            <div class="col-md-12 mt-2 pt-2 border-top">
                                <small class="text-muted">สร้างเมื่อ:</small> 
                                <?php echo date('d/m/Y H:i', strtotime($doc_data['created_at'])); ?> 
                                โดย <?php echo htmlspecialchars($doc_data['creator_name'] ?? 'ระบบ'); ?>
                            </div>
                        </div>

                        <h6 class="fw-bold text-secondary mb-4 ps-2 border-start border-4 border-success">
                            &nbsp;ประวัติการดำเนินงาน (Timeline)
                        </h6>

                        <!-- Timeline -->
                        <div class="timeline">
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $index => $log): ?>
                                    <div class="timeline-item">
                                        <!-- จุดสี (ตัวแรกให้เป็นสีเขียว active) -->
                                        <div class="timeline-dot <?php echo ($index === 0) ? 'active' : ''; ?>"></div>
                                        
                                        <div class="ps-3">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="fw-bold text-dark mb-0">
                                                    <?php echo htmlspecialchars($log['status']); ?>
                                                </h6>
                                                <span class="badge bg-light text-secondary border">
                                                    <i class="far fa-clock me-1"></i>
                                                    <?php echo date('d/m/Y H:i', strtotime($log['action_time'])); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-muted small mb-1">
                                                ดำเนินการโดย: 
                                                <strong><?php echo htmlspecialchars($log['actor_name'] ?? $log['line_user_id_action'] ?? 'Unknown'); ?></strong>
                                            </p>
                                            
                                            <?php if(!empty($log['location_note'])): ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($log['location_note']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted ps-3">ยังไม่มีประวัติการอัปเดตสถานะ</p>
                            <?php endif; ?>
                        </div>
                        <!-- End Timeline -->
                    </div>
                </div>

            <?php elseif (!empty($search_query)): ?>
                <!-- กรณีค้นหาแล้วไม่เจอ -->
                <div class="text-center py-5">
                    <div class="text-muted opacity-50 mb-3">
                        <i class="fas fa-search fa-4x"></i>
                    </div>
                    <h5 class="text-secondary">ไม่พบเอกสารที่ค้นหา</h5>
                    <p class="text-muted small">กรุณาตรวจสอบเลขทะเบียน หรือคำค้นหาอีกครั้ง</p>
                </div>
            <?php else: ?>
                <!-- หน้าจอเริ่มต้น (ยังไม่ค้นหา) -->
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-4 opacity-75" alt="Search Icon">
                    <h5 class="text-secondary">กรุณาระบุคำค้นหาเพื่อติดตามสถานะ</h5>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
    /* Animation เล็กๆ ให้ดูดี */
    .animate-fade-in { animation: fadeIn 0.5s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .cursor-pointer { cursor: pointer; transition: color 0.2s; }
    .cursor-pointer:hover { color: var(--color-tracking); }
</style>

</body>
</html>