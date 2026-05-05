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
	<title>Reset mat khau | EduAdmin</title>
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
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Reset mat khau</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Quan tri reset mat khau cho tai khoan he thong, theo doi log va chinh sach bao mat.</p>
				</div>
				<button class="flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
					<span class="material-symbols-outlined" data-icon="lock_reset">lock_reset</span>
					Tao mat khau moi
				</button>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-3 gap-sm mb-lg">
				<div class="xl:col-span-2 bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<h2 class="font-title-lg text-title-lg text-on-background mb-4">Thong tin tai khoan can reset</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Tim kiem tai khoan</label>
							<div class="relative">
								<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
								<input class="w-full h-11 pl-10 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Nhap email, ma tai khoan..." type="text">
							</div>
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Vai tro</label>
							<select class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md">
								<option>Chon vai tro</option>
								<option>ADMIN</option>
								<option>GIAO_VU</option>
								<option>GIANG_VIEN</option>
								<option>SINH_VIEN</option>
							</select>
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Mat khau moi</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Nhap mat khau moi" type="password">
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Xac nhan mat khau</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Nhap lai mat khau" type="password">
						</div>
						<div class="md:col-span-2">
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ly do reset</label>
							<textarea class="w-full min-h-[96px] border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Ghi chu ly do reset mat khau"></textarea>
						</div>
					</div>
					<div class="flex flex-wrap gap-2 mt-5">
						<button class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
							<span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
							Xac nhan reset
						</button>
						<button class="flex items-center gap-2 border border-outline-variant text-secondary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-colors" type="button">
							<span class="material-symbols-outlined" data-icon="refresh">refresh</span>
							Nhap lai
						</button>
					</div>
				</div>
				<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<h2 class="font-title-lg text-title-lg text-on-background mb-4">Chinh sach bao mat</h2>
					<ul class="space-y-3 text-body-sm text-on-surface-variant">
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Mat khau toi thieu 8 ky tu, bao gom chu hoa va so.
						</li>
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Khong su dung lai 3 mat khau gan nhat.
						</li>
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Gui thong bao qua email sau khi reset.
						</li>
					</ul>
					<div class="mt-6 p-4 rounded-xl border border-error-container bg-error-container/20 text-on-error-container">
						<p class="text-sm font-semibold">Luu y</p>
						<p class="text-xs mt-1">Chi reset khi co yeu cau chinh thuc. Tat ca thao tac duoc ghi log.</p>
					</div>
				</div>
			</div>

			<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
				<div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
					<h2 class="font-title-lg text-title-lg text-on-background">Lich su reset gan day</h2>
					<button class="text-primary text-sm font-semibold" type="button">Xem chi tiet</button>
				</div>
				<table class="w-full text-left border-collapse font-data-table text-data-table">
					<thead class="bg-slate-50/80">
						<tr>
							<th class="px-6 py-4 font-semibold text-slate-700">Thoi gian</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Tai khoan</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Nguoi thuc hien</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Ly do</th>
							<th class="px-6 py-4 font-semibold text-slate-700 text-right">Trang thai</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">09:42 - 05/05</td>
							<td class="px-6 py-4 text-slate-600">giangvien@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">Yeu cau tu giang vien</td>
							<td class="px-6 py-4 text-right">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Thanh cong</span>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">08:58 - 05/05</td>
							<td class="px-6 py-4 text-slate-600">sinhvien@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">Xac minh danh tinh sinh vien</td>
							<td class="px-6 py-4 text-right">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Thanh cong</span>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">16:21 - 04/05</td>
							<td class="px-6 py-4 text-slate-600">giaovu@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">Kich hoat lai tai khoan</td>
							<td class="px-6 py-4 text-right">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Can kiem tra</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<?php include __DIR__ . '/../layouts/footer.php'; ?>
	</main>
</body>
</html>
