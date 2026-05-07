<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'GIANG_VIEN')) {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$taiKhoanId = (string)($_SESSION['user_id'] ?? '');
$giangVienId = '';
$pageError = '';
$dsLhpRows = [];

if (!$pdo) {
    $pageError = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->prepare('SELECT giang_vien_id FROM giang_vien WHERE giang_vien_id = :id OR tai_khoan_id = :id LIMIT 1');
    $stmt->execute(['id' => $taiKhoanId]);
    $gv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gv) {
        $pageError = 'Khong tim thay giang vien.';
    } else {
        $giangVienId = (string)$gv['giang_vien_id'];

        $stmt = $pdo->prepare(
            'SELECT d.ds_lhp_id, sv.msv, sv.ho_ten, lhp.ma_lhp, mh.ten_mon
             FROM ds_lhp d
             JOIN sinh_vien sv ON sv.sinh_vien_id = d.sinh_vien_id
             JOIN lop_hoc_phan lhp ON lhp.lhp_id = d.lhp_id
             JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
             WHERE lhp.giang_vien_id = :gv_id
             ORDER BY lhp.ma_lhp ASC, sv.msv ASC'
        );
        $stmt->execute(['gv_id' => $giangVienId]);
        $dsLhpRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeu cau sua diem</title>
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
                    <h1>Yeu cau sua diem</h1>
                    <p class="muted-text">Tao va theo doi cac yeu cau sua diem gui den giao vu.</p>
                </div>
            </div>

            <?php if ($pageError !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($pageError); ?></div>
            <?php endif; ?>

            <div class="card" style="margin-bottom:12px;">
                <h3 style="margin-bottom:10px;">Tao yeu cau moi</h3>
                <div class="form-grid" style="grid-template-columns:1.2fr 2fr auto;align-items:end;">
                    <div class="form-group" style="margin:0;">
                        <label>Chon sinh vien - lop hoc phan</label>
                        <select id="ds-lhp-id">
                            <option value="">-- Chon --</option>
                            <?php foreach ($dsLhpRows as $row): ?>
                                <option value="<?php echo htmlspecialchars((string)$row['ds_lhp_id']); ?>">
                                    <?php echo htmlspecialchars(($row['ma_lhp'] ?? '') . ' | ' . ($row['msv'] ?? '') . ' - ' . ($row['ho_ten'] ?? '') . ' | ' . ($row['ten_mon'] ?? '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Ly do sua diem</label>
                        <input id="ly-do" type="text" placeholder="Nhap ly do...">
                    </div>
                    <button id="btn-tao-yeu-cau" class="btn-primary" type="button">Gui yeu cau</button>
                </div>
            </div>

            <div id="status-box" class="alert-info" style="display:none;margin-bottom:12px;"></div>

            <div class="card">
                <h3 style="margin-bottom:10px;">Danh sach yeu cau cua toi</h3>
                <div style="overflow-x:auto;">
                    <table class="table-modern" id="yc-table">
                        <thead>
                            <tr>
                                <th>Yeu cau ID</th>
                                <th>DS LHP ID</th>
                                <th>Ly do</th>
                                <th>Trang thai</th>
                                <th>Ghi chu giao vu</th>
                                <th>Ngay tao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align:center;">Chua tai du lieu.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const giangVienId = '<?php echo htmlspecialchars($giangVienId); ?>';
        const tbody = document.querySelector('#yc-table tbody');
        const statusBox = document.getElementById('status-box');

        function showStatus(message, ok = true) {
            statusBox.style.display = 'block';
            statusBox.className = ok ? 'alert-info' : 'alert-danger';
            statusBox.textContent = message;
        }

        async function loadYeuCau() {
            const res = await fetch(`${PY_API}/api/diem/yeu-cau-sua`);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || 'Khong the tai yeu cau');
            }

            const rows = (data.data || []).filter((item) => String(item.giang_vien_id || '') === giangVienId);
            tbody.innerHTML = '';

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Ban chua tao yeu cau nao.</td></tr>';
                return;
            }

            rows.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.yc_id || ''}</td>
                    <td>${item.ds_lhp_id || ''}</td>
                    <td>${item.ly_do || ''}</td>
                    <td>${item.trang_thai || ''}</td>
                    <td>${item.ghi_chu_giao_vu || '--'}</td>
                    <td>${item.ngay_tao || ''}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('btn-tao-yeu-cau').addEventListener('click', async () => {
            const dsLhpId = (document.getElementById('ds-lhp-id').value || '').trim();
            const lyDo = (document.getElementById('ly-do').value || '').trim();

            if (!dsLhpId || !lyDo) {
                showStatus('Vui long chon DS LHP va nhap ly do.', false);
                return;
            }

            try {
                const res = await fetch(`${PY_API}/api/diem/yeu-cau-sua`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ds_lhp_id: dsLhpId,
                        giang_vien_id: giangVienId,
                        ly_do: lyDo,
                    })
                });
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'Khong the tao yeu cau');
                }

                showStatus('Da gui yeu cau sua diem.');
                document.getElementById('ly-do').value = '';
                await loadYeuCau();
            } catch (err) {
                showStatus(err.message, false);
            }
        });

        if (giangVienId) {
            loadYeuCau().catch((err) => showStatus(err.message, false));
        }
    </script>
</body>

</html>