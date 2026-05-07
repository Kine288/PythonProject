<?php
session_start();
require_once __DIR__ . '/../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
}

function newId32(): string
{
    return bin2hex(random_bytes(16));
}

$notice = '';
$error = '';
$reopenStudentModalLhpId = '';
$activeTab = trim($_POST['active_tab'] ?? ($_GET['tab'] ?? 'lhp'));
$activeTab = in_array($activeTab, ['lhp', 'structure'], true) ? $activeTab : 'lhp';
$selectedNamHoc = trim($_GET['nam_hoc'] ?? '');
$selectedHocKyId = trim($_GET['hoc_ky_id'] ?? '');

$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['form_action'] ?? '');
    $payload = $_POST;

    try {
        if (!$pdo) {
            throw new RuntimeException('Khong ket noi duoc CSDL');
        }

        if ($action === 'create_nam_hoc') {
            $namHoc = trim($payload['nam_hoc'] ?? '');
            $setCurrent = isset($payload['set_hien_tai']) ? 1 : 0;
            if (!preg_match('/^\d{4}-\d{4}$/', $namHoc)) {
                throw new RuntimeException('Nam hoc khong hop le (YYYY-YYYY)');
            }

            $checkStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM hoc_ky WHERE nam_hoc = :nam_hoc');
            $checkStmt->execute(['nam_hoc' => $namHoc]);
            $exists = (int)($checkStmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
            if ($exists > 0) {
                throw new RuntimeException('Nam hoc da ton tai. Hay them hoc ky cho nam hoc nay.');
            }

            if ($setCurrent) {
                $pdo->exec('UPDATE hoc_ky SET is_hien_tai = 0');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO hoc_ky (hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai, ngay_bat_dau, ngay_ket_thuc)
                 VALUES (:id, :ten, :nam_hoc, :ky_hoc, :is_hien_tai, :ngay_bat_dau, :ngay_ket_thuc)'
            );
            $stmt->execute([
                'id' => newId32(),
                'ten' => 'Hoc ky 1',
                'nam_hoc' => $namHoc,
                'ky_hoc' => 1,
                'is_hien_tai' => $setCurrent,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
            ]);
            $notice = 'Da tao nam hoc moi voi Hoc ky 1 mac dinh.';
            $selectedNamHoc = $namHoc;
            $activeTab = 'structure';
        } elseif ($action === 'create_hoc_ky') {
            $tenHocKy = trim($payload['ten_hoc_ky'] ?? '');
            $namHoc = trim($payload['nam_hoc'] ?? '');
            $kyHoc = (int)($payload['ky_hoc'] ?? 0);
            $ngayBatDau = trim($payload['ngay_bat_dau'] ?? '');
            $ngayKetThuc = trim($payload['ngay_ket_thuc'] ?? '');
            $isHienTai = isset($payload['is_hien_tai']) ? 1 : 0;
            if ($tenHocKy === '' || $namHoc === '' || $kyHoc <= 0) {
                throw new RuntimeException('Thieu du lieu hoc ky');
            }

            $dupStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM hoc_ky WHERE nam_hoc = :nam_hoc AND ky_hoc = :ky_hoc');
            $dupStmt->execute(['nam_hoc' => $namHoc, 'ky_hoc' => $kyHoc]);
            $dup = (int)($dupStmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
            if ($dup > 0) {
                throw new RuntimeException('Hoc ky nay da ton tai trong nam hoc da chon');
            }

            if ($isHienTai) {
                $pdo->exec('UPDATE hoc_ky SET is_hien_tai = 0');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO hoc_ky (hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai, ngay_bat_dau, ngay_ket_thuc)
                 VALUES (:id, :ten, :nam_hoc, :ky_hoc, :is_hien_tai, :ngay_bat_dau, :ngay_ket_thuc)'
            );
            $stmt->execute([
                'id' => newId32(),
                'ten' => $tenHocKy,
                'nam_hoc' => $namHoc,
                'ky_hoc' => $kyHoc,
                'is_hien_tai' => $isHienTai,
                'ngay_bat_dau' => $ngayBatDau !== '' ? $ngayBatDau : null,
                'ngay_ket_thuc' => $ngayKetThuc !== '' ? $ngayKetThuc : null,
            ]);
            $notice = 'Da them hoc ky moi';
            $selectedNamHoc = $namHoc;
            $activeTab = 'structure';
        } elseif ($action === 'update_hoc_ky') {
            $hocKyId = trim($payload['hoc_ky_id'] ?? '');
            $isHienTai = isset($payload['is_hien_tai']) ? 1 : 0;
            if ($hocKyId === '') {
                throw new RuntimeException('Thieu hoc_ky_id');
            }

            if ($isHienTai) {
                $clearStmt = $pdo->prepare('UPDATE hoc_ky SET is_hien_tai = 0 WHERE hoc_ky_id <> :id');
                $clearStmt->execute(['id' => $hocKyId]);
            }

            $stmt = $pdo->prepare(
                'UPDATE hoc_ky
                 SET ten_hoc_ky = :ten, nam_hoc = :nam_hoc, ky_hoc = :ky_hoc,
                     is_hien_tai = :is_hien_tai, ngay_bat_dau = :ngay_bat_dau, ngay_ket_thuc = :ngay_ket_thuc
                 WHERE hoc_ky_id = :id'
            );
            $stmt->execute([
                'id' => $hocKyId,
                'ten' => trim($payload['ten_hoc_ky'] ?? ''),
                'nam_hoc' => trim($payload['nam_hoc'] ?? ''),
                'ky_hoc' => (int)($payload['ky_hoc'] ?? 0),
                'is_hien_tai' => $isHienTai,
                'ngay_bat_dau' => trim($payload['ngay_bat_dau'] ?? '') ?: null,
                'ngay_ket_thuc' => trim($payload['ngay_ket_thuc'] ?? '') ?: null,
            ]);
            $notice = 'Da cap nhat hoc ky';
            $activeTab = 'structure';
        } elseif ($action === 'create_lhp') {
            $response = sinhVienProxyRequest('POST', '/lhp', [
                'ma_lhp' => trim($payload['ma_lhp'] ?? ''),
                'mon_hoc_id' => trim($payload['mon_hoc_id'] ?? ''),
                'hoc_ky_id' => trim($payload['hoc_ky_id'] ?? ''),
                'giang_vien_id' => trim($payload['giang_vien_id'] ?? ''),
                'ty_le_cc' => (float)($payload['ty_le_cc'] ?? 10),
                'ty_le_gk' => (float)($payload['ty_le_gk'] ?? 30),
                'ty_le_ck' => (float)($payload['ty_le_ck'] ?? 60),
            ]);
            if (!empty($response['success'])) {
                $notice = $response['message'] ?? 'Da tao LHP';
            } else {
                $error = $response['message'] ?? 'Khong the tao LHP';
            }
            $activeTab = 'structure';
        } elseif ($action === 'update_lhp_meta') {
            $response = sinhVienProxyRequest(
                'PUT',
                '/lhp/' . trim($payload['lhp_id'] ?? ''),
                [
                    'mon_hoc_id' => trim($payload['mon_hoc_id'] ?? ''),
                    'hoc_ky_id' => trim($payload['hoc_ky_id'] ?? ''),
                    'giang_vien_id' => trim($payload['giang_vien_id'] ?? '') ?: null,
                    'ty_le_cc' => (float)($payload['ty_le_cc'] ?? 10),
                    'ty_le_gk' => (float)($payload['ty_le_gk'] ?? 30),
                    'ty_le_ck' => (float)($payload['ty_le_ck'] ?? 60),
                    'trang_thai' => trim($payload['trang_thai'] ?? 'MO'),
                ]
            );
            if (!empty($response['success'])) {
                $notice = $response['message'] ?? 'Da cap nhat LHP';
            } else {
                $error = $response['message'] ?? 'Khong the cap nhat LHP';
            }
            $activeTab = 'lhp';
        } elseif ($action === 'assign_lecturer') {
            $response = sinhVienProxyRequest(
                'PUT',
                '/lhp/' . trim($payload['lhp_id'] ?? ''),
                ['giang_vien_id' => trim($payload['giang_vien_id'] ?? '')]
            );
            if (!empty($response['success'])) {
                $notice = $response['message'] ?? 'Da phan cong giang vien';
            } else {
                $error = $response['message'] ?? 'Khong the phan cong giang vien';
            }
            $activeTab = 'lhp';
        } elseif ($action === 'add_student_to_lhp') {
            $response = sinhVienProxyRequest(
                'POST',
                '/lhp/' . trim($payload['lhp_id'] ?? '') . '/sinh-vien',
                ['sinh_vien_id' => trim($payload['sinh_vien_id'] ?? '')]
            );
            if (!empty($response['success'])) {
                $notice = $response['message'] ?? 'Da them sinh vien vao LHP';
            } else {
                $error = $response['message'] ?? 'Khong the them sinh vien vao LHP';
            }
            $reopenStudentModalLhpId = trim($payload['lhp_id'] ?? '');
            $activeTab = 'lhp';
        } elseif ($action === 'remove_student_from_lhp') {
            $response = sinhVienProxyRequest(
                'DELETE',
                '/lhp/' . trim($payload['lhp_id'] ?? '') . '/sinh-vien/' . trim($payload['sinh_vien_id'] ?? '')
            );
            if (!empty($response['success'])) {
                $notice = $response['message'] ?? 'Da xoa sinh vien khoi LHP';
            } else {
                $error = $response['message'] ?? 'Khong the xoa sinh vien khoi LHP';
            }
            $reopenStudentModalLhpId = trim($payload['lhp_id'] ?? '');
            $activeTab = 'lhp';
        } else {
            throw new RuntimeException('Thao tac khong hop le');
        }
    } catch (Throwable $exc) {
        $error = $exc->getMessage();
    }
}

$hocKys = [];
$monHocs = [];
$giangViens = [];
$students = [];
$namHocs = [];
$lhps = [];

if ($pdo) {
    $hocKys = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai, ngay_bat_dau, ngay_ket_thuc FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC')->fetchAll(PDO::FETCH_ASSOC);
    $monHocs = $pdo->query('SELECT mon_hoc_id, ma_mon, ten_mon, so_tin_chi FROM mon_hoc ORDER BY ma_mon ASC')->fetchAll(PDO::FETCH_ASSOC);
    $giangViens = $pdo->query('SELECT giang_vien_id, ma_gv, ho_ten FROM giang_vien ORDER BY ma_gv ASC')->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($hocKys as $hk) {
    if (!in_array($hk['nam_hoc'], $namHocs, true)) {
        $namHocs[] = $hk['nam_hoc'];
    }
}

if ($selectedNamHoc === '' && !empty($namHocs)) {
    $selectedNamHoc = $namHocs[0];
}

$hocKyOptions = array_values(array_filter($hocKys, function ($hk) use ($selectedNamHoc) {
    return $selectedNamHoc === '' || $hk['nam_hoc'] === $selectedNamHoc;
}));

if ($selectedHocKyId === '' && !empty($hocKyOptions)) {
    foreach ($hocKyOptions as $hk) {
        if (!empty($hk['is_hien_tai'])) {
            $selectedHocKyId = $hk['hoc_ky_id'];
            break;
        }
    }
    if ($selectedHocKyId === '') {
        $selectedHocKyId = $hocKyOptions[0]['hoc_ky_id'];
    }
}

$selectedHocKyLabel = '';
foreach ($hocKys as $hk) {
    if (($hk['hoc_ky_id'] ?? '') === $selectedHocKyId) {
        $selectedHocKyLabel = (string)($hk['ten_hoc_ky'] ?? '');
        break;
    }
}

$yearStatusMap = [];
foreach ($namHocs as $nh) {
    $yearStatusMap[$nh] = false;
}
foreach ($hocKys as $hk) {
    $nh = (string)($hk['nam_hoc'] ?? '');
    if ($nh !== '' && !empty($hk['is_hien_tai'])) {
        $yearStatusMap[$nh] = true;
    }
}

$studentsRes = sinhVienProxyRequest('GET', '/sinh-vien', null, []);
$students = $studentsRes['data'] ?? [];

$lhpRes = sinhVienProxyRequest('GET', '/lhp', null, ['hoc_ky_id' => $selectedHocKyId]);
if (!empty($lhpRes['success']) && is_array($lhpRes['data'] ?? null)) {
    $lhps = $lhpRes['data'];
} elseif ($pdo) {
    $sql = "
        SELECT lhp.lhp_id, lhp.ma_lhp, lhp.mon_hoc_id, mh.ma_mon, mh.ten_mon, mh.so_tin_chi,
               lhp.hoc_ky_id, hk.ten_hoc_ky,
               lhp.giang_vien_id, gv.ho_ten AS ten_gv,
               lhp.ty_le_cc, lhp.ty_le_gk, lhp.ty_le_ck,
               lhp.trang_thai,
               (SELECT COUNT(*) FROM ds_lhp ds WHERE ds.lhp_id = lhp.lhp_id) AS so_sv
        FROM lop_hoc_phan lhp
        JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
        JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
        LEFT JOIN giang_vien gv ON gv.giang_vien_id = lhp.giang_vien_id
        WHERE lhp.hoc_ky_id = :hoc_ky_id
        ORDER BY hk.ten_hoc_ky DESC, lhp.ma_lhp ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['hoc_ky_id' => $selectedHocKyId]);
    $lhps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $apiMessage = trim((string)($lhpRes['message'] ?? ''));
    $fallbackMessage = 'Danh sach LHP dang fallback truc tiep tu CSDL do Python API khong phan hoi. Hay bat API: python src/python/main.py';
    if ($apiMessage !== '') {
        $fallbackMessage .= ' Chi tiet: ' . $apiMessage;
    }
    $error = $error === '' ? $fallbackMessage : ($error . ' | ' . $fallbackMessage);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh muc co so</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <style>
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 500;
        }

        .modal-panel {
            background: #fff;
            border-radius: 12px;
            width: min(860px, 92vw);
            max-height: 86vh;
            overflow: auto;
            padding: 16px;
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

        .btn-inline-danger {
            border: 0;
            border-radius: 8px;
            background: #dc2626;
            color: #fff;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-inline-danger:hover {
            background: #b91c1c;
        }

        .tab-switcher {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .tab-switcher .tab-btn {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .tab-switcher .tab-btn.active {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: grid;
            gap: 14px;
        }

        .page-stack {
            display: grid;
            gap: 14px;
        }

        .section-card {
            margin: 0;
        }

        .stepper-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .stepper-item {
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .crumb {
            font-size: 13px;
            color: #334155;
            margin-bottom: 10px;
        }

        .step-link {
            color: #0f172a;
            text-decoration: underline;
            font-weight: 600;
        }

        .badge-open {
            background: #dcfce7;
            color: #166534;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-close {
            background: #e2e8f0;
            color: #475569;
            border-radius: 999px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .step-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern th,
        .table-modern td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .table-modern th {
            background: #f8fafc;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            text-align: left;
        }

        .table-modern td {
            color: #1f2937;
            overflow-wrap: anywhere;
        }

        .step1-table,
        .step2-table,
        .step3-table {
            min-width: 760px;
        }

        .step1-table {
            min-width: 620px;
        }

        .step3-table {
            min-width: 820px;
        }

        .cell-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cell-actions .btn-secondary {
            white-space: nowrap;
            padding: 6px 10px;
        }

        @media (max-width: 992px) {
            .stepper-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner page-stack" id="quan-ly-lhp">
            <div class="dashboard-header">
                <div>
                    <h1>Danh muc co so</h1>
                    <p class="muted-text">Tab 1 tra cuu nhanh LHP. Tab 2 quan ly theo luong Nam hoc -> Hoc ky -> Lop hoc phan.</p>
                </div>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="alert-info"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="tab-switcher" role="tablist" aria-label="Danh muc co so tabs">
                <button type="button" class="tab-btn <?php echo $activeTab === 'lhp' ? 'active' : ''; ?>" data-tab-target="lhp">Tab 1: Danh sach LHP</button>
                <button type="button" class="tab-btn <?php echo $activeTab === 'structure' ? 'active' : ''; ?>" data-tab-target="structure">Tab 2: Quan ly Nam hoc & Hoc ky</button>
            </div>

            <section class="tab-pane <?php echo $activeTab === 'lhp' ? 'active' : ''; ?>" data-tab-pane="lhp">
                <div class="card section-card">
                    <form method="get" style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                        <input type="hidden" name="tab" id="active-tab-query" value="<?php echo htmlspecialchars($activeTab); ?>">
                        <div class="form-group" style="margin:0;">
                            <label>Nam hoc</label>
                            <select name="nam_hoc" id="nam-hoc-select">
                                <?php foreach ($namHocs as $namHoc): ?>
                                    <option value="<?php echo htmlspecialchars($namHoc); ?>" <?php echo $selectedNamHoc === $namHoc ? 'selected' : ''; ?>><?php echo htmlspecialchars($namHoc); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Hoc ky</label>
                            <select name="hoc_ky_id" id="hoc-ky-select" required>
                                <?php foreach ($hocKyOptions as $hk): ?>
                                    <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selectedHocKyId === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (Ky ' . $hk['ky_hoc'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn-primary" type="submit">Tai danh sach</button>
                    </form>
                </div>

                <div class="card section-card">
                    <div class="form-group" style="margin:0;max-width:380px;">
                        <label>Tim ma LHP / ma mon / ten mon</label>
                        <input id="lhp-search-input" placeholder="Nhap tu khoa de loc nhanh...">
                    </div>
                </div>

                <div class="card section-card">
                    <h3 style="margin-bottom:10px;">Danh sach lop hoc phan</h3>
                    <div style="overflow-x:auto;">
                        <table class="table-modern step3-table">
                            <thead>
                                <tr>
                                    <th>Ma LHP</th>
                                    <th>Mon hoc</th>
                                    <th>TC</th>
                                    <th>Giang vien</th>
                                    <th>Si so</th>
                                    <th>Trang thai</th>
                                    <th>Thao tac</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lhps)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Khong co LHP trong hoc ky da chon.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lhps as $lhp): ?>
                                        <tr class="lhp-search-row" data-search="<?php echo htmlspecialchars(strtolower(trim(($lhp['ma_lhp'] ?? '') . ' ' . ($lhp['ma_mon'] ?? '') . ' ' . ($lhp['ten_mon'] ?? '')))); ?>">
                                            <td><?php echo htmlspecialchars($lhp['ma_lhp']); ?></td>
                                            <td><?php echo htmlspecialchars(($lhp['ma_mon'] ?? '') . ' - ' . ($lhp['ten_mon'] ?? '')); ?></td>
                                            <td><?php echo (int)($lhp['so_tin_chi'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars($lhp['ten_gv'] ?? '-- Chua phan cong --'); ?></td>
                                            <td><?php echo (int)($lhp['so_sv'] ?? 0); ?>/45</td>
                                            <td><span class="badge-state"><?php echo htmlspecialchars($lhp['trang_thai'] ?? '--'); ?></span></td>
                                            <td class="cell-actions">
                                                <button type="button" class="btn-secondary open-assign" data-lhp-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>" data-ma-lhp="<?php echo htmlspecialchars($lhp['ma_lhp']); ?>">Phan cong giang vien</button>
                                                <button type="button" class="btn-secondary open-students" data-lhp-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>" data-ma-lhp="<?php echo htmlspecialchars($lhp['ma_lhp']); ?>">Quan ly sinh vien</button>
                                                <button
                                                    type="button"
                                                    class="btn-secondary open-edit-lhp"
                                                    data-lhp-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"
                                                    data-ma-lhp="<?php echo htmlspecialchars($lhp['ma_lhp']); ?>"
                                                    data-mon-hoc-id="<?php echo htmlspecialchars((string)($lhp['mon_hoc_id'] ?? '')); ?>"
                                                    data-hoc-ky-id="<?php echo htmlspecialchars((string)($lhp['hoc_ky_id'] ?? '')); ?>"
                                                    data-giang-vien-id="<?php echo htmlspecialchars((string)($lhp['giang_vien_id'] ?? '')); ?>"
                                                    data-ty-le-cc="<?php echo htmlspecialchars((string)($lhp['ty_le_cc'] ?? '10')); ?>"
                                                    data-ty-le-gk="<?php echo htmlspecialchars((string)($lhp['ty_le_gk'] ?? '30')); ?>"
                                                    data-ty-le-ck="<?php echo htmlspecialchars((string)($lhp['ty_le_ck'] ?? '60')); ?>"
                                                    data-trang-thai="<?php echo htmlspecialchars((string)($lhp['trang_thai'] ?? 'MO')); ?>">
                                                    Chinh sua lop hoc phan
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="tab-pane <?php echo $activeTab === 'structure' ? 'active' : ''; ?>" data-tab-pane="structure">
                <div class="card section-card">
                    <div class="crumb">
                        <a class="step-link" href="?tab=structure">Danh muc co so</a>
                        &gt;
                        <?php if ($selectedNamHoc !== ''): ?>
                            <a class="step-link" href="?tab=structure&amp;nam_hoc=<?php echo urlencode($selectedNamHoc); ?>"><?php echo htmlspecialchars($selectedNamHoc); ?></a>
                        <?php else: ?>
                            -- Nam hoc --
                        <?php endif; ?>
                        &gt;
                        <?php if ($selectedHocKyId !== ''): ?>
                            <a class="step-link" href="?tab=structure&amp;nam_hoc=<?php echo urlencode($selectedNamHoc); ?>&amp;hoc_ky_id=<?php echo urlencode($selectedHocKyId); ?>"><?php echo htmlspecialchars($selectedHocKyLabel !== '' ? $selectedHocKyLabel : '-- Hoc ky --'); ?></a>
                        <?php else: ?>
                            -- Hoc ky --
                        <?php endif; ?>
                        &gt; Danh sach LHP
                    </div>
                    <div class="stepper-row">
                        <div class="stepper-item"><strong>Buoc 1</strong><br>Nam hoc</div>
                        <div class="stepper-item"><strong>Buoc 2</strong><br>Hoc ky</div>
                        <div class="stepper-item"><strong>Buoc 3</strong><br>LHP + phan cong</div>
                    </div>
                </div>

                <div class="card section-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <h3 style="margin:0;">Buoc 1 - Danh sach nam hoc</h3>
                        <button type="button" class="btn-primary" id="open-create-year">+ Tao nam hoc</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table-modern step1-table">
                            <thead>
                                <tr>
                                    <th>Nam hoc</th>
                                    <th>Trang thai</th>
                                    <th>Dieu huong</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($namHocs)): ?>
                                    <tr>
                                        <td colspan="3" style="text-align:center;">Chua co nam hoc.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($namHocs as $nh): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($nh); ?></td>
                                            <td><?php echo !empty($yearStatusMap[$nh]) ? '<span class="badge-open">Dang mo</span>' : '<span class="badge-close">Da dong</span>'; ?></td>
                                            <td><a class="btn-secondary" href="?tab=structure&amp;nam_hoc=<?php echo urlencode($nh); ?>">Xem hoc ky</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card section-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <h3 style="margin:0;">Buoc 2 - Danh sach hoc ky (<?php echo htmlspecialchars($selectedNamHoc !== '' ? $selectedNamHoc : '-- Chua chon nam hoc --'); ?>)</h3>
                        <button type="button" class="btn-primary" id="open-create-semester" <?php echo $selectedNamHoc === '' ? 'disabled' : ''; ?>>+ Tao hoc ky</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table-modern step2-table">
                            <thead>
                                <tr>
                                    <th>Ten HK</th>
                                    <th>Ky</th>
                                    <th>Hien tai</th>
                                    <th>Ngay BD</th>
                                    <th>Ngay KT</th>
                                    <th>Dieu huong</th>
                                    <th>Sua</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hocKyOptions)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Chua co hoc ky.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($hocKyOptions as $hk): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($hk['ten_hoc_ky']); ?></td>
                                            <td>Ky <?php echo (int)($hk['ky_hoc'] ?? 0); ?></td>
                                            <td><?php echo !empty($hk['is_hien_tai']) ? 'Co' : 'Khong'; ?></td>
                                            <td><?php echo htmlspecialchars((string)($hk['ngay_bat_dau'] ?? '--')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($hk['ngay_ket_thuc'] ?? '--')); ?></td>
                                            <td><a class="btn-secondary" href="?tab=structure&amp;nam_hoc=<?php echo urlencode($selectedNamHoc); ?>&amp;hoc_ky_id=<?php echo urlencode((string)$hk['hoc_ky_id']); ?>">Xem LHP</a></td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn-secondary open-edit-semester"
                                                    data-hoc-ky-id="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>"
                                                    data-ten-hoc-ky="<?php echo htmlspecialchars($hk['ten_hoc_ky']); ?>"
                                                    data-nam-hoc="<?php echo htmlspecialchars($hk['nam_hoc']); ?>"
                                                    data-ky-hoc="<?php echo htmlspecialchars((string)$hk['ky_hoc']); ?>"
                                                    data-is-hien-tai="<?php echo !empty($hk['is_hien_tai']) ? '1' : '0'; ?>"
                                                    data-ngay-bat-dau="<?php echo htmlspecialchars((string)($hk['ngay_bat_dau'] ?? '')); ?>"
                                                    data-ngay-ket-thuc="<?php echo htmlspecialchars((string)($hk['ngay_ket_thuc'] ?? '')); ?>">
                                                    Sua
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card section-card">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
                        <h3 style="margin:0;">Buoc 3 - Danh sach LHP (<?php echo htmlspecialchars($selectedHocKyLabel !== '' ? $selectedHocKyLabel : '-- Chua chon hoc ky --'); ?>)</h3>
                        <div class="step-actions">
                            <button type="button" class="btn-primary" id="open-create-lhp" <?php echo $selectedHocKyId === '' ? 'disabled' : ''; ?>>+ Tao LHP</button>
                        </div>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table-modern step3-table">
                            <thead>
                                <tr>
                                    <th>Ma LHP</th>
                                    <th>Ten mon</th>
                                    <th>TC</th>
                                    <th>Giang vien</th>
                                    <th>Si so</th>
                                    <th>Thao tac</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lhps)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;">Khong co LHP trong hoc ky da chon.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lhps as $lhp): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lhp['ma_lhp']); ?></td>
                                            <td><?php echo htmlspecialchars(($lhp['ma_mon'] ?? '') . ' - ' . ($lhp['ten_mon'] ?? '')); ?></td>
                                            <td><?php echo (int)($lhp['so_tin_chi'] ?? 0); ?></td>
                                            <td><?php echo htmlspecialchars($lhp['ten_gv'] ?? '-- Chua phan cong --'); ?></td>
                                            <td><?php echo (int)($lhp['so_sv'] ?? 0); ?>/45</td>
                                            <td class="cell-actions">
                                                <button type="button" class="btn-secondary open-assign" data-lhp-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>" data-ma-lhp="<?php echo htmlspecialchars($lhp['ma_lhp']); ?>">GV</button>
                                                <button type="button" class="btn-secondary open-students" data-lhp-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>" data-ma-lhp="<?php echo htmlspecialchars($lhp['ma_lhp']); ?>">SV</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <div class="modal-backdrop" id="edit-lhp-modal">
        <div class="modal-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Chinh sua lop hoc phan</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <p class="muted-text" id="edit-lhp-caption" style="margin:8px 0 12px;">--</p>

            <form method="post" style="display:grid;grid-template-columns:repeat(2,minmax(220px,1fr));gap:12px;align-items:end;">
                <input type="hidden" name="form_action" value="update_lhp_meta">
                <input type="hidden" name="lhp_id" id="edit-lhp-id" value="">
                <div class="form-group" style="margin:0;">
                    <label>Mon hoc</label>
                    <select name="mon_hoc_id" id="edit-mon-hoc-id" required>
                        <?php foreach ($monHocs as $mon): ?>
                            <option value="<?php echo htmlspecialchars($mon['mon_hoc_id']); ?>"><?php echo htmlspecialchars($mon['ma_mon'] . ' - ' . $mon['ten_mon']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Hoc ky</label>
                    <select name="hoc_ky_id" id="edit-hoc-ky-id" required>
                        <?php foreach ($hocKys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>"><?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' - ' . $hk['nam_hoc']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Giang vien</label>
                    <select name="giang_vien_id" id="edit-giang-vien-id">
                        <option value="">-- Chua phan cong --</option>
                        <?php foreach ($giangViens as $gv): ?>
                            <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Trang thai</label>
                    <select name="trang_thai" id="edit-trang-thai" required>
                        <option value="MO">MO</option>
                        <option value="DANG_NHAP">DANG_NHAP</option>
                        <option value="CHO_DUYET">CHO_DUYET</option>
                        <option value="DA_DUYET">DA_DUYET</option>
                        <option value="DONG">DONG</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le CC (%)</label>
                    <input type="number" name="ty_le_cc" id="edit-ty-le-cc" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le GK (%)</label>
                    <input type="number" name="ty_le_gk" id="edit-ty-le-gk" min="0" max="100" step="0.01" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le CK (%)</label>
                    <input type="number" name="ty_le_ck" id="edit-ty-le-ck" min="0" max="100" step="0.01" required>
                </div>
                <button class="btn-primary" type="submit">Luu thay doi</button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="assign-modal">
        <div class="modal-panel" style="width:min(520px,92vw);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Phan cong giang vien</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <p class="muted-text" id="assign-caption" style="margin:8px 0 12px;">--</p>

            <form method="post">
                <input type="hidden" name="form_action" value="assign_lecturer">
                <input type="hidden" name="lhp_id" id="assign-lhp-id" value="">
                <div class="form-group">
                    <label>Giang vien phu trach</label>
                    <select name="giang_vien_id" required>
                        <option value="">-- Chon giang vien --</option>
                        <?php foreach ($giangViens as $gv): ?>
                            <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn-primary" type="submit">Luu phan cong</button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="student-modal">
        <div class="modal-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Quan ly sinh vien LHP</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <p class="muted-text" id="student-caption" style="margin:8px 0 12px;">--</p>

            <div class="card" style="margin-bottom:12px;">
                <h4 style="margin-bottom:8px;">Danh sach hien tai</h4>
                <div style="overflow-x:auto;">
                    <table class="table-modern" id="selected-lhp-table">
                        <thead>
                            <tr>
                                <th>MSV</th>
                                <th>Ho ten</th>
                                <th>Lop</th>
                                <th>Mon hoc</th>
                                <th>Thao tac</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" style="text-align:center;">Chua tai du lieu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form method="post" style="max-width:520px;">
                <input type="hidden" name="form_action" value="add_student_to_lhp">
                <input type="hidden" name="lhp_id" id="add-lhp-id" value="">
                <div class="form-group">
                    <label>Them sinh vien vao LHP</label>
                    <select name="sinh_vien_id" id="add-student-select" required>
                        <option value="">-- Chon sinh vien --</option>
                        <?php foreach ($students as $sv): ?>
                            <?php $isExpelled = (($sv['trang_thai'] ?? '') === 'BUOC_THOI_HOC'); ?>
                            <option
                                value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"
                                <?php echo $isExpelled ? 'disabled' : ''; ?>>
                                <?php
                                echo htmlspecialchars(
                                    $sv['msv'] .
                                        ' - ' .
                                        $sv['ho_ten'] .
                                        ($isExpelled ? ' [BUOC_THOI_HOC - Khong duoc them vao LHP]' : '')
                                );
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="muted-text">Sinh vien trang thai BUOC_THOI_HOC se bi chan khi them vao LHP.</small>
                </div>
                <button class="btn-primary" type="submit">Them vao LHP</button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="create-year-modal">
        <div class="modal-panel" style="width:min(520px,92vw);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Tao nam hoc</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <form method="post" id="create-year-form" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;">
                <input type="hidden" name="form_action" value="create_nam_hoc">
                <input type="hidden" name="nam_hoc" id="year-label-hidden" value="">
                <div class="form-group" style="margin:0;">
                    <label>Nam bat dau</label>
                    <input id="year-start-input" type="number" min="2000" max="2100" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Nam ket thuc</label>
                    <input id="year-end-preview" type="number" readonly>
                </div>
                <div class="form-group" style="margin:0;grid-column:1 / -1;">
                    <label>Nam hoc</label>
                    <input id="year-label-preview" readonly>
                </div>
                <label style="display:flex;gap:8px;align-items:center;grid-column:1 / -1;">
                    <input type="checkbox" name="set_hien_tai" value="1">
                    <span>Dat hoc ky mac dinh la hien tai</span>
                </label>
                <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;gap:8px;">
                    <button class="btn-secondary close-modal" type="button">Huy</button>
                    <button class="btn-primary" type="submit">Tao</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="create-semester-modal">
        <div class="modal-panel" style="width:min(560px,92vw);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Tao hoc ky</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <form method="post" id="create-semester-form" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;">
                <input type="hidden" name="form_action" value="create_hoc_ky">
                <div class="form-group" style="margin:0;grid-column:1 / -1;">
                    <label>Thuoc nam hoc</label>
                    <input id="semester-year-readonly" value="<?php echo htmlspecialchars($selectedNamHoc); ?>" readonly>
                    <input type="hidden" name="nam_hoc" id="semester-year-hidden" value="<?php echo htmlspecialchars($selectedNamHoc); ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Hoc ky</label>
                    <select name="ky_hoc" id="semester-ky-select" required>
                        <option value="1">Hoc ky 1</option>
                        <option value="2">Hoc ky 2</option>
                        <option value="3">Hoc ky he</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ten hoc ky</label>
                    <input name="ten_hoc_ky" id="semester-name-input" placeholder="VD: Hoc ky 1" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ngay bat dau</label>
                    <input type="date" name="ngay_bat_dau">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ngay ket thuc</label>
                    <input type="date" name="ngay_ket_thuc">
                </div>
                <label style="display:flex;gap:8px;align-items:center;grid-column:1 / -1;">
                    <input type="checkbox" name="is_hien_tai" value="1">
                    <span>Dat lam hoc ky hien tai</span>
                </label>
                <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;gap:8px;">
                    <button class="btn-secondary close-modal" type="button">Huy</button>
                    <button class="btn-primary" type="submit">Tao</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="edit-semester-modal">
        <div class="modal-panel" style="width:min(560px,92vw);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Sua hoc ky</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <form method="post" id="edit-semester-form" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;">
                <input type="hidden" name="form_action" value="update_hoc_ky">
                <input type="hidden" name="hoc_ky_id" id="edit-semester-id" value="">
                <div class="form-group" style="margin:0;">
                    <label>Ten hoc ky</label>
                    <input name="ten_hoc_ky" id="edit-semester-name" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Nam hoc</label>
                    <input name="nam_hoc" id="edit-semester-year" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ky hoc</label>
                    <input type="number" name="ky_hoc" id="edit-semester-ky" min="1" max="3" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Hien tai</label>
                    <label style="display:flex;gap:8px;align-items:center;height:38px;">
                        <input type="checkbox" name="is_hien_tai" id="edit-semester-current" value="1">
                        <span>Dat hien tai</span>
                    </label>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ngay bat dau</label>
                    <input type="date" name="ngay_bat_dau" id="edit-semester-start">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ngay ket thuc</label>
                    <input type="date" name="ngay_ket_thuc" id="edit-semester-end">
                </div>
                <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;gap:8px;">
                    <button class="btn-secondary close-modal" type="button">Huy</button>
                    <button class="btn-primary" type="submit">Luu</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="create-lhp-modal">
        <div class="modal-panel" style="width:min(640px,92vw);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <h3 style="margin:0;">Tao lop hoc phan</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <form method="post" id="create-lhp-form-popup" style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;">
                <input type="hidden" name="form_action" value="create_lhp">
                <div class="form-group" style="margin:0;grid-column:1 / -1;">
                    <label>Thuoc hoc ky</label>
                    <input value="<?php echo htmlspecialchars(($selectedHocKyLabel !== '' ? $selectedHocKyLabel : '--') . ' / ' . ($selectedNamHoc !== '' ? $selectedNamHoc : '--')); ?>" readonly>
                    <input type="hidden" name="hoc_ky_id" id="create-lhp-hoc-ky-id" value="<?php echo htmlspecialchars($selectedHocKyId); ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ma mon hoc</label>
                    <select name="mon_hoc_id" id="popup-mon-hoc-id" required>
                        <option value="">-- Chon mon --</option>
                        <?php foreach ($monHocs as $mon): ?>
                            <option value="<?php echo htmlspecialchars($mon['mon_hoc_id']); ?>" data-ma-mon="<?php echo htmlspecialchars($mon['ma_mon']); ?>"><?php echo htmlspecialchars($mon['ma_mon'] . ' - ' . $mon['ten_mon']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ma LHP</label>
                    <input name="ma_lhp" id="popup-ma-lhp" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le CC (%)</label>
                    <input name="ty_le_cc" type="number" min="0" max="100" step="0.01" value="10" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le GK (%)</label>
                    <input name="ty_le_gk" type="number" min="0" max="100" step="0.01" value="30" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ty le CK (%)</label>
                    <input name="ty_le_ck" type="number" min="0" max="100" step="0.01" value="60" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Giang vien</label>
                    <select name="giang_vien_id">
                        <option value="">-- Chua phan cong --</option>
                        <?php foreach ($giangViens as $gv): ?>
                            <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;gap:8px;">
                    <button class="btn-secondary close-modal" type="button">Huy</button>
                    <button class="btn-primary" type="submit">Tao</button>
                </div>
            </form>
        </div>
    </div>

    <form id="remove-student-form" method="post" style="display:none;">
        <input type="hidden" name="form_action" value="remove_student_from_lhp">
        <input type="hidden" name="lhp_id" id="remove-lhp-id" value="">
        <input type="hidden" name="sinh_vien_id" id="remove-sv-id" value="">
    </form>

    <script>
        const hocKyByYear = <?php echo json_encode(array_values($hocKys), JSON_UNESCAPED_UNICODE); ?>;
        const currentTab = <?php echo json_encode($activeTab, JSON_UNESCAPED_UNICODE); ?>;
        const selectedHocKyLabel = <?php echo json_encode($selectedHocKyLabel, JSON_UNESCAPED_UNICODE); ?>;
        const selectedNamHoc = <?php echo json_encode($selectedNamHoc, JSON_UNESCAPED_UNICODE); ?>;
        const selectedHocKyId = <?php echo json_encode($selectedHocKyId, JSON_UNESCAPED_UNICODE); ?>;
        const reopenStudentModalLhpId = <?php echo json_encode($reopenStudentModalLhpId, JSON_UNESCAPED_UNICODE); ?>;

        const tabButtons = document.querySelectorAll('[data-tab-target]');
        const tabPanes = document.querySelectorAll('[data-tab-pane]');
        const activeTabQueryInput = document.getElementById('active-tab-query');

        function setActiveTab(tabName) {
            tabButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.tabTarget === tabName));
            tabPanes.forEach((pane) => pane.classList.toggle('active', pane.dataset.tabPane === tabName));
            if (activeTabQueryInput) activeTabQueryInput.value = tabName;
        }

        tabButtons.forEach((btn) => btn.addEventListener('click', () => setActiveTab(btn.dataset.tabTarget || 'lhp')));
        setActiveTab(currentTab || 'lhp');

        document.querySelectorAll('form[method="post"]').forEach((form) => {
            form.addEventListener('submit', () => {
                const activeBtn = document.querySelector('.tab-btn.active');
                const value = (activeBtn && activeBtn.dataset.tabTarget) ? activeBtn.dataset.tabTarget : (currentTab || 'lhp');
                let hidden = form.querySelector('input[name="active_tab"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'active_tab';
                    form.appendChild(hidden);
                }
                hidden.value = value;
            });
        });

        const namHocSelect = document.getElementById('nam-hoc-select');
        const hocKySelect = document.getElementById('hoc-ky-select');

        function rebuildHocKy() {
            if (!namHocSelect || !hocKySelect) return;
            const selectedYear = namHocSelect.value;
            const oldValue = hocKySelect.value;
            hocKySelect.innerHTML = '';
            const options = hocKyByYear.filter((item) => item.nam_hoc === selectedYear);
            options.forEach((hk, idx) => {
                const opt = document.createElement('option');
                opt.value = hk.hoc_ky_id;
                opt.textContent = `${hk.ten_hoc_ky} (Ky ${hk.ky_hoc})`;
                if (oldValue === hk.hoc_ky_id || (!oldValue && idx === 0)) opt.selected = true;
                hocKySelect.appendChild(opt);
            });
        }
        namHocSelect?.addEventListener('change', rebuildHocKy);

        const lhpSearchInput = document.getElementById('lhp-search-input');
        lhpSearchInput?.addEventListener('input', () => {
            const keyword = (lhpSearchInput.value || '').toLowerCase().trim();
            document.querySelectorAll('.lhp-search-row').forEach((row) => {
                const haystack = row.dataset.search || '';
                row.style.display = haystack.includes(keyword) ? '' : 'none';
            });
        });

        const editLhpModal = document.getElementById('edit-lhp-modal');
        const assignModal = document.getElementById('assign-modal');
        const studentModal = document.getElementById('student-modal');
        const createYearModal = document.getElementById('create-year-modal');
        const createSemesterModal = document.getElementById('create-semester-modal');
        const editSemesterModal = document.getElementById('edit-semester-modal');
        const createLhpModal = document.getElementById('create-lhp-modal');

        function closeModals() {
            [editLhpModal, assignModal, studentModal, createYearModal, createSemesterModal, editSemesterModal, createLhpModal].forEach((m) => {
                if (m) m.style.display = 'none';
            });
        }

        document.querySelectorAll('.close-modal').forEach((btn) => btn.addEventListener('click', closeModals));
        [editLhpModal, assignModal, studentModal, createYearModal, createSemesterModal, editSemesterModal, createLhpModal].forEach((modal) => {
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModals();
            });
        });

        const editLhpCaption = document.getElementById('edit-lhp-caption');
        document.querySelectorAll('.open-edit-lhp').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-lhp-id').value = btn.dataset.lhpId || '';
                document.getElementById('edit-mon-hoc-id').value = btn.dataset.monHocId || '';
                document.getElementById('edit-hoc-ky-id').value = btn.dataset.hocKyId || '';
                document.getElementById('edit-giang-vien-id').value = btn.dataset.giangVienId || '';
                document.getElementById('edit-trang-thai').value = btn.dataset.trangThai || 'MO';
                document.getElementById('edit-ty-le-cc').value = btn.dataset.tyLeCc || '10';
                document.getElementById('edit-ty-le-gk').value = btn.dataset.tyLeGk || '30';
                document.getElementById('edit-ty-le-ck').value = btn.dataset.tyLeCk || '60';
                editLhpCaption.textContent = `LHP: ${btn.dataset.maLhp || ''}`;
                editLhpModal.style.display = 'flex';
            });
        });

        const assignCaption = document.getElementById('assign-caption');
        const assignLhpId = document.getElementById('assign-lhp-id');
        document.querySelectorAll('.open-assign').forEach((btn) => {
            btn.addEventListener('click', () => {
                assignLhpId.value = btn.dataset.lhpId || '';
                assignCaption.textContent = `LHP: ${btn.dataset.maLhp || ''}`;
                assignModal.style.display = 'flex';
            });
        });

        const studentCaption = document.getElementById('student-caption');
        const addLhpId = document.getElementById('add-lhp-id');
        const removeLhpId = document.getElementById('remove-lhp-id');
        const removeSvId = document.getElementById('remove-sv-id');
        const removeStudentForm = document.getElementById('remove-student-form');
        const selectedLhpTbody = document.querySelector('#selected-lhp-table tbody');

        async function loadLhpStudents(lhpId) {
            selectedLhpTbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Dang tai...</td></tr>';
            try {
                const res = await fetch(`../../../api/sinh_vien.php?action=list_lhp_students&lhp_id=${encodeURIComponent(lhpId)}`);
                const raw = await res.json();
                const rows = raw.data || [];
                if (!rows.length) {
                    selectedLhpTbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">LHP chua co sinh vien.</td></tr>';
                    return;
                }
                selectedLhpTbody.innerHTML = '';
                rows.forEach((sv) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                    <td>${sv.msv || ''}</td>
                    <td>${sv.ho_ten || ''}</td>
                    <td>${sv.ma_lop || sv.ten_lop || ''}</td>
                    <td>${(sv.ma_mon || '') + ((sv.ten_mon ? ' - ' + sv.ten_mon : ''))}</td>
                    <td>
                        <button type="button" class="btn-inline-danger btn-remove-student" data-sv-id="${sv.sinh_vien_id || ''}" data-msv="${sv.msv || ''}">Xoa</button>
                    </td>
                `;
                    selectedLhpTbody.appendChild(tr);
                });
                selectedLhpTbody.querySelectorAll('.btn-remove-student').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const svId = btn.dataset.svId || '';
                        const msv = btn.dataset.msv || '';
                        if (!svId) return;
                        if (!window.confirm(`Ban co chac chan xoa sinh vien ${msv} khoi LHP nay khong?`)) return;
                        removeLhpId.value = lhpId;
                        removeSvId.value = svId;
                        removeStudentForm.submit();
                    });
                });
            } catch (_) {
                selectedLhpTbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Khong the tai danh sach sinh vien.</td></tr>';
            }
        }

        function openStudentModal(lhpId, maLhp) {
            addLhpId.value = lhpId;
            studentCaption.textContent = `LHP: ${maLhp}`;
            studentModal.style.display = 'flex';
            loadLhpStudents(lhpId);
        }

        document.querySelectorAll('.open-students').forEach((btn) => {
            btn.addEventListener('click', () => openStudentModal(btn.dataset.lhpId || '', btn.dataset.maLhp || ''));
        });

        if (reopenStudentModalLhpId) {
            const button = document.querySelector(`.open-students[data-lhp-id="${reopenStudentModalLhpId}"]`);
            const label = button ? (button.dataset.maLhp || reopenStudentModalLhpId) : reopenStudentModalLhpId;
            openStudentModal(reopenStudentModalLhpId, label);
        }

        document.getElementById('open-create-year')?.addEventListener('click', () => {
            createYearModal.style.display = 'flex';
        });
        document.getElementById('open-create-semester')?.addEventListener('click', () => {
            createSemesterModal.style.display = 'flex';
        });
        document.getElementById('open-create-lhp')?.addEventListener('click', () => {
            createLhpModal.style.display = 'flex';
        });

        const yearStartInput = document.getElementById('year-start-input');
        const yearEndPreview = document.getElementById('year-end-preview');
        const yearLabelPreview = document.getElementById('year-label-preview');
        const yearLabelHidden = document.getElementById('year-label-hidden');

        function syncYearForm() {
            const start = parseInt(yearStartInput?.value || '', 10);
            if (Number.isNaN(start)) {
                yearEndPreview.value = '';
                yearLabelPreview.value = '';
                yearLabelHidden.value = '';
                return;
            }
            const end = start + 1;
            const label = `${start}-${end}`;
            yearEndPreview.value = String(end);
            yearLabelPreview.value = label;
            yearLabelHidden.value = label;
        }
        yearStartInput?.addEventListener('input', syncYearForm);

        const semesterKySelect = document.getElementById('semester-ky-select');
        const semesterNameInput = document.getElementById('semester-name-input');

        function syncSemesterName() {
            if (!semesterKySelect || !semesterNameInput) return;
            const map = {
                '1': 'Hoc ky 1',
                '2': 'Hoc ky 2',
                '3': 'Hoc ky he'
            };
            semesterNameInput.value = map[semesterKySelect.value] || 'Hoc ky 1';
        }
        semesterKySelect?.addEventListener('change', syncSemesterName);
        syncSemesterName();

        document.querySelectorAll('.open-edit-semester').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-semester-id').value = btn.dataset.hocKyId || '';
                document.getElementById('edit-semester-name').value = btn.dataset.tenHocKy || '';
                document.getElementById('edit-semester-year').value = btn.dataset.namHoc || '';
                document.getElementById('edit-semester-ky').value = btn.dataset.kyHoc || '1';
                document.getElementById('edit-semester-current').checked = (btn.dataset.isHienTai || '0') === '1';
                document.getElementById('edit-semester-start').value = btn.dataset.ngayBatDau || '';
                document.getElementById('edit-semester-end').value = btn.dataset.ngayKetThuc || '';
                editSemesterModal.style.display = 'flex';
            });
        });

        const popupMonHocId = document.getElementById('popup-mon-hoc-id');
        const popupMaLhp = document.getElementById('popup-ma-lhp');
        const createLhpHocKyId = document.getElementById('create-lhp-hoc-ky-id');

        function suggestLhpCode() {
            if (!popupMonHocId || !popupMaLhp) return;
            const selectedOption = popupMonHocId.options[popupMonHocId.selectedIndex];
            const maMon = selectedOption?.dataset?.maMon || 'LHP';
            const hkCompact = (selectedHocKyLabel || 'HK1').replace(/\s+/g, '');
            const yearCode = (selectedNamHoc || '').replace(/[^0-9]/g, '').slice(-4) || '2526';
            popupMaLhp.value = `${maMon}-${hkCompact}-${yearCode}`;
            if (createLhpHocKyId && !createLhpHocKyId.value) createLhpHocKyId.value = selectedHocKyId || '';
        }
        popupMonHocId?.addEventListener('change', suggestLhpCode);
    </script>
</body>

</html>