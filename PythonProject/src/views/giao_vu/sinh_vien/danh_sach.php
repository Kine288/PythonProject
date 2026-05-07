<?php
session_start();
require_once __DIR__ . '/../../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../../auth/login.php');
    exit;
}

$notice = '';
$error = '';
$nguoiThayDoi = $_SESSION['user_id'] ?? ($_SESSION['tai_khoan_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    $payload = $_POST;

    if ($action === 'update_student_profile') {
        $response = sinhVienProxyRequest(
            'PUT',
            '/sinh-vien/' . trim($payload['sinh_vien_id'] ?? ''),
            [
                'ho_ten' => trim($payload['ho_ten'] ?? ''),
                'ngay_sinh' => trim($payload['ngay_sinh'] ?? '') ?: null,
                'gioi_tinh' => trim($payload['gioi_tinh'] ?? '') ?: null,
                'trang_thai' => trim($payload['trang_thai'] ?? ''),
                'lop_id' => trim($payload['lop_id'] ?? '') ?: null,
                'nguoi_thay_doi' => $nguoiThayDoi,
                'ly_do' => trim($payload['ly_do'] ?? ''),
            ]
        );
    } elseif ($action === 'transfer_class') {
        $response = sinhVienProxyRequest(
            'PUT',
            '/sinh-vien/' . trim($payload['sinh_vien_id'] ?? ''),
            [
                'lop_id' => trim($payload['lop_id_moi'] ?? '') ?: null,
                'nguoi_thay_doi' => $nguoiThayDoi,
                'ly_do' => trim($payload['ly_do'] ?? ''),
            ]
        );
    } elseif ($action === 'delete_student') {
        $response = sinhVienProxyRequest(
            'DELETE',
            '/sinh-vien/' . trim($payload['sinh_vien_id'] ?? ''),
            [
                'nguoi_thay_doi' => $nguoiThayDoi,
            ]
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
$khoaFilter = trim($_GET['nien_khoa_id'] ?? '');
$trangThaiFilter = trim($_GET['trang_thai'] ?? '');

$studentsRes = sinhVienProxyRequest('GET', '/sinh-vien', null, [
    'search' => $keyword,
    'lop_id' => $lopFilter,
    'trang_thai' => $trangThaiFilter,
]);
$students = $studentsRes['data'] ?? [];

$pdo = getDatabaseConnection();
$lops = [];
$nienKhoas = [];
$lopById = [];
$khoaByLopId = [];
$gpaByStudentId = [];

if ($pdo) {
    $lops = $pdo->query('SELECT lop_id, ma_lop, ten_lop, nien_khoa_id FROM lop_sinh_hoat ORDER BY ma_lop')->fetchAll(PDO::FETCH_ASSOC);
    $nienKhoas = $pdo->query('SELECT nien_khoa_id, ten_nien_khoa FROM nien_khoa ORDER BY nam_bat_dau DESC')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lops as $lop) {
        $lopById[$lop['lop_id']] = $lop;
    }

    foreach ($nienKhoas as $nk) {
        $khoaByLopId[$nk['nien_khoa_id']] = $nk['ten_nien_khoa'];
    }

    $gpaRows = $pdo->query('SELECT sinh_vien_id, MAX(gpa_tich_luy_he4) AS gpa_tich_luy_he4 FROM ket_qua_hoc_ky GROUP BY sinh_vien_id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($gpaRows as $row) {
        $gpaByStudentId[$row['sinh_vien_id']] = $row['gpa_tich_luy_he4'];
    }
}

$students = array_values(array_filter($students, function ($sv) use ($lopById, $khoaFilter) {
    if ($khoaFilter === '') {
        return true;
    }
    $lopId = $sv['lop_id'] ?? '';
    if ($lopId === '' || !isset($lopById[$lopId])) {
        return false;
    }
    return ($lopById[$lopId]['nien_khoa_id'] ?? '') === $khoaFilter;
}));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quan ly Sinh vien</title>
    <link rel="stylesheet" href="../../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
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
            width: min(760px, 92vw);
            max-height: 84vh;
            overflow: auto;
            padding: 16px;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            background: #e2e8f0;
            color: #334155;
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
                    <h1>Quan ly sinh vien</h1>
                    <p class="muted-text">Tra cuu va cap nhat ho so sinh vien theo lop hanh chinh, khoa va trang thai.</p>
                </div>
            </div>

            <?php if ($notice): ?>
                <div class="alert-info" style="margin-bottom:12px;"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card" style="margin-bottom:12px;">
                <form method="get" class="filter-bar" style="grid-template-columns:1.4fr 1fr 1fr 1fr auto;gap:12px;">
                    <input type="text" class="search-bar" name="keyword" placeholder="Tim theo MSSV/Ho ten..." value="<?php echo htmlspecialchars($keyword); ?>">

                    <select name="lop_id" class="select-control">
                        <option value="">Lop hanh chinh</option>
                        <?php foreach ($lops as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $lopFilter === $lop['lop_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['ma_lop'] . ' - ' . $lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="nien_khoa_id" class="select-control">
                        <option value="">Khoa</option>
                        <?php foreach ($nienKhoas as $nk): ?>
                            <option value="<?php echo htmlspecialchars($nk['nien_khoa_id']); ?>" <?php echo $khoaFilter === $nk['nien_khoa_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nk['ten_nien_khoa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="trang_thai" class="select-control">
                        <option value="">Trang thai hoc tap</option>
                        <option value="DANG_HOC" <?php echo $trangThaiFilter === 'DANG_HOC' ? 'selected' : ''; ?>>DANG_HOC</option>
                        <option value="BAO_LUU" <?php echo $trangThaiFilter === 'BAO_LUU' ? 'selected' : ''; ?>>BAO_LUU</option>
                        <option value="TOT_NGHIEP" <?php echo $trangThaiFilter === 'TOT_NGHIEP' ? 'selected' : ''; ?>>TOT_NGHIEP</option>
                        <option value="BUOC_THOI_HOC" <?php echo $trangThaiFilter === 'BUOC_THOI_HOC' ? 'selected' : ''; ?>>BUOC_THOI_HOC</option>
                    </select>

                    <button type="submit" class="btn-primary">Loc</button>
                </form>
            </div>

            <div class="card">
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>MSSV</th>
                                <th>Ho ten</th>
                                <th>Lop HC</th>
                                <th>Khoa</th>
                                <th>GPA tich luy</th>
                                <th>Trang thai</th>
                                <th>Thao tac</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">Khong co du lieu sinh vien.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $sv): ?>
                                    <?php
                                    $lopId = $sv['lop_id'] ?? '';
                                    $lop = $lopById[$lopId] ?? null;
                                    $nienKhoaName = $lop ? ($khoaByLopId[$lop['nien_khoa_id']] ?? '--') : '--';
                                    $gpa = $gpaByStudentId[$sv['sinh_vien_id']] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sv['msv']); ?></td>
                                        <td><?php echo htmlspecialchars($sv['ho_ten']); ?></td>
                                        <td><?php echo htmlspecialchars($lop['ma_lop'] ?? ($sv['ten_lop'] ?? '--')); ?></td>
                                        <td><?php echo htmlspecialchars($nienKhoaName); ?></td>
                                        <td><?php echo $gpa !== null ? htmlspecialchars(number_format((float)$gpa, 2)) : '--'; ?></td>
                                        <td><span class="badge-status"><?php echo htmlspecialchars($sv['trang_thai'] ?? '--'); ?></span></td>
                                        <td style="display:flex;gap:8px;flex-wrap:wrap;">
                                            <button
                                                type="button"
                                                class="btn-secondary open-edit"
                                                data-sv-id="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"
                                                data-ho-ten="<?php echo htmlspecialchars($sv['ho_ten']); ?>"
                                                data-ngay-sinh="<?php echo htmlspecialchars((string)($sv['ngay_sinh'] ?? '')); ?>"
                                                data-gioi-tinh="<?php echo htmlspecialchars((string)($sv['gioi_tinh'] ?? '')); ?>"
                                                data-trang-thai="<?php echo htmlspecialchars((string)($sv['trang_thai'] ?? 'DANG_HOC')); ?>"
                                                data-lop-id="<?php echo htmlspecialchars((string)($sv['lop_id'] ?? '')); ?>"
                                                data-msv="<?php echo htmlspecialchars($sv['msv']); ?>">
                                                Sua thong tin
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-secondary open-transfer"
                                                data-sv-id="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>"
                                                data-msv="<?php echo htmlspecialchars($sv['msv']); ?>"
                                                data-lop-id="<?php echo htmlspecialchars((string)($sv['lop_id'] ?? '')); ?>">
                                                Chuyen lop
                                            </button>
                                            <form method="post" onsubmit="return confirm('Xac nhan xoa sinh vien nay?');" style="margin:0;">
                                                <input type="hidden" name="form_action" value="delete_student">
                                                <input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
                                                <button type="submit" class="btn-danger">Xoa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/footer.php'; ?>
    </div>

    <div class="modal-backdrop" id="edit-modal">
        <div class="modal-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                <h3 style="margin:0;">Sua thong tin sinh vien</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <p class="muted-text" id="edit-caption" style="margin:8px 0 12px;">--</p>

            <form method="post">
                <input type="hidden" name="form_action" value="update_student_profile">
                <input type="hidden" name="sinh_vien_id" id="edit-sv-id">

                <div class="form-grid" style="grid-template-columns:repeat(2,minmax(220px,1fr));">
                    <div class="form-group">
                        <label>Ho ten</label>
                        <input name="ho_ten" id="edit-ho-ten" required>
                    </div>
                    <div class="form-group">
                        <label>Ngay sinh</label>
                        <input type="date" name="ngay_sinh" id="edit-ngay-sinh">
                    </div>
                    <div class="form-group">
                        <label>Gioi tinh</label>
                        <select name="gioi_tinh" id="edit-gioi-tinh">
                            <option value="">-- Chon --</option>
                            <option value="Nam">Nam</option>
                            <option value="Nu">Nu</option>
                            <option value="Khac">Khac</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trang thai</label>
                        <select name="trang_thai" id="edit-trang-thai" required>
                            <option value="DANG_HOC">DANG_HOC</option>
                            <option value="BAO_LUU">BAO_LUU</option>
                            <option value="TOT_NGHIEP">TOT_NGHIEP</option>
                            <option value="BUOC_THOI_HOC">BUOC_THOI_HOC</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lop hanh chinh</label>
                        <select name="lop_id" id="edit-lop-id">
                            <?php foreach ($lops as $lop): ?>
                                <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ma_lop'] . ' - ' . $lop['ten_lop']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ly do cap nhat</label>
                        <input name="ly_do" placeholder="Vi du: cap nhat theo ho so bo sung">
                    </div>
                </div>

                <button class="btn-primary" type="submit">Luu thong tin</button>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="transfer-modal">
        <div class="modal-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                <h3 style="margin:0;">Chuyen lop hanh chinh</h3>
                <button type="button" class="btn-secondary close-modal">Dong</button>
            </div>
            <p class="muted-text" id="transfer-caption" style="margin:8px 0 12px;">--</p>

            <form method="post">
                <input type="hidden" name="form_action" value="transfer_class">
                <input type="hidden" name="sinh_vien_id" id="transfer-sv-id">
                <div class="form-group">
                    <label>Lop chuyen den</label>
                    <select name="lop_id_moi" id="transfer-lop-id" required>
                        <?php foreach ($lops as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ma_lop'] . ' - ' . $lop['ten_lop']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ly do chuyen lop</label>
                    <input name="ly_do" required placeholder="Nhap ly do bat buoc">
                </div>
                <button class="btn-primary" type="submit">Xac nhan chuyen lop</button>
            </form>
        </div>
    </div>

    <script>
        const editModal = document.getElementById('edit-modal');
        const transferModal = document.getElementById('transfer-modal');

        function closeModals() {
            editModal.style.display = 'none';
            transferModal.style.display = 'none';
        }

        document.querySelectorAll('.close-modal').forEach((btn) => {
            btn.addEventListener('click', closeModals);
        });

        [editModal, transferModal].forEach((modal) => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModals();
                }
            });
        });

        document.querySelectorAll('.open-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-sv-id').value = btn.dataset.svId || '';
                document.getElementById('edit-ho-ten').value = btn.dataset.hoTen || '';
                document.getElementById('edit-ngay-sinh').value = btn.dataset.ngaySinh || '';
                document.getElementById('edit-gioi-tinh').value = btn.dataset.gioiTinh || '';
                document.getElementById('edit-trang-thai').value = btn.dataset.trangThai || 'DANG_HOC';
                document.getElementById('edit-lop-id').value = btn.dataset.lopId || '';
                document.getElementById('edit-caption').textContent = `MSSV: ${btn.dataset.msv || ''}`;
                editModal.style.display = 'flex';
            });
        });

        document.querySelectorAll('.open-transfer').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('transfer-sv-id').value = btn.dataset.svId || '';
                document.getElementById('transfer-lop-id').value = btn.dataset.lopId || '';
                document.getElementById('transfer-caption').textContent = `MSSV: ${btn.dataset.msv || ''}`;
                transferModal.style.display = 'flex';
            });
        });
    </script>
</body>

</html>