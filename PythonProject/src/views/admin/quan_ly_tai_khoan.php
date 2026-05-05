<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
	header('Location: ../auth/login.php');
	exit;
}

require_once __DIR__ . '/../../../config/database.php';

$keyword = trim($_GET['keyword'] ?? '');
$role_filter = trim($_GET['role'] ?? '');
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$page_size = 10;
$offset = ($page - 1) * $page_size;

$pdo = getDatabaseConnection();
$accounts = [];
$total_rows = 0;
$page_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
	$action = $_POST['form_action'] ?? '';
	if ($action === 'toggle_active') {
		$target_id = $_POST['tai_khoan_id'] ?? '';
		$target_status = $_POST['is_active'] ?? '';
		if ($target_id !== '' && ($target_status === '0' || $target_status === '1')) {
			$stmt = $pdo->prepare('UPDATE tai_khoan SET is_active = :status WHERE tai_khoan_id = :id');
			$stmt->execute(['status' => (int) $target_status, 'id' => $target_id]);
		}
	}
}

if (!$pdo) {
	$page_error = 'Khong ket noi duoc co so du lieu.';
} else {
	$where = [];
	$params = [];

	if ($keyword !== '') {
		$where[] = '(tk.tai_khoan_id LIKE :kw OR tk.email LIKE :kw)';
		$params['kw'] = '%' . $keyword . '%';
	}
	if ($role_filter !== '' && $role_filter !== 'ALL') {
		$where[] = 'vt.ten_vai_tro = :role';
		$params['role'] = $role_filter;
	}
	if ($status_filter !== '' && in_array($status_filter, ['1', '0'], true)) {
		$where[] = 'tk.is_active = :status';
		$params['status'] = (int) $status_filter;
	}

	$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

	$count_stmt = $pdo->prepare(
		"SELECT COUNT(*) FROM tai_khoan tk JOIN vai_tro vt ON vt.vai_tro_id = tk.vai_tro_id $where_sql"
	);
	$count_stmt->execute($params);
	$total_rows = (int) $count_stmt->fetchColumn();

	$data_stmt = $pdo->prepare(
		"SELECT tk.tai_khoan_id, tk.email, tk.is_active, vt.ten_vai_tro
		 FROM tai_khoan tk
		 JOIN vai_tro vt ON vt.vai_tro_id = tk.vai_tro_id
		 $where_sql
		 ORDER BY tk.created_at DESC
		 LIMIT :limit OFFSET :offset"
	);

	foreach ($params as $key => $value) {
		$param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
		$data_stmt->bindValue(':' . $key, $value, $param_type);
	}
	$data_stmt->bindValue(':limit', $page_size, PDO::PARAM_INT);
	$data_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$data_stmt->execute();
	$accounts = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total_pages = max(1, (int) ceil($total_rows / $page_size));
$page = min($page, $total_pages);
$start_row = $total_rows === 0 ? 0 : $offset + 1;
$end_row = min($offset + $page_size, $total_rows);

$query_base = [
	'keyword' => $keyword,
	'role' => $role_filter,
	'status' => $status_filter,
];
$prev_page = $page > 1 ? $page - 1 : null;
$next_page = $page < $total_pages ? $page + 1 : null;
$prev_href = $prev_page ? '?' . http_build_query($query_base + ['page' => $prev_page]) : '#';
$next_href = $next_page ? '?' . http_build_query($query_base + ['page' => $next_page]) : '#';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quan ly tai khoan | EduAdmin</title>
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
						"label-md": ["12px", {
							"lineHeight": "16px",
							"letterSpacing": "0.02em",
							"fontWeight": "500"
						}],
						"display-lg": ["32px", {
							"lineHeight": "40px",
							"fontWeight": "700"
						}],
						"title-lg": ["18px", {
							"lineHeight": "28px",
							"fontWeight": "600"
						}],
						"display-md": ["24px", {
							"lineHeight": "32px",
							"fontWeight": "600"
						}],
						"data-table": ["14px", {
							"lineHeight": "20px",
							"fontWeight": "450"
						}],
						"body-sm": ["13px", {
							"lineHeight": "18px",
							"fontWeight": "400"
						}],
						"body-md": ["14px", {
							"lineHeight": "20px",
							"fontWeight": "400"
						}]
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
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Quan ly Tai khoan</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Quan tri danh sach tai khoan he thong, vai tro va tinh trang kich hoat. Ho tro tao moi, khoa/mo khoa va reset mat khau.</p>
				</div>
				<a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" href="tai_khoan_form.php">
					<span class="material-symbols-outlined" data-icon="manage_accounts">manage_accounts</span>
					Them tai khoan moi
				</a>
			</div>

			<?php if ($page_error !== ''): ?>
			<div class="mb-lg rounded-xl border border-error-container bg-error-container/40 p-4 text-on-error-container">
				<?php echo htmlspecialchars($page_error); ?>
			</div>
			<?php endif; ?>

			<form class="grid grid-cols-1 md:grid-cols-12 gap-sm mb-lg bg-white p-sm rounded-xl shadow-sm border border-outline-variant/30" method="get">
				<div class="md:col-span-5 relative">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tim kiem tai khoan</label>
					<div class="relative">
						<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
						<input class="w-full h-11 pl-10 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Tim theo email, ma tai khoan..." type="text">
					</div>
				</div>
				<div class="md:col-span-3">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Vai tro</label>
					<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="role">
						<option value="ALL">Tat ca vai tro</option>
						<option value="ADMIN" <?php echo $role_filter === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
						<option value="GIAO_VU" <?php echo $role_filter === 'GIAO_VU' ? 'selected' : ''; ?>>GIAO_VU</option>
						<option value="GIANG_VIEN" <?php echo $role_filter === 'GIANG_VIEN' ? 'selected' : ''; ?>>GIANG_VIEN</option>
						<option value="SINH_VIEN" <?php echo $role_filter === 'SINH_VIEN' ? 'selected' : ''; ?>>SINH_VIEN</option>
					</select>
				</div>
				<div class="md:col-span-2">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Trang thai</label>
					<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" name="status">
						<option value="">Tat ca</option>
						<option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Dang hoat dong</option>
						<option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Bi khoa</option>
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
							<th class="px-6 py-4 font-semibold text-slate-700">Tai khoan ID</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Email</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Vai tro</th>
							<th class="px-6 py-4 font-semibold text-slate-700 text-center">Trang thai</th>
							<th class="px-6 py-4 font-semibold text-slate-700 text-right">Thao tac</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<?php if ($page_error !== ''): ?>
						<tr>
							<td class="px-6 py-6 text-error" colspan="5"><?php echo htmlspecialchars($page_error); ?></td>
						</tr>
						<?php elseif (count($accounts) === 0): ?>
						<tr>
							<td class="px-6 py-6 text-slate-500" colspan="5">Khong co du lieu de hien thi.</td>
						</tr>
						<?php else: ?>
						<?php foreach ($accounts as $account): ?>
							<?php
								$is_active = (int) $account['is_active'] === 1;
								$status_label = $is_active ? 'Dang hoat dong' : 'Bi khoa';
								$status_class = $is_active
									? 'bg-teal-100 text-teal-800'
									: 'bg-error-container text-on-error-container';
							?>
						<tr class="hover:bg-slate-50/50 transition-colors group">
							<td class="px-6 py-4 font-medium text-primary"><?php echo htmlspecialchars($account['tai_khoan_id']); ?></td>
							<td class="px-6 py-4 text-slate-600"><?php echo htmlspecialchars($account['email']); ?></td>
							<td class="px-6 py-4 text-slate-600 font-medium"><?php echo htmlspecialchars($account['ten_vai_tro']); ?></td>
							<td class="px-6 py-4 text-center">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
							</td>
							<td class="px-6 py-4 text-right">
								<div class="flex justify-end gap-1">
									<a class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" href="tai_khoan_form.php?id=<?php echo urlencode($account['tai_khoan_id']); ?>" title="Sua thong tin">
										<span class="material-symbols-outlined text-[20px]" data-icon="edit_square">edit_square</span>
									</a>
									<form method="post">
										<input type="hidden" name="form_action" value="toggle_active">
										<input type="hidden" name="tai_khoan_id" value="<?php echo htmlspecialchars($account['tai_khoan_id']); ?>">
										<input type="hidden" name="is_active" value="<?php echo $is_active ? '0' : '1'; ?>">
										<button class="p-2 text-slate-400 hover:text-<?php echo $is_active ? 'error' : 'primary'; ?> hover:bg-<?php echo $is_active ? 'error-container/20' : 'primary-container/10'; ?> rounded-lg transition-all" type="submit" title="<?php echo $is_active ? 'Khoa tai khoan' : 'Mo khoa'; ?>">
											<span class="material-symbols-outlined text-[20px]" data-icon="<?php echo $is_active ? 'lock' : 'lock_open'; ?>"><?php echo $is_active ? 'lock' : 'lock_open'; ?></span>
										</button>
									</form>
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
					<p class="text-label-md text-secondary">Hien thi <?php echo $start_row; ?> - <?php echo $end_row; ?> tren tong so <?php echo $total_rows; ?> tai khoan</p>
					<div class="flex items-center gap-2">
						<a class="p-1.5 border border-outline-variant rounded-md hover:bg-white text-secondary transition-colors <?php echo $prev_page ? '' : 'pointer-events-none opacity-40'; ?>" href="<?php echo htmlspecialchars($prev_href); ?>">
							<span class="material-symbols-outlined" data-icon="chevron_left">chevron_left</span>
						</a>
						<span class="text-xs text-slate-500">Trang <?php echo $page; ?> / <?php echo $total_pages; ?></span>
						<a class="p-1.5 border border-outline-variant rounded-md hover:bg-white text-secondary transition-colors <?php echo $next_page ? '' : 'pointer-events-none opacity-40'; ?>" href="<?php echo htmlspecialchars($next_href); ?>">
							<span class="material-symbols-outlined" data-icon="chevron_right">chevron_right</span>
						</a>
					</div>
				</div>
			</div>
		</div>

		<?php include __DIR__ . '/../layouts/footer.php'; ?>
	</main>
</body>

</html>