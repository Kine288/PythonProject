<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

$pdo = getDatabaseConnection();
$hoc_kys = [];
$selected_hoc_ky_id = $_GET['hoc_ky_id'] ?? '';

if ($pdo) {
    $stmt = $pdo->query("SELECT hoc_ky_id, ma_hoc_ky, ten_hoc_ky, is_hien_tai FROM hoc_ky ORDER BY ten_hoc_ky DESC");
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
    <title>Tong ket hoc vu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">Tong ket hoc vu</h1>
                <p class="text-sm text-slate-500">UC6.1 Kich hoat tinh GPA va UC6.2 loc danh sach canh bao hoc vu.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Hoc ky</label>
                    <select id="hoc-ky-id" class="w-full rounded-lg border-slate-300">
                        <?php foreach ($hoc_kys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selected_hoc_ky_id === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hk['ma_hoc_ky'] . ' - ' . $hk['ten_hoc_ky']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-recalculate" class="w-full bg-teal-600 hover:bg-teal-700 text-white rounded-lg px-4 py-2 font-semibold" type="button">
                        Kich hoat tinh GPA va xep loai
                    </button>
                </div>
                <div class="flex items-end">
                    <button id="btn-load-warnings" class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-lg px-4 py-2 font-semibold" type="button">
                        Loc danh sach canh bao
                    </button>
                </div>
            </div>

            <div id="status-box" class="hidden rounded-lg border px-4 py-3 text-sm"></div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-3">
                <h2 class="font-semibold">Tra cuu ket qua mot sinh vien</h2>
                <div class="grid md:grid-cols-3 gap-3">
                    <input id="sinh-vien-id" class="rounded-lg border-slate-300" placeholder="Nhap sinh_vien_id..." type="text">
                    <button id="btn-student-result" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 font-semibold" type="button">Xem ket qua</button>
                </div>
                <pre id="student-result" class="bg-slate-900 text-slate-100 p-3 rounded-lg text-xs overflow-x-auto">Chua co du lieu</pre>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="font-semibold">Danh sach canh bao hoc vu</h2>
                    <span id="warnings-count" class="text-sm text-slate-500">0 ban ghi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="warnings-table">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left">MSV</th>
                                <th class="px-4 py-2 text-left">Ten sinh vien</th>
                                <th class="px-4 py-2 text-left">GPA tich luy he 4</th>
                                <th class="px-4 py-2 text-left">Xep loai</th>
                                <th class="px-4 py-2 text-left">Muc canh bao</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </main>

    <script>
        const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';
        const statusBox = document.getElementById('status-box');
        const hocKyEl = document.getElementById('hoc-ky-id');
        const warningsBody = document.querySelector('#warnings-table tbody');
        const warningsCount = document.getElementById('warnings-count');

        function showStatus(message, ok = true) {
            statusBox.classList.remove('hidden');
            statusBox.className = ok ?
                'rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm' :
                'rounded-lg border border-rose-300 bg-rose-50 text-rose-800 px-4 py-3 text-sm';
            statusBox.textContent = message;
        }

        async function loadWarnings() {
            const hocKyId = hocKyEl.value;
            const res = await fetch(`${PY_API}/api/gpa/warnings?hoc_ky_id=${encodeURIComponent(hocKyId)}`);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || data.error || 'Khong the tai danh sach canh bao');
            }

            warningsBody.innerHTML = '';
            (data.data || []).forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-100';
                tr.innerHTML = `
            <td class="px-4 py-2">${item.msv || ''}</td>
            <td class="px-4 py-2">${item.ten_sv || ''}</td>
            <td class="px-4 py-2">${item.gpa_tich_luy_he_4 ?? ''}</td>
            <td class="px-4 py-2">${item.xep_loai || ''}</td>
            <td class="px-4 py-2">${item.muc_canh_bao ?? 0}</td>
        `;
                warningsBody.appendChild(tr);
            });

            warningsCount.textContent = `${(data.data || []).length} ban ghi`;
        }

        document.getElementById('btn-recalculate').addEventListener('click', async () => {
            try {
                const hocKyId = hocKyEl.value;
                const res = await fetch(`${PY_API}/api/gpa/recalculate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        hoc_ky_id: hocKyId
                    })
                });
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || data.error || 'Khong the tinh GPA');
                }
                showStatus(`Da tinh xong ${(data.data || []).length} sinh vien`);
                await loadWarnings();
            } catch (err) {
                showStatus(err.message, false);
            }
        });

        document.getElementById('btn-load-warnings').addEventListener('click', async () => {
            try {
                await loadWarnings();
                showStatus('Da tai danh sach canh bao hoc vu');
            } catch (err) {
                showStatus(err.message, false);
            }
        });

        document.getElementById('btn-student-result').addEventListener('click', async () => {
            try {
                const svId = document.getElementById('sinh-vien-id').value.trim();
                if (!svId) {
                    throw new Error('Vui long nhap sinh_vien_id');
                }
                const hocKyId = hocKyEl.value;
                const url = `${PY_API}/api/gpa/students/${encodeURIComponent(svId)}?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || data.error || 'Khong tim thay ket qua');
                }
                document.getElementById('student-result').textContent = JSON.stringify(data.data, null, 2);
                showStatus('Da tai ket qua sinh vien');
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