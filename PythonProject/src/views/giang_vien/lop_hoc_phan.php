<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}

$giang_vien_id = $_SESSION['user_id'];
require_once __DIR__ . '/../../../config/database.php';

$pdo = getDatabaseConnection();
$ds_lhp = [];
$page_error = '';
$page_info = '';

if (!$pdo) {
  $page_error = 'Khong ket noi duoc co so du lieu.';
} else {
  $stmt = $pdo->prepare("SELECT giang_vien_id FROM giang_vien WHERE giang_vien_id = ? OR tai_khoan_id = ?");
  $stmt->execute([$giang_vien_id, $giang_vien_id]);
  $giang_vien = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$giang_vien) {
    $page_error = 'Giang vien khong ton tai trong he thong.';
  } else {
    $giang_vien_id = $giang_vien['giang_vien_id'];
    $stmt = $pdo->prepare("
      SELECT lhp.lhp_id, lhp.ma_lhp, lhp.trang_thai_giao_vu, lhp.trang_thai_giang_vien,
           mh.ten_mon, hk.ten_hoc_ky,
           COUNT(dslhp.ds_lhp_id) AS so_sv
      FROM lop_hoc_phan lhp
      LEFT JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
      LEFT JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
      LEFT JOIN ds_lhp dslhp ON dslhp.lhp_id = lhp.lhp_id
      WHERE lhp.giang_vien_id = ?
      GROUP BY lhp.lhp_id, lhp.ma_lhp, lhp.trang_thai_giao_vu, lhp.trang_thai_giang_vien, mh.ten_mon, hk.ten_hoc_ky
      ORDER BY lhp.ma_lhp
    ");
    $stmt->execute([$giang_vien_id]);
    $ds_lhp = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($ds_lhp) === 0) {
      $page_info = 'Chua co lop hoc phan nao duoc phan cong.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lop hoc phan | EduAdmin</title>
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
      <div class="flex justify-between items-end mb-lg">
        <div>
          <h1 class="font-display-lg text-display-lg text-on-background mb-2">Lop hoc phan</h1>
          <p class="text-body-md text-on-surface-variant max-w-2xl">Tong hop cac lop hoc phan duoc phan cong. Chon lop de nhap diem.</p>
        </div>
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

      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left border-collapse font-data-table text-data-table">
          <thead class="bg-slate-50/80 sticky top-0 border-b border-slate-100">
            <tr>
              <th class="px-6 py-4 font-semibold text-slate-700">Ma LHP</th>
              <th class="px-6 py-4 font-semibold text-slate-700">Mon hoc</th>
              <th class="px-6 py-4 font-semibold text-slate-700">Hoc ky</th>
              <th class="px-6 py-4 font-semibold text-slate-700">Trang thai</th>
              <th class="px-6 py-4 font-semibold text-slate-700 text-center">So SV</th>
              <th class="px-6 py-4 font-semibold text-slate-700 text-right">Thao tac</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (count($ds_lhp) === 0): ?>
            <tr>
              <td class="px-6 py-6 text-slate-500" colspan="6">Khong co du lieu de hien thi.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($ds_lhp as $lhp): ?>
            <tr class="hover:bg-slate-50/50 transition-colors group">
              <td class="px-6 py-4 font-medium text-primary"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></td>
              <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($lhp['ten_mon']); ?></td>
              <td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($lhp['ten_hoc_ky']); ?></td>
              <td class="px-6 py-4 text-slate-600">
                GV: <?php echo htmlspecialchars($lhp['trang_thai_giang_vien']); ?> | GVU: <?php echo htmlspecialchars($lhp['trang_thai_giao_vu']); ?>
              </td>
              <td class="px-6 py-4 text-center text-slate-600"><?php echo htmlspecialchars($lhp['so_sv']); ?></td>
              <td class="px-6 py-4 text-right">
                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-body-md font-semibold text-primary border border-primary hover:bg-primary-container/10 transition-colors" href="nhap_diem.php?lhp_id=<?php echo htmlspecialchars($lhp['lhp_id']); ?>">
                  <span class="material-symbols-outlined text-[18px]" data-icon="edit_square">edit_square</span>
                  Nhap diem
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
  </main>
</body>
</html>
