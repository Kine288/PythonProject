<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/constants.php';

$pdo = getDatabaseConnection();
$hoc_kys = [];
$selected_hoc_ky_id = $_GET['hoc_ky_id'] ?? '';

if ($pdo) {
    $stmt = $pdo->query("SELECT hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc FROM hoc_ky ORDER BY nam_hoc DESC, ky_hoc DESC");
    $hoc_kys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$selected_hoc_ky_id && !empty($hoc_kys)) {
        $selected_hoc_ky_id = $hoc_kys[0]['hoc_ky_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thong ke xep loai</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">Thong ke ty le xep loai hoc luc</h1>
                <p class="text-sm text-slate-500">Tong hop theo hoc ky toan khoa.</p>
            </div>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-semibold">Hoc ky</label>
                    <select id="hoc-ky-id" class="w-full rounded-lg border-slate-300">
                        <?php foreach ($hoc_kys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selected_hoc_ky_id === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hk['ten_hoc_ky'] . ' (' . $hk['nam_hoc'] . ' - Ky ' . $hk['ky_hoc'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-load" type="button" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700">Tai thong ke</button>
                </div>
            </div>

            <div id="status-box" class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">Chua co du lieu.</div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-sm" id="stats-table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Xep loai</th>
                            <th class="px-3 py-2 text-left">So luong</th>
                            <th class="px-3 py-2 text-left">Ty le (%)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <?php include __DIR__ . '/../../layouts/footer.php'; ?>
    </main>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const hocKyEl = document.getElementById('hoc-ky-id');
        const tbody = document.querySelector('#stats-table tbody');
        const statusBox = document.getElementById('status-box');

        async function loadStats() {
            const hocKyId = hocKyEl.value;
            const url = `${PY_API}/api/bao-cao/thong-ke-xep-loai?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || data.error || 'Khong the tai thong ke');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-3 py-3">Khong co du lieu.</td></tr>';
                statusBox.textContent = 'Khong co du lieu thong ke cho hoc ky da chon.';
                return;
            }

            rows.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-100';
                tr.innerHTML = `
                    <td class="px-3 py-2">${item.xep_loai || ''}</td>
                    <td class="px-3 py-2">${item.so_luong ?? 0}</td>
                    <td class="px-3 py-2">${item.ty_le ?? 0}</td>
                `;
                tbody.appendChild(tr);
            });

            statusBox.textContent = `Da tai ${rows.length} nhom xep loai.`;
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadStats().catch(err => {
                statusBox.textContent = err.message;
            });
        });

        if (hocKyEl.value) {
            loadStats().catch(err => {
                statusBox.textContent = err.message;
            });
        }
    </script>
</body>

</html>