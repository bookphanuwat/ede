<?php 
session_start();
require_once 'config/db.php';

// --- ส่วนดึงข้อมูลผู้ใช้งานจากฐานข้อมูล ---
$users = [];
try {
    if (isset($pdo)) {
        // ดึงข้อมูล users และ join กับ roles เพื่อเอาชื่อสิทธิ์
        // เรียงตาม ID ล่าสุดขึ้นก่อน
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.role_id 
                ORDER BY u.user_id ASC";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // กรณีเกิด Error (เช่นยังไม่สร้างตาราง)
    $error_msg = "ไม่สามารถดึงข้อมูลได้: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตั้งค่า - EDE System</title>
    <!-- Bootstrap 5 Bundle (JS สำหรับ Dropdown) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <!-- Header สีม่วง -->
        <?php 
            $page_title = "ตั้งค่าระบบ"; 
            $header_class = "header-settings"; 
            include 'includes/topbar.php'; 
        ?>

        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-secondary mb-0">**⚙️ จัดการผู้ใช้งาน**</h5>
                <a href="settings_form.php" class="btn btn-success rounded-pill px-4 shadow-sm" style="background-color: #00E676; border:none; color:black; font-weight: bold;">
                    <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้งานใหม่
                </a>
            </div>

            <!-- แสดง Error ถ้ามี -->
            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <div class="table-responsive rounded-4 shadow-sm border">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light text-center border-bottom">
                        <tr>
                            <th class="py-3 bg-light text-secondary">ชื่อ-สกุล</th>
                            <th class="py-3 bg-light text-secondary">แผนก</th>
                            <th class="py-3 bg-light text-secondary">สิทธิ์</th>
                            <th class="py-3 bg-light text-secondary" width="150">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <!-- ชื่อ-สกุล (แสดง Username ตัวเล็กๆ ด้วย) -->
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo htmlspecialchars($user['fullname']); ?></div>
                                        <div class="small text-muted"><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['username']); ?></div>
                                    </td>
                                    
                                    <!-- แผนก -->
                                    <td class="text-center text-secondary">
                                        <?php echo !empty($user['department']) ? htmlspecialchars($user['department']) : '-'; ?>
                                    </td>
                                    
                                    <!-- สิทธิ์ (Badge สีต่างกันตาม Role) -->
                                    <td class="text-center">
                                        <?php 
                                            $badge_color = 'bg-secondary'; // Default
                                            if (stripos($user['role_name'], 'admin') !== false) $badge_color = 'bg-primary';
                                            if (stripos($user['role_name'], 'staff') !== false) $badge_color = 'bg-info text-dark';
                                        ?>
                                        <span class="badge rounded-pill <?php echo $badge_color; ?> px-3 py-2">
                                            <?php echo htmlspecialchars($user['role_name']); ?>
                                        </span>
                                    </td>
                                    
                                    <!-- ปุ่มจัดการ -->
                                    <td class="text-center">
                                        <!-- ปุ่มแก้ไข (ส่ง id ไปด้วย) -->
                                        <a href="settings_form.php?id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-sm btn-light rounded-pill border me-1 text-primary" 
                                           title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- ปุ่มลบ (มีแจ้งเตือนก่อนลบ) -->
                                        <a href="javascript:void(0);" 
                                           onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                           class="btn btn-sm btn-light rounded-pill border text-danger" 
                                           title="ลบ">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 opacity-50"></i><br>
                                    ยังไม่มีข้อมูลผู้ใช้งานในระบบ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Mockup ไว้ก่อน ถ้าข้อมูลเยอะค่อยเขียนเพิ่ม) -->
            <nav class="mt-4 d-flex justify-content-center">
                <ul class="pagination pagination-sm">
                    <li class="page-item disabled"><a class="page-link rounded-start-pill border-0 bg-light" href="#">ก่อนหน้า</a></li>
                    <li class="page-item active"><a class="page-link border-0" style="background: var(--color-settings);" href="#">1</a></li>
                    <li class="page-item disabled"><a class="page-link rounded-end-pill border-0 bg-light" href="#">ถัดไป</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Script สำหรับปุ่มลบ -->
<script>
function confirmDelete(userId, username) {
    if (confirm("คุณต้องการลบผู้ใช้ '" + username + "' ใช่หรือไม่?\nการกระทำนี้ไม่สามารถเรียกคืนได้")) {
        // ส่งไปที่ API ลบ (ต้องสร้างไฟล์ api/delete_user.php เพิ่มถ้าต้องการให้ลบจริง)
        window.location.href = 'api/delete_user.php?id=' + userId;
    }
}
</script>

</body>
</html>