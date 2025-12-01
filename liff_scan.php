<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สแกนเอกสาร</title>
    <!-- Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        
        /* หน้าจอกล้อง */
        #camera-screen { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; z-index: 100; display: flex; flex-direction: column; }
        #reader { flex-grow: 1; width: 100%; }
        .camera-overlay { position: absolute; bottom: 50px; left: 0; width: 100%; text-align: center; color: white; z-index: 101; pointer-events: none; }
        
        /* หน้าจอแบบฟอร์ม (ซ่อนตอนแรก) */
        #form-screen { display: none; padding: 20px; max-width: 600px; margin: 0 auto; }
        
        .doc-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #29B6F6; }
        .timeline-item { border-left: 2px solid #ddd; padding-left: 15px; margin-bottom: 10px; position: relative; }
        .timeline-item::before { content: ''; width: 10px; height: 10px; background: #ddd; border-radius: 50%; position: absolute; left: -6px; top: 5px; }
        .timeline-item.latest::before { background: #00C853; }
    </style>
</head>
<body>

    <!-- 1. หน้าจอกล้อง -->
    <div id="camera-screen">
        <div id="reader"></div>
        <div class="camera-overlay">
            <h4 class="fw-bold text-shadow">ส่องไปที่ QR Code</h4>
            <small>เพื่อดูข้อมูลหรืออัปเดตสถานะ</small>
        </div>
        <button onclick="liff.closeWindow()" class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle" style="width:40px;height:40px;z-index:200;"><i class="fas fa-times"></i></button>
    </div>

    <!-- 2. หน้าจอรายละเอียดและฟอร์ม (จะโชว์เมื่อสแกนติด) -->
    <div id="form-screen">
        <h4 class="fw-bold text-secondary mb-3"><i class="fas fa-file-alt me-2"></i>รายละเอียดเอกสาร</h4>
        
        <!-- ข้อมูลเอกสาร -->
        <div class="doc-card">
            <h5 id="docTitle" class="fw-bold text-primary mb-1">...</h5>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted" id="docCode">...</small>
                <span id="docStatus" class="badge bg-secondary rounded-pill">...</span>
            </div>
            <hr>
            <p class="mb-1 small"><strong>ผู้รับปัจจุบัน:</strong> <span id="docReceiver" class="text-success">...</span></p>
            
            <!-- Timeline ย่อ -->
            <div class="mt-3 bg-light p-2 rounded">
                <small class="text-muted fw-bold">ประวัติล่าสุด:</small>
                <div id="timelineContainer" class="mt-2" style="font-size: 0.85rem;"></div>
            </div>
        </div>

        <!-- ฟอร์มอัปเดต -->
        <div class="card border-0 shadow-sm rounded-4 p-3">
            <h5 class="fw-bold mb-3">⚡ อัปเดตสถานะ</h5>
            <form id="updateForm" onsubmit="submitStatus(event)">
                <input type="hidden" id="scanCode" name="doc_code">
                
                <!-- เลือกสถานะ -->
                <div class="mb-3">
                    <label class="form-label small text-muted">การดำเนินการ</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="status" id="st_receive" value="Received" checked onclick="toggleReceiverInput(false)">
                        <label class="btn btn-outline-success" for="st_receive"><i class="fas fa-check me-1"></i> ได้รับแล้ว</label>

                        <input type="radio" class="btn-check" name="status" id="st_send" value="Sent" onclick="toggleReceiverInput(true)">
                        <label class="btn btn-outline-warning" for="st_send"><i class="fas fa-paper-plane me-1"></i> ส่งต่อ</label>
                    </div>
                </div>

                <!-- ช่องกรอกชื่อผู้รับ (จะโชว์เฉพาะตอนเลือก "ส่งต่อ") -->
                <div class="mb-3" id="receiverInputGroup" style="display:none;">
                    <label class="form-label small text-muted">ระบุชื่อผู้รับคนต่อไป</label>
                    <input type="text" class="form-control rounded-pill bg-light" id="receiverName" placeholder="เช่น ธุรการ, การเงิน, คุณสมชาย">
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">
                        บันทึกข้อมูล
                    </button>
                    <button type="button" onclick="location.reload()" class="btn btn-outline-secondary rounded-pill py-2">
                        สแกนรายการอื่น
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const MY_LIFF_ID = "2008591805-LlbR2M99"; // LIFF ID เดิมของคุณ
        let html5QrCode;
        let userProfile = {};

        // 1. เริ่มต้นระบบ
        async function main() {
            await liff.init({ liffId: MY_LIFF_ID });
            if (liff.isLoggedIn()) {
                userProfile = await liff.getProfile();
            } else {
                liff.login();
            }
            startCamera();
        }

        // 2. เปิดกล้อง
        function startCamera() {
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: 250 },
                onScanSuccess,
                () => {}
            );
        }

        // 3. เมื่อสแกนเจอ
        function onScanSuccess(decodedText) {
            html5QrCode.stop(); // ปิดกล้อง
            fetchDocInfo(decodedText); // ดึงข้อมูล
        }

        // 4. ดึงข้อมูลเอกสารจาก API
        async function fetchDocInfo(code) {
            Swal.fire({ title: 'กำลังโหลด...', didOpen: () => Swal.showLoading() });
            
            try {
                const res = await fetch(`api/get_doc_info.php?code=${code}`);
                const data = await res.json();

                if (data.error) throw new Error(data.error);

                renderDocInfo(data.doc, data.logs);
                Swal.close();
                
                // สลับหน้าจอ
                document.getElementById('camera-screen').style.display = 'none';
                document.getElementById('form-screen').style.display = 'block';
                document.getElementById('scanCode').value = code;

            } catch (err) {
                Swal.fire('ไม่พบข้อมูล', 'รหัสเอกสารไม่ถูกต้อง หรือไม่มีในระบบ', 'error').then(() => location.reload());
            }
        }

        // 5. แสดงข้อมูลบนหน้าจอ
        function renderDocInfo(doc, logs) {
            document.getElementById('docTitle').innerText = doc.title;
            document.getElementById('docCode').innerText = doc.document_code;
            document.getElementById('docStatus').innerText = doc.current_status;
            document.getElementById('docReceiver').innerText = doc.receiver_name || '-';

            // สร้าง Timeline
            let html = '';
            logs.forEach((log, index) => {
                const cls = index === 0 ? 'latest text-dark fw-bold' : 'text-muted';
                html += `<div class="timeline-item ${index === 0 ? 'latest' : ''}">
                            <div class="${cls}">${log.status}</div>
                            <small class="text-secondary">${log.location_note || ''} โดย ${log.fullname || 'Unknown'}</small>
                         </div>`;
            });
            document.getElementById('timelineContainer').innerHTML = html;
        }

        // 6. ควบคุมการแสดงช่องกรอกชื่อ
        function toggleReceiverInput(show) {
            const input = document.getElementById('receiverInputGroup');
            input.style.display = show ? 'block' : 'none';
            if(show) document.getElementById('receiverName').focus();
        }

        // 7. บันทึกข้อมูล
        async function submitStatus(e) {
            e.preventDefault();
            const code = document.getElementById('scanCode').value;
            const status = document.querySelector('input[name="status"]:checked').value;
            const receiver = document.getElementById('receiverName').value;

            // ถ้าเลือกส่งต่อ ต้องกรอกชื่อคนรับ
            if (status === 'Sent' && !receiver.trim()) {
                Swal.fire('กรุณาระบุ', 'ชื่อผู้รับเอกสารคนต่อไป', 'warning');
                return;
            }

            Swal.fire({ title: 'กำลังบันทึก...', didOpen: () => Swal.showLoading() });

            try {
                const formData = new FormData();
                formData.append('doc_code', code);
                formData.append('status', status);
                formData.append('receiver_name', receiver);
                formData.append('line_user_id', userProfile.userId || '');

                const res = await fetch('api/update_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (await res.text() === 'Success') {
                    await Swal.fire('สำเร็จ', 'อัปเดตสถานะเรียบร้อยแล้ว', 'success');
                    liff.closeWindow();
                } else {
                    throw new Error('Server Error');
                }
            } catch (err) {
                Swal.fire('Error', 'บันทึกไม่สำเร็จ', 'error');
            }
        }

        main();
    </script>
</body>
</html>