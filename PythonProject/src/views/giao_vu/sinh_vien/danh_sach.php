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
        $response = ['success' => false, 'message' => 'Chỉ Admin được ngừng hoạt động tài khoản'];
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
                    ? ['success' => true, 'message' => 'Đã ngừng hoạt động sinh viên và khóa tài khoản']
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
        $response = ['success' => false, 'message' => 'Thao tác không hợp lệ'];
    }

    if (!empty($response['success'])) {
        $notice = $response['message'] ?? 'Thực hiện thành công';
    } else {
        $error = $response['message'] ?? 'Có lỗi xảy ra';
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
    <title>Quản lý Sinh viên & Học vụ | Hệ thống Quản lý Sinh viên</title>
    <link rel="stylesheet" href="../../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Đồng bộ CSS với giao diện Admin/Dashboard */
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
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .dashboard-header h1 {
            font-size: 24px;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }

        .content-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 24px;
            margin-bottom: 24px;
        }

        .panel-header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .panel-header h3 {
            margin: 0;
            font-size: 18px;
            color: var(--text-dark);
        }

        /* Form Filter & Inputs */
        .filter-bar {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 16px;
            margin-bottom: 24px;
            align-items: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .search-bar, .select-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            background: #fff;
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

        /* Nút hành động dạng Icon */
        .action-cell {
            display: flex;
            gap: 8px;
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
            text-decoration: none;
        }
        .btn-icon .material-symbols-outlined { font-size: 18px; }
        
        .btn-primary-icon { background: var(--primary-color); }
        .btn-primary-icon:hover { background: var(--primary-hover); }
        .btn-danger-icon { background: var(--danger-color); }
        .btn-danger-icon:hover { background: var(--danger-hover); }
        .btn-success-icon { background: var(--success-color); }
        .btn-success-icon:hover { background: #059669; }

        /* Nút thông thường */
        .btn-primary { background: var(--primary-color); color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;}
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: #e2e8f0; color: #475569; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none;}
        .btn-secondary:hover { background: #cbd5e1; }

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
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Quản lý Sinh viên & Học vụ</h1>
                    <p class="muted-text">Quản lý hồ sơ sinh viên, thiết lập lớp học phần và phân công giảng dạy.</p>
                </div>
            </div>

            <?php if ($notice): ?>
                <div class="alert-info" style="margin-bottom:16px; padding: 12px 16px; background: #ecfdf5; color: #047857; border-radius: 8px; font-weight: 500;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">check_circle</span>
                    <?php echo htmlspecialchars($notice); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-danger" style="margin-bottom:16px; padding: 12px 16px; background: #fee2e2; color: #991b1b; border-radius: 8px; font-weight: 500;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">error</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!$isAdmin): ?>
                <div class="alert-info" style="margin-bottom:16px; padding: 12px 16px; background: #e0e7ff; color: #3730a3; border-radius: 8px; font-weight: 500;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">info</span>
                    Lưu ý: Thao tác tạo mới/ngừng hoạt động tài khoản sinh viên thuộc quyền của Admin. Giáo vụ chỉ thực hiện điều chỉnh học vụ.
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="panel-header">
                    <h3>Danh sách Sinh viên</h3>
                </div>
                <form method="get" class="filter-bar">
                    <input type="text" class="search-bar" name="keyword" placeholder="🔍 Tìm theo tên hoặc MSV..." value="<?php echo htmlspecialchars($keyword); ?>">
                    
                    <select name="lop_id" class="select-control">
                        <option value="">Tất cả các lớp</option>
                        <?php foreach ($lops as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $lopFilter === $lop['lop_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn-primary" style="display:flex; align-items:center; gap:6px;">
                        <span class="material-symbols-outlined">search</span> Lọc
                    </button>

                    <?php if ($isAdmin): ?>
                        <a href="../../admin/them_sinh_vien.php" class="btn-secondary" style="display:flex; align-items:center; gap:6px;">
                            <span class="material-symbols-outlined">person_add</span> Thêm mới
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn-secondary" disabled style="opacity:0.6; cursor:not-allowed;">Thêm mới (Chỉ Admin)</button>
                    <?php endif; ?>
                </form>

                <div style="overflow-x: auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>MSV</th>
                                <th>Họ và tên</th>
                                <th>Tài khoản đăng nhập</th>
                                <th>Lớp sinh hoạt</th>
                                <th>Ngày sinh</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">Không có dữ liệu sinh viên.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $sv): ?>
                                    <tr>
                                        <td style="font-weight:600;"><?php echo htmlspecialchars($sv['msv']); ?></td>
                                        <td><?php echo htmlspecialchars($sv['ho_ten']); ?></td>
                                        <td><?php echo htmlspecialchars((string)($sv['dang_nhap'] ?? $sv['msv'])); ?></td>
                                        <td><span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight:600; font-size: 12px;"><?php echo htmlspecialchars($sv['ten_lop']); ?></span></td>
                                        <td><?php echo htmlspecialchars((string)($sv['ngay_sinh'] ?? '--')); ?></td>
                                        <td>
                                            <div class="action-cell">
                                                <a href="form.php?id=<?php echo urlencode($sv['sinh_vien_id']); ?>" class="btn-icon btn-primary-icon" title="Cập nhật thông tin">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </a>
                                                
                                                <?php if ($isAdmin): ?>
                                                    <form method="post" onsubmit="return confirm('Xác nhận ngừng hoạt động tài khoản sinh viên này?');" style="margin:0;">
                                                        <input type="hidden" name="form_action" value="deactivate_student">
                                                        <input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
                                                        <button type="submit" class="btn-icon btn-danger-icon" title="Ngừng hoạt động">
                                                            <span class="material-symbols-outlined">person_off</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="post" class="role-group" style="margin:0;">
                                                    <input type="hidden" name="form_action" value="transfer_class">
                                                    <input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
                                                    <select name="lop_id_moi" title="Chọn lớp mới">
                                                        <?php foreach ($lops as $lop): ?>
                                                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn-icon btn-success-icon" style="width:28px; height:28px;" title="Lưu chuyển lớp">
                                                        <span class="material-symbols-outlined" style="font-size:16px;">move_up</span>
                                                    </button>
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

            <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                
                <div class="content-card" style="margin-bottom:0;">
                    <div class="panel-header">
                        <h3>Mở Lớp Học Phần mới (UC4.1)</h3>
                    </div>
                    <form method="post">
                        <input type="hidden" name="form_action" value="create_lhp">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Mã LHP</label>
                                <input type="text" class="search-bar" name="ma_lhp" placeholder="Nhập mã LHP..." required>
                            </div>
                            <div class="form-group">
                                <label>Môn học</label>
                                <select class="select-control" name="mon_hoc_id" required>
                                    <option value="" disabled selected>-- Chọn môn học --</option>
                                    <?php foreach ($monHocs as $mon): ?>
                                        <option value="<?php echo htmlspecialchars($mon['mon_hoc_id']); ?>"><?php echo htmlspecialchars($mon['ma_mon'] . ' - ' . $mon['ten_mon']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Học kỳ</label>
                                <select class="select-control" name="hoc_ky_id" required>
                                    <?php foreach ($hocKys as $hk): ?>
                                        <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>"><?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Kỳ ' . $hk['ky_hoc'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Giảng viên phụ trách</label>
                                <select class="select-control" name="giang_vien_id" required>
                                    <option value="" disabled selected>-- Chọn Giảng viên --</option>
                                    <?php foreach ($giangViens as $gv): ?>
                                        <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Tỷ lệ điểm Chuyên Cần (%)</label>
                                <input type="number" class="search-bar" min="0" max="100" name="ty_le_cc" value="10" required>
                            </div>
                            <div class="form-group">
                                <label>Tỷ lệ điểm Giữa Kỳ (%)</label>
                                <input type="number" class="search-bar" min="0" max="100" name="ty_le_gk" value="30" required>
                            </div>
                            <div class="form-group">
                                <label>Tỷ lệ điểm Cuối Kỳ (%)</label>
                                <input type="number" class="search-bar" min="0" max="100" name="ty_le_ck" value="60" required>
                            </div>
                            <div class="form-group" style="display:flex; align-items:flex-end;">
                                <button type="submit" class="btn-primary" style="width:100%; height:42px;">Tạo Lớp Học Phần</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
                    
                    <div class="content-card" style="margin-bottom:0;">
                        <div class="panel-header">
                            <h3>Phân công lại Giảng viên (UC4.2)</h3>
                        </div>
                        <form method="post">
                            <input type="hidden" name="form_action" value="assign_lecturer">
                            <div class="form-group">
                                <label>Chọn Lớp Học Phần</label>
                                <select class="select-control" name="lhp_id" required>
                                    <option value="" disabled selected>-- Chọn LHP --</option>
                                    <?php foreach ($lhps as $lhp): ?>
                                        <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp'] . ' - ' . ($lhp['ten_mon'] ?? '')); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Giảng viên mới</label>
                                <select class="select-control" name="giang_vien_id" required>
                                    <option value="" disabled selected>-- Chọn Giảng viên --</option>
                                    <?php foreach ($giangViens as $gv): ?>
                                        <option value="<?php echo htmlspecialchars($gv['giang_vien_id']); ?>"><?php echo htmlspecialchars($gv['ma_gv'] . ' - ' . $gv['ho_ten']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary" style="width: 100%;">Cập nhật phân công</button>
                        </form>
                    </div>

                    <div class="content-card" style="margin-bottom:0;">
                        <div class="panel-header">
                            <h3>Điều chỉnh danh sách LHP (UC4.3)</h3>
                        </div>
                        <div style="display: flex; gap: 16px;">
                            <form method="post" style="flex: 1; border-right: 1px solid var(--border-color); padding-right: 16px;">
                                <input type="hidden" name="form_action" value="add_student_to_lhp">
                                <div class="form-group">
                                    <label>Lớp học phần</label>
                                    <select class="select-control" name="lhp_id" required>
                                        <option value="" disabled selected>-- Chọn LHP --</option>
                                        <?php foreach ($lhps as $lhp): ?>
                                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Sinh viên</label>
                                    <select class="select-control" name="sinh_vien_id" required>
                                        <option value="" disabled selected>-- Chọn SV --</option>
                                        <?php foreach ($students as $sv): ?>
                                            <option value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"><?php echo htmlspecialchars($sv['msv'] . ' - ' . $sv['ho_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary" style="width: 100%;"><span class="material-symbols-outlined" style="vertical-align: middle; font-size:18px;">add</span> Thêm vào Lớp</button>
                            </form>

                            <form method="post" style="flex: 1;">
                                <input type="hidden" name="form_action" value="remove_student_from_lhp">
                                <div class="form-group">
                                    <label>Lớp học phần</label>
                                    <select class="select-control" name="lhp_id" required>
                                        <option value="" disabled selected>-- Chọn LHP --</option>
                                        <?php foreach ($lhps as $lhp): ?>
                                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Sinh viên</label>
                                    <select class="select-control" name="sinh_vien_id" required>
                                        <option value="" disabled selected>-- Chọn SV --</option>
                                        <?php foreach ($students as $sv): ?>
                                            <option value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"><?php echo htmlspecialchars($sv['msv'] . ' - ' . $sv['ho_ten']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary" style="width: 100%; background: var(--danger-color);"><span class="material-symbols-outlined" style="vertical-align: middle; font-size:18px;">remove</span> Xóa khỏi Lớp</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <?php include __DIR__ . '/../../layouts/footer.php'; ?>
    </div>
</body>

</html>