<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SINH_VIEN') {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$taiKhoanId = (string)($_SESSION['user_id'] ?? '');
$sinhVienId = '';
$error = '';

if (!$pdo) {
    $error = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->prepare('SELECT sinh_vien_id, msv, ho_ten FROM sinh_vien WHERE sinh_vien_id = :id OR tai_khoan_id = :id LIMIT 1');
    $stmt->execute(['id' => $taiKhoanId]);
    $sv = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sv) {
        $sinhVienId = (string)$sv['sinh_vien_id'];
    } else {
        $error = 'Khong tim thay sinh vien trong session hien tai.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xuat phieu diem</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Xuat phieu diem PDF</h1>
                    <p class="muted-text">Tai phieu diem ca nhan dang nhap duoi dang file PDF.</p>
                </div>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div id="status-box" class="alert-info" style="margin-bottom:12px;">San sang xuat phieu diem.</div>

            <div class="card" style="max-width:620px;">
                <div class="form-grid" style="grid-template-columns:1fr auto;align-items:end;">
                    <div>
                        <div class="muted-text">Sinh vien ID</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars($sinhVienId ?: '--'); ?></div>
                    </div>
                    <button id="btn-export" type="button" class="btn-primary" <?php echo $sinhVienId ? '' : 'disabled'; ?>>Xuat PDF</button>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const sinhVienId = '<?php echo htmlspecialchars($sinhVienId); ?>';
        const statusBox = document.getElementById('status-box');

        document.getElementById('btn-export').addEventListener('click', async () => {
            if (!sinhVienId) {
                statusBox.className = 'alert-danger';
                statusBox.textContent = 'Khong tim thay sinh_vien_id de xuat PDF.';
                return;
            }

            try {
                const res = await fetch(`${PY_API}/api/bao-cao/bang-diem-pdf/${encodeURIComponent(sinhVienId)}`);

                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || 'Khong the xuat PDF');
                }

                const blob = await res.blob();
                const fileUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = `phieu_diem_${sinhVienId}.pdf`;
                link.click();
                URL.revokeObjectURL(fileUrl);

                statusBox.className = 'alert-info';
                statusBox.textContent = 'Da xuat phieu diem thanh cong.';
            } catch (err) {
                statusBox.className = 'alert-danger';
                statusBox.textContent = err.message;
            }
        });
    </script>
</body>

</html>