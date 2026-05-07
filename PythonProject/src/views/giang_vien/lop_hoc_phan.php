<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'GIANG_VIEN')) {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$taiKhoanId = (string)($_SESSION['user_id'] ?? '');
$dsLhp = [];
$pageError = '';
$pageInfo = '';

if (!$pdo) {
    $pageError = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->prepare('SELECT giang_vien_id FROM giang_vien WHERE giang_vien_id = :id OR tai_khoan_id = :id LIMIT 1');
    $stmt->execute(['id' => $taiKhoanId]);
    $gv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gv) {
        $pageError = 'Giang vien khong ton tai trong he thong.';
    } else {
        $gvId = $gv['giang_vien_id'];
        $stmt = $pdo->prepare(
            "SELECT lhp.lhp_id, lhp.ma_lhp, lhp.trang_thai,
                    mh.ten_mon, hk.ten_hoc_ky,
                    COUNT(ds.ds_lhp_id) AS so_sv
             FROM lop_hoc_phan lhp
             LEFT JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
             LEFT JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
             LEFT JOIN ds_lhp ds ON ds.lhp_id = lhp.lhp_id
             WHERE lhp.giang_vien_id = :gv_id
             GROUP BY lhp.lhp_id, lhp.ma_lhp, lhp.trang_thai, mh.ten_mon, hk.ten_hoc_ky
             ORDER BY hk.ten_hoc_ky DESC, lhp.ma_lhp ASC"
        );
        $stmt->execute(['gv_id' => $gvId]);
        $dsLhp = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($dsLhp)) {
            $pageInfo = 'Chua co lop hoc phan nao duoc phan cong.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lop phu trach</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .page-stack {
            display: grid;
            gap: 14px;
        }

        .section-card {
            margin: 0;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner page-stack">
            <div class="dashboard-header">
                <div>
                    <h1>Lop hoc phan phu trach</h1>
                    <p class="muted-text">Danh sach cac lop hoc phan da duoc phan cong cho giang vien hien tai.</p>
                </div>
            </div>

            <?php if ($pageError !== ''): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
            <?php elseif ($pageInfo !== ''): ?>
                <div class="alert-info"><?php echo htmlspecialchars($pageInfo); ?></div>
            <?php endif; ?>

            <div class="card section-card">
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Ma LHP</th>
                                <th>Mon hoc</th>
                                <th>Hoc ky</th>
                                <th>Trang thai</th>
                                <th>So SV</th>
                                <th>Thao tac</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dsLhp)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Khong co du lieu de hien thi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dsLhp as $lhp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)$lhp['ma_lhp']); ?></td>
                                        <td><?php echo htmlspecialchars((string)($lhp['ten_mon'] ?? '--')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($lhp['ten_hoc_ky'] ?? '--')); ?></td>
                                        <td><?php echo htmlspecialchars((string)($lhp['trang_thai'] ?? '--')); ?></td>
                                        <td><?php echo (int)($lhp['so_sv'] ?? 0); ?></td>
                                        <td>
                                            <a class="btn-secondary" style="text-decoration:none;" href="nhap_diem.php?lhp_id=<?php echo urlencode((string)$lhp['lhp_id']); ?>">Nhap diem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>