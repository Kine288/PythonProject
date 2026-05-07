<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ./login.php');
    exit;
}

$pdo = getDatabaseConnection();
$userId = (string)($_SESSION['user_id'] ?? '');
$userRole = (string)($_SESSION['user_role'] ?? 'GUEST');

$account = null;
$profile = [];
$errorMessage = '';

if ($pdo === null) {
    $errorMessage = 'Khong the ket noi co so du lieu.';
} else {
    $stmt = $pdo->prepare(
        'SELECT tai_khoan_id, email, vai_tro, is_active, ngay_tao, lan_dang_nhap_cuoi
         FROM tai_khoan
         WHERE tai_khoan_id = :tai_khoan_id
         LIMIT 1'
    );
    $stmt->execute(['tai_khoan_id' => $userId]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        $errorMessage = 'Khong tim thay thong tin tai khoan.';
    } else {
        if ($userRole === 'SINH_VIEN') {
            $svStmt = $pdo->prepare(
                'SELECT sv.msv, sv.ho_ten, sv.ngay_sinh, sv.gioi_tinh, sv.trang_thai,
                        lsh.ma_lop, lsh.ten_lop
                 FROM sinh_vien sv
                 LEFT JOIN lop_sinh_hoat lsh ON lsh.lop_id = sv.lop_id
                 WHERE sv.tai_khoan_id = :tai_khoan_id
                 LIMIT 1'
            );
            $svStmt->execute(['tai_khoan_id' => $userId]);
            $profile = $svStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } elseif ($userRole === 'GIANG_VIEN') {
            $gvStmt = $pdo->prepare(
                'SELECT gv.ma_gv, gv.ho_ten, gv.hoc_vi, gv.hoc_ham, gv.so_dien_thoai,
                        kbm.ma_khoa, kbm.ten_khoa
                 FROM giang_vien gv
                 LEFT JOIN khoa_bo_mon kbm ON kbm.khoa_id = gv.khoa_id
                 WHERE gv.tai_khoan_id = :tai_khoan_id
                 LIMIT 1'
            );
            $gvStmt->execute(['tai_khoan_id' => $userId]);
            $profile = $gvStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } elseif ($userRole === 'GIAO_VU') {
            $gvuStmt = $pdo->prepare(
                'SELECT gvu.ma_giao_vu, gvu.ho_ten, gvu.so_dien_thoai,
                        kbm.ma_khoa, kbm.ten_khoa
                 FROM giao_vu gvu
                 LEFT JOIN khoa_bo_mon kbm ON kbm.khoa_id = gvu.khoa_id
                 WHERE gvu.tai_khoan_id = :tai_khoan_id
                 LIMIT 1'
            );
            $gvuStmt->execute(['tai_khoan_id' => $userId]);
            $profile = $gvuStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }
    }
}

function label_role(string $role): string
{
    if ($role === 'ADMIN') {
        return 'Quan tri he thong';
    }
    if ($role === 'GIAO_VU') {
        return 'Giao vu';
    }
    if ($role === 'GIANG_VIEN') {
        return 'Giang vien';
    }
    if ($role === 'SINH_VIEN') {
        return 'Sinh vien';
    }
    return $role;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thong tin ca nhan</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">

</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Thong tin ca nhan</h1>
                    <p class="muted-text">Thong tin tai khoan va ho so theo vai tro dang dang nhap.</p>
                </div>
            </div>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php else: ?>
                <div class="card" style="margin-bottom:12px;">
                    <h3 style="margin-bottom:10px;">Tai khoan</h3>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;">
                        <div><strong>Email:</strong> <?php echo htmlspecialchars((string)($account['email'] ?? '--')); ?></div>
                        <div><strong>Vai tro:</strong> <?php echo htmlspecialchars(label_role((string)($account['vai_tro'] ?? ''))); ?></div>
                        <div><strong>Trang thai:</strong> <?php echo !empty($account['is_active']) ? 'Dang hoat dong' : 'Bi khoa'; ?></div>
                        <div><strong>Lan dang nhap cuoi:</strong> <?php echo htmlspecialchars((string)($account['lan_dang_nhap_cuoi'] ?? '--')); ?></div>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom:10px;">Ho so</h3>
                    <?php if ($userRole === 'SINH_VIEN'): ?>
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;">
                            <div><strong>MSSV:</strong> <?php echo htmlspecialchars((string)($profile['msv'] ?? '--')); ?></div>
                            <div><strong>Ho ten:</strong> <?php echo htmlspecialchars((string)($profile['ho_ten'] ?? '--')); ?></div>
                            <div><strong>Lop:</strong> <?php echo htmlspecialchars((string)(($profile['ma_lop'] ?? '--') . ' - ' . ($profile['ten_lop'] ?? '--'))); ?></div>
                            <div><strong>Trang thai hoc tap:</strong> <?php echo htmlspecialchars((string)($profile['trang_thai'] ?? '--')); ?></div>
                            <div><strong>Ngay sinh:</strong> <?php echo htmlspecialchars((string)($profile['ngay_sinh'] ?? '--')); ?></div>
                            <div><strong>Gioi tinh:</strong> <?php echo htmlspecialchars((string)($profile['gioi_tinh'] ?? '--')); ?></div>
                        </div>
                    <?php elseif ($userRole === 'GIANG_VIEN'): ?>
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;">
                            <div><strong>Ma GV:</strong> <?php echo htmlspecialchars((string)($profile['ma_gv'] ?? '--')); ?></div>
                            <div><strong>Ho ten:</strong> <?php echo htmlspecialchars((string)($profile['ho_ten'] ?? '--')); ?></div>
                            <div><strong>Hoc vi:</strong> <?php echo htmlspecialchars((string)($profile['hoc_vi'] ?? '--')); ?></div>
                            <div><strong>Hoc ham:</strong> <?php echo htmlspecialchars((string)($profile['hoc_ham'] ?? '--')); ?></div>
                            <div><strong>Khoa:</strong> <?php echo htmlspecialchars((string)(($profile['ma_khoa'] ?? '--') . ' - ' . ($profile['ten_khoa'] ?? '--'))); ?></div>
                            <div><strong>So dien thoai:</strong> <?php echo htmlspecialchars((string)($profile['so_dien_thoai'] ?? '--')); ?></div>
                        </div>
                    <?php elseif ($userRole === 'GIAO_VU'): ?>
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:10px;">
                            <div><strong>Ma giao vu:</strong> <?php echo htmlspecialchars((string)($profile['ma_giao_vu'] ?? '--')); ?></div>
                            <div><strong>Ho ten:</strong> <?php echo htmlspecialchars((string)($profile['ho_ten'] ?? '--')); ?></div>
                            <div><strong>Khoa:</strong> <?php echo htmlspecialchars((string)(($profile['ma_khoa'] ?? '--') . ' - ' . ($profile['ten_khoa'] ?? '--'))); ?></div>
                            <div><strong>So dien thoai:</strong> <?php echo htmlspecialchars((string)($profile['so_dien_thoai'] ?? '--')); ?></div>
                        </div>
                    <?php else: ?>
                        <p class="muted-text" style="margin:0;">Vai tro hien tai khong co ho so chi tiet rieng. Ban van co the xem thong tin tai khoan o muc ben tren.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>