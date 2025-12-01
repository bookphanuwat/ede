<?php 
session_start();
require_once 'config/db.php';

// เตรียมตัวแปร
$user_data = null;
$is_edit = false;
$roles = [];

try {
    if (isset($pdo)) {
        // 1. ดึงรายการสิทธิ์ (Roles) ทั้งหมดจากฐานข้อมูล มาใส่ Dropdown
        $stmt_roles = $pdo->query("SELECT * FROM roles ORDER BY role_id ASC");
        $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

        // 2. ถ้ามี ID ส่งมา ให้ดึงข้อมูลผู้ใช้นั้นมาแสดง (โหมดแก้ไข)
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data) {
                $is_edit = true;
            }
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'แก้ไขผู้ใช้งาน' : 'เพิ่มผู้ใช้งานใหม่'; ?></title>
    <!-- Bootstrap & CSS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <!-- เรียกใช้ Header กลาง -->
        <?php 
            $page_title = $is_edit ? "แก้ไขผู้ใช้งาน" : "เพิ่มผู้ใช้งานใหม่"; 
            $header_class = "header-settings"; 
            include 'includes/topbar.php'; 
        ?>

        <div class="page-content">
            <h5 class="mb-5 fw-bold text-secondary">**จัดการข้อมูลผู้ใช้งาน**</h5>

            <form action="api/save_user.php" method="POST" class="mx-auto" style="max-width: 800px;">
                <!-- ถ้าแก้ไข ต้องส่ง ID ไปด้วย -->
                <?php if ($is_edit): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_data['user_id']; ?>">
                <?php endif; ?>

                <!-- 1. Username (ห้ามแก้ถ้ามีอยู่แล้ว) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">Username</label></div>
                    <div class="col-md-9">
                        <input type="text" name="username" class="form-control custom-input" 
                               value="<?php echo $user_data ? htmlspecialchars($user_data['username']) : ''; ?>" 
                               <?php echo $is_edit ? 'readonly style="background-color: #e9ecef !important;"' : 'required'; ?>>
                        <?php if($is_edit): ?>
                            <small class="text-muted ms-3">*Username ไม่สามารถแก้ไขได้</small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Password (เว้นว่างได้ถ้าแก้ไข) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">Password</label></div>
                    <div class="col-md-9">
                        <input type="password" name="password" class="form-control custom-input" 
                               placeholder="<?php echo $is_edit ? 'กรอกเฉพาะเมื่อต้องการเปลี่ยนรหัสผ่านใหม่' : 'กำหนดรหัสผ่าน...'; ?>"
                               <?php echo $is_edit ? '' : 'required'; ?>>
                    </div>
                </div>

                <!-- 3. ชื่อ-สกุล -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">ชื่อ-สกุล</label></div>
                    <div class="col-md-9">
                        <input type="text" name="fullname" class="form-control custom-input" 
                               value="<?php echo $user_data ? htmlspecialchars($user_data['fullname']) : ''; ?>" required>
                    </div>
                </div>

                <!-- 4. แผนก -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">แผนก/ฝ่าย</label></div>
                    <div class="col-md-9">
                        <input type="text" name="department" class="form-control custom-input" 
                               value="<?php echo $user_data ? htmlspecialchars($user_data['department'] ?? '') : ''; ?>">
                    </div>
                </div>

                <!-- 5. สิทธิ์การใช้งาน (Dropdown จาก DB) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">สิทธิ์การใช้งาน</label></div>
                    <div class="col-md-9">
                        <select name="role_id" class="form-select custom-input">
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['role_id']; ?>" 
                                        <?php echo ($user_data && $user_data['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback กรณีดึง DB ไม่ได้ -->
                                <option value="2">User</option>
                                <option value="1">Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- ปุ่มกด -->
                <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                    <a href="settings.php" class="btn btn-danger rounded-pill px-4 me-2 shadow-sm text-decoration-none">
                        <i class="fas fa-times me-2"></i>ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-success rounded-pill px-5 shadow-sm" style="background-color: #00E676; border:none; color: #000; font-weight:600;">
                        <i class="fas fa-save me-2"></i><?php echo $is_edit ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>