<?php
session_start();
require_once __DIR__ . '/../../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../../config/database.php';

$currentRole = $_SESSION['user_role'] ?? '';
$isAdmin = $currentRole === 'ADMIN';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    $payload = $_POST;

    if ($action === 'deactivate_student' && !$isAdmin) {
        $response = ['success' => false, 'message' => 'Chi Admin duoc ngung hoat dong tai khoan'];
    } elseif ($action === 'deactivate_student') {
        $svId = $payload['sinh_vien_id'] ?? '';
        $svRes = sinhVienProxyRequest('GET', '/sinh-vien/' . $svId);
        if (empty($svRes['success'])) {
            $response = $svRes;
        } else {
            $taiKhoanId = $svRes['data']['tai_khoan_id'] ?? '';
            $updateRes = sinhVienProxyRequest('PUT', '/sinh-vien/' . $svId, ['trang_thai' => 'THOI_HOC']);
            if (!empty($updateRes['success']) && $taiKhoanId !== '') {
                $lockRes = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/khoa');
                $response = !empty($lockRes['success'])
                    ? ['success' => true, 'message' => 'Da ngung hoat dong sinh vien va khoa tai khoan']
                    : $lockRes;
            } else {
                $response = $updateRes;
            }
        }
    } elseif ($action === 'transfer_class') {
        $response = sinhVienProxyRequest(
            'PUT',
            '/sinh-vien/' . ($payload['sinh_vien_id'] ?? ''),
            ['lop_id' => $payload['lop_id_moi'] ?? null]
        );
    } elseif ($action === 'create_lhp') {
        $response = sinhVienProxyRequest('POST', '/lhp', [
            'ma_lhp' => $payload['ma_lhp'] ?? '',
            'giang_vien_id' => $payload['giang_vien_id'] ?? '',
            'mon_hoc_id' => $payload['mon_hoc_id'] ?? '',
            'hoc_ky_id' => $payload['hoc_ky_id'] ?? '',
            'ty_le_cc' => $payload['ty_le_cc'] ?? 10,
            'ty_le_gk' => $payload['ty_le_gk'] ?? 30,
            'ty_le_ck' => $payload['ty_le_ck'] ?? 60,
        ]);
    } elseif ($action === 'assign_lecturer') {
        $response = sinhVienProxyRequest(
            'PUT',
            '/lhp/' . ($payload['lhp_id'] ?? ''),
            ['giang_vien_id' => $payload['giang_vien_id'] ?? '']
        );
    } elseif ($action === 'add_student_to_lhp') {
        $response = sinhVienProxyRequest(
            'POST',
            '/lhp/' . ($payload['lhp_id'] ?? '') . '/sinh-vien',
            ['sinh_vien_id' => $payload['sinh_vien_id'] ?? '']
        );
    } elseif ($action === 'remove_student_from_lhp') {
        $response = sinhVienProxyRequest(
            'DELETE',
            '/lhp/' . ($payload['lhp_id'] ?? '') . '/sinh-vien/' . ($payload['sinh_vien_id'] ?? '')
        );
    } else {
        $response = ['success' => false, 'message' => 'Thao tac khong hop le'];
    }

    if (!empty($response['success'])) {
        $notice = $response['message'] ?? 'Thuc hien thanh cong';
    } else {
        $error = $response['message'] ?? 'Co loi xay ra';
    }
}

$keyword = trim($_GET['keyword'] ?? '');
$lopFilter = trim($_GET['lop_id'] ?? '');

$studentsRes = sinhVienProxyRequest('GET', '/sinh-vien', null, ['search' => $keyword, 'lop_id' => $lopFilter]);
$lhpRes = sinhVienProxyRequest('GET', '/lhp');

$pdo = getDatabaseConnection();
$lops = [];
$giangViens = [];
$monHocs = [];
$hocKys = [];
if ($pdo) {
    $lops = $pdo->query('SELECT lop_id, ma_lop, ten_lop FROM lop_sinh_hoat ORDER BY ma_lop')->fetchAll(PDO::FETCH_ASSOC);
    $giangViens = $pdo->query('SELECT giang_vien_id, ma_gv, ho_ten FROM giang_vien ORDER BY ma_gv')->fetchAll(PDO::FETCH_ASSOC);
    $monHocs = $pdo->query('SELECT mon_hoc_id, ma_mon, ten_mon FROM mon_hoc ORDER BY ma_mon')->fetchAll(PDO::FETCH_ASSOC);
    $hocKys = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC')->fetchAll(PDO::FETCH_ASSOC);
}

$students = $studentsRes['data'] ?? [];
$lhps = $lhpRes['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sach sinh vien</title>
    <link rel="stylesheet" href="../../../../assets/css/components.css">
    <style>
        body {
            margin: 0;
            background: #f4f8fb;
            font-family: 'Segoe UI', sans-serif;
            color: #1f2937;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .title {
            margin: 0 0 10px;
            font-size: 22px;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .row-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
            color: #475569;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d3dde7;
            box-sizing: border-box;
        }

        button {
            background: #0f766e;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        button.secondary {
            background: #334155;
        }

        button.danger {
            background: #be123c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }

        .notice {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .inline-form {
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }

        @media (max-width: 960px) {

            .row,
            .row-3 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1 class="title">Quan ly sinh vien</h1>
            <?php if ($notice): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if (!$isAdmin): ?><div class="notice">Luu y: Tao/ngung hoat dong tai khoan sinh vien thuoc vai tro Admin. Giao vu chi cap nhat nghiep vu hoc vu.</div><?php endif; ?>

            <form method="get" class="row">
                <div>
                    <label>Tim theo ten hoac msv</label>
                    <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
                <div>
                    <label>Loc theo lop</label>
                    <select name="lop_id">
                        <option value="">Tat ca lop</option>
                        <?php foreach ($lops as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $lopFilter === $lop['lop_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button type="submit">Tim kiem</button>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <?php if ($isAdmin): ?>
                        <a href="../../admin/them_sinh_vien.php"><button type="button" class="secondary">Them sinh vien</button></a>
                    <?php else: ?>
                        <button type="button" class="secondary" disabled>Them sinh vien (Admin)</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>MSV</th>
                        <th>Ho ten</th>
                        <th>Tai khoan dang nhap</th>
                        <th>Lop</th>
                        <th>Ngay sinh</th>
                        <th>Thao tac</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6">Chua co du lieu sinh vien</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $sv): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sv['msv']); ?></td>
                                <td><?php echo htmlspecialchars($sv['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars((string)($sv['dang_nhap'] ?? $sv['msv'])); ?></td>
                                <td><?php echo htmlspecialchars($sv['ten_lop']); ?></td>
                                <td><?php echo htmlspecialchars((string)($sv['ngay_sinh'] ?? '')); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="form.php?id=<?php echo urlencode($sv['sinh_vien_id']); ?>"><button type="button" class="secondary">Sua</button></a>
                                        <?php if ($isAdmin): ?>
                                            <form method="post" class="inline-form" onsubmit="return confirm('Xac nhan ngung hoat dong sinh vien?');">
                                                <input type="hidden" name="form_action" value="deactivate_student">
                                                <input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
                                                <button type="submit" class="danger">Ngung hoat dong</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <form method="post" class="inline-form" style="margin-top:6px;">
                                        <input type="hidden" name="form_action" value="transfer_class">
                                        <input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
                                        <select name="lop_id_moi" style="width:170px;">
                                            <?php foreach ($lops as $lop): ?>
                                                <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit">Chuyen lop</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="title" style="font-size:18px;">UC4.1 - Mo lop hoc phan</h2>
            <form method="post" class="row">
                <input type="hidden" name="form_action" value="create_lhp">
                <div><label>Ma LHP</label><input name="ma_lhp" required></div>
                <div>
                    <label>Mon hoc</label>
                    <select name="mon_hoc_id" required>
                        <?php foreach ($monHocs as $mon): ?>
                            <option value="<?php echo htmlspecialchars($mon['mon_hoc_id']); ?>"><?php echo htmlspecialchars($mon['ma_mon'] . ' - ' . $mon['ten_mon']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Hoc ky</label>
                    <select name="hoc_ky_id" required>
                        <?php foreach ($hocKys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>"><?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Giang vien</label>
                    <select name="giang_vien_id" required>
                        <?php foreach ($giangViens as $gv): ?>
                            <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label>Ty le CC</label><input type="number" min="0" max="100" name="ty_le_cc" value="10" required></div>
                <div><label>Ty le GK</label><input type="number" min="0" max="100" name="ty_le_gk" value="30" required></div>
                <div><label>Ty le CK</label><input type="number" min="0" max="100" name="ty_le_ck" value="60" required></div>
                <div><label>&nbsp;</label><button type="submit">Tao LHP</button></div>
            </form>
        </div>

        <div class="card">
            <h2 class="title" style="font-size:18px;">UC4.2 - Phan cong giang vien</h2>
            <form method="post" class="row-3">
                <input type="hidden" name="form_action" value="assign_lecturer">
                <div>
                    <label>Lop hoc phan</label>
                    <select name="lhp_id" required>
                        <?php foreach ($lhps as $lhp): ?>
                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp'] . ' - ' . ($lhp['ten_mon'] ?? '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Giang vien</label>
                    <select name="giang_vien_id" required>
                        <?php foreach ($giangViens as $gv): ?>
                            <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label>&nbsp;</label><button type="submit">Cap nhat phan cong</button></div>
            </form>
        </div>

        <div class="card">
            <h2 class="title" style="font-size:18px;">UC4.3 - Them/xoa sinh vien vao lop hoc phan</h2>
            <div class="row-3">
                <form method="post">
                    <input type="hidden" name="form_action" value="add_student_to_lhp">
                    <label>Lop hoc phan</label>
                    <select name="lhp_id" required>
                        <?php foreach ($lhps as $lhp): ?>
                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label style="margin-top:8px;">Sinh vien</label>
                    <select name="sinh_vien_id" required>
                        <?php foreach ($students as $sv): ?>
                            <option value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"><?php echo htmlspecialchars($sv['msv'] . ' - ' . $sv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" style="margin-top:10px;">Them vao LHP</button>
                </form>

                <form method="post">
                    <input type="hidden" name="form_action" value="remove_student_from_lhp">
                    <label>Lop hoc phan</label>
                    <select name="lhp_id" required>
                        <?php foreach ($lhps as $lhp): ?>
                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label style="margin-top:8px;">Sinh vien</label>
                    <select name="sinh_vien_id" required>
                        <?php foreach ($students as $sv): ?>
                            <option value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"><?php echo htmlspecialchars($sv['msv'] . ' - ' . $sv['ho_ten']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="danger" style="margin-top:10px;">Xoa khoi LHP</button>
                </form>

                <div>
                    <label>Thong tin LHP hien co</label>
                    <table>
                        <thead>
                            <tr>
                                <th>Ma LHP</th>
                                <th>Mon hoc</th>
                                <th>So SV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lhps as $lhp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lhp['ma_lhp']); ?></td>
                                    <td><?php echo htmlspecialchars((string)($lhp['ten_mon'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string)($lhp['so_sv'] ?? 0)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>