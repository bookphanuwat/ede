<?php 
session_start();
require_once 'config/db.php';

// --- ฟังก์ชันสร้าง UUID ---
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}
$auto_ref_no = gen_uuid();

// --- เตรียมข้อมูล ---
$types = [];
$users_list = []; // เก็บรายชื่อผู้ใช้ทั้งหมด

try { 
    if(isset($pdo)) { 
        // 1. ดึงประเภทเอกสาร
        $stmt = $pdo->query("SELECT * FROM document_type"); 
        $types = $stmt->fetchAll(); 

        // 2. ดึงรายชื่อผู้ใช้ทั้งหมด (เอามาทำตัวเลือก ผู้รับ/ผู้ส่ง)
        $stmt_users = $pdo->query("SELECT fullname, department FROM users ORDER BY fullname ASC");
        $users_list = $stmt_users->fetchAll();
    } 
} catch (PDOException $e) {}

// ดึงชื่อคน Login มาเป็นค่าเริ่มต้นผู้ส่ง
$current_user_name = $_SESSION['fullname'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลงทะเบียน</title>
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
            $page_title = "ลงทะเบียน"; 
            $header_class = "header-register"; 
            include 'includes/topbar.php'; 
        ?>
        <div class="page-content">
            <h5 class="mb-5 fw-bold text-secondary">**ลงทะเบียนเอกสารใหม่**</h5>
            
            <form action="api/save_document.php" method="POST" class="mx-auto" style="max-width: 900px;">
                <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id'] ?? 0; ?>">
                
                <!-- 1. ชื่อเรื่อง -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">ชื่อเรื่อง</label></div>
                    <div class="col-md-9">
                        <input type="text" name="title" required class="form-control custom-input" placeholder="ระบุชื่อเรื่อง...">
                    </div>
                </div>

                <!-- 2. เลขอ้างอิง (UUID) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">เลขอ้างอิง (UUID)</label></div>
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text border-0 bg-light rounded-start-pill ps-3 text-muted"><i class="fas fa-fingerprint"></i></span>
                            <input type="text" name="reference_no" 
                                   class="form-control custom-input rounded-end-pill bg-white text-primary fw-bold" 
                                   value="<?php echo $auto_ref_no; ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- 3. ประเภทเอกสาร -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">ประเภท</label></div>
                    <div class="col-md-9">
                        <select name="type_id" class="form-select custom-input">
                            <?php if (!empty($types)): foreach ($types as $type): ?>
                                <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                            <?php endforeach; else: ?>
                                <option value="1">หนังสือภายนอก</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- 4. ผู้ส่ง (Auto Fill คน Login + Datalist) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">ผู้ส่ง</label></div>
                    <div class="col-md-9">
                        <input type="text" name="sender_name" required class="form-control custom-input" 
                               value="<?php echo htmlspecialchars($current_user_name); ?>" 
                               list="userList" placeholder="ระบุชื่อผู้ส่ง...">
                    </div>
                </div>

                <!-- 5. ผู้รับ (Datalist) -->
                <div class="row mb-4 align-items-center">
                    <div class="col-md-3 text-md-end"><label class="fw-bold text-secondary">ผู้รับ</label></div>
                    <div class="col-md-9">
                        <input type="text" name="receiver_name" required class="form-control custom-input" 
                               list="userList" placeholder="ระบุชื่อผู้รับ...">
                    </div>
                </div>

                <!-- Datalist เก็บรายชื่อผู้ใช้ -->
                <datalist id="userList">
                    <?php foreach ($users_list as $u): ?>
                        <option value="<?php echo htmlspecialchars($u['fullname']); ?>">
                            <?php echo htmlspecialchars($u['department']); ?>
                        </option>
                    <?php endforeach; ?>
                </datalist>

                <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                    <button type="reset" class="btn btn-danger rounded-pill px-4 me-2">ยกเลิก</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5" style="background-color: #00E676; border:none; color:black;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>