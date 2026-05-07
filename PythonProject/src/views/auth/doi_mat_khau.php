<?php
session_start();
require_once __DIR__ . '/../../../config/constants.php';
require_once __DIR__ . '/../../../config/database.php';

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
        $pdo = getDatabaseConnection();
        if ($pdo === null) {
            $error = 'Khong the ket noi co so du lieu.';
        } else {
            $stmt = $pdo->prepare('SELECT mat_khau_hash FROM tai_khoan WHERE tai_khoan_id = :tai_khoan_id LIMIT 1');
            $stmt->execute(['tai_khoan_id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'Khong tim thay tai khoan.';
            } else {
                $storedHash = (string)($user['mat_khau_hash'] ?? '');
                $oldPasswordOk = false;

                if ($storedHash !== '') {
                    if (password_verify($matKhauCu, $storedHash)) {
                        $oldPasswordOk = true;
                    } else {
                        $legacySha256 = hash('sha256', $matKhauCu);
                        $isLegacy = (strlen($storedHash) < 60) || (strpos($storedHash, '$2y$') !== 0);
                        if (($isLegacy && hash_equals($storedHash, $matKhauCu)) || hash_equals($storedHash, $legacySha256)) {
                            $oldPasswordOk = true;
                        }
                    }
                }

                if (!$oldPasswordOk) {
                    $error = 'Mat khau hien tai khong dung.';
                } else {
                    $newHash = password_hash($matKhauMoi, PASSWORD_BCRYPT);
                    if ($newHash === false) {
                        $error = 'Khong the tao mat khau moi.';
                    } else {
                        $update = $pdo->prepare('UPDATE tai_khoan SET mat_khau_hash = :mat_khau_hash WHERE tai_khoan_id = :tai_khoan_id');
                        $update->execute([
                            'mat_khau_hash' => $newHash,
                            'tai_khoan_id' => $_SESSION['user_id'],
                        ]);
                        $message = 'Doi mat khau thanh cong.';
                    }
                }
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