<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}
$pdo = getDatabaseConnection();
$stats = ['total' => 0, 'active' => 0, 'locked' => 0, 'today' => 0];
$roleStats = [];
$recentLogs = [];
$recentAccounts = [];
if ($pdo) {
    $stats['total'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan')->fetchColumn();
    $stats['active'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE is_active = 1')->fetchColumn();
    $stats['locked'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE is_active = 0')->fetchColumn();
    $stats['today'] = (int)$pdo->query('SELECT COUNT(*) FROM tai_khoan WHERE DATE(lan_dang_nhap_cuoi) = CURDATE()')->fetchColumn();
    $roleStats = $pdo->query('SELECT vai_tro, COUNT(*) AS total FROM tai_khoan GROUP BY vai_tro ORDER BY total DESC')->fetchAll(PDO::FETCH_ASSOC);
    $recentLogs = $pdo->query('SELECT al.hanh_dong, al.doi_tuong_loai, al.thoi_diem, tk.email FROM admin_log al LEFT JOIN tai_khoan tk ON tk.tai_khoan_id = al.tai_khoan_id ORDER BY al.thoi_diem DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
    $recentAccounts = $pdo->query('SELECT email, vai_tro, is_active, lan_dang_nhap_cuoi FROM tai_khoan ORDER BY ngay_tao DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head >
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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

            <div class="dashboard-grid">
                <div class="content-card">
                    <div class="panel-header">
                        <h3>Hoat dong gan day</h3>
                        <span class="pill"><?php echo count($recentLogs); ?> muc</span>
                    </div>
                    <?php if (empty($recentLogs)): ?>
                        <div class="muted-text">Chua co log he thong nao.</div>
                    <?php else: ?>
                        <div class="activity-table">
                            <div class="activity-row header">
                                <div>Thoi diem</div>
                                <div>Nguoi thuc hien</div>
                                <div>Hanh dong</div>
                                <div>Doi tuong</div>
                                <div>Trang thai</div>
                            </div>
                            <?php foreach ($recentLogs as $log): ?>
                                <div class="activity-row">
                                    <div><?php echo htmlspecialchars((string)$log['thoi_diem']); ?></div>
                                    <div><?php echo htmlspecialchars((string)($log['email'] ?? '')); ?></div>
                                    <div><?php echo htmlspecialchars((string)$log['hanh_dong']); ?></div>
                                    <div><?php echo htmlspecialchars((string)$log['doi_tuong_loai']); ?></div>
                                    <div><span class="status-tag success">OK</span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="content-card task-panel">
                    <div class="panel-header">
                        <h3>Thao tac nhanh</h3>
                    </div>
                    <a class="quick-task" href="quan_ly_tai_khoan.php">
                        <div class="task-icon">TK</div>
                        <div>
                            <div class="task-title">Quan ly tai khoan</div>
                            <div class="muted-text">Khoa/Mo khoa va doi vai tro</div>
                        </div>
                    </a>
                    <a class="quick-task" href="them_sinh_vien.php">
                        <div class="task-icon">SV</div>
                        <div>
                            <div class="task-title">Them tai khoan moi</div>
                            <div class="muted-text">Tao tai khoan sinh vien/giang vien</div>
                        </div>
                    </a>
                    <a class="quick-task" href="log_he_thong.php">
                        <div class="task-icon">LOG</div>
                        <div>
                            <div class="task-title">Xem log he thong</div>
                            <div class="muted-text">Theo doi cac thao tac quan tri</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="content-card">
                    <div class="panel-header">
                        <h3>Phan bo vai tro</h3>
                    </div>
                    <div class="task-panel">
                        <?php if (empty($roleStats)): ?>
                            <div class="muted-text">Khong co du lieu vai tro.</div>
                        <?php else: ?>
                            <?php foreach ($roleStats as $row): ?>
                                <div class="quick-task">
                                    <div class="task-icon"><?php echo htmlspecialchars(substr((string)$row['vai_tro'], 0, 2)); ?></div>
                                    <div>
                                        <div class="task-title"><?php echo htmlspecialchars((string)$row['vai_tro']); ?></div>
                                        <div class="muted-text"><?php echo (int)$row['total']; ?> tai khoan</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-card">
                    <div class="panel-header">
                        <h3>Tai khoan moi nhat</h3>
                    </div>
                    <?php if (empty($recentAccounts)): ?>
                        <div class="muted-text">Chua co tai khoan moi.</div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Vai tro</th>
                                    <th>Trang thai</th>
                                    <th>Lan dang nhap cuoi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAccounts as $acc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$acc['email']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$acc['vai_tro']); ?></td>
                                        <td>
                                            <span class="badge <?php echo ((int)$acc['is_active'] === 1) ? 'badge-active' : 'badge-locked'; ?>">
                                                <?php echo ((int)$acc['is_active'] === 1) ? 'Dang hoat dong' : 'Bi khoa'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars((string)($acc['lan_dang_nhap_cuoi'] ?? '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>