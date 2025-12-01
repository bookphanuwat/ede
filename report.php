<?php 
session_start();
require_once 'config/db.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// --- 1. จัดการตัวกรองวันที่ ---
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // เริ่มต้น: วันที่ 1 ของเดือนนี้
$end_date   = $_GET['end_date']   ?? date('Y-m-t');  // สิ้นสุด: วันสุดท้ายของเดือนนี้

// --- 2. ดึงข้อมูลสรุปจากฐานข้อมูล ---
$report_data = [];

try {
    if (isset($pdo)) {
        // 2.1 หาแผนกทั้งหมดที่มีในระบบ
        $stmt_dept = $pdo->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != ''");
        $departments = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);

        foreach ($departments as $dept) {
            // A. นับยอด "ส่งออก" (Sent)
            $sql_sent = "SELECT COUNT(*) FROM documents d 
                         JOIN users u ON d.created_by = u.user_id 
                         WHERE u.department = ? 
                         AND DATE(d.created_at) BETWEEN ? AND ?";
            $stmt_sent = $pdo->prepare($sql_sent);
            $stmt_sent->execute([$dept, $start_date, $end_date]);
            $sent_count = $stmt_sent->fetchColumn();

            // B. นับยอด "รับเข้า" (Received)
            $sql_recv = "SELECT COUNT(*) FROM documents d 
                         JOIN users u ON d.receiver_name = u.fullname 
                         WHERE u.department = ? 
                         AND DATE(d.created_at) BETWEEN ? AND ?";
            $stmt_recv = $pdo->prepare($sql_recv);
            $stmt_recv->execute([$dept, $start_date, $end_date]);
            $recv_count = $stmt_recv->fetchColumn();

            // เก็บข้อมูลลง Array
            $report_data[] = [
                'department' => $dept,
                'sent' => $sent_count,
                'received' => $recv_count
            ];
        }
    }
} catch (PDOException $e) {
    $error_msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานสรุปผล - EDE System</title>
    <!-- CSS & Scripts -->
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
            $page_title = "รายงานสรุปผล"; 
            $header_class = "header-report"; 
            include 'includes/topbar.php'; 
        ?>

        <div class="page-content">
            <h5 class="mb-4 fw-bold text-secondary">**📊 สรุปการรับ-ส่งเอกสารตามหน่วยงาน**</h5>

            <!-- ส่วนตัวกรองวันที่ -->
            <form method="GET" action="report.php" class="row justify-content-center mb-5">
                <div class="col-md-9 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 bg-light p-3 rounded-pill shadow-sm border">
                        <span class="fw-bold text-secondary"><i class="far fa-calendar-alt"></i> ช่วงเวลา:</span>
                        <input type="date" name="start_date" class="form-control rounded-pill border-0 custom-input py-2" style="max-width: 160px;" value="<?php echo $start_date; ?>">
                        <span class="text-muted">ถึง</span>
                        <input type="date" name="end_date" class="form-control rounded-pill border-0 custom-input py-2" style="max-width: 160px;" value="<?php echo $end_date; ?>">
                        <button type="submit" class="btn btn-danger rounded-circle shadow-sm" style="width: 40px; height: 40px;"><i class="fas fa-search"></i></button>
                        <a href="report.php" class="btn btn-secondary rounded-circle shadow-sm" style="width: 40px; height: 40px; display:flex; align-items:center; justify-content:center;" title="ค่าเริ่มต้น"><i class="fas fa-sync-alt"></i></a>
                    </div>
                </div>
            </form>

            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger text-center"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- ตารางแสดงผล (เพิ่ม ID เพื่อใช้อ้างอิงตอน Export) -->
            <h6 class="text-center fw-bold mb-3 text-secondary">
                | ข้อมูลระหว่าง: <span class="text-primary"><?php echo date('d/m/Y', strtotime($start_date)); ?></span> 
                ถึง <span class="text-primary"><?php echo date('d/m/Y', strtotime($end_date)); ?></span> |
            </h6>
            
            <div class="table-responsive rounded-4 shadow-sm border mx-auto bg-white" style="max-width: 900px;">
                <table id="reportTable" class="table table-hover mb-0 text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 bg-light text-secondary">แผนก / หน่วยงาน</th>
                            <th class="py-3 bg-light text-success">📤 ส่งออก (รายการ)</th>
                            <th class="py-3 bg-light text-primary">📥 รับเข้า (รายการ)</th>
                            <th class="py-3 bg-light text-secondary">รวมทั้งหมด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($report_data) > 0): ?>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td class="text-start ps-5 fw-bold text-secondary">
                                        <?php echo htmlspecialchars($row['department']); ?>
                                    </td>
                                    <td class="text-success fw-bold" style="font-size: 1.1rem;">
                                        <?php echo number_format($row['sent']); ?>
                                    </td>
                                    <td class="text-primary fw-bold" style="font-size: 1.1rem;">
                                        <?php echo number_format($row['received']); ?>
                                    </td>
                                    <td class="text-secondary fw-bold">
                                        <?php echo number_format($row['sent'] + $row['received']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- แถวรวมยอด -->
                            <tr class="table-secondary fw-bold">
                                <td class="text-end pe-3">รวมทั้งสิ้น</td>
                                <td class="text-success"><?php echo number_format(array_sum(array_column($report_data, 'sent'))); ?></td>
                                <td class="text-primary"><?php echo number_format(array_sum(array_column($report_data, 'received'))); ?></td>
                                <td><?php echo number_format(array_sum(array_column($report_data, 'sent')) + array_sum(array_column($report_data, 'received'))); ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-5 text-muted">ไม่พบข้อมูลแผนก หรือ ยังไม่มีการทำรายการในช่วงเวลานี้</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ปุ่ม Export พร้อมฟังก์ชัน JavaScript -->
            <div class="text-end mt-4 mx-auto" style="max-width: 900px;">
                <button onclick="window.print()" class="btn btn-outline-dark border-0 fw-bold rounded-pill px-4">
                    <i class="fas fa-print me-2"></i>พิมพ์รายงาน
                </button>
                <button onclick="exportTableToExcel('reportTable', 'รายงานสรุป_EDE_<?php echo date('Ymd'); ?>')" class="btn btn-success border-0 fw-bold rounded-pill px-4 ms-2" style="background-color: #1D6F42;">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </button>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript สำหรับ Export Excel -->
<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // ตั้งชื่อไฟล์
    filename = filename?filename+'.xls':'excel_data.xls';
    
    // สร้างลิงก์ดาวน์โหลด
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // สร้าง Link ไปยังข้อมูล
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // ตั้งชื่อไฟล์ที่จะดาวน์โหลด
        downloadLink.download = filename;
        
        // จำลองการกดปุ่ม
        downloadLink.click();
    }
}
</script>

</body>
</html>