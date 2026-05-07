<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$baseUrl = rtrim(APP_BASE_URL, '/');

$stats = [
    'tong_sv' => 0,
    'tong_gv' => 0,
    'gv_ncs' => 0,
    'gv_thac_si' => 0,
    'gv_tien_si' => 0,
    'gv_pgs_gs' => 0,
    'lhp_mo' => 0,
    'hoc_ky_hien_tai_id' => '',
    'tong_ket_status' => 'Chua tong ket',
];

if ($pdo) {
    $stats['tong_sv'] = (int)$pdo->query('SELECT COUNT(*) FROM sinh_vien')->fetchColumn();

    $gvRow = $pdo->query(
        "SELECT
            COUNT(*) AS tong_gv,
            SUM(CASE WHEN LOWER(COALESCE(hoc_vi, '')) LIKE '%ncs%' THEN 1 ELSE 0 END) AS gv_ncs,
            SUM(CASE WHEN LOWER(COALESCE(hoc_vi, '')) LIKE '%thac%' THEN 1 ELSE 0 END) AS gv_thac_si,
            SUM(CASE WHEN LOWER(COALESCE(hoc_vi, '')) LIKE '%tien%' THEN 1 ELSE 0 END) AS gv_tien_si,
            SUM(CASE WHEN LOWER(COALESCE(hoc_ham, '')) IN ('pgs','gs') OR LOWER(COALESCE(hoc_ham, '')) LIKE '%pho giao su%' OR LOWER(COALESCE(hoc_ham, '')) LIKE '%giao su%' THEN 1 ELSE 0 END) AS gv_pgs_gs
         FROM giang_vien"
    )->fetch(PDO::FETCH_ASSOC);

    if ($gvRow) {
        $stats['tong_gv'] = (int)$gvRow['tong_gv'];
        $stats['gv_ncs'] = (int)$gvRow['gv_ncs'];
        $stats['gv_thac_si'] = (int)$gvRow['gv_thac_si'];
        $stats['gv_tien_si'] = (int)$gvRow['gv_tien_si'];
        $stats['gv_pgs_gs'] = (int)$gvRow['gv_pgs_gs'];
    }

    $hkCurrent = $pdo->query('SELECT hoc_ky_id FROM hoc_ky WHERE is_hien_tai = 1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($hkCurrent) {
        $stats['hoc_ky_hien_tai_id'] = (string)$hkCurrent['hoc_ky_id'];

        $stmt = $pdo->prepare(
            'SELECT
                COUNT(*) AS tong_lhp,
                SUM(CASE WHEN trang_thai IN (\'MO\', \'DANG_NHAP\') THEN 1 ELSE 0 END) AS so_chua_tong_ket,
                SUM(CASE WHEN trang_thai = \'CHO_DUYET\' THEN 1 ELSE 0 END) AS so_cho_duyet
             FROM lop_hoc_phan
             WHERE hoc_ky_id = :hoc_ky_id'
        );
        $stmt->execute(['hoc_ky_id' => $stats['hoc_ky_hien_tai_id']]);
        $lhpStatus = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['tong_lhp' => 0, 'so_chua_tong_ket' => 0, 'so_cho_duyet' => 0];

        $stats['lhp_mo'] = (int)$lhpStatus['tong_lhp'];

        if ((int)$lhpStatus['tong_lhp'] === 0 || (int)$lhpStatus['so_chua_tong_ket'] > 0) {
            $stats['tong_ket_status'] = 'Chua tong ket';
        } elseif ((int)$lhpStatus['so_cho_duyet'] > 0) {
            $stats['tong_ket_status'] = 'Dang xu ly';
        } else {
            $stats['tong_ket_status'] = 'Hoan tat';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Giao vu</title>
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
                    <h1>Dashboard Giao vu</h1>
                    <p class="muted-text">Tong quan nhanh hoat dong cua khoa trong hoc ky hien tai.</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:14px;">
                <div class="card" style="margin:0;">
                    <div class="muted-text">Tong so sinh vien</div>
                    <div style="font-size:28px;font-weight:700;margin:8px 0;"><?php echo htmlspecialchars((string)$stats['tong_sv']); ?></div>
                    <a class="btn-secondary" href="<?php echo htmlspecialchars($baseUrl . '/src/views/giao_vu/sinh_vien/danh_sach.php'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;">Xem chi tiet</a>
                </div>

                <div class="card" style="margin:0;">
                    <div class="muted-text">Giang vien</div>
                    <div style="font-size:28px;font-weight:700;margin:8px 0;"><?php echo htmlspecialchars((string)$stats['tong_gv']); ?></div>
                    <div style="font-size:13px;color:#475569;line-height:1.6;">
                        NCS: <?php echo (int)$stats['gv_ncs']; ?> |
                        Thac si: <?php echo (int)$stats['gv_thac_si']; ?> |
                        Tien si: <?php echo (int)$stats['gv_tien_si']; ?> |
                        PGS/GS: <?php echo (int)$stats['gv_pgs_gs']; ?>
                    </div>
                    <a class="btn-secondary" href="<?php echo htmlspecialchars($baseUrl . '/src/views/giao_vu/danh_muc.php#quan-ly-lhp'); ?>" style="margin-top:8px;text-decoration:none;display:inline-flex;align-items:center;">Xem chi tiet</a>
                </div>

                <div class="card" style="margin:0;">
                    <div class="muted-text">Lop hoc phan dang mo</div>
                    <div style="font-size:28px;font-weight:700;margin:8px 0;"><?php echo htmlspecialchars((string)$stats['lhp_mo']); ?></div>
                    <a class="btn-secondary" href="<?php echo htmlspecialchars($baseUrl . '/src/views/giao_vu/danh_muc.php'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;">Xem chi tiet</a>
                </div>

                <div class="card" style="margin:0;">
                    <div class="muted-text">Tong ket hoc vu</div>
                    <div style="font-size:22px;font-weight:700;margin:10px 0;color:#0f766e;"><?php echo htmlspecialchars($stats['tong_ket_status']); ?></div>
                    <a class="btn-secondary" href="<?php echo htmlspecialchars($baseUrl . '/src/views/giao_vu/tong_ket_hoc_vu.php'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;">Xem chi tiet</a>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>