<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'GIANG_VIEN')) {
    header('Location: ../auth/login.php');
    exit;
}

$lhpId = trim($_GET['lhp_id'] ?? '');
$taiKhoanId = (string)($_SESSION['user_id'] ?? '');
$pdo = getDatabaseConnection();
$dsSinhVien = [];
$lhpInfo = null;
$pageError = '';
$pageInfo = '';
$giangVienId = '';

if (!$pdo) {
    $pageError = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->prepare('SELECT giang_vien_id FROM giang_vien WHERE giang_vien_id = :id OR tai_khoan_id = :id LIMIT 1');
    $stmt->execute(['id' => $taiKhoanId]);
    $gv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$gv) {
        $pageError = 'Giang vien khong ton tai trong he thong.';
    } else {
        $giangVienId = (string)$gv['giang_vien_id'];

        if ($lhpId === '') {
            $pageInfo = 'Vui long chon lop hoc phan de nhap diem.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT lhp.lhp_id, lhp.ma_lhp, lhp.trang_thai, mh.ten_mon, hk.ten_hoc_ky
                 FROM lop_hoc_phan lhp
                 JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
                 JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
                 WHERE lhp.lhp_id = :lhp_id AND lhp.giang_vien_id = :gv_id
                 LIMIT 1'
            );
            $stmt->execute(['lhp_id' => $lhpId, 'gv_id' => $giangVienId]);
            $lhpInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lhpInfo) {
                $pageError = 'Lop hoc phan khong ton tai hoac khong thuoc giang vien hien tai.';
            } else {
                $stmt = $pdo->prepare(
                    'SELECT sv.sinh_vien_id, sv.msv, sv.ho_ten,
                            d.ds_lhp_id, d.diem_cc, d.diem_gk, d.diem_ck
                     FROM ds_lhp d
                     JOIN sinh_vien sv ON sv.sinh_vien_id = d.sinh_vien_id
                     WHERE d.lhp_id = :lhp_id
                     ORDER BY sv.msv ASC'
                );
                $stmt->execute(['lhp_id' => $lhpId]);
                $dsSinhVien = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($dsSinhVien)) {
                    $pageInfo = 'Chua co sinh vien nao trong lop hoc phan nay.';
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
    <title>Nhap diem</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .page-stack {
            display: grid;
            gap: 14px;
        }

        .section-card {
            margin: 0;
        }

        .card-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner page-stack">
            <div class="dashboard-header" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-end;">
                <div>
                    <h1>Nhap diem lop hoc phan</h1>
                    <p class="muted-text">Cap nhat diem thanh phan va gui duyet bang diem cho giao vu.</p>
                </div>
                <button id="btn-gui-duyet" type="button" class="btn-primary" data-lhp="<?php echo htmlspecialchars($lhpId); ?>" <?php echo $lhpInfo ? '' : 'disabled'; ?>>Gui duyet LHP</button>
            </div>

            <?php if ($pageError !== ''): ?>
                <div class="alert-danger"><?php echo htmlspecialchars($pageError); ?></div>
            <?php elseif ($pageInfo !== ''): ?>
                <div class="alert-info"><?php echo htmlspecialchars($pageInfo); ?></div>
            <?php endif; ?>

            <div class="card section-card">
                <div class="form-grid" style="grid-template-columns:repeat(4,minmax(170px,1fr));">
                    <div>
                        <div class="muted-text">Ma LHP</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars((string)($lhpInfo['ma_lhp'] ?? '--')); ?></div>
                    </div>
                    <div>
                        <div class="muted-text">Mon hoc</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars((string)($lhpInfo['ten_mon'] ?? '--')); ?></div>
                    </div>
                    <div>
                        <div class="muted-text">Hoc ky</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars((string)($lhpInfo['ten_hoc_ky'] ?? '--')); ?></div>
                    </div>
                    <div>
                        <div class="muted-text">Trang thai LHP</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars((string)($lhpInfo['trang_thai'] ?? '--')); ?></div>
                    </div>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-toolbar">
                    <div class="form-group" style="margin:0;max-width:360px;">
                        <label>Tim sinh vien</label>
                        <input id="tim-sv" type="text" placeholder="Nhap MSSV hoac ho ten...">
                    </div>
                    <button id="btn-luu-tat-ca" type="button" class="btn-primary" data-lhp="<?php echo htmlspecialchars($lhpId); ?>" <?php echo empty($dsSinhVien) ? 'disabled' : ''; ?>>Luu toan bo diem</button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table-modern" id="bang-diem">
                        <thead>
                            <tr>
                                <th>MSSV</th>
                                <th>Ho ten</th>
                                <th>Diem CC</th>
                                <th>Diem GK</th>
                                <th>Diem CK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dsSinhVien)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;">Khong co du lieu de hien thi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dsSinhVien as $sv): ?>
                                    <tr data-id="<?php echo htmlspecialchars((string)$sv['ds_lhp_id']); ?>">
                                        <td><?php echo htmlspecialchars((string)$sv['msv']); ?></td>
                                        <td><?php echo htmlspecialchars((string)$sv['ho_ten']); ?></td>
                                        <td><input type="number" class="diem-cc" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars((string)$sv['diem_cc']); ?>"></td>
                                        <td><input type="number" class="diem-gk" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars((string)$sv['diem_gk']); ?>"></td>
                                        <td><input type="number" class="diem-ck" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars((string)$sv['diem_ck']); ?>"></td>
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

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const taiKhoanId = '<?php echo htmlspecialchars($taiKhoanId); ?>';

        function validateScore(rawValue, label, rowIndex) {
            const value = String(rawValue ?? '').trim();
            if (value === '') {
                throw new Error(`Dong ${rowIndex}: ${label} khong duoc de trong`);
            }

            const number = Number(value);
            if (!Number.isFinite(number)) {
                throw new Error(`Dong ${rowIndex}: ${label} phai la so`);
            }
            if (number < 0 || number > 10) {
                throw new Error(`Dong ${rowIndex}: ${label} phai trong khoang 0-10`);
            }

            return Math.round(number * 10) / 10;
        }

        const btnLuuTatCa = document.getElementById('btn-luu-tat-ca');
        if (btnLuuTatCa) {
            btnLuuTatCa.addEventListener('click', () => {
                const rows = Array.from(document.querySelectorAll('#bang-diem tbody tr[data-id]'));
                if (!rows.length) return;

                let payloadRows;
                try {
                    payloadRows = rows.map((row, index) => ({
                        ds_lhp_id: row.dataset.id,
                        diem_cc: validateScore(row.querySelector('.diem-cc')?.value, 'Diem CC', index + 1),
                        diem_gk: validateScore(row.querySelector('.diem-gk')?.value, 'Diem GK', index + 1),
                        diem_ck: validateScore(row.querySelector('.diem-ck')?.value, 'Diem CK', index + 1)
                    }));
                } catch (err) {
                    alert(err.message || 'Du lieu diem khong hop le');
                    return;
                }

                const payload = {
                    tai_khoan_id: taiKhoanId,
                    rows: payloadRows
                };

                fetch(`${PY_API}/api/diem/lhp/${encodeURIComponent(btnLuuTatCa.dataset.lhp)}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then((r) => r.json())
                    .then((res) => {
                        alert(res.success ? 'Da luu toan bo diem.' : ('Loi: ' + (res.message || 'Khong the luu diem')));
                    })
                    .catch(() => alert('Khong the ket noi API nhap diem.'));
            });
        }

        const btnGuiDuyet = document.getElementById('btn-gui-duyet');
        if (btnGuiDuyet) {
            btnGuiDuyet.addEventListener('click', () => {
                if (!btnGuiDuyet.dataset.lhp) return;
                if (!window.confirm('Gui duyet toan bo LHP nay?')) return;

                fetch(`${PY_API}/api/diem/lhp/${encodeURIComponent(btnGuiDuyet.dataset.lhp)}/gui-duyet`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then((r) => r.json())
                    .then((res) => {
                        alert(res.success ? 'Da gui duyet LHP.' : ('Loi: ' + (res.message || 'Khong the gui duyet')));
                    })
                    .catch(() => alert('Khong the ket noi API duyet diem.'));
            });
        }

        const searchInput = document.getElementById('tim-sv');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const keyword = searchInput.value.toLowerCase();
                document.querySelectorAll('#bang-diem tbody tr').forEach((row) => {
                    const msv = (row.children[0]?.textContent || '').toLowerCase();
                    const ten = (row.children[1]?.textContent || '').toLowerCase();
                    row.style.display = (msv.includes(keyword) || ten.includes(keyword)) ? '' : 'none';
                });
            });
        }
    </script>
</body>

</html>