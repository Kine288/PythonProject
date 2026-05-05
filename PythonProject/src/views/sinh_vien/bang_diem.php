<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

$pdo = getDatabaseConnection();
$tai_khoan_id = $_SESSION['user_id'] ?? '';
$sinh_vien_id = '';
$error = '';

$hoc_kys = [];
$selected_hoc_ky_id = $_GET['hoc_ky_id'] ?? '';

if (!$pdo) {
    $error = 'Khong ket noi duoc co so du lieu.';
} else {
    $stmt = $pdo->query("SELECT hoc_ky_id, ma_hoc_ky, ten_hoc_ky FROM hoc_ky ORDER BY ten_hoc_ky DESC");
    $hoc_kys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$selected_hoc_ky_id && !empty($hoc_kys)) {
        $selected_hoc_ky_id = $hoc_kys[0]['hoc_ky_id'];
    }

    if ($tai_khoan_id !== '') {
        $stmt = $pdo->prepare("SELECT sinh_vien_id FROM sinh_vien WHERE sinh_vien_id = ? OR tai_khoan_id = ? LIMIT 1");
        $stmt->execute([$tai_khoan_id, $tai_khoan_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $sinh_vien_id = $row['sinh_vien_id'];
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
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Bang diem ca nhan</h1>
                    <p class="text-sm text-slate-500">Tra cuu diem mon hoc, GPA va xep loai hoc luc.</p>
                </div>

                <button id="btn-export-pdf" type="button" class="rounded-lg bg-slate-800 px-4 py-2 text-white hover:bg-slate-900">Xuat PDF bang diem</button>
            </div>

            <?php if ($error): ?>
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-3">
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
                <div class="flex items-end">
                    <button id="btn-load" type="button" class="w-full rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700">Tai bang diem</button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 text-sm text-slate-500" id="meta-box">Chua co du lieu.</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" id="bang-diem-table">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Hoc ky</th>
                                <th class="px-3 py-2 text-left">Ma mon</th>
                                <th class="px-3 py-2 text-left">Ten mon</th>
                                <th class="px-3 py-2 text-left">CC</th>
                                <th class="px-3 py-2 text-left">GK</th>
                                <th class="px-3 py-2 text-left">CK</th>
                                <th class="px-3 py-2 text-left">Diem tong</th>
                                <th class="px-3 py-2 text-left">Diem chu</th>
                                <th class="px-3 py-2 text-left">He 4</th>
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
        const sinhVienId = '<?php echo htmlspecialchars($sinh_vien_id); ?>';
        const hocKyEl = document.getElementById('hoc-ky-id');
        const tbody = document.querySelector('#bang-diem-table tbody');
        const metaBox = document.getElementById('meta-box');

        async function loadTranscript() {
            if (!sinhVienId) {
                return;
            }

            const hocKyId = hocKyEl.value;
            const url = `${PY_API}/api/gpa/transcript/${encodeURIComponent(sinhVienId)}?hoc_ky_id=${encodeURIComponent(hocKyId)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.message || data.error || 'Khong the tai bang diem');
            }

            const rows = data.data || [];
            tbody.innerHTML = '';

            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-3 py-3">Khong co du lieu.</td></tr>';
                metaBox.textContent = 'Khong co ket qua trong hoc ky da chon.';
                return;
            }

            rows.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-100';
                tr.innerHTML = `
                    <td class="px-3 py-2">${item.ma_hoc_ky || ''}</td>
                    <td class="px-3 py-2">${item.ma_mon || ''}</td>
                    <td class="px-3 py-2">${item.ten_mon || ''}</td>
                    <td class="px-3 py-2">${item.diem_cc ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_gk ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_ck ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_tong ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_chu ?? ''}</td>
                    <td class="px-3 py-2">${item.diem_he_4 ?? ''}</td>
                `;
                tbody.appendChild(tr);
            });

            metaBox.textContent = `${rows[0].msv || ''} - ${rows[0].ten_sv || ''}`;
        }

        document.getElementById('btn-load').addEventListener('click', () => {
            loadTranscript().catch(err => {
                metaBox.textContent = err.message;
            });
        });

        document.getElementById('btn-export-pdf').addEventListener('click', async () => {
            if (!sinhVienId) {
                metaBox.textContent = 'Khong tim thay sinh_vien_id de xuat PDF.';
                return;
            }

            try {
                const res = await fetch(`${PY_API}/api/bao-cao/export/bang-diem-pdf`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sinh_vien_id: sinhVienId })
                });

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
                metaBox.textContent = err.message;
            }
        });

        if (sinhVienId && hocKyEl.value) {
            loadTranscript().catch(err => {
                metaBox.textContent = err.message;
            });
        }
    </script>
</body>

</html>
