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
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "outline-variant": "#bbcac3",
                        "on-secondary-fixed": "#161d1f",
                        "surface-container-low": "#eaf5fa",
                        "on-primary": "#ffffff",
                        "surface-container": "#e4f0f4",
                        "on-background": "#131d21",
                        "on-surface": "#131d21",
                        "primary-container": "#00b894",
                        "tertiary-fixed-dim": "#a2c9ff",
                        "primary-fixed": "#6dfad2",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-fixed-variant": "#004881",
                        "secondary-container": "#dae1e3",
                        "surface-variant": "#d9e4e9",
                        "primary": "#006b55",
                        "secondary-fixed-dim": "#c1c8ca",
                        "on-tertiary-fixed": "#001c38",
                        "error-container": "#ffdad6",
                        "primary-fixed-dim": "#4bddb7",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "surface": "#f1fbff",
                        "on-tertiary-container": "#003a6a",
                        "surface-tint": "#006b55",
                        "on-secondary-container": "#5d6466",
                        "tertiary": "#0060a9",
                        "surface-container-high": "#dfeaef",
                        "on-surface-variant": "#3c4a44",
                        "secondary": "#586062",
                        "inverse-surface": "#283236",
                        "surface-bright": "#f1fbff",
                        "surface-dim": "#d1dce0",
                        "on-primary-container": "#004233",
                        "surface-container-lowest": "#ffffff",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#002018",
                        "inverse-primary": "#4bddb7",
                        "tertiary-fixed": "#d3e4ff",
                        "secondary-fixed": "#dde4e6",
                        "on-secondary-fixed-variant": "#41484a",
                        "inverse-on-surface": "#e7f3f7",
                        "outline": "#6c7a74",
                        "tertiary-container": "#55a6ff",
                        "on-error": "#ffffff",
                        "on-primary-fixed-variant": "#005140",
                        "surface-container-highest": "#d9e4e9",
                        "background": "#f1fbff"
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    spacing: {
                        gutter: "20px",
                        base: "8px",
                        xs: "4px",
                        md: "24px",
                        sm: "12px",
                        lg: "40px",
                        margin: "32px"
                    },
                    fontFamily: {
                        "label-md": ["Inter"],
                        "display-lg": ["Manrope"],
                        "title-lg": ["Inter"],
                        "display-md": ["Manrope"],
                        "data-table": ["Inter"],
                        "body-sm": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    fontSize: {
                        "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}],
                        "display-lg": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                        "title-lg": ["18px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "display-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "data-table": ["14px", {"lineHeight": "20px", "fontWeight": "450"}],
                        "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body {
            background-color: #f1fbff;
        }
    </style>
</head>

<body class="font-body-md text-on-background">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <section class="p-margin space-y-lg">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="font-display-lg text-display-lg text-on-background mb-2">Bang diem ca nhan</h1>
                    <p class="text-body-md text-on-surface-variant">Tra cuu diem mon hoc, GPA va xep loai hoc luc.</p>
                </div>

                <button id="btn-export-pdf" type="button" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined" data-icon="picture_as_pdf">picture_as_pdf</span>
                    Xuat PDF bang diem
                </button>
            </div>

            <?php if ($error): ?>
                <div class="rounded-xl border border-error-container bg-error-container/40 p-4 text-on-error-container"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="grid gap-sm rounded-xl border border-outline-variant/30 bg-white p-sm md:grid-cols-3">
                <div>
                    <label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Hoc ky</label>
                    <select id="hoc-ky-id" class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md">
                        <?php foreach ($hoc_kys as $hk): ?>
                            <option value="<?php echo htmlspecialchars($hk['hoc_ky_id']); ?>" <?php echo $selected_hoc_ky_id === $hk['hoc_ky_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hk['ma_hoc_ky'] . ' - ' . $hk['ten_hoc_ky']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-load" type="button" class="w-full h-11 flex items-center justify-center gap-2 border border-primary text-primary rounded-lg font-semibold hover:bg-primary-container/10 transition-colors">
                        <span class="material-symbols-outlined" data-icon="sync">sync</span>
                        Tai bang diem
                    </button>
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="mb-3 text-body-sm text-on-surface-variant" id="meta-box">Chua co du lieu.</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse font-data-table text-data-table" id="bang-diem-table">
                        <thead class="bg-slate-50/80 sticky top-0 border-b border-slate-100">
                            <tr>
                                <th class="px-3 py-2 font-semibold text-slate-700">Hoc ky</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">Ma mon</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">Ten mon</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">CC</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">GK</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">CK</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">Diem tong</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">Diem chu</th>
                                <th class="px-3 py-2 font-semibold text-slate-700">He 4</th>
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
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        sinh_vien_id: sinhVienId
                    })
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