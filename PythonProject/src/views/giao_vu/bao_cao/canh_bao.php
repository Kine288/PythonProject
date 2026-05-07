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
    <title>Bao cao canh bao hoc vu</title>
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
                    <h1>Bao cao canh bao hoc vu</h1>
                    <p class="muted-text">Loc theo hoc ky va muc canh bao, sau do xuat danh sach Excel.</p>
                </div>
            </div>

            <div class="card" style="margin-bottom:12px;">
                <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:12px;align-items:end;">
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
                    <div class="form-group" style="margin:0;">
                        <label>Muc canh bao</label>
                        <select id="muc-canh-bao">
                            <option value="">Tat ca</option>
                            <option value="1">Muc 1</option>
                            <option value="2">Muc 2</option>
                            <option value="3">Buoc thoi hoc</option>
                        </select>
                    </div>
                    <button id="btn-load" class="btn-primary" type="button">Tai danh sach</button>
                    <button id="btn-export" class="btn-secondary" type="button">Xuat Excel</button>
                </div>
            </div>

            <div id="status-box" class="alert-info" style="margin-bottom:12px;">Chua co du lieu.</div>

            <div class="card">
                <div style="overflow-x:auto;">
                    <table class="table-modern" id="warnings-table">
                        <thead>
                            <tr>
                                <th>MSV</th>
                                <th>Ten sinh vien</th>
                                <th>Lop</th>
                                <th>GPA tich luy he 4</th>
                                <th>Xep loai</th>
                                <th>Muc canh bao</th>
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
        const mucEl = document.getElementById('muc-canh-bao');
        const tbody = document.querySelector('#warnings-table tbody');
        const statusBox = document.getElementById('status-box');

        function showStatus(message, ok = true) {
            statusBox.className = ok ? 'alert-info' : 'alert-danger';
            statusBox.textContent = message;
        }

        async function loadWarnings() {
            const hocKyId = hocKyEl.value;
            const muc = mucEl.value;
            let url = `${PY_API}/api/bao-cao/canh-bao?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
            if (muc) {
                url += `&muc_canh_bao=${encodeURIComponent(muc)}`;
            }

            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || 'Khong the tai danh sach canh bao');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Khong co du lieu.</td></tr>';
                showStatus('Khong co sinh vien trong danh sach canh bao.');
                return;
            }

            rows.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.msv || ''}</td>
                    <td>${item.ho_ten || ''}</td>
                    <td>${item.ten_lop || ''}</td>
                    <td>${item.gpa_tich_luy_he4 ?? ''}</td>
                    <td>${item.xep_loai || ''}</td>
                    <td>${item.muc_canh_bao ?? 0}</td>
                `;
                tbody.appendChild(tr);
            });

            showStatus(`Da tai ${rows.length} ban ghi canh bao.`);
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadWarnings().catch((err) => showStatus(err.message, false));
        });

        document.getElementById('btn-export').addEventListener('click', async () => {
            try {
                const response = await fetch(`${PY_API}/api/bao-cao/export/canh-bao-excel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        hoc_ky_id: hocKyEl.value,
                        muc_canh_bao: mucEl.value || null,
                    }),
                });

                if (!response.ok) {
                    const err = await response.json();
                    throw new Error(err.message || 'Khong the xuat Excel');
                }

                const blob = await response.blob();
                const fileUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = `canh_bao_hoc_ky_${hocKyEl.value}.xlsx`;
                link.click();
                URL.revokeObjectURL(fileUrl);
            } catch (err) {
                showStatus(err.message, false);
            }
        });

        if (hocKyEl.value) {
            loadWarnings().catch(() => {});
        }
    </script>
</body>

</html>