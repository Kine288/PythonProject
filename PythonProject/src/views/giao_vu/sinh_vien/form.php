<?php
require_once __DIR__ . '/../../../../api/sinh_vien.php';

$sinhVienId = $_GET['id'] ?? '';
$isEdit = $sinhVienId !== '';
$error = '';

$lopRes = sinhVienProxyRequest('GET', '/catalog/lop');
$lops = $lopRes['data'] ?? [];

$student = [
    'sinh_vien_id' => '',
    'msv' => '',
    'ten_sv' => '',
    'email' => '',
    'gioi_tinh' => '',
    'ngay_sinh' => '',
    'lop_id' => '',
];

if ($isEdit) {
    $res = sinhVienProxyRequest('GET', '/students/' . $sinhVienId);
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
    $payload = [
        'msv' => trim($_POST['msv'] ?? ''),
        'ten_sv' => trim($_POST['ten_sv'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
        'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
        'lop_id' => $_POST['lop_id'] ?? '',
    ];

    if ($isEdit) {
        $res = sinhVienProxyRequest('PUT', '/students/' . $sinhVienId, $payload);
    } else {
        $payload['mat_khau'] = trim($_POST['mat_khau'] ?? '123456');
        $res = sinhVienProxyRequest('POST', '/students', $payload);
    }

    if (!empty($res['success'])) {
        header('Location: danh_sach.php');
        exit;
    }

    $error = $res['message'] ?? 'Khong the luu sinh vien';
    $student = array_merge($student, $payload);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Sua sinh vien' : 'Them sinh vien'; ?></title>
    <style>
        body { margin: 0; background: #f4f8fb; font-family: 'Segoe UI', sans-serif; color: #1f2937; }
        .wrap { max-width: 680px; margin: 26px auto; padding: 20px; }
        .card { background: #fff; border: 1px solid #e5edf5; border-radius: 14px; padding: 20px; }
        .title { margin-top: 0; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        label { display: block; font-size: 13px; margin-bottom: 4px; color: #475569; }
        input, select, button { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d3dde7; box-sizing: border-box; }
        button { background: #0f766e; color: #fff; border: none; font-weight: 600; cursor: pointer; }
        .secondary { background: #334155; text-decoration: none; color: #fff; text-align: center; display: inline-block; padding: 10px; border-radius: 8px; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 10px; border-radius: 8px; margin-bottom: 12px; }
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; }
        @media (max-width: 680px) { .row, .actions { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h2 class="title"><?php echo $isEdit ? 'Cap nhat sinh vien' : 'Them moi sinh vien'; ?></h2>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="post">
            <div class="row">
                <div>
                    <label>Ma sinh vien</label>
                    <input name="msv" required value="<?php echo htmlspecialchars((string)$student['msv']); ?>">
                </div>
                <div>
                    <label>Ho ten</label>
                    <input name="ten_sv" required value="<?php echo htmlspecialchars((string)$student['ten_sv']); ?>">
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars((string)$student['email']); ?>">
                </div>
                <div>
                    <label>Ngay sinh</label>
                    <input type="date" name="ngay_sinh" value="<?php echo htmlspecialchars((string)$student['ngay_sinh']); ?>">
                </div>
                <div>
                    <label>Gioi tinh</label>
                    <select name="gioi_tinh">
                        <option value="">Khong xac dinh</option>
                        <option value="1" <?php echo (string)$student['gioi_tinh'] === '1' ? 'selected' : ''; ?>>Nam</option>
                        <option value="0" <?php echo (string)$student['gioi_tinh'] === '0' ? 'selected' : ''; ?>>Nu</option>
                    </select>
                </div>
                <div>
                    <label>Lop</label>
                    <select name="lop_id" required>
                        <option value="">-- Chon lop --</option>
                        <?php foreach ($lops as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $student['lop_id'] === $lop['lop_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$isEdit): ?>
                    <div>
                        <label>Mat khau khoi tao</label>
                        <input type="text" name="mat_khau" value="123456">
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
