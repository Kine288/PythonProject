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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">

</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Log he thong</h1>
                    <p class="muted-text">Theo doi hanh dong quan tri gan nhat.</p>
                </div>
                <div class="dashboard-actions">
                    <a class="btn-secondary" href="log_he_thong.php">Lam moi</a>
                </div>
            </div>

            <?php if (empty($logs)): ?>
                <div class="alert-warning">Chua co log he thong nao.</div>
            <?php else: ?>
                <div class="content-card">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Thoi diem</th>
                                <th>Nguoi thuc hien</th>
                                <th>Hanh dong</th>
                                <th>Doi tuong</th>
                                <th>Doi tuong ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)$log['thoi_diem']); ?></td>
                                    <td><?php echo htmlspecialchars((string)($log['email'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string)$log['hanh_dong']); ?></td>
                                    <td><?php echo htmlspecialchars((string)$log['doi_tuong_loai']); ?></td>
                                    <td><?php echo htmlspecialchars((string)($log['doi_tuong_id'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>