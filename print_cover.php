<?php
require 'vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

// รับรหัสเอกสาร
$doc_code = $_GET['code'] ?? 'EDE-TEST-001';

// สร้าง QR Code
$dataUri = "";
try {
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($doc_code)
        ->encoding(new Encoding('UTF-8'))
        ->size(300)
        ->margin(10)
        ->build();
    $dataUri = $result->getDataUri();
} catch (Exception $e) {
    $dataUri = ""; // กรณี Error
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบปะหน้า - <?php echo htmlspecialchars($doc_code); ?></title>
    
    <!-- ฟอนต์สารบรรณ (Sarabun) สำหรับงานราชการ -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome สำหรับปุ่ม -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        /* --- สไตล์หน้าจอปกติ (Screen) --- */
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #555; /* พื้นหลังสีเข้มเพื่อให้เห็นกระดาษชัด */
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }

        .btn-print { background-color: #007bff; color: white; }
        .btn-back  { background-color: #6c757d; color: white; }

        /* --- สไตล์กระดาษ A4 --- */
        .a4-page {
            background: white;
            width: 210mm;
            height: 297mm; /* ขนาด A4 */
            padding: 20mm; /* ขอบกระดาษ */
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            box-sizing: border-box; /* รวม padding ในขนาด */
        }

        .header-box {
            border: 3px solid #000;
            padding: 15px;
            margin-bottom: 40px;
            border-radius: 8px;
        }

        h1 { margin: 0; font-size: 28pt; line-height: 1.2; }
        p.subtitle { margin: 5px 0 0; font-size: 16pt; color: #444; }

        .doc-code {
            font-size: 42pt;
            font-weight: bold;
            margin: 40px 0 10px;
            letter-spacing: 2px;
        }

        .qr-box {
            margin: 20px auto;
            border: 1px dashed #ccc;
            display: inline-block;
            padding: 10px;
        }

        .divider {
            border-top: 2px dashed #999;
            margin: 40px 20px;
        }

        .instruction { font-size: 18pt; line-height: 1.6; }
        
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 0;
            right: 0;
            font-size: 12pt;
            color: #777;
        }

        /* --- สไตล์สำหรับเครื่องพิมพ์ (สำคัญ!) --- */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .action-bar { display: none !important; } /* ซ่อนปุ่ม */
            .a4-page {
                box-shadow: none;
                width: 100%;
                height: 100%;
                padding: 0; /* เครื่องพิมพ์มักมีขอบขาวอยู่แล้ว */
                margin: 0;
            }
            @page {
                size: A4;
                margin: 2cm; /* ตั้งขอบกระดาษระดับ Driver */
            }
        }
    </style>
</head>
<body>

    <!-- ปุ่มควบคุม (จะไม่แสดงตอนพิมพ์) -->
    <div class="action-bar">
        <a href="index.php" class="btn btn-back">
            <i class="fas fa-arrow-left" style="margin-right:8px;"></i> กลับหน้าหลัก
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print" style="margin-right:8px;"></i> สั่งพิมพ์หน้านี้
        </button>
    </div>

    <!-- ส่วนที่จะถูกพิมพ์ -->
    <div class="a4-page">
        <div class="header-box">
            <h1>ใบติดตามเอกสาร (EDE)</h1>
            <p class="subtitle">ระบบทะเบียนเอกสารอิเล็กทรอนิกส์</p>
        </div>

        <div class="doc-code"><?php echo htmlspecialchars($doc_code); ?></div>
        <p style="font-size: 14pt; color: #555;">รหัสอ้างอิงเอกสาร</p>

        <div class="qr-box">
            <?php if ($dataUri): ?>
                <img src="<?php echo $dataUri; ?>" width="350" height="350" alt="QR Code">
            <?php else: ?>
                <p style="color:red;">ไม่สามารถสร้าง QR Code ได้</p>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <div class="instruction">
            <p><strong>คำแนะนำสำหรับเจ้าหน้าที่</strong></p>
            <p>กรุณาสแกน QR Code นี้ทุกครั้ง<br>เมื่อมีการ <strong>รับ</strong> หรือ <strong>ส่งต่อ</strong> เอกสารฉบับนี้</p>
        </div>

        <div class="footer">
            พิมพ์เมื่อ: <?php echo date("d/m/Y H:i"); ?> | EDE System v1.0
        </div>
    </div>

</body>
</html>