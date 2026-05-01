<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
	header('Location: ../auth/login.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quan ly sinh vien | EduAdmin</title>
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
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Quan ly Tai khoan</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Quan tri danh sach tai khoan he thong, vai tro va tinh trang kich hoat. Ho tro tao moi, khoa/mo khoa va reset mat khau.</p>
				</div>
				<button class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
					<span class="material-symbols-outlined" data-icon="person_add">person_add</span>
					<span>Them tai khoan moi</span>
				</button>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-12 gap-sm mb-lg bg-white p-sm rounded-xl shadow-sm border border-outline-variant/30">
				<div class="md:col-span-5 relative">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tim kiem tai khoan</label>
					<div class="relative">
						<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
						<input class="w-full h-11 pl-10 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Tim theo email, ma tai khoan..." type="text">
					</div>
				</div>
				<div class="md:col-span-3">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Vai tro</label>
					<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md">
						<option>Tat ca vai tro</option>
						<option>ADMIN</option>
						<option>GIAO_VU</option>
						<option>GIANG_VIEN</option>
						<option>SINH_VIEN</option>
					</select>
				</div>
				<div class="md:col-span-2">
					<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Trang thai</label>
					<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md">
						<option>Tat ca</option>
						<option>Dang hoat dong</option>
						<option>Bi khoa</option>
					</select>
				</div>
				<div class="md:col-span-2 flex items-end">
					<button class="w-full h-11 flex items-center justify-center gap-2 border border-primary text-primary rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" type="button">
						<span class="material-symbols-outlined" data-icon="filter_list">filter_list</span>
						Loc du lieu
					</button>
				</div>
			</div>

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
						<tr class="hover:bg-slate-50/50 transition-colors group">
							<td class="px-6 py-4 font-medium text-primary">tk_admin_01...</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600 font-medium">ADMIN</td>
							<td class="px-6 py-4 text-center">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Dang hoat dong</span>
							</td>
							<td class="px-6 py-4 text-right">
								<div class="flex justify-end gap-1">
									<button class="p-2 text-slate-400 hover:text-primary hover:bg-primary-container/10 rounded-lg transition-all" type="button" title="Xem chi tiet">
										<span class="material-symbols-outlined text-[20px]" data-icon="visibility">visibility</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button" title="Doi vai tro">
										<span class="material-symbols-outlined text-[20px]" data-icon="manage_accounts">manage_accounts</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button" title="Sua thong tin">
										<span class="material-symbols-outlined text-[20px]" data-icon="edit_square">edit_square</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-all" type="button" title="Khoa tai khoan">
										<span class="material-symbols-outlined text-[20px]" data-icon="lock">lock</span>
									</button>
								</div>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors group">
							<td class="px-6 py-4 font-medium text-primary">tk_giaovu_01...</td>
							<td class="px-6 py-4 text-slate-600">giaovu@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600 font-medium">GIAO_VU</td>
							<td class="px-6 py-4 text-center">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Dang hoat dong</span>
							</td>
							<td class="px-6 py-4 text-right">
								<div class="flex justify-end gap-1">
									<button class="p-2 text-slate-400 hover:text-primary hover:bg-primary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="visibility">visibility</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button" title="Doi vai tro">
										<span class="material-symbols-outlined text-[20px]" data-icon="manage_accounts">manage_accounts</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="edit_square">edit_square</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-all" type="button" title="Khoa tai khoan">
										<span class="material-symbols-outlined text-[20px]" data-icon="lock">lock</span>
									</button>
								</div>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors group">
							<td class="px-6 py-4 font-medium text-primary">tk_giangvien_01...</td>
							<td class="px-6 py-4 text-slate-600">giangvien@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600 font-medium">GIANG_VIEN</td>
							<td class="px-6 py-4 text-center">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Bi khoa</span>
							</td>
							<td class="px-6 py-4 text-right">
								<div class="flex justify-end gap-1">
									<button class="p-2 text-slate-400 hover:text-primary hover:bg-primary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="visibility">visibility</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button" title="Doi vai tro">
										<span class="material-symbols-outlined text-[20px]" data-icon="manage_accounts">manage_accounts</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="edit_square">edit_square</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-primary hover:bg-primary-container/10 rounded-lg transition-all" type="button" title="Mo khoa">
										<span class="material-symbols-outlined text-[20px]" data-icon="lock_open">lock_open</span>
									</button>
								</div>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors group">
							<td class="px-6 py-4 font-medium text-primary">tk_sinhvien_01...</td>
							<td class="px-6 py-4 text-slate-600">sinhvien@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600 font-medium">SINH_VIEN</td>
							<td class="px-6 py-4 text-center">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Dang hoat dong</span>
							</td>
							<td class="px-6 py-4 text-right">
								<div class="flex justify-end gap-1">
									<button class="p-2 text-slate-400 hover:text-primary hover:bg-primary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="visibility">visibility</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button" title="Doi vai tro">
										<span class="material-symbols-outlined text-[20px]" data-icon="manage_accounts">manage_accounts</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-tertiary hover:bg-tertiary-container/10 rounded-lg transition-all" type="button">
										<span class="material-symbols-outlined text-[20px]" data-icon="edit_square">edit_square</span>
									</button>
									<button class="p-2 text-slate-400 hover:text-error hover:bg-error-container/20 rounded-lg transition-all" type="button" title="Khoa tai khoan">
										<span class="material-symbols-outlined text-[20px]" data-icon="lock">lock</span>
									</button>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
					<p class="text-label-md text-secondary">Hien thi 1 - 10 tren tong so 1,240 sinh vien</p>
					<div class="flex items-center gap-2">
						<button class="p-1.5 border border-outline-variant rounded-md hover:bg-white text-secondary transition-colors disabled:opacity-40" type="button" disabled>
							<span class="material-symbols-outlined" data-icon="chevron_left">chevron_left</span>
						</button>
						<div class="flex items-center gap-1">
							<button class="w-8 h-8 flex items-center justify-center bg-primary text-on-primary rounded-md text-xs font-bold" type="button">1</button>
							<button class="w-8 h-8 flex items-center justify-center hover:bg-white border border-transparent hover:border-outline-variant rounded-md text-xs font-medium" type="button">2</button>
							<button class="w-8 h-8 flex items-center justify-center hover:bg-white border border-transparent hover:border-outline-variant rounded-md text-xs font-medium" type="button">3</button>
							<span class="px-1 text-slate-400 text-xs">...</span>
							<button class="w-8 h-8 flex items-center justify-center hover:bg-white border border-transparent hover:border-outline-variant rounded-md text-xs font-medium" type="button">124</button>
						</div>
						<button class="p-1.5 border border-outline-variant rounded-md hover:bg-white text-secondary transition-colors" type="button">
							<span class="material-symbols-outlined" data-icon="chevron_right">chevron_right</span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<?php include __DIR__ . '/../layouts/footer.php'; ?>
	</main>
</body>
</html>
