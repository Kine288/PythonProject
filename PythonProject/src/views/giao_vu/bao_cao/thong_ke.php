<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/constants.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$hocKys = [];
$selectedHocKy = trim($_GET['hoc_ky_id'] ?? '');

if ($pdo) {
    $hocKys = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC')->fetchAll(PDO::FETCH_ASSOC);
    if ($selectedHocKy === '' && !empty($hocKys)) {
        foreach ($hocKys as $hk) {
            if (!empty($hk['is_hien_tai'])) {
                $selectedHocKy = $hk['hoc_ky_id'];
                break;
            }
        }
        if ($selectedHocKy === '') {
            $selectedHocKy = $hocKys[0]['hoc_ky_id'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thong ke xep loai hoc luc</title>
    <link rel="stylesheet" href="../../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Thong ke xep loai hoc luc</h1>
                    <p class="muted-text">Tong hop so luong va ty le xep loai theo hoc ky toan khoa.</p>
                </div>
            </div>

            <div class="card" style="margin-bottom:12px;">
                <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end;">
                    <div class="form-group" style="margin:0;">
                        <label>Hoc ky</label>
                        <select id="hoc-ky-id">
                            <?php foreach ($hocKys as $hk): ?>
                                <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selectedHocKy === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button id="btn-load" type="button" class="btn-primary">Tai thong ke</button>
                </div>
            </div>

            <div id="status-box" class="alert-info" style="margin-bottom:12px;">Chua co du lieu.</div>

            <div style="display:grid;grid-template-columns:repeat(3,minmax(160px,1fr));gap:12px;margin-bottom:12px;">
                <div class="card" style="margin:0;">
                    <div class="muted-text">Tong nhom xep loai</div>
                    <div id="kpi-groups" style="font-size:24px;font-weight:700;">0</div>
                </div>
                <div class="card" style="margin:0;">
                    <div class="muted-text">Tong so sinh vien</div>
                    <div id="kpi-students" style="font-size:24px;font-weight:700;">0</div>
                </div>
                <div class="card" style="margin:0;">
                    <div class="muted-text">Nhom cao nhat</div>
                    <div id="kpi-top" style="font-size:20px;font-weight:700;">--</div>
                </div>
            </div>

            <div class="card">
                <div style="overflow-x:auto;">
                    <table class="table-modern" id="stats-table">
                        <thead>
                            <tr>
                                <th>Xep loai</th>
                                <th>So luong</th>
                                <th>Ty le (%)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/footer.php'; ?>
    </div>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const hocKyEl = document.getElementById('hoc-ky-id');
        const tbody = document.querySelector('#stats-table tbody');
        const statusBox = document.getElementById('status-box');

        function showStatus(message, ok = true) {
            statusBox.className = ok ? 'alert-info' : 'alert-danger';
            statusBox.textContent = message;
        }

        async function loadStats() {
            const hocKyId = hocKyEl.value;
            const url = `${PY_API}/api/bao-cao/thong-ke-xep-loai?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || 'Khong the tai thong ke');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Khong co du lieu.</td></tr>';
                document.getElementById('kpi-groups').textContent = '0';
                document.getElementById('kpi-students').textContent = '0';
                document.getElementById('kpi-top').textContent = '--';
                showStatus('Khong co du lieu thong ke cho hoc ky da chon.');
                return;
            }

            let totalStudents = 0;
            let topName = '--';
            let topCount = -1;

            rows.forEach((item) => {
                const soLuong = Number(item.so_luong || 0);
                totalStudents += soLuong;
                if (soLuong > topCount) {
                    topCount = soLuong;
                    topName = item.xep_loai || '--';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.xep_loai || ''}</td>
                    <td>${soLuong}</td>
                    <td>${item.ty_le ?? 0}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('kpi-groups').textContent = String(rows.length);
            document.getElementById('kpi-students').textContent = String(totalStudents);
            document.getElementById('kpi-top').textContent = topName;
            showStatus(`Da tai ${rows.length} nhom xep loai.`);
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadStats().catch((err) => showStatus(err.message, false));
        });

        if (hocKyEl.value) {
            loadStats().catch(() => {});
        }
    </script>
</body>

</html>