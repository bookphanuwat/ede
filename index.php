<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เมนูหลัก - EDE System</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        /* สไตล์พิเศษสำหรับหน้าเมนูหลัก */
        .menu-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: none;
            height: 100%;
        }
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        .menu-icon-box {
            width: 80px; height: 80px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem; color: white;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <!-- Header สีเทาเรียบๆ สำหรับหน้าเมนู -->
        <div class="top-header bg-secondary bg-gradient d-flex justify-content-between align-items-center px-4 py-3 shadow-sm">
            <div class="d-flex align-items-center text-white">
                <i class="fas fa-th-large fa-lg me-3"></i>
                <h4 class="mb-0 fw-bold">เมนูหลัก (Main Menu)</h4>
            </div>
            <!-- Profile Dropdown (เหมือนหน้าอื่น) -->
            <div class="dropdown">
                <button class="btn btn-link text-white text-decoration-none dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <div class="text-end me-3 d-none d-md-block">
                        <div class="fw-bold"><?php echo $_SESSION['fullname']; ?></div>
                        <small style="opacity: 0.9;"><?php echo $_SESSION['role']; ?></small>
                    </div>
                    <div class="bg-white text-dark rounded-circle d-flex justify-content-center align-items-center fw-bold" style="width: 40px; height: 40px;">
                        <?php echo mb_substr($_SESSION['fullname'], 0, 1); ?>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item text-danger" href="logout.php">ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>

        <div class="page-content bg-light">
            <div class="container py-4">
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-secondary">ยินดีต้อนรับสู่ระบบ EDE</h2>
                    <p class="text-muted">กรุณาเลือกเมนูที่ต้องการใช้งาน</p>
                </div>

                <div class="row g-4 justify-content-center">
                    
                    <!-- 1. Dashboard -->
                    <div class="col-md-6 col-lg-4">
                        <a href="dashboard.php" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-dashboard);">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h4 class="fw-bold text-dark">Dashboard</h4>
                                <p class="text-muted small">ดูภาพรวมสถานะเอกสาร กราฟสรุปผล</p>
                            </div>
                        </a>
                    </div>

                    <!-- 2. ลงทะเบียน -->
                    <div class="col-md-6 col-lg-4">
                        <a href="register.php" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-register);">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ลงทะเบียน</h4>
                                <p class="text-muted small">สร้างเอกสารใหม่ ออกเลข และพิมพ์ใบปะหน้า</p>
                            </div>
                        </a>
                    </div>

                    <!-- 3. ติดตามเอกสาร -->
                    <div class="col-md-6 col-lg-4">
                        <a href="tracking.php" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-tracking);">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ติดตามเอกสาร</h4>
                                <p class="text-muted small">ค้นหา ตรวจสอบสถานะ และดู Timeline</p>
                            </div>
                        </a>
                    </div>

                    <!-- 4. รายงาน -->
                    <div class="col-md-6 col-lg-4">
                        <a href="report.php" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-report);">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4 class="fw-bold text-dark">รายงาน</h4>
                                <p class="text-muted small">สรุปยอดประจำเดือน Export ข้อมูล</p>
                            </div>
                        </a>
                    </div>

                    <!-- 5. ตั้งค่า -->
                    <div class="col-md-6 col-lg-4">
                        <a href="settings.php" class="text-decoration-none">
                            <div class="card menu-card shadow-sm rounded-4 p-4 text-center">
                                <div class="menu-icon-box shadow-sm" style="background: var(--color-settings);">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h4 class="fw-bold text-dark">ตั้งค่าระบบ</h4>
                                <p class="text-muted small">จัดการผู้ใช้งาน สิทธิ์ และข้อมูลพื้นฐาน</p>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>