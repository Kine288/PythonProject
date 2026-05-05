<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/constants.php';

$pdo = getDatabaseConnection();
$hoc_kys = [];
$selected_hoc_ky_id = $_GET['hoc_ky_id'] ?? '';

if ($pdo) {
    $stmt = $pdo->query("SELECT hoc_ky_id, ma_hoc_ky, ten_hoc_ky FROM hoc_ky ORDER BY ten_hoc_ky DESC");
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
    <title>Bao cao canh bao hoc vu</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">Bao cao canh bao hoc vu</h1>
                <p class="text-sm text-slate-500">Loc theo hoc ky, muc canh bao va xuat danh sach Excel.</p>
            </div>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-semibold">Hoc ky</label>
                    <select id="hoc-ky-id" class="w-full rounded-lg border-slate-300">
                        <?php foreach ($hoc_kys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selected_hoc_ky_id === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hk['ma_hoc_ky'] . ' - ' . $hk['ten_hoc_ky']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">Muc canh bao</label>
                    <select id="muc-canh-bao" class="w-full rounded-lg border-slate-300">
                        <option value="">Tat ca</option>
                        <option value="1">Muc 1</option>
                        <option value="2">Muc 2</option>
                        <option value="3">Buoc thoi hoc</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button id="btn-load" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700" type="button">Tai danh sach</button>
                </div>

                <div class="flex items-end">
                    <button id="btn-export" type="button" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-900">Xuat Excel</button>
                </div>
            </div>

            <div id="status-box" class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">Chua co du lieu.</div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-sm" id="warnings-table">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">MSV</th>
                            <th class="px-3 py-2 text-left">Ten sinh vien</th>
                            <th class="px-3 py-2 text-left">Lop</th>
                            <th class="px-3 py-2 text-left">GPA tich luy he 4</th>
                            <th class="px-3 py-2 text-left">Xep loai</th>
                            <th class="px-3 py-2 text-left">Muc canh bao</th>
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
        const mucEl = document.getElementById('muc-canh-bao');
        const tbody = document.querySelector('#warnings-table tbody');
        const statusBox = document.getElementById('status-box');

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
                throw new Error(data.message || data.error || 'Khong the tai danh sach canh bao');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-3 py-3">Khong co du lieu.</td></tr>';
                statusBox.textContent = 'Khong co sinh vien trong danh sach canh bao.';
                return;
            }

            rows.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-100';
                tr.innerHTML = `
                    <td class="px-3 py-2">${item.msv || ''}</td>
                    <td class="px-3 py-2">${item.ten_sv || ''}</td>
                    <td class="px-3 py-2">${item.ten_lop || ''}</td>
                    <td class="px-3 py-2">${item.gpa_tich_luy_he_4 ?? ''}</td>
                    <td class="px-3 py-2">${item.xep_loai || ''}</td>
                    <td class="px-3 py-2">${item.muc_canh_bao ?? 0}</td>
                `;
                tbody.appendChild(tr);
            });

            statusBox.textContent = `Da tai ${rows.length} ban ghi canh bao.`;
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadWarnings().catch(err => {
                statusBox.textContent = err.message;
            });
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
                        muc_canh_bao: mucEl.value || null
                    })
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
                statusBox.textContent = err.message;
            }
        });

        if (hocKyEl.value) {
            loadWarnings().catch(err => {
                statusBox.textContent = err.message;
            });
        }
    </script>
</body>

</html>