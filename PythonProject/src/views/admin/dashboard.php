<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$pdo = getDatabaseConnection();
$stats = ['total' => 0, 'active' => 0, 'locked' => 0, 'today' => 0];
if ($pdo) {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan')->fetchColumn();
    $stats['active'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE is_active = 1')->fetchColumn();
    $stats['locked'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE is_active = 0')->fetchColumn();
    $stats['today'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE DATE(lan_dang_nhap_cuoi) = CURDATE()')->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="app-content"><?php include __DIR__ . '/../layouts/header.php'; ?>
        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Dashboard He thong</h1>
                    <p class="muted-text">Tong quan trang thai tai khoan va hoat dong dang nhap.</p>
                </div>
            </div>
            <div class="dashboard-cards">
                <div class="stat-tile">
                    <div class="stat-title">Tong tai khoan</div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-tile">
                    <div class="stat-title">Dang hoat dong</div>
                    <div class="stat-value"><?php echo $stats['active']; ?></div>
                </div>
                <div class="stat-tile danger">
                    <div class="stat-title">Bi khoa</div>
                    <div class="stat-value"><?php echo $stats['locked']; ?></div>
                </div>
                <div class="stat-tile">
                    <div class="stat-title">Dang nhap hom nay</div>
                    <div class="stat-value"><?php echo $stats['today']; ?></div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>