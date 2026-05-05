<?php
session_start();
require_once __DIR__ . '/../../../../api/sinh_vien.php';

$role = $_SESSION['user_role'] ?? '';
$isAdmin = $role === 'ADMIN';

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['form_action'] ?? '';
	$payload = $_POST;

	if ($action === 'delete_student' && !$isAdmin) {
		$response = ['success' => false, 'message' => 'Chi Admin duoc xoa tai khoan sinh vien'];
	} elseif ($action === 'delete_student') {
		$response = sinhVienProxyRequest('DELETE', '/students/' . ($payload['sinh_vien_id'] ?? ''));
	} elseif ($action === 'transfer_class') {
		$response = sinhVienProxyRequest('PUT', '/chuyen-lop', [
			'sinh_vien_id' => $payload['sinh_vien_id'] ?? '',
			'lop_id_moi' => $payload['lop_id_moi'] ?? null,
		]);
	} else {
		$response = ['success' => false, 'message' => 'Thao tac khong hop le'];
	}

	if (!empty($response['success'])) {
		$notice = $response['message'] ?? 'Thuc hien thanh cong';
	} else {
		$error = $response['message'] ?? 'Co loi xay ra';
	}
}

$keyword = trim($_GET['keyword'] ?? '');
$lopFilter = trim($_GET['lop_id'] ?? '');

$studentsRes = sinhVienProxyRequest('GET', '', null, ['keyword' => $keyword, 'lop_id' => $lopFilter]);
$lopRes = sinhVienProxyRequest('GET', '/catalog/lop');

$students = $studentsRes['data'] ?? [];
$lops = $lopRes['data'] ?? [];

if (!empty($studentsRes) && empty($studentsRes['success'])) {
	$error = $studentsRes['message'] ?? 'Khong the tai danh sach sinh vien';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Danh sach sinh vien | EduAdmin</title>
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
	<?php include __DIR__ . '/../../layouts/sidebar.php'; ?>
	<main class="ml-64 min-h-screen">
		<?php include __DIR__ . '/../../layouts/header.php'; ?>

		<div class="p-margin">
			<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-lg">
				<div>
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Quan ly sinh vien</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Theo doi danh sach sinh vien, cap nhat lop hoc va thong tin tai khoan.</p>
				</div>
				<?php if ($role === 'ADMIN'): ?>
				<a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" href="form.php">
					<span class="material-symbols-outlined" data-icon="person_add">person_add</span>
					Them sinh vien
				</a>
				<?php endif; ?>
			</div>

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

			<form method="get" class="grid grid-cols-1 md:grid-cols-12 gap-sm mb-lg bg-white p-sm rounded-xl shadow-sm border border-outline-variant/30">
				<div class="md:col-span-5">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tim theo ten, msv, email</label>
					<div class="relative">
						<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
						<input class="w-full h-11 pl-10 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" id="client-search" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Loc nhanh tren bang..." type="text">
					</div>
				</div>
				<div class="md:col-span-5">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Loc theo lop</label>
					<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="lop_id">
						<option value="">Tat ca lop</option>
						<?php foreach ($lops as $lop): ?>
							<option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo $lopFilter === $lop['lop_id'] ? 'selected' : ''; ?>>
								<?php echo htmlspecialchars($lop['ten_lop']); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="md:col-span-2 flex items-end">
					<button class="w-full h-11 flex items-center justify-center gap-2 border border-primary text-primary rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" type="submit">
						<span class="material-symbols-outlined" data-icon="filter_list">filter_list</span>
						Loc du lieu
					</button>
				</div>
			</form>

			<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
				<table class="w-full text-left border-collapse font-data-table text-data-table">
					<thead class="bg-slate-50/80 sticky top-0 border-b border-slate-100">
						<tr>
							<th class="px-6 py-4 font-semibold text-slate-700">MSV</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Ho ten</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Lop</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Email</th>
							<th class="px-6 py-4 font-semibold text-slate-700 text-right">Thao tac</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<?php if (empty($students)): ?>
						<tr>
							<td class="px-6 py-6 text-slate-500" colspan="5">Chua co du lieu sinh vien</td>
						</tr>
						<?php else: ?>
						<?php foreach ($students as $sv): ?>
							<tr class="student-row hover:bg-slate-50/50 transition-colors">
								<td class="px-6 py-4 font-medium text-primary"><?php echo htmlspecialchars($sv['msv']); ?></td>
								<td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($sv['ten_sv']); ?></td>
								<td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($sv['ten_lop']); ?></td>
								<td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($sv['email']); ?></td>
								<td class="px-6 py-4 text-right">
									<div class="flex justify-end gap-2">
										<a class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-body-md font-semibold text-primary border border-primary hover:bg-primary-container/10 transition-colors" href="form.php?id=<?php echo urlencode($sv['sinh_vien_id']); ?>">
											<span class="material-symbols-outlined text-[18px]" data-icon="edit_square">edit_square</span>
											Sua
										</a>
										<form method="post" onsubmit="return confirm('Xac nhan xoa sinh vien?');">
											<input type="hidden" name="form_action" value="delete_student">
											<input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
											<button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-body-md font-semibold text-error border border-error hover:bg-error-container/20 transition-colors" type="submit">
												<span class="material-symbols-outlined text-[18px]" data-icon="delete">delete</span>
												Xoa
											</button>
										</form>
									</div>
									<form method="post" class="mt-3 flex flex-wrap items-center justify-end gap-2">
										<input type="hidden" name="form_action" value="transfer_class">
										<input type="hidden" name="sinh_vien_id" value="<?php echo htmlspecialchars($sv['sinh_vien_id']); ?>">
										<select class="h-10 w-56 border-outline-variant rounded-lg text-body-md" name="lop_id_moi">
											<?php foreach ($lops as $lop): ?>
												<option value="<?php echo htmlspecialchars($lop['lop_id']); ?>"><?php echo htmlspecialchars($lop['ten_lop']); ?></option>
											<?php endforeach; ?>
										</select>
										<button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-body-md font-semibold text-primary border border-primary hover:bg-primary-container/10 transition-colors" type="submit">
											<span class="material-symbols-outlined text-[18px]" data-icon="swap_horiz">swap_horiz</span>
											Chuyen lop
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

		<?php include __DIR__ . '/../../layouts/footer.php'; ?>
	</main>
	<script>
	const searchInput = document.getElementById('client-search');
	const rows = document.querySelectorAll('.student-row');

	function filterRows() {
		const keyword = (searchInput?.value || '').toLowerCase();
		rows.forEach((row) => {
			const cells = row.querySelectorAll('td');
			const text = Array.from(cells).slice(0, 4).map(cell => cell.textContent.toLowerCase()).join(' ');
			row.style.display = text.includes(keyword) ? '' : 'none';
		});
	}

	if (searchInput) {
		searchInput.addEventListener('input', filterRows);
	}
	</script>
</body>

</html>