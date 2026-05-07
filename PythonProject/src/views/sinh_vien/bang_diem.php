<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SINH_VIEN') {
    header('Location: ../auth/login.php');
    exit;
}

$pdo = getDatabaseConnection();
$taiKhoanId = (string)($_SESSION['user_id'] ?? '');
$sinhVienId = '';
$error = '';

$hocKys = [];
$selectedHocKyId = trim($_GET['hoc_ky_id'] ?? '');

if (!$pdo) {
    $error = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->query('SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC');
    $hocKys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($selectedHocKyId === '' && !empty($hocKys)) {
        $selectedHocKyId = $hocKys[0]['hoc_ky_id'];
    }

    if ($taiKhoanId !== '') {
        $stmt = $pdo->prepare('SELECT sinh_vien_id FROM sinh_vien WHERE sinh_vien_id = :id OR tai_khoan_id = :id LIMIT 1');
        $stmt->execute(['id' => $taiKhoanId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sinhVienId = (string)$row['sinh_vien_id'];
        } else {
            $error = 'Khong tim thay sinh vien trong session hien tai.';
        }
    } else {
        $error = 'Khong tim thay user_id trong session.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bang diem ca nhan</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-end;">
                <div>
                    <h1>Bang diem ca nhan</h1>
                    <p class="muted-text">Tra cuu diem mon hoc, diem chu va quy doi he 4 theo hoc ky.</p>
                </div>
                <button id="btn-export-pdf" type="button" class="btn-secondary">Xuat PDF</button>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card" style="margin-bottom:12px;">
                <div class="form-grid" style="grid-template-columns:1fr auto;align-items:end;">
                    <div class="form-group" style="margin:0;">
                        <label>Hoc ky</label>
                        <select id="hoc-ky-id">
                            <?php foreach ($hocKys as $hk): ?>
                                <option value="<?php echo htmlspecialchars((string)$hk['hoc_ky_id']); ?>" <?php echo $selectedHocKyId === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button id="btn-load" type="button" class="btn-primary">Tai bang diem</button>
                </div>
            </div>

            <div id="meta-box" class="alert-info" style="margin-bottom:12px;">Chua co du lieu.</div>

            <div class="card">
                <div style="overflow-x:auto;">
                    <table class="table-modern" id="bang-diem-table">
                        <thead>
                            <tr>
                                <th>Hoc ky</th>
                                <th>Ma mon</th>
                                <th>Ten mon</th>
                                <th>CC</th>
                                <th>GK</th>
                                <th>CK</th>
                                <th>Diem tong</th>
                                <th>Diem chu</th>
                                <th>He 4</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const sinhVienId = '<?php echo htmlspecialchars($sinhVienId); ?>';
        const hocKyEl = document.getElementById('hoc-ky-id');
        const tbody = document.querySelector('#bang-diem-table tbody');
        const metaBox = document.getElementById('meta-box');

        async function loadTranscript() {
            if (!sinhVienId) return;

            const hocKyId = hocKyEl.value;
            const url = `${PY_API}/api/sinh-vien/${encodeURIComponent(sinhVienId)}/bang-diem?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || data.error || 'Khong the tai bang diem');
            }

            const rows = (data.data && data.data.bang_diem) ? data.data.bang_diem : [];
            tbody.innerHTML = '';

            if (!rows.length) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Khong co du lieu.</td></tr>';
                metaBox.textContent = 'Khong co ket qua trong hoc ky da chon.';
                return;
            }

            rows.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.ten_hoc_ky || ''}</td>
                    <td>${item.ma_mon || ''}</td>
                    <td>${item.ten_mon || ''}</td>
                    <td>${item.diem_cc ?? ''}</td>
                    <td>${item.diem_gk ?? ''}</td>
                    <td>${item.diem_ck ?? ''}</td>
                    <td>${item.diem_tong ?? ''}</td>
                    <td>${item.diem_chu ?? ''}</td>
                    <td>${item.diem_he_4 ?? ''}</td>
                `;
                tbody.appendChild(tr);
            });

            metaBox.textContent = `Da tai ${rows.length} mon hoc da duyet.`;
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadTranscript().catch((err) => {
                metaBox.className = 'alert-danger';
                metaBox.textContent = err.message;
            });
        });

        document.getElementById('btn-export-pdf').addEventListener('click', async () => {
            if (!sinhVienId) {
                metaBox.className = 'alert-danger';
                metaBox.textContent = 'Khong tim thay sinh_vien_id de xuat PDF.';
                return;
            }

            try {
                const res = await fetch(`${PY_API}/api/bao-cao/bang-diem-pdf/${encodeURIComponent(sinhVienId)}`);

                if (!res.ok) {
                    const errorData = await res.json();
                    throw new Error(errorData.message || 'Khong the xuat PDF');
                }

                const blob = await res.blob();
                const fileUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = fileUrl;
                link.download = `bang_diem_${sinhVienId}.pdf`;
                link.click();
                URL.revokeObjectURL(fileUrl);
            } catch (err) {
                metaBox.className = 'alert-danger';
                metaBox.textContent = err.message;
            }
        });

        if (sinhVienId && hocKyEl.value) {
            loadTranscript().catch((err) => {
                metaBox.className = 'alert-danger';
                metaBox.textContent = err.message;
            });
        }
    </script>
</body>

</html>