<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ - EDE System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background: linear-gradient(135deg, #29B6F6 0%, #7E57C2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        .btn-login { border-radius: 50px; padding: 12px; font-weight: bold; background: #7E57C2; border: none; width: 100%; color: white; transition: 0.3s; }
        .btn-login:hover { background: #5E35B1; transform: translateY(-2px); }
        .form-control { border-radius: 50px; padding: 12px 20px; background: #f0f2f5; border: none; }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <div class="mb-4">
            <i class="fas fa-file-signature fa-4x text-primary mb-3"></i>
            <h4 class="fw-bold text-secondary">EDE System</h4>
            <p class="text-muted small">ระบบทะเบียนเอกสารอิเล็กทรอนิกส์</p>
        </div>
        
        <!-- ฟอร์มส่งข้อมูลไปที่ api/auth.php -->
        <form action="api/auth.php" method="POST">
            <div class="mb-3 text-start">
                <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" required>
            </div>
            <div class="mb-4 text-start">
                <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
            </div>
            <button type="submit" class="btn btn-login shadow-sm">
                เข้าสู่ระบบ <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </form>
        
        <div class="mt-4 pt-3 border-top">
            <small class="text-muted">© 2025 EDE System</small>
        </div>
    </div>
</body>
</html>