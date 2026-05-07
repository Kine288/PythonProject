<?php
session_start();
require_once __DIR__ . '/../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
}

$notice = '';
$error = '';
$hocKyId = trim($_GET['hoc_ky_id'] ?? '');
$selectedLhpId = trim($_GET['lhp_id'] ?? '');
$giaoVuTaiKhoanId = $_SESSION['user_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    $lhpId = trim($_POST['lhp_id'] ?? '');

    if ($lhpId === '') {
        $response = ['success' => false, 'message' => 'Thieu lhp_id'];
    } elseif ($action === 'approve') {
        $response = sinhVienProxyRequest('POST', '/diem/lhp/' . $lhpId . '/duyet', ['tai_khoan_id' => $giaoVuTaiKhoanId]);
    } elseif ($action === 'reject') {
        $response = sinhVienProxyRequest('POST', '/diem/lhp/' . $lhpId . '/tu-choi', ['ly_do' => trim($_POST['ly_do'] ?? '')]);
    } else {
        $response = ['success' => false, 'message' => 'Thao tac khong hop le'];
    }

    if (!empty($response['success'])) {
        $notice = $response['message'] ?? 'Thuc hien thanh cong';
        $selectedLhpId = $lhpId;
    } else {
        $error = $response['message'] ?? 'Khong the xu ly';
    }
}

$pdo = getDatabaseConnection();
$hocKys = [];
if ($pdo) {
    $hocKys = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC')->fetchAll(PDO::FETCH_ASSOC);
}

if ($hocKyId === '' && !empty($hocKys)) {
    foreach ($hocKys as $hk) {
        if (!empty($hk['is_hien_tai'])) {
            $hocKyId = $hk['hoc_ky_id'];
            break;
        }
    }
    if ($hocKyId === '') {
        $hocKyId = $hocKys[0]['hoc_ky_id'];
    }
}

$lhpRes = sinhVienProxyRequest('GET', '/lhp', null, ['hoc_ky_id' => $hocKyId]);
$lhps = $lhpRes['data'] ?? [];

$selectedLhp = null;
foreach ($lhps as $lhp) {
    if (($lhp['lhp_id'] ?? '') === $selectedLhpId) {
        $selectedLhp = $lhp;
        break;
    }
}

$chiTietBangDiem = [];
if ($selectedLhpId !== '') {
    $detailsRes = sinhVienProxyRequest('GET', '/diem/lhp/' . $selectedLhpId);
    $chiTietBangDiem = $detailsRes['data'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyet diem</title>
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

        .badge-ready {
            background: #ccfbf1;
            color: #0f766e;
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
                    <h1>Duyet diem</h1>
                    <p class="muted-text">Cap 1 chon LHP can duyet, cap 2 xem bang diem chi tiet de duyet hoac tu choi.</p>
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
                                <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $hocKyId === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn-primary" type="submit">Tai danh sach LHP</button>
                </form>
            </div>

            <div class="card section-card">
                <h3 style="margin-bottom:10px;">Cap 1 - Danh sach LHP</h3>
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Ma LHP</th>
                                <th>Mon hoc</th>
                                <th>Giang vien</th>
                                <th>So SV</th>
                                <th>Trang thai</th>
                                <th>Chi tiet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lhps)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Khong co LHP trong hoc ky da chon.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($lhps as $lhp): ?>
                                    <?php $canClick = in_array(($lhp['trang_thai'] ?? ''), ['CHO_DUYET', 'DA_DUYET'], true); ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lhp['ma_lhp'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars(($lhp['ma_mon'] ?? '') . ' - ' . ($lhp['ten_mon'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($lhp['ten_gv'] ?? '--'); ?></td>
                                        <td><?php echo (int)($lhp['so_sv'] ?? 0); ?></td>
                                        <td><span class="badge-state <?php echo $canClick ? 'badge-ready' : ''; ?>"><?php echo htmlspecialchars($lhp['trang_thai'] ?? '--'); ?></span></td>
                                        <td>
                                            <?php if ($canClick): ?>
                                                <a class="btn-secondary" style="text-decoration:none;" href="?hoc_ky_id=<?php echo urlencode($hocKyId); ?>&lhp_id=<?php echo urlencode($lhp['lhp_id']); ?>">Xem bang diem</a>
                                            <?php else: ?>
                                                <span class="muted-text">Chi CHO_DUYET/DA_DUYET moi duoc mo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($selectedLhp !== null): ?>
                <div class="card section-card">
                    <h3 style="margin-bottom:10px;">Cap 2 - Bang diem chi tiet LHP <?php echo htmlspecialchars($selectedLhp['ma_lhp'] ?? ''); ?></h3>
                    <p class="muted-text" style="margin-bottom:12px;">
                        Mon: <?php echo htmlspecialchars(($selectedLhp['ma_mon'] ?? '') . ' - ' . ($selectedLhp['ten_mon'] ?? '')); ?> |
                        GV: <?php echo htmlspecialchars($selectedLhp['ten_gv'] ?? '--'); ?>
                    </p>

                    <div style="overflow-x:auto;">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>MSSV</th>
                                    <th>Ho ten</th>
                                    <th>Diem CC</th>
                                    <th>Diem GK</th>
                                    <th>Diem CK</th>
                                    <th>Diem tong</th>
                                    <th>Xep loai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($chiTietBangDiem)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center;">LHP chua co bang diem chi tiet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($chiTietBangDiem as $idx => $row): ?>
                                        <tr>
                                            <td><?php echo (int)$idx + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['msv'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['ho_ten'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['diem_cc'] ?? '--')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['diem_gk'] ?? '--')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['diem_ck'] ?? '--')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['diem_tong'] ?? '--')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($row['diem_chu'] ?? '--')); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (($selectedLhp['trang_thai'] ?? '') === 'CHO_DUYET'): ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;align-items:start;">
                            <form method="post">
                                <input type="hidden" name="form_action" value="approve">
                                <input type="hidden" name="lhp_id" value="<?php echo htmlspecialchars($selectedLhpId); ?>">
                                <button class="btn-primary" type="submit" style="width:100%;">Duyet bang diem</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="form_action" value="reject">
                                <input type="hidden" name="lhp_id" value="<?php echo htmlspecialchars($selectedLhpId); ?>">
                                <div class="form-group" style="margin:0 0 8px 0;">
                                    <input name="ly_do" placeholder="Ly do tu choi (khuyen nghi nhap)">
                                </div>
                                <button class="btn-danger" type="submit" style="width:100%;">Tu choi bang diem</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>