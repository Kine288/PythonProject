<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
	header('Location: ../auth/login.php');
	exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../api/sinh_vien.php';

$pdo = getDatabaseConnection();
$error = '';
$notice = '';

$account_id = $_GET['id'] ?? '';
$is_edit = $account_id !== '';

$account = [
	'tai_khoan_id' => '',
	'email' => '',
	'vai_tro_id' => '',
	'is_active' => 1,
];

$roles = [];
if ($pdo) {
	$stmt = $pdo->query('SELECT vai_tro_id, ten_vai_tro FROM vai_tro ORDER BY ten_vai_tro');
	$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$lopRes = sinhVienProxyRequest('GET', '/catalog/lop');
$lops = $lopRes['data'] ?? [];
$lopError = '';
if (!empty($lopRes) && empty($lopRes['success'])) {
	$lopError = $lopRes['message'] ?? 'Khong the tai danh sach lop';
}

$student_profile = [
	'sinh_vien_id' => '',
	'msv' => '',
	'ten_sv' => '',
	'email' => '',
	'gioi_tinh' => '',
	'ngay_sinh' => '',
	'lop_id' => '',
];

$pref_role = $_GET['role'] ?? '';
if (!$is_edit && $pref_role !== '' && $pdo) {
	$stmt = $pdo->prepare('SELECT vai_tro_id FROM vai_tro WHERE ten_vai_tro = :role LIMIT 1');
	$stmt->execute(['role' => $pref_role]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($row) {
		$account['vai_tro_id'] = $row['vai_tro_id'];
	}
}

if ($is_edit && $pdo) {
	$stmt = $pdo->prepare('SELECT tai_khoan_id, email, vai_tro_id, is_active FROM tai_khoan WHERE tai_khoan_id = :id');
	$stmt->execute(['id' => $account_id]);
	$found = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($found) {
		$account = $found;
		$role_stmt = $pdo->prepare('SELECT ten_vai_tro FROM vai_tro WHERE vai_tro_id = :id');
		$role_stmt->execute(['id' => $account['vai_tro_id']]);
		$role_row = $role_stmt->fetch(PDO::FETCH_ASSOC);
		$role_name = $role_row['ten_vai_tro'] ?? '';
		if ($role_name === 'SINH_VIEN') {
			$student_res = sinhVienProxyRequest('GET', '/students/by-account/' . $account_id);
			if (!empty($student_res['success']) && !empty($student_res['data'])) {
				$student_profile = array_merge($student_profile, $student_res['data']);
				if (!empty($student_profile['ngay_sinh'])) {
					$student_profile['ngay_sinh'] = substr((string)$student_profile['ngay_sinh'], 0, 10);
				}
			}
		}
	} else {
		$error = 'Khong tim thay tai khoan.';
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
	$email = trim($_POST['email'] ?? '');
	$vai_tro_id = trim($_POST['vai_tro_id'] ?? '');
	$is_active = isset($_POST['is_active']) ? 1 : 0;
	$password = $_POST['mat_khau'] ?? '';

	if ($email === '' || $vai_tro_id === '') {
		$error = 'Vui long nhap day du email va vai tro.';
	} else {
		$role_stmt = $pdo->prepare('SELECT ten_vai_tro FROM vai_tro WHERE vai_tro_id = :id');
		$role_stmt->execute(['id' => $vai_tro_id]);
		$role_row = $role_stmt->fetch(PDO::FETCH_ASSOC);
		$role_name = $role_row['ten_vai_tro'] ?? '';

		if ($is_edit) {
			$stmt = $pdo->prepare('UPDATE tai_khoan SET email = :email, vai_tro_id = :role, is_active = :active WHERE tai_khoan_id = :id');
			$stmt->execute([
				'email' => $email,
				'role' => $vai_tro_id,
				'active' => $is_active,
				'id' => $account_id,
			]);

			if ($password !== '') {
				$hash = password_hash($password, PASSWORD_BCRYPT);
				$pwd_stmt = $pdo->prepare('UPDATE tai_khoan SET mat_khau = :pwd WHERE tai_khoan_id = :id');
				$pwd_stmt->execute(['pwd' => $hash, 'id' => $account_id]);
			}
			$notice = 'Cap nhat tai khoan thanh cong.';
		} else {
			$account_id = bin2hex(random_bytes(16));
			$hash = password_hash($password !== '' ? $password : '123456', PASSWORD_BCRYPT);
			$stmt = $pdo->prepare('INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau, vai_tro_id, is_active) VALUES (:id, :email, :pwd, :role, :active)');
			$stmt->execute([
				'id' => $account_id,
				'email' => $email,
				'pwd' => $hash,
				'role' => $vai_tro_id,
				'active' => $is_active,
			]);
			$notice = 'Tao tai khoan moi thanh cong.';
			$is_edit = true;
		}

		if ($role_name === 'SINH_VIEN') {
			$student_payload = [
				'msv' => trim($_POST['sv_msv'] ?? ''),
				'ten_sv' => trim($_POST['sv_ten_sv'] ?? ''),
				'email' => trim($_POST['sv_email'] ?? $email),
				'gioi_tinh' => $_POST['sv_gioi_tinh'] ?? null,
				'ngay_sinh' => $_POST['sv_ngay_sinh'] ?? null,
				'lop_id' => $_POST['sv_lop_id'] ?? '',
			];

			$student_profile = array_merge($student_profile, $student_payload);

			if ($student_payload['msv'] === '' || $student_payload['ten_sv'] === '' || $student_payload['lop_id'] === '') {
				$error = 'Vui long nhap day du thong tin sinh vien.';
			} else {
				$student_res = sinhVienProxyRequest('GET', '/students/by-account/' . $account_id);
				if (!empty($student_res['success']) && !empty($student_res['data']['sinh_vien_id'])) {
					$student_id = $student_res['data']['sinh_vien_id'];
					$save_res = sinhVienProxyRequest('PUT', '/students/' . $student_id, $student_payload);
				} else {
					$save_res = sinhVienProxyRequest('POST', '/students/by-account/' . $account_id, $student_payload);
				}

				if (!empty($save_res['success']) && !empty($save_res['data'])) {
					$student_profile = array_merge($student_profile, $save_res['data']);
					if (!empty($student_profile['ngay_sinh'])) {
						$student_profile['ngay_sinh'] = substr((string)$student_profile['ngay_sinh'], 0, 10);
					}
				} else {
					$error = $save_res['message'] ?? 'Khong the luu thong tin sinh vien.';
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
	<title><?php echo $is_edit ? 'Sua tai khoan' : 'Them tai khoan'; ?> | EduAdmin</title>
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
			<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-lg">
				<div>
					<h1 class="font-display-lg text-display-lg text-on-background mb-2"><?php echo $is_edit ? 'Sua tai khoan' : 'Them tai khoan'; ?></h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Quan tri thong tin tai khoan, vai tro va trang thai kich hoat.</p>
				</div>
				<a class="flex items-center gap-2 border border-primary text-primary px-5 py-2.5 rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" href="quan_ly_tai_khoan.php">
					<span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
					Quay lai danh sach
				</a>
			</div>
			<?php if ($is_edit && $pdo): ?>
				<?php
					$role_stmt = $pdo->prepare('SELECT ten_vai_tro FROM vai_tro WHERE vai_tro_id = :id');
					$role_stmt->execute(['id' => $account['vai_tro_id']]);
					$role_row = $role_stmt->fetch(PDO::FETCH_ASSOC);
					$role_name = $role_row['ten_vai_tro'] ?? '';
					$student_link = '';
					if ($role_name === 'SINH_VIEN') {
						$stmt = $pdo->prepare('SELECT sinh_vien_id FROM sinh_vien WHERE tai_khoan_id = :id LIMIT 1');
						$stmt->execute(['id' => $account_id]);
						$student_row = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($student_row && !empty($student_row['sinh_vien_id'])) {
							$student_link = 'sinh_vien/form.php?id=' . urlencode($student_row['sinh_vien_id']);
						} else {
							$student_link = 'sinh_vien/form.php?tai_khoan_id=' . urlencode($account_id);
						}
					}
				?>
				<?php if ($student_link !== ''): ?>
				<div class="mb-lg rounded-xl border border-outline-variant/40 bg-white p-4 flex items-center justify-between">
					<div>
						<p class="text-sm text-slate-500">Ho so sinh vien</p>
						<p class="text-title-lg font-semibold">Lien ket tai khoan sinh vien</p>
					</div>
					<a class="flex items-center gap-2 border border-primary text-primary px-4 py-2 rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" href="<?php echo $student_link; ?>">
						<span class="material-symbols-outlined" data-icon="open_in_new">open_in_new</span>
						Mo ho so
					</a>
				</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($notice): ?>
			<div class="mb-lg rounded-xl border border-primary-container/30 bg-primary-container/10 p-4 text-on-surface-variant">
				<?php echo htmlspecialchars($notice); ?>
			</div>
			<?php endif; ?>
			<?php if ($error): ?>
			<div class="mb-lg rounded-xl border border-error-container bg-error-container/40 p-4 text-on-error-container">
				<?php echo htmlspecialchars($error); ?>
			</div>
			<?php endif; ?>

			<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
				<form method="post">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Email</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="email" required value="<?php echo htmlspecialchars($account['email']); ?>">
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Vai tro</label>
							<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="vai_tro_id" id="role-select" required>
								<option value="">-- Chon vai tro --</option>
								<?php foreach ($roles as $role): ?>
									<option value="<?php echo htmlspecialchars($role['vai_tro_id']); ?>" data-role="<?php echo htmlspecialchars($role['ten_vai_tro']); ?>" <?php echo $account['vai_tro_id'] === $role['vai_tro_id'] ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($role['ten_vai_tro']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1"><?php echo $is_edit ? 'Mat khau moi (neu doi)' : 'Mat khau khoi tao'; ?></label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="mat_khau" type="text" value="<?php echo $is_edit ? '' : '123456'; ?>">
						</div>
						<div class="flex items-center gap-2 mt-6">
							<input type="checkbox" name="is_active" <?php echo (int)$account['is_active'] === 1 ? 'checked' : ''; ?> class="h-4 w-4">
							<span class="text-sm text-slate-600">Kich hoat tai khoan</span>
						</div>
					</div>

					<div id="student-fields" class="mt-6 border border-outline-variant/40 rounded-xl p-4 bg-slate-50/60">
						<p class="text-sm text-slate-500 mb-3">Thong tin sinh vien</p>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ma sinh vien</label>
								<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="sv_msv" id="sv_msv" value="<?php echo htmlspecialchars((string)$student_profile['msv']); ?>">
							</div>
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ho ten</label>
								<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="sv_ten_sv" id="sv_ten_sv" value="<?php echo htmlspecialchars((string)$student_profile['ten_sv']); ?>">
							</div>
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Email</label>
								<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" type="email" name="sv_email" id="sv_email" value="<?php echo htmlspecialchars((string)($student_profile['email'] ?: $account['email'])); ?>">
							</div>
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ngay sinh</label>
								<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" type="date" name="sv_ngay_sinh" value="<?php echo htmlspecialchars((string)$student_profile['ngay_sinh']); ?>">
							</div>
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Gioi tinh</label>
								<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="sv_gioi_tinh">
									<option value="">Khong xac dinh</option>
									<option value="1" <?php echo (string)$student_profile['gioi_tinh'] === '1' ? 'selected' : ''; ?>>Nam</option>
									<option value="0" <?php echo (string)$student_profile['gioi_tinh'] === '0' ? 'selected' : ''; ?>>Nu</option>
								</select>
							</div>
							<div>
								<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Lop</label>
								<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="sv_lop_id" id="sv_lop_id">
									<option value="">-- Chon lop --</option>
									<?php foreach ($lops as $lop): ?>
										<option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $student_profile['lop_id'] === $lop['lop_id'] ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($lop['ten_lop']); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<?php if ($lopError !== ''): ?>
									<p class="text-xs text-error mt-1"><?php echo htmlspecialchars($lopError); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="flex flex-wrap gap-2 mt-6">
						<button class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="submit">
							<span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
							<?php echo $is_edit ? 'Luu thay doi' : 'Tao tai khoan'; ?>
						</button>
						<a class="flex items-center gap-2 border border-outline-variant text-secondary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-colors" href="quan_ly_tai_khoan.php">
							<span class="material-symbols-outlined" data-icon="close">close</span>
							Huy
						</a>
					</div>
				</form>
			</div>
		</div>

		<?php include __DIR__ . '/../layouts/footer.php'; ?>
	</main>
		<script>
			const roleSelect = document.getElementById('role-select');
			const studentFields = document.getElementById('student-fields');
			const requiredInputs = ['sv_msv', 'sv_ten_sv', 'sv_lop_id'];

			function setStudentRequired(isRequired) {
				requiredInputs.forEach((id) => {
					const el = document.getElementById(id);
					if (el) {
						if (isRequired) {
							el.setAttribute('required', 'required');
						} else {
							el.removeAttribute('required');
						}
					}
				});
			}

			function toggleStudentFields() {
				const selected = roleSelect?.selectedOptions?.[0];
				const roleName = selected?.dataset?.role;
				const show = roleName === 'SINH_VIEN';
				if (studentFields) {
					studentFields.style.display = show ? 'block' : 'none';
				}
				setStudentRequired(show);
			}

			if (roleSelect) {
				roleSelect.addEventListener('change', toggleStudentFields);
			}
			toggleStudentFields();
		</script>
</body>
</html>
