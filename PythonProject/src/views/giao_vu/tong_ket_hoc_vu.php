<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';
require_once __DIR__ . '/../../../api/sinh_vien.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$hocKys = [];
$selectedHocKy = trim($_GET['hoc_ky_id'] ?? '');
$selectedLopId = trim($_GET['lop_id'] ?? '');
$notice = '';
$error = '';

if ($pdo) {
    $hocKys = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC')->fetchAll(PDO::FETCH_ASSOC);
}

if ($selectedHocKy === '' && !empty($hocKys)) {
    foreach ($hocKys as $hk) {
        if (!empty($hk['is_hien_tai'])) {
            $selectedHocKy = $hk['hoc_ky_id'];
            break;
        }
    }
    if ($selectedHocKy === '') {
        $selectedHocKy = $hocKys[0]['hoc_ky_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    $selectedHocKy = trim($_POST['hoc_ky_id'] ?? $selectedHocKy);
    $selectedLopId = trim($_POST['lop_id'] ?? $selectedLopId);

    if ($action === 'finalize_summary' && $selectedHocKy !== '') {
        $gpaRes = sinhVienProxyRequest('POST', '/gpa/tinh-hoc-ky/' . $selectedHocKy);

        if (empty($gpaRes['success'])) {
            $error = $gpaRes['message'] ?? 'Khong the tinh tong ket hoc ky';
        } elseif ($pdo) {
            $stmt = $pdo->prepare("UPDATE lop_hoc_phan SET trang_thai = 'DONG' WHERE hoc_ky_id = :hoc_ky_id AND trang_thai = 'DA_DUYET'");
            $stmt->execute(['hoc_ky_id' => $selectedHocKy]);
            $notice = 'Da xac nhan tong ket hoc vu va khoa cac LHP da duyet trong hoc ky.';
        }
    }
}

$lopSummary = [];
$chiTietLop = [];

if ($pdo && $selectedHocKy !== '') {
    $stmt = $pdo->prepare(
        "SELECT
            lsh.lop_id,
            lsh.ma_lop,
            lsh.ten_lop,
            nk.ten_nien_khoa,
            COUNT(sv.sinh_vien_id) AS tong_sv,
            SUM(CASE WHEN kq.kqhk_id IS NOT NULL THEN 1 ELSE 0 END) AS da_tong_ket,
            SUM(CASE WHEN COALESCE(kq.muc_canh_bao, 0) > 0 THEN 1 ELSE 0 END) AS so_sv_canh_bao,
            ROUND(AVG(kq.gpa_tich_luy_he4), 2) AS gpa_tb,
            CASE
                WHEN COUNT(sv.sinh_vien_id) = 0 THEN 'CHUA_CO_DU_LIEU'
                WHEN SUM(CASE WHEN kq.kqhk_id IS NOT NULL THEN 1 ELSE 0 END) < COUNT(sv.sinh_vien_id) THEN 'DANG_XU_LY'
                ELSE 'HOAN_TAT'
            END AS trang_thai_tong_ket
         FROM lop_sinh_hoat lsh
         LEFT JOIN nien_khoa nk ON nk.nien_khoa_id = lsh.nien_khoa_id
         LEFT JOIN sinh_vien sv ON sv.lop_id = lsh.lop_id
         LEFT JOIN ket_qua_hoc_ky kq ON kq.sinh_vien_id = sv.sinh_vien_id AND kq.hoc_ky_id = :hoc_ky_id
         GROUP BY lsh.lop_id, lsh.ma_lop, lsh.ten_lop, nk.ten_nien_khoa
         ORDER BY lsh.ma_lop ASC"
    );
    $stmt->execute(['hoc_ky_id' => $selectedHocKy]);
    $lopSummary = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($selectedLopId !== '') {
        $detailStmt = $pdo->prepare(
            "SELECT
                sv.sinh_vien_id,
                sv.msv,
                sv.ho_ten,
                sv.trang_thai,
                kq.gpa_hk_he4,
                kq.gpa_tich_luy_he4,
                kq.xep_loai,
                kq.muc_canh_bao
             FROM sinh_vien sv
             LEFT JOIN ket_qua_hoc_ky kq ON kq.sinh_vien_id = sv.sinh_vien_id AND kq.hoc_ky_id = :hoc_ky_id
             WHERE sv.lop_id = :lop_id
             ORDER BY sv.msv ASC"
        );
        $detailStmt->execute(['hoc_ky_id' => $selectedHocKy, 'lop_id' => $selectedLopId]);
        $chiTietLop = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tong ket hoc vu</title>
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

        .badge-state {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            background: #e2e8f0;
            color: #334155;
        }

        .badge-ok {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warn {
            background: #fef9c3;
            color: #854d0e;
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
                    <h1>Tong ket hoc vu</h1>
                    <p class="muted-text">Cap 1 theo lop hanh chinh, cap 2 xem tong ket tung sinh vien va xac nhan khoa tong ket hoc ky.</p>
                </div>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="alert-info"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card section-card">
                <form method="get" style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end;">
                    <div class="form-group" style="margin:0;">
                        <label>Hoc ky</label>
                        <select name="hoc_ky_id" required>
                            <?php foreach ($hocKys as $hk): ?>
                                <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selectedHocKy === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn-primary" type="submit">Tai tong ket</button>
                        <a
                            class="btn-secondary"
                            style="text-decoration:none;display:inline-flex;align-items:center;"
                            href="../../../api/bao_cao.php?action=xuat_excel_tong_ket&hoc_ky_id=<?php echo urlencode($selectedHocKy); ?>&lop_id=<?php echo urlencode($selectedLopId); ?>">
                            Xuat Excel
                        </a>
                    </div>
                </form>
            </div>

            <div class="card section-card">
                <h3 style="margin-bottom:10px;">Cap 1 - Danh sach lop hanh chinh</h3>
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Ma lop</th>
                                <th>Ten lop</th>
                                <th>Khoa</th>
                                <th>Tong SV</th>
                                <th>SV canh bao</th>
                                <th>GPA TB</th>
                                <th>Trang thai</th>
                                <th>Chi tiet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lopSummary)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">Chua co du lieu tong ket lop.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lopSummary as $lop): ?>
                                    <?php $isDone = ($lop['trang_thai_tong_ket'] ?? '') === 'HOAN_TAT'; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lop['ma_lop'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ten_lop'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ten_nien_khoa'] ?? '--'); ?></td>
                                        <td><?php echo (int)($lop['tong_sv'] ?? 0); ?></td>
                                        <td><?php echo (int)($lop['so_sv_canh_bao'] ?? 0); ?></td>
                                        <td><?php echo $lop['gpa_tb'] !== null ? htmlspecialchars((string)$lop['gpa_tb']) : '--'; ?></td>
                                        <td><span class="badge-state <?php echo $isDone ? 'badge-ok' : 'badge-warn'; ?>"><?php echo htmlspecialchars($lop['trang_thai_tong_ket'] ?? '--'); ?></span></td>
                                        <td>
                                            <a class="btn-secondary" style="text-decoration:none;" href="?hoc_ky_id=<?php echo urlencode($selectedHocKy); ?>&lop_id=<?php echo urlencode($lop['lop_id']); ?>">Xem chi tiet</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($selectedLopId !== ''): ?>
                <div class="card section-card">
                    <h3 style="margin-bottom:10px;">Cap 2 - Tong ket chi tiet lop</h3>
                    <div style="overflow-x:auto;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>MSSV</th>
                                    <th>Ho ten</th>
                                    <th>GPA hoc ky (he 4)</th>
                                    <th>GPA tich luy (he 4)</th>
                                    <th>Xep loai</th>
                                    <th>Muc canh bao</th>
                                    <th>Trang thai hoc tap</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($chiTietLop)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Khong co sinh vien trong lop hoac chua co tong ket.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($chiTietLop as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['msv'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['ho_ten'] ?? ''); ?></td>
                                            <td><?php echo $row['gpa_hk_he4'] !== null ? htmlspecialchars((string)$row['gpa_hk_he4']) : '--'; ?></td>
                                            <td><?php echo $row['gpa_tich_luy_he4'] !== null ? htmlspecialchars((string)$row['gpa_tich_luy_he4']) : '--'; ?></td>
                                            <td><?php echo htmlspecialchars($row['xep_loai'] ?? '--'); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['muc_canh_bao'] ?? 0)); ?></td>
                                            <td><?php echo htmlspecialchars($row['trang_thai'] ?? '--'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <form method="post" style="margin-top:12px;" onsubmit="return confirm('Xac nhan tong ket va khoa hoc ky nay?');">
                        <input type="hidden" name="form_action" value="finalize_summary">
                        <input type="hidden" name="hoc_ky_id" value="<?php echo htmlspecialchars($selectedHocKy); ?>">
                        <input type="hidden" name="lop_id" value="<?php echo htmlspecialchars($selectedLopId); ?>">
                        <button class="btn-primary" type="submit">Xac nhan tong ket va khoa hoc ky</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>