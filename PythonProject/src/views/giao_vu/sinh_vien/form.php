<?php
session_start();
require_once __DIR__ . '/../../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../../config/database.php';

$currentRole = $_SESSION['user_role'] ?? '';
$isAdmin = $currentRole === 'ADMIN';
$isGiaoVu = $currentRole === 'GIAO_VU';

if (!$isAdmin && !$isGiaoVu) {
    header('Location: ../../auth/login.php');
    exit;
}

$sinhVienId = $_GET['id'] ?? '';
$isEdit = $sinhVienId !== '';
$error = '';

if (!$isAdmin && !$isEdit) {
    $error = 'Chi Admin duoc tao moi tai khoan sinh vien.';
}

$pdo = getDatabaseConnection();
$lops = [];
if ($pdo) {
    $lops = $pdo->query('SELECT lop_id, ma_lop, ten_lop FROM lop_sinh_hoat ORDER BY ma_lop')->fetchAll(PDO::FETCH_ASSOC);
}

$student = [
    'sinh_vien_id' => '',
    'msv' => '',
    'ho_ten' => '',
    'dang_nhap' => '',
    'gioi_tinh' => '',
    'ngay_sinh' => '',
    'lop_id' => '',
    'trang_thai' => 'DANG_HOC',
];

if ($isEdit) {
    $res = sinhVienProxyRequest('GET', '/sinh-vien/' . $sinhVienId);
    if (!empty($res['success'])) {
        $student = array_merge($student, $res['data'] ?? []);
        if (!empty($student['ngay_sinh'])) {
            $student['ngay_sinh'] = substr((string)$student['ngay_sinh'], 0, 10);
        }
    } else {
        $error = $res['message'] ?? 'Khong tim thay sinh vien';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isAdmin && !$isEdit) {
        $error = 'Chi Admin duoc tao moi tai khoan sinh vien.';
    } else {
        if ($isEdit) {
            $payload = [
                'lop_id' => $_POST['lop_id'] ?? '',
                'trang_thai' => $_POST['trang_thai'] ?? 'DANG_HOC',
                'nguoi_thay_doi' => (string)($_SESSION['user_id'] ?? ''),
            ];
            $res = sinhVienProxyRequest('PUT', '/sinh-vien/' . $sinhVienId, $payload);
        } else {
            $payload = [
                'msv' => trim($_POST['msv'] ?? ''),
                'ho_ten' => trim($_POST['ho_ten'] ?? ''),
                'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
                'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                'lop_id' => $_POST['lop_id'] ?? '',
                'mat_khau' => trim($_POST['mat_khau'] ?? ''),
                'admin_tai_khoan_id' => (string)($_SESSION['user_id'] ?? ''),
            ];
            $res = sinhVienProxyRequest('POST', '/sinh-vien', $payload);
        }

        if (!empty($res['success'])) {
            header('Location: danh_sach.php');
            exit;
        }

        $error = $res['message'] ?? 'Khong the luu sinh vien';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Cap nhat sinh vien' : 'Them sinh vien'; ?></title>
    <style>
        body {
            margin: 0;
            background: #f4f8fb;
            font-family: 'Segoe UI', sans-serif;
            color: #1f2937;
        }

        .wrap {
            max-width: 720px;
            margin: 26px auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: 14px;
            padding: 20px;
        }

        .title {
            margin-top: 0;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
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

        input[readonly] {
            background: #f8fafc;
            color: #64748b;
        }

        button {
            background: #0f766e;
            color: #fff;
            border: none;
            font-weight: 600;
            cursor: pointer;
        }

        .secondary {
            background: #334155;
            text-decoration: none;
            color: #fff;
            text-align: center;
            display: inline-block;
            padding: 10px;
            border-radius: 8px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        @media (max-width: 680px) {

            .row,
            .actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <h2 class="title"><?php echo $isEdit ? 'Cap nhat nghiep vu sinh vien' : 'Them moi sinh vien'; ?></h2>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <form method="post">
                <div class="row">
                    <div>
                        <label>Ma sinh vien</label>
                        <input name="msv" required value="<?php echo htmlspecialchars((string)$student['msv']); ?>" <?php echo $isEdit ? 'readonly' : ''; ?>>
                    </div>
                    <div>
                        <label>Ho ten</label>
                        <input name="ho_ten" required value="<?php echo htmlspecialchars((string)$student['ho_ten']); ?>" <?php echo $isEdit ? 'readonly' : ''; ?>>
                    </div>
                    <div>
                        <label>Tai khoan dang nhap</label>
                        <input value="<?php echo htmlspecialchars((string)($student['dang_nhap'] ?: $student['msv'])); ?>" readonly>
                    </div>
                    <div>
                        <label>Ngay sinh</label>
                        <input type="date" name="ngay_sinh" value="<?php echo htmlspecialchars((string)$student['ngay_sinh']); ?>" <?php echo $isEdit ? 'readonly' : ''; ?>>
                    </div>
                    <div>
                        <label>Gioi tinh</label>
                        <select name="gioi_tinh" <?php echo $isEdit ? 'disabled' : ''; ?>>
                            <option value="">Khong xac dinh</option>
                            <option value="Nam" <?php echo (string)$student['gioi_tinh'] === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nu" <?php echo (string)$student['gioi_tinh'] === 'Nu' ? 'selected' : ''; ?>>Nu</option>
                        </select>
                    </div>
                    <div>
                        <label>Lop</label>
                        <select name="lop_id" required>
                            <option value="">-- Chon lop --</option>
                            <?php foreach ($lops as $lop): ?>
                                <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo (string)$student['lop_id'] === (string)$lop['lop_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lop['ma_lop'] . ' - ' . $lop['ten_lop']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                        <div>
                            <label>Trang thai hoc tap</label>
                            <select name="trang_thai">
                                <?php $tt = (string)($student['trang_thai'] ?? 'DANG_HOC'); ?>
                                <option value="DANG_HOC" <?php echo $tt === 'DANG_HOC' ? 'selected' : ''; ?>>Dang hoc</option>
                                <option value="TAM_NGUNG" <?php echo $tt === 'TAM_NGUNG' ? 'selected' : ''; ?>>Tam ngung</option>
                                <option value="THOI_HOC" <?php echo $tt === 'THOI_HOC' ? 'selected' : ''; ?>>Thoi hoc</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <div>
                            <label>Mat khau khoi tao</label>
                            <input type="text" name="mat_khau" placeholder="De trong = dung msv">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <button type="submit"><?php echo $isEdit ? 'Luu thay doi' : 'Tao sinh vien'; ?></button>
                    <a class="secondary" href="danh_sach.php">Quay lai danh sach</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>