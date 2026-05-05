<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

$lhp_id = $_GET['lhp_id'] ?? '';
$tai_khoan_id = $_SESSION['user_id'] ?? '';
$giang_vien_id = '';
$pdo = getDatabaseConnection();
$ds_sinh_vien = [];
$page_error = '';
$page_info = '';
$is_editable = false;

if (!$pdo) {
  $page_error = 'Khong ket noi duoc co so du lieu.';
} elseif ($tai_khoan_id === '') {
  $page_error = 'Khong tim thay thong tin tai khoan trong session.';
} else {
  $stmt = $pdo->prepare("SELECT giang_vien_id FROM giang_vien WHERE giang_vien_id = ? OR tai_khoan_id = ?");
  $stmt->execute([$tai_khoan_id, $tai_khoan_id]);
  $giang_vien = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$giang_vien) {
    $page_error = 'Giang vien khong ton tai trong he thong.';
  } else {
    $giang_vien_id = $giang_vien['giang_vien_id'];

    if ($lhp_id === '') {
      $page_info = 'Vui long chon lop hoc phan de nhap diem.';
    } else {
      $stmt = $pdo->prepare("SELECT lhp_id FROM lop_hoc_phan WHERE lhp_id = ? AND giang_vien_id = ?");
      $stmt->execute([$lhp_id, $giang_vien_id]);
      $lhp = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$lhp) {
        $page_error = 'Lop hoc phan khong ton tai hoac khong thuoc giang vien hien tai.';
      } else {
        $stmt = $pdo->prepare("\
          SELECT sv.sinh_vien_id, sv.msv, sv.ten_sv,
               d.ds_lhp_id, d.diem_cc, d.diem_gk, d.diem_ck
          FROM ds_lhp d
          JOIN sinh_vien sv ON sv.sinh_vien_id = d.sinh_vien_id
          WHERE d.lhp_id = ?
          ORDER BY sv.ten_sv
        ");
        $stmt->execute([$lhp_id]);
        $ds_sinh_vien = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $is_editable = true;

        if (count($ds_sinh_vien) === 0) {
          $page_info = 'Chua co sinh vien nao trong lop hoc phan nay.';
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
  <title>Nhap diem | EduAdmin</title>
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

    <div class="p-margin">
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-3">
        <a class="hover:text-primary" href="lop_hoc_phan.php">Trang chu</a>
        <span>/</span>
        <a class="hover:text-primary" href="lop_hoc_phan.php">LHP</a>
        <span>/</span>
        <span class="text-slate-700 font-semibold">Nhap diem</span>
      </nav>
      <div class="flex justify-between items-end mb-lg">
        <div>
          <h1 class="font-display-lg text-display-lg text-on-background mb-2">Nhap diem LHP</h1>
          <p class="text-body-md text-on-surface-variant max-w-2xl">Cap nhat diem thanh phan CC, GK, CK cho sinh vien theo LHP.</p>
        </div>
        <button id="btn-gui-duyet" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98] disabled:opacity-50" data-lhp="<?php echo htmlspecialchars($lhp_id); ?>" type="button" <?php echo $is_editable ? '' : 'disabled'; ?>>
          <span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
          <span>Gui duyet LHP</span>
        </button>
      </div>

      <?php if ($page_error !== ''): ?>
      <div class="mb-lg rounded-xl border border-error-container bg-error-container/40 p-4 text-on-error-container">
        <?php echo htmlspecialchars($page_error); ?>
      </div>
      <?php elseif ($page_info !== ''): ?>
      <div class="mb-lg rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-4 text-on-surface-variant">
        <?php echo htmlspecialchars($page_info); ?>
      </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-sm mb-lg bg-white p-sm rounded-xl shadow-sm border border-outline-variant/30">
        <div class="md:col-span-4">
          <label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">LHP ID</label>
          <div class="h-11 px-4 flex items-center border border-outline-variant rounded-lg text-body-md text-on-background bg-surface-container-lowest">
            <?php echo htmlspecialchars($lhp_id); ?>
          </div>
        </div>
        <div class="md:col-span-4">
          <label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Giang vien</label>
          <div class="h-11 px-4 flex items-center border border-outline-variant rounded-lg text-body-md text-on-background bg-surface-container-lowest">
            <?php echo htmlspecialchars($giang_vien_id); ?>
          </div>
        </div>
        <div class="md:col-span-4 relative">
          <label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tim sinh vien</label>
          <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
            <input class="w-full h-11 pl-10 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Tim theo MSV hoac ten..." type="text" id="tim-sv">
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse font-data-table text-data-table" id="bang-diem">
          <thead class="bg-slate-50/80 sticky top-0 border-b border-slate-100">
            <tr>
              <th class="px-6 py-4 font-semibold text-slate-700">MSV</th>
              <th class="px-6 py-4 font-semibold text-slate-700">Ten sinh vien</th>
              <th class="px-6 py-4 font-semibold text-slate-700">CC</th>
              <th class="px-6 py-4 font-semibold text-slate-700">GK</th>
              <th class="px-6 py-4 font-semibold text-slate-700">CK</th>
              <th class="px-6 py-4 font-semibold text-slate-700 text-right">Thao tac</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (count($ds_sinh_vien) === 0): ?>
            <tr>
              <td class="px-6 py-6 text-slate-500" colspan="6">Khong co du lieu de hien thi.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($ds_sinh_vien as $sv): ?>
            <tr class="hover:bg-slate-50/50 transition-colors group" data-id="<?php echo htmlspecialchars($sv['ds_lhp_id']); ?>">
              <td class="px-6 py-4 font-medium text-primary"><?php echo htmlspecialchars($sv['msv']); ?></td>
              <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($sv['ten_sv']); ?></td>
              <td class="px-6 py-4">
                <input type="number" min="0" max="10" step="0.1" class="diem-cc w-24 h-9 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" value="<?php echo htmlspecialchars($sv['diem_cc']); ?>">
              </td>
              <td class="px-6 py-4">
                <input type="number" min="0" max="10" step="0.1" class="diem-gk w-24 h-9 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" value="<?php echo htmlspecialchars($sv['diem_gk']); ?>">
              </td>
              <td class="px-6 py-4">
                <input type="number" min="0" max="10" step="0.1" class="diem-ck w-24 h-9 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" value="<?php echo htmlspecialchars($sv['diem_ck']); ?>">
              </td>
              <td class="px-6 py-4 text-right">
                <button class="btn-luu inline-flex items-center gap-2 px-4 py-2 rounded-lg text-body-md font-semibold text-primary border border-primary hover:bg-primary-container/10 transition-colors disabled:opacity-50" data-lhp="<?php echo htmlspecialchars($lhp_id); ?>" type="button" <?php echo $is_editable ? '' : 'disabled'; ?>>
                  <span class="material-symbols-outlined text-[18px]" data-icon="save">save</span>
                  Luu
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
  </main>

<script>
document.querySelectorAll('.btn-luu').forEach(btn => {
  btn.addEventListener('click', function () {
    const row = this.closest('tr');
    const payload = {
      ds_lhp_id: row.dataset.id,
      lhp_id: this.dataset.lhp,
      nguoi_thay_doi_id: '<?php echo htmlspecialchars($tai_khoan_id); ?>',
      diem_cc: parseFloat(row.querySelector('.diem-cc').value || 0),
      diem_gk: parseFloat(row.querySelector('.diem-gk').value || 0),
      diem_ck: parseFloat(row.querySelector('.diem-ck').value || 0),
    };

    fetch('../../../api/diem.php?action=luu_nhap', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        alert('Da luu');
      } else {
        alert('Loi: ' + res.error);
      }
    });
  });
});

document.getElementById('btn-gui-duyet').addEventListener('click', function () {
  if (!confirm('Gui duyet toan bo LHP nay?')) return;
  fetch('../../../api/diem.php?action=gui_duyet', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ lhp_id: this.dataset.lhp })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      alert('Da gui duyet');
    } else {
      alert('Loi: ' + res.error);
    }
  });
});

const searchInput = document.getElementById('tim-sv');
if (searchInput) {
  searchInput.addEventListener('input', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('#bang-diem tbody tr').forEach(row => {
      const msv = row.children[0].textContent.toLowerCase();
      const ten = row.children[1].textContent.toLowerCase();
      row.style.display = (msv.includes(keyword) || ten.includes(keyword)) ? '' : 'none';
    });
  });
}
</script>
</body>
</html>

