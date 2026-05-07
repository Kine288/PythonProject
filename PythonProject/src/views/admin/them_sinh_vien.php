<?php
session_start();
require_once __DIR__ . '/../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}

function newId32(): string
{
    return bin2hex(random_bytes(16));
}

function hashPassword(string $raw): string
{
    return hash('sha256', $raw);
}

$pdo = getDatabaseConnection();
$notice = '';
$error = '';
$selectedRole = strtoupper(trim($_POST['role'] ?? 'SINH_VIEN'));

$lops = [];
$khoas = [];
if ($pdo) {
    $lops = $pdo->query('SELECT lop_id, ma_lop, ten_lop FROM lop_sinh_hoat ORDER BY ma_lop ASC')->fetchAll(PDO::FETCH_ASSOC);
    $khoas = $pdo->query('SELECT khoa_id, ma_khoa, ten_khoa FROM khoa_bo_mon ORDER BY ma_khoa ASC')->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    if ($selectedRole === 'SINH_VIEN') {
        $payload = [
            'msv' => trim($_POST['msv'] ?? ''),
            'ho_ten' => trim($_POST['ho_ten'] ?? ''),
            'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
            'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
            'lop_id' => trim($_POST['lop_id'] ?? ''),
            'mat_khau' => trim($_POST['mat_khau'] ?? ''),
            'admin_tai_khoan_id' => $_SESSION['user_id'] ?? '',
        ];
        $res = sinhVienProxyRequest('POST', '/sinh-vien', $payload);
        if (!empty($res['success'])) {
            $notice = 'Tao tai khoan + ho so sinh vien thanh cong.';
        } else {
            $error = $res['message'] ?? 'Khong the tao sinh vien.';
        }
    } else {
        if (!$pdo) {
            $error = 'Khong the ket noi CSDL.';
        } else {
            $email = trim($_POST['email'] ?? '');
            $hoTen = trim($_POST['ho_ten'] ?? '');
            $matKhau = trim($_POST['mat_khau'] ?? '');

            if ($email === '' || $hoTen === '' || $matKhau === '') {
                $error = 'Vui long nhap day du thong tin bat buoc.';
            } else {
                try {
                    $pdo->beginTransaction();
                    $taiKhoanId = newId32();

                    $stmt = $pdo->prepare('INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau_hash, vai_tro, is_active) VALUES (:id, :email, :mk, :role, 1)');
                    $stmt->execute([
                        'id' => $taiKhoanId,
                        'email' => $email,
                        'mk' => hashPassword($matKhau),
                        'role' => $selectedRole,
                    ]);

                    if ($selectedRole === 'GIANG_VIEN') {
                        $gvId = newId32();
                        $maGv = trim($_POST['ma_gv'] ?? '');
                        $hocVi = trim($_POST['hoc_vi'] ?? '');
                        $hocHam = trim($_POST['hoc_ham'] ?? '');
                        $khoaId = trim($_POST['khoa_id'] ?? '') ?: null;
                        $soDienThoai = trim($_POST['so_dien_thoai'] ?? '') ?: null;

                        $stmt = $pdo->prepare('INSERT INTO giang_vien (giang_vien_id, tai_khoan_id, ma_gv, ho_ten, hoc_vi, hoc_ham, khoa_id, so_dien_thoai) VALUES (:gv_id, :tk_id, :ma_gv, :ho_ten, :hoc_vi, :hoc_ham, :khoa_id, :sdt)');
                        $stmt->execute([
                            'gv_id' => $gvId,
                            'tk_id' => $taiKhoanId,
                            'ma_gv' => $maGv,
                            'ho_ten' => $hoTen,
                            'hoc_vi' => $hocVi,
                            'hoc_ham' => $hocHam,
                            'khoa_id' => $khoaId,
                            'sdt' => $soDienThoai,
                        ]);
                    }

                    $stmt = $pdo->prepare('INSERT INTO admin_log (log_id, tai_khoan_id, hanh_dong, doi_tuong_loai, doi_tuong_id, du_lieu) VALUES (:log_id, :admin_id, :action, :type, :target, :payload)');
                    $stmt->execute([
                        'log_id' => newId32(),
                        'admin_id' => $_SESSION['user_id'] ?? $taiKhoanId,
                        'action' => 'CREATE_ACCOUNT',
                        'type' => $selectedRole,
                        'target' => $taiKhoanId,
                        'payload' => json_encode(['email' => $email, 'ho_ten' => $hoTen], JSON_UNESCAPED_UNICODE),
                    ]);

                    $pdo->commit();
                    $notice = 'Tao tai khoan ' . $selectedRole . ' thanh cong.';
                } catch (Throwable $th) {
                    $pdo->rollBack();
                    $error = 'Khong the tao tai khoan: ' . $th->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Them tai khoan</title>
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
                    <h1>Them tai khoan moi</h1>
                    <p class="muted-text">Buoc 1 chon vai tro, buoc 2 nhap thong tin theo vai tro.</p>
                </div>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="alert-info" style="margin-bottom:12px;"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="post" id="form-create-account">
                    <div class="form-section">
                        <div class="form-section-title">Buoc 1 - Chon vai tro</div>
                        <div class="role-grid">
                            <?php foreach (['SINH_VIEN', 'GIANG_VIEN', 'GIAO_VU', 'ADMIN'] as $roleOption): ?>
                                <label class="role-card <?php echo $selectedRole === $roleOption ? 'active' : ''; ?>">
                                    <input type="radio" name="role" value="<?php echo $roleOption; ?>" <?php echo $selectedRole === $roleOption ? 'checked' : ''; ?> onchange="document.getElementById('form-create-account').submit();">
                                    <span><?php echo $roleOption; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title">Buoc 2 - Thong tin</div>
                        <div class="form-grid">
                            <?php if ($selectedRole === 'SINH_VIEN'): ?>
                                <div class="form-group">
                                    <label>Ma sinh vien</label>
                                    <input name="msv" placeholder="VD: 725101001" required>
                                </div>
                                <div class="form-group">
                                    <label>Ho va ten</label>
                                    <input name="ho_ten" required>
                                </div>
                                <div class="form-group">
                                    <label>Lop sinh hoat</label>
                                    <select name="lop_id" required>
                                        <option value="">-- Chon lop --</option>
                                        <?php foreach ($lops as $lop): ?>
                                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ma_lop'] . ' - ' . $lop['ten_lop']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ngay sinh</label>
                                    <input type="date" name="ngay_sinh">
                                </div>
                                <div class="form-group">
                                    <label>Gioi tinh</label>
                                    <select name="gioi_tinh">
                                        <option value="">-- Chon gioi tinh --</option>
                                        <option value="Nam">Nam</option>
                                        <option value="Nu">Nu</option>
                                        <option value="Khac">Khac</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Mat khau khoi tao</label>
                                    <input name="mat_khau" value="" placeholder="De trong = dung msv">
                                    <div class="form-help">Neu de trong, mat khau se dung ma sinh vien.</div>
                                </div>
                            <?php elseif ($selectedRole === 'GIANG_VIEN'): ?>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input name="email" type="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Mat khau khoi tao</label>
                                    <input name="mat_khau" required>
                                </div>
                                <div class="form-group">
                                    <label>Ma giang vien</label>
                                    <input name="ma_gv" required>
                                </div>
                                <div class="form-group">
                                    <label>Ho va ten</label>
                                    <input name="ho_ten" required>
                                </div>
                                <div class="form-group">
                                    <label>Hoc vi</label>
                                    <input name="hoc_vi" placeholder="Cu nhan/Thac si/Tien si">
                                </div>
                                <div class="form-group">
                                    <label>Hoc ham</label>
                                    <input name="hoc_ham">
                                </div>
                                <div class="form-group">
                                    <label>Khoa/Bo mon</label>
                                    <select name="khoa_id">
                                        <option value="">-- Chon khoa --</option>
                                        <?php foreach ($khoas as $khoa): ?>
                                            <option value="<?php echo htmlspecialchars($khoa['khoa_id']); ?>"><?php echo htmlspecialchars($khoa['ma_khoa'] . ' - ' . $khoa['ten_khoa']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>So dien thoai</label>
                                    <input name="so_dien_thoai">
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input name="email" type="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Mat khau khoi tao</label>
                                    <input name="mat_khau" required>
                                </div>
                                <div class="form-group">
                                    <label>Ho va ten</label>
                                    <input name="ho_ten" required>
                                </div>
                                <div class="form-group">
                                    <label>So dien thoai</label>
                                    <input name="so_dien_thoai">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" name="create_account" value="1">Tao tai khoan</button>
                        <a href="quan_ly_tai_khoan.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Quay lai</a>
                    </div>
                </form>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>