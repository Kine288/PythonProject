<?php
session_start();
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matKhauCu = trim($_POST['mat_khau_cu'] ?? '');
    $matKhauMoi = trim($_POST['mat_khau_moi'] ?? '');
    $xacNhan = trim($_POST['xac_nhan_mat_khau'] ?? '');

    if ($matKhauCu === '' || $matKhauMoi === '' || $xacNhan === '') {
        $error = 'Vui long nhap day du thong tin.';
    } elseif ($matKhauMoi !== $xacNhan) {
        $error = 'Mat khau moi va xac nhan mat khau khong trung khop.';
    } else {
        $apiUrl = rtrim(PYTHON_API_URL, '/') . '/api/auth/doi-mat-khau';

        $payload = json_encode([
            'tai_khoan_id' => $_SESSION['user_id'],
            'mat_khau_cu' => $matKhauCu,
            'mat_khau_moi' => $matKhauMoi,
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $error = 'Khong the ket noi Python API: ' . curl_error($ch);
            curl_close($ch);
        } else {
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                $error = 'Phan hoi API khong hop le.';
            } elseif ($status >= 200 && $status < 300 && !empty($decoded['success'])) {
                $message = 'Doi mat khau thanh cong.';
            } else {
                $error = $decoded['message'] ?? 'Khong the doi mat khau.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doi mat khau</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
</head>

<body>
    <div class="container" style="max-width:520px;">
        <div class="card">
            <h2>Doi mat khau</h2>

            <?php if ($message !== ''): ?>
                <div class="alert-info" style="margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Mat khau hien tai</label>
                    <input type="password" name="mat_khau_cu" required>
                </div>
                <div class="form-group">
                    <label>Mat khau moi</label>
                    <input type="password" name="mat_khau_moi" required>
                </div>
                <div class="form-group">
                    <label>Xac nhan mat khau moi</label>
                    <input type="password" name="xac_nhan_mat_khau" required>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn-primary">Cap nhat mat khau</button>
                    <a href="./login.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Quay lai dang nhap</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>