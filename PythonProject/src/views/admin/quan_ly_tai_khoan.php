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
            $message = 'Đã khóa tài khoản thành công.';
        } else {
            $error = $res['message'] ?? 'Không thể khóa tài khoản.';
        }
    } elseif ($action === 'mo_khoa') {
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/mo-khoa');
        if (!empty($res['success'])) {
            $message = 'Đã mở khóa tài khoản thành công.';
        } else {
            $error = $res['message'] ?? 'Không thể mở khóa tài khoản.';
        }
    } elseif ($action === 'reset_mk') {
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/reset-mat-khau', [
            'mat_khau_moi' => '123456',
        ]);
        if (!empty($res['success'])) {
            $message = 'Đã reset mật khẩu về 123456.';
        } else {
            $error = $res['message'] ?? 'Không thể reset mật khẩu.';
        }
    } elseif ($action === 'doi_vai_tro') {
        $vaiTroMoi = trim($_POST['vai_tro_moi'] ?? '');
        $res = sinhVienProxyRequest('PUT', '/tai-khoan/' . $taiKhoanId . '/vai-tro', [
            'vai_tro' => $vaiTroMoi,
        ]);
        if (!empty($res['success'])) {
            $message = 'Đã cập nhật vai trò.';
        } else {
            $error = $res['message'] ?? 'Không thể cập nhật vai trò.';
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
if (empty($response['success'])) {
    $apiMessage = $response['message'] ?? 'Không thể tải danh sách tài khoản từ API.';
    if ($error === '') {
        $error = $apiMessage;
    }
}

function accountDisplayName(array $acc): string
{
    if (!empty($acc['ten_sinh_vien'])) {
        return (string)$acc['ten_sinh_vien'];
    }
    if (!empty($acc['ten_giang_vien'])) {
        return (string)$acc['ten_giang_vien'];
    }
    if (!empty($acc['ten_giao_vu'])) {
        return (string)$acc['ten_giao_vu'];
    }
    return 'Hệ thống';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tài khoản | Hệ thống Quản lý Sinh viên</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Bổ sung làm đẹp Giao diện Admin */
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .dashboard-header h1 {
            font-size: 24px;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }

        /* Khung Card chứa bộ lọc và bảng */
        .content-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
        }

        /* Thanh công cụ lọc */
        .filter-bar {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 16px;
            margin-bottom: 24px;
        }

        .search-bar, .select-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-bar:focus, .select-control:focus {
            border-color: var(--primary-color);
        }

        /* Nâng cấp Bảng dữ liệu */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table th {
            background-color: var(--bg-light);
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 14px 16px;
            border-bottom: 2px solid var(--border-color);
            text-align: left;
        }

        .modern-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
            font-size: 14px;
            vertical-align: middle;
        }

        .modern-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .modern-table tbody tr.row-locked td {
            color: var(--text-muted);
            background-color: #fdf2f2;
        }

        /* Badges trạng thái */
        .badge {
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-locked { background: #fee2e2; color: #991b1b; }

        /* Nút hành động dạng Icon */
        .action-cell {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            align-items: center;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            color: #fff;
        }
        .btn-icon .material-symbols-outlined { font-size: 18px; }
        
        .btn-primary-icon { background: var(--primary-color); }
        .btn-primary-icon:hover { background: var(--primary-hover); }
        
        .btn-danger-icon { background: var(--danger-color); }
        .btn-danger-icon:hover { background: var(--danger-hover); }
        
        .btn-warning-icon { background: var(--warning-color); color: #fff; }
        .btn-warning-icon:hover { background: #d97706; }

        .btn-success-icon { background: var(--success-color); }
        .btn-success-icon:hover { background: #059669; }

        /* Nhóm đổi vai trò */
        .role-group {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--bg-light);
            padding: 4px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .role-group select {
            border: none;
            background: transparent;
            font-size: 13px;
            padding: 4px 8px;
            outline: none;
            cursor: pointer;
        }
    </style>
    
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Quản lý Tài khoản</h1>
                    <p class="muted-text">Tìm kiếm, lọc và quản lý trạng thái, quyền hạn của người dùng hệ thống.</p>
                </div>
                <div>
                    <a class="btn-primary" href="them_sinh_vien.php" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px;">
                        <span class="material-symbols-outlined">person_add</span> Thêm tài khoản
                    </a>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert-info" style="margin-bottom:16px; padding: 12px 16px; background: #e0e7ff; color: #3730a3; border-radius: 8px; font-weight: 500;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">check_circle</span>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:16px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; font-weight: 500;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">error</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <form class="filter-bar" method="get">
                    <input type="text" class="search-bar" name="keyword" placeholder="🔍 Tìm theo email hoặc họ tên..." value="<?php echo htmlspecialchars($keyword); ?>">
                    
                    <select name="vai_tro" class="select-control">
                        <option value="">Tất cả vai trò</option>
                        <option value="ADMIN" <?php echo $vaiTro === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                        <option value="GIAO_VU" <?php echo $vaiTro === 'GIAO_VU' ? 'selected' : ''; ?>>GIÁO VỤ</option>
                        <option value="GIANG_VIEN" <?php echo $vaiTro === 'GIANG_VIEN' ? 'selected' : ''; ?>>GIẢNG VIÊN</option>
                        <option value="SINH_VIEN" <?php echo $vaiTro === 'SINH_VIEN' ? 'selected' : ''; ?>>SINH VIÊN</option>
                    </select>
                    
                    <select name="is_active" class="select-control">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" <?php echo $isActive === '1' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="0" <?php echo $isActive === '0' ? 'selected' : ''; ?>>Bị khóa</option>
                    </select>
                    
                    <button class="btn-primary" type="submit" style="padding: 10px 24px;">Lọc dữ liệu</button>
                </form>

                <div style="overflow-x: auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tài khoản (Email)</th>
                                <th>Họ và tên</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accounts)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                        <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5;">inbox</span>
                                        <p style="margin-top: 8px;">Không tìm thấy dữ liệu tài khoản nào.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($accounts as $index => $acc): ?>
                                    <?php 
                                        $isMe = ($_SESSION['user_id'] ?? '') === ($acc['tai_khoan_id'] ?? ''); 
                                        $isActiveStatus = (int)$acc['is_active'] === 1;
                                    ?>
                                    <tr class="<?php echo !$isActiveStatus ? 'row-locked' : ''; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td style="font-weight: 500;"><?php echo htmlspecialchars((string)$acc['email']); ?></td>
                                        <td><?php echo htmlspecialchars(accountDisplayName($acc)); ?></td>
                                        <td>
                                            <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569;">
                                                <?php echo htmlspecialchars((string)$acc['vai_tro']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($isActiveStatus): ?>
                                                <span class="badge badge-active"><span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span> Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge badge-locked"><span class="material-symbols-outlined" style="font-size: 14px;">lock</span> Bị khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-cell">
                                                <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn reset mật khẩu tài khoản này về 123456?');">
                                                    <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                    <button class="btn-icon btn-warning-icon" name="action" value="reset_mk" type="submit" title="Reset mật khẩu">
                                                        <span class="material-symbols-outlined">key</span>
                                                    </button>
                                                </form>

                                                <form method="post" class="role-group">
                                                    <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                    <select name="vai_tro_moi" title="Chọn vai trò mới">
                                                        <option value="ADMIN" <?php echo $acc['vai_tro'] === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                                                        <option value="GIAO_VU" <?php echo $acc['vai_tro'] === 'GIAO_VU' ? 'selected' : ''; ?>>GIÁO VỤ</option>
                                                        <option value="GIANG_VIEN" <?php echo $acc['vai_tro'] === 'GIANG_VIEN' ? 'selected' : ''; ?>>GIẢNG VIÊN</option>
                                                        <option value="SINH_VIEN" <?php echo $acc['vai_tro'] === 'SINH_VIEN' ? 'selected' : ''; ?>>SINH VIÊN</option>
                                                    </select>
                                                    <button class="btn-icon btn-primary-icon" name="action" value="doi_vai_tro" type="submit" title="Lưu vai trò mới" style="width: 28px; height: 28px;">
                                                        <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                                                    </button>
                                                </form>

                                                <form method="post">
                                                    <input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars((string)$acc['tai_khoan_id']); ?>">
                                                    <?php if ($isActiveStatus): ?>
                                                        <button class="btn-icon btn-danger-icon" name="action" value="khoa" type="submit" title="Khóa tài khoản" <?php echo $isMe ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                                                            <span class="material-symbols-outlined">lock_person</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn-icon btn-success-icon" name="action" value="mo_khoa" type="submit" title="Mở khóa tài khoản">
                                                            <span class="material-symbols-outlined">lock_open_right</span>
                                                        </button>
                                                    <?php endif; ?>
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
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>