<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
	header('Location: ../auth/login.php');
	exit;
}

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../api/sinh_vien.php';

$sinhVienId = $_GET['id'] ?? '';
$isEdit = $sinhVienId !== '';
$taiKhoanId = $_GET['tai_khoan_id'] ?? '';
$error = '';
$toast = null;

$lopRes = sinhVienProxyRequest('GET', '/catalog/lop');
$lops = $lopRes['data'] ?? [];

$student = [
	'sinh_vien_id' => '',
	'msv' => '',
	'ten_sv' => '',
	'email' => '',
	'gioi_tinh' => '',
	'ngay_sinh' => '',
	'lop_id' => '',
];

if (!$isEdit && $taiKhoanId === '') {
	header('Location: ../tai_khoan_form.php?role=SINH_VIEN');
	exit;
}

if (!$isEdit && $taiKhoanId !== '') {
	$pdo = getDatabaseConnection();
	if ($pdo) {
		$stmt = $pdo->prepare('SELECT email FROM tai_khoan WHERE tai_khoan_id = :id LIMIT 1');
		$stmt->execute(['id' => $taiKhoanId]);
		$account_row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($account_row) {
			$student['email'] = $account_row['email'] ?? '';
		} else {
			$error = 'Khong tim thay tai khoan sinh vien';
		}
	}

	if ($error === '') {
		$existRes = sinhVienProxyRequest('GET', '/students/by-account/' . $taiKhoanId);
		if (!empty($existRes['success']) && !empty($existRes['data']['sinh_vien_id'])) {
			header('Location: form.php?id=' . urlencode($existRes['data']['sinh_vien_id']));
			exit;
		}
	}
}

if ($isEdit) {
	$res = sinhVienProxyRequest('GET', '/students/' . $sinhVienId);
	if (!empty($res['success'])) {
		$student = array_merge($student, $res['data'] ?? []);
		if (!empty($student['ngay_sinh'])) {
			$student['ngay_sinh'] = substr((string)$student['ngay_sinh'], 0, 10);
		}
	} else {
		$error = $res['message'] ?? 'Khong tim thay sinh vien';
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!$isEdit && $taiKhoanId === '') {
		$error = 'Vui long tao tai khoan sinh vien truoc.';
	} else {
	$payload = [
		'msv' => trim($_POST['msv'] ?? ''),
		'ten_sv' => trim($_POST['ten_sv'] ?? ''),
		'email' => trim($_POST['email'] ?? ''),
		'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
		'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
		'lop_id' => $_POST['lop_id'] ?? '',
	];

	if ($isEdit) {
		$res = sinhVienProxyRequest('PUT', '/students/' . $sinhVienId, $payload);
	} else {
		$res = sinhVienProxyRequest('POST', '/students/by-account/' . $taiKhoanId, $payload);
	}

	if (!empty($res['success'])) {
		$toast = [
			'success' => true,
			'message' => $res['message'] ?? ($isEdit ? 'Cap nhat sinh vien thanh cong' : 'Tao sinh vien thanh cong'),
		];
		if (!empty($res['data'])) {
			$student = array_merge($student, $res['data']);
			if (!empty($student['ngay_sinh'])) {
				$student['ngay_sinh'] = substr((string)$student['ngay_sinh'], 0, 10);
			}
		}
	} else {
		$error = $res['message'] ?? 'Khong the luu sinh vien';
		$student = array_merge($student, $payload);
		$toast = [
			'success' => false,
			'message' => $error,
		];
	}
	}
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $isEdit ? 'Sua sinh vien' : 'Them sinh vien'; ?> | EduAdmin</title>
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
		.toast { position: fixed; top: 18px; right: 18px; background: #0f766e; color: #fff; padding: 12px 16px; border-radius: 10px; box-shadow: 0 10px 20px rgba(15, 118, 110, 0.2); display: none; align-items: center; gap: 8px; z-index: 50; }
		.toast.error { background: #b91c1c; }
	</style>
</head>
<body class="font-body-md text-on-background">
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<?php include __DIR__ . '/../../layouts/sidebar.php'; ?>
<main class="ml-64 min-h-screen">
	<?php include __DIR__ . '/../../layouts/header.php'; ?>

	<div class="p-margin">
		<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-lg">
			<div>
				<h1 class="font-display-lg text-display-lg text-on-background mb-2"><?php echo $isEdit ? 'Cap nhat sinh vien' : 'Them moi sinh vien'; ?></h1>
				<p class="text-body-md text-on-surface-variant max-w-2xl">Tao tai khoan sinh vien moi va khoi tao thong tin co ban.</p>
			</div>
			<a class="flex items-center gap-2 border border-primary text-primary px-5 py-2.5 rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" href="../quan_ly_tai_khoan.php">
				<span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
				Quay lai danh sach
			</a>
		</div>

		<?php if ($error): ?>
		<div class="mb-lg rounded-xl border border-error-container bg-error-container/40 p-4 text-on-error-container">
			<?php echo htmlspecialchars($error); ?>
		</div>
		<?php endif; ?>

		<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
			<form method="post">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ma sinh vien</label>
						<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="msv" required value="<?php echo htmlspecialchars((string)$student['msv']); ?>">
					</div>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ho ten</label>
						<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="ten_sv" required value="<?php echo htmlspecialchars((string)$student['ten_sv']); ?>">
					</div>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Email (dong bo voi tai khoan)</label>
						<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" type="email" name="email" required value="<?php echo htmlspecialchars((string)$student['email']); ?>">
					</div>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ngay sinh</label>
						<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" type="date" name="ngay_sinh" value="<?php echo htmlspecialchars((string)$student['ngay_sinh']); ?>">
					</div>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Gioi tinh</label>
						<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="gioi_tinh">
							<option value="">Khong xac dinh</option>
							<option value="1" <?php echo (string)$student['gioi_tinh'] === '1' ? 'selected' : ''; ?>>Nam</option>
							<option value="0" <?php echo (string)$student['gioi_tinh'] === '0' ? 'selected' : ''; ?>>Nu</option>
						</select>
					</div>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Lop</label>
						<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="lop_id" required>
							<option value="">-- Chon lop --</option>
							<?php foreach ($lops as $lop): ?>
								<option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $student['lop_id'] === $lop['lop_id'] ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($lop['ten_lop']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php if (!$isEdit): ?>
					<div>
						<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tai khoan lien ket</label>
						<input class="w-full h-11 border-outline-variant rounded-lg bg-slate-50 text-body-md" type="text" value="<?php echo htmlspecialchars($taiKhoanId); ?>" readonly>
					</div>
					<?php endif; ?>
				</div>

				<div class="flex flex-wrap gap-2 mt-6">
					<button class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="submit">
						<span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
						<?php echo $isEdit ? 'Luu thay doi' : 'Tao sinh vien'; ?>
					</button>
					<a class="flex items-center gap-2 border border-outline-variant text-secondary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-colors" href="../quan_ly_tai_khoan.php">
						<span class="material-symbols-outlined" data-icon="close">close</span>
						Huy
					</a>
				</div>
			</form>
		</div>
	</div>

	<?php include __DIR__ . '/../../layouts/footer.php'; ?>
</main>
<script>
const toastData = <?php echo json_encode($toast, JSON_UNESCAPED_UNICODE); ?>;
const toastEl = document.getElementById('toast');

if (toastData && toastEl) {
	toastEl.textContent = toastData.message || '';
	if (!toastData.success) {
		toastEl.classList.add('error');
	}
	toastEl.style.display = 'flex';
	setTimeout(() => {
		toastEl.style.display = 'none';
	}, 3500);
}
</script>
</body>
</html>
