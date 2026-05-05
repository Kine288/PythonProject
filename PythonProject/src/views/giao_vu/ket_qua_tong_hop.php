<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

$pdo = getDatabaseConnection();
$lhps = [];
$selected_lhp_id = $_GET['lhp_id'] ?? '';
$error = '';

if (!$pdo) {
    $error = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->query("SELECT lhp_id, ma_lhp FROM lop_hoc_phan ORDER BY ma_lhp");
    $lhps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$selected_lhp_id && !empty($lhps)) {
        $selected_lhp_id = $lhps[0]['lhp_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ket qua tong hop LHP</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">Bang diem tong hop toan LHP</h1>
                <p class="text-sm text-slate-500">Tong hop diem thanh phan, diem tong, xep loai va muc canh bao theo lop hoc phan.</p>
            </div>

            <?php if ($error): ?>
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-semibold">Lop hoc phan</label>
                    <select id="lhp-id" class="w-full rounded-lg border-slate-300">
                        <?php foreach ($lhps as $lhp): ?>
                            <option value="<?php echo htmlspecialchars($lhp['lhp_id']); ?>" <?php echo $selected_lhp_id === $lhp['lhp_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lhp['ma_lhp']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-load" type="button" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700">Tai tong hop</button>
                </div>
            </div>

            <div id="status-box" class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">Chua co du lieu.</div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-sm" id="summary-table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">MSV</th>
                            <th class="px-3 py-2 text-left">Ho ten</th>
                            <th class="px-3 py-2 text-left">CC</th>
                            <th class="px-3 py-2 text-left">GK</th>
                            <th class="px-3 py-2 text-left">CK</th>
                            <th class="px-3 py-2 text-left">Diem tong</th>
                            <th class="px-3 py-2 text-left">GPA tich luy he 4</th>
                            <th class="px-3 py-2 text-left">Xep loai</th>
                            <th class="px-3 py-2 text-left">Muc canh bao</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </main>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const lhpEl = document.getElementById('lhp-id');
        const tbody = document.querySelector('#summary-table tbody');
        const statusBox = document.getElementById('status-box');

        async function loadSummary() {
            const lhpId = lhpEl.value;
            const res = await fetch(`${PY_API}/api/gpa/summary/lhp/${encodeURIComponent(lhpId)}`);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || data.error || 'Khong the tai tong hop LHP');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-3 py-3">Khong co du lieu.</td></tr>';
                statusBox.textContent = 'Khong co du lieu tong hop cho LHP da chon.';
                return;
            }

            rows.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-100';
                tr.innerHTML = `
                    <td class="px-3 py-2">${item.msv || ''}</td>
                    <td class="px-3 py-2">${item.ten_sv || ''}</td>
                    <td class="px-3 py-2">${item.diem_cc ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_gk ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_ck ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_tong ?? ''}</td>
                    <td class="px-3 py-2">${item.gpa_tich_luy_he_4 ?? ''}</td>
                    <td class="px-3 py-2">${item.xep_loai || ''}</td>
                    <td class="px-3 py-2">${item.muc_canh_bao ?? 0}</td>
                `;
                tbody.appendChild(tr);
            });

            statusBox.textContent = `Da tai ${rows.length} ban ghi cho LHP ${rows[0].ma_lhp || ''}.`;
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadSummary().catch(err => {
                statusBox.textContent = err.message;
            });
        });

        if (lhpEl.value) {
            loadSummary().catch(err => {
                statusBox.textContent = err.message;
            });
        }
    </script>
</body>

</html>