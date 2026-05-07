<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$logs = [];
$pdo = getDatabaseConnection();
if ($pdo) {
    $stmt = $pdo->query('SELECT al.*, tk.email FROM admin_log al LEFT JOIN tai_khoan tk ON tk.tai_khoan_id = al.tai_khoan_id ORDER BY al.thoi_diem DESC LIMIT 100');
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log he thong</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <div class="app-content"><?php include __DIR__ . '/../layouts/header.php'; ?>
        <div class="app-content-inner">
            <h1>Log he thong</h1>
            <p class="muted-text">Theo doi hanh dong quan tri gan nhat.</p>
            <div class="table-wrapper" style="background:#fff;border:1px solid #e2e8f0;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="padding:10px;text-align:left;">Thoi diem</th>
                            <th style="padding:10px;text-align:left;">Nguoi thuc hien</th>
                            <th style="padding:10px;text-align:left;">Hanh dong</th>
                            <th style="padding:10px;text-align:left;">Doi tuong</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($logs as $log): ?><tr style="border-top:1px solid #f1f5f9;">
                                <td style="padding:10px;"><?php echo htmlspecialchars((string)$log['thoi_diem']); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars((string)($log['email'] ?? '')); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars((string)$log['hanh_dong']); ?></td>
                                <td style="padding:10px;"><?php echo htmlspecialchars((string)$log['doi_tuong_loai']); ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>