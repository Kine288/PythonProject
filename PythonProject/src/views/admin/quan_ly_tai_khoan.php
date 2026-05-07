<?php
session_start();
require_once __DIR__ . '/../../../api/sinh_vien.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $taiKhoanId = trim($_POST['tai_khoan_id'] ?? '');

    if ($action === 'khoa') {
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/khoa');
        if (!empty($res['success'])) {
            $message = 'Da khoa tai khoan thanh cong.';
        } else {
            $error = $res['message'] ?? 'Khong the khoa tai khoan.';
        }
    } elseif ($action === 'mo_khoa') {
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/mo-khoa');
        if (!empty($res['success'])) {
            $message = 'Da mo khoa tai khoan thanh cong.';
        } else {
            $error = $res['message'] ?? 'Khong the mo khoa tai khoan.';
        }
    } elseif ($action === 'reset_mk') {
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/reset-mat-khau', [
            'mat_khau_moi' => '123456',
        ]);
        if (!empty($res['success'])) {
            $message = 'Da reset mat khau ve 123456.';
        } else {
            $error = $res['message'] ?? 'Khong the reset mat khau.';
        }
    } elseif ($action === 'doi_vai_tro') {
        $vaiTroMoi = trim($_POST['vai_tro_moi'] ?? '');
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/vai-tro', [
            'vai_tro' => $vaiTroMoi,
        ]);
        if (!empty($res['success'])) {
            $message = 'Da cap nhat vai tro.';
        } else {
            $error = $res['message'] ?? 'Khong the cap nhat vai tro.';
        }
    }
}

$keyword = trim($_GET['keyword'] ?? '');
$vaiTro = trim($_GET['vai_tro'] ?? '');
$isActive = trim($_GET['is_active'] ?? '');

$response = sinhVienProxyRequest('GET', '/tai-khoan', null, [
    'search' => $keyword,
    'vai_tro' => $vaiTro,
    'is_active' => $isActive,
]);

$accounts = $response['data'] ?? [];

function accountDisplayName(array $acc): string
{
    if (!empty($acc['ten_sinh_vien'])) {
        return (string)$acc['ten_sinh_vien'];
    }
    if (!empty($acc['ten_giang_vien'])) {
        return (string)$acc['ten_giang_vien'];
    }
    return 'He thong';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quan ly tai khoan</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Quan ly Tai khoan</h1>
                    <p class="muted-text">Tim kiem, loc va quan ly trang thai tai khoan theo vai tro.</p>
                </div>
                <div class="dashboard-actions">
                    <a class="btn-primary" href="them_sinh_vien.php">Them tai khoan sinh vien</a>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert-info" style="margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="filter-bar" method="get" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;margin-bottom:16px;">
                <input class="search-bar" name="keyword" placeholder="Tim email/ho ten" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="vai_tro" class="search-bar">
                    <option value="">Tat ca vai tro</option>
                    <option value="ADMIN" <?php echo $vaiTro === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                    <option value="GIAO_VU" <?php echo $vaiTro === 'GIAO_VU' ? 'selected' : ''; ?>>GIAO_VU</option>
                    <option value="GIANG_VIEN" <?php echo $vaiTro === 'GIANG_VIEN' ? 'selected' : ''; ?>>GIANG_VIEN</option>
                    <option value="SINH_VIEN" <?php echo $vaiTro === 'SINH_VIEN' ? 'selected' : ''; ?>>SINH_VIEN</option>
                </select>
                <select name="is_active" class="search-bar">
                    <option value="">Tat ca trang thai</option>
                    <option value="1" <?php echo $isActive === '1' ? 'selected' : ''; ?>>Dang hoat dong</option>
                    <option value="0" <?php echo $isActive === '0' ? 'selected' : ''; ?>>Bi khoa</option>
                </select>
                <button class="btn-secondary" type="submit">Loc</button>
            </form>

            <div class="table-wrapper" style="background:#fff;border:1px solid #e2e8f0;padding:0;overflow:auto;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:12px;text-align:left;">STT</th>
                            <th style="padding:12px;text-align:left;">Email dang nhap</th>
                            <th style="padding:12px;text-align:left;">Ho ten</th>
                            <th style="padding:12px;text-align:left;">Vai tro</th>
                            <th style="padding:12px;text-align:left;">Trang thai</th>
                            <th style="padding:12px;text-align:left;">Lan dang nhap cuoi</th>
                            <th style="padding:12px;text-align:right;">Hanh dong</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="7" style="padding:16px;" class="muted-text">Khong co du lieu tai khoan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $index => $acc): ?>
                                <?php $isMe = ($_SESSION['user_id'] ?? '') === ($acc['tai_khoan_id'] ?? ''); ?>
                                <tr style="border-top:1px solid #f1f5f9;">
                                    <td style="padding:12px;"><?php echo $index + 1; ?></td>
                                    <td style="padding:12px;"><?php echo htmlspecialchars((string)$acc['email']); ?></td>
                                    <td style="padding:12px;"><?php echo htmlspecialchars(accountDisplayName($acc)); ?></td>
                                    <td style="padding:12px;"><?php echo htmlspecialchars((string)$acc['vai_tro']); ?></td>
                                    <td style="padding:12px;">
                                        <span class="<?php echo ((int)$acc['is_active'] === 1) ? 'badge-active' : 'badge-locked'; ?>" style="padding:4px 10px;border-radius:999px;font-size:12px;">
                                            <?php echo ((int)$acc['is_active'] === 1) ? 'Dang hoat dong' : 'Bi khoa'; ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;"><?php echo htmlspecialchars((string)($acc['lan_dang_nhap_cuoi'] ?? '')); ?></td>
                                    <td style="padding:12px;text-align:right;">
                                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                <?php if ((int)$acc['is_active'] === 1): ?>
                                                    <button class="btn-danger" name="action" value="khoa" type="submit" <?php echo $isMe ? 'disabled' : ''; ?>>Khoa</button>
                                                <?php else: ?>
                                                    <button class="btn-secondary" name="action" value="mo_khoa" type="submit">Mo khoa</button>
                                                <?php endif; ?>
                                            </form>

                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                <button class="btn-secondary" name="action" value="reset_mk" type="submit">Reset MK</button>
                                            </form>

                                            <form method="post" style="display:inline-flex;gap:6px;">
                                                <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                <select name="vai_tro_moi" class="search-bar" style="padding:6px 10px;">
                                                    <option value="ADMIN" <?php echo $acc['vai_tro'] === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                                                    <option value="GIAO_VU" <?php echo $acc['vai_tro'] === 'GIAO_VU' ? 'selected' : ''; ?>>GIAO_VU</option>
                                                    <option value="GIANG_VIEN" <?php echo $acc['vai_tro'] === 'GIANG_VIEN' ? 'selected' : ''; ?>>GIANG_VIEN</option>
                                                    <option value="SINH_VIEN" <?php echo $acc['vai_tro'] === 'SINH_VIEN' ? 'selected' : ''; ?>>SINH_VIEN</option>
                                                </select>
                                                <button class="btn-ghost" name="action" value="doi_vai_tro" type="submit">Doi vai tro</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>