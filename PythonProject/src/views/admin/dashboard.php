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
	<title>Dashboard admin | EduAdmin</title>
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
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Dashboard Admin</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Thong ke he thong, tinh trang tai khoan va luong yeu cau quan tri trong ngay.</p>
				</div>
				<div class="flex flex-wrap gap-2">
					<button class="flex items-center gap-2 border border-primary text-primary px-4 py-2 rounded-lg font-semibold hover:bg-primary-container/10 transition-colors" type="button">
						<span class="material-symbols-outlined" data-icon="download">download</span>
						Xuat bao cao
					</button>
					<button class="flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
						<span class="material-symbols-outlined" data-icon="person_add">person_add</span>
						Tao tai khoan
					</button>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-sm mb-lg">
				<div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
					<p class="text-label-md text-secondary mb-2">Tong tai khoan</p>
					<p class="text-3xl font-bold text-on-background">4,280</p>
					<p class="text-xs text-slate-500 mt-1">Tang 6.2% so voi thang truoc</p>
				</div>
				<div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
					<p class="text-label-md text-secondary mb-2">Dang hoat dong</p>
					<p class="text-3xl font-bold text-on-background">4,120</p>
					<p class="text-xs text-teal-600 mt-1">Duy tri 96.2% hoat dong</p>
				</div>
				<div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
					<p class="text-label-md text-secondary mb-2">Bi khoa</p>
					<p class="text-3xl font-bold text-on-background">160</p>
					<p class="text-xs text-slate-500 mt-1">12 tai khoan khoa moi</p>
				</div>
				<div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
					<p class="text-label-md text-secondary mb-2">Yeu cau reset</p>
					<p class="text-3xl font-bold text-on-background">42</p>
					<p class="text-xs text-slate-500 mt-1">8 yeu cau cho xu ly</p>
				</div>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-3 gap-sm mb-lg">
				<div class="xl:col-span-2 bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<div class="flex items-center justify-between mb-4">
						<h2 class="font-title-lg text-title-lg text-on-background">Thong ke he thong</h2>
						<span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary-container/20 text-primary">Cap nhat 5 phut truoc</span>
					</div>
					<div class="h-64 rounded-xl border border-outline-variant/40 bg-gradient-to-br from-surface-container-lowest via-surface-container to-surface-container-low flex items-center justify-center">
						<div class="text-center">
							<span class="material-symbols-outlined text-5xl text-primary" data-icon="monitoring">monitoring</span>
							<p class="text-body-md text-on-surface-variant mt-2">Bieu do thong ke se hien thi tai day</p>
						</div>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
						<div class="rounded-lg border border-outline-variant/30 p-4">
							<p class="text-xs text-secondary">API Response</p>
							<p class="text-lg font-semibold text-on-background">158ms</p>
						</div>
						<div class="rounded-lg border border-outline-variant/30 p-4">
							<p class="text-xs text-secondary">DB Health</p>
							<p class="text-lg font-semibold text-on-background">99.8%</p>
						</div>
						<div class="rounded-lg border border-outline-variant/30 p-4">
							<p class="text-xs text-secondary">Storage</p>
							<p class="text-lg font-semibold text-on-background">68% su dung</p>
						</div>
					</div>
				</div>
				<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<div class="flex items-center justify-between mb-4">
						<h2 class="font-title-lg text-title-lg text-on-background">Canh bao & hien trang</h2>
						<button class="text-primary text-sm font-semibold" type="button">Xem chi tiet</button>
					</div>
					<div class="space-y-3">
						<div class="p-4 rounded-xl border border-outline-variant/30 bg-surface-container-lowest">
							<p class="text-sm font-semibold text-on-background">Dang co 8 yeu cau reset cho xu ly</p>
							<p class="text-xs text-slate-500 mt-1">Kiem tra danh sach de xu ly kip thoi.</p>
						</div>
						<div class="p-4 rounded-xl border border-outline-variant/30 bg-surface-container-lowest">
							<p class="text-sm font-semibold text-on-background">2 tai khoan bi khoa tam thoi</p>
							<p class="text-xs text-slate-500 mt-1">Xem ly do khoa va mo neu can.</p>
						</div>
						<div class="p-4 rounded-xl border border-outline-variant/30 bg-surface-container-lowest">
							<p class="text-sm font-semibold text-on-background">He thong hoat dong binh thuong</p>
							<p class="text-xs text-slate-500 mt-1">Khong co su co bat thuong trong 24h.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
				<div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
					<h2 class="font-title-lg text-title-lg text-on-background">Hoat dong gan day</h2>
					<button class="text-primary text-sm font-semibold" type="button">Xem tat ca</button>
				</div>
				<table class="w-full text-left border-collapse font-data-table text-data-table">
					<thead class="bg-slate-50/80">
						<tr>
							<th class="px-6 py-4 font-semibold text-slate-700">Thoi gian</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Tac nhan</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Su kien</th>
							<th class="px-6 py-4 font-semibold text-slate-700">Trang thai</th>
							<th class="px-6 py-4 font-semibold text-slate-700 text-right">Chi tiet</th>
						</tr>
					</thead>
					<tbody class="divide-y divide-slate-100">
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">09:42 - 05/05</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">Reset mat khau tai khoan GIANG_VIEN</td>
							<td class="px-6 py-4">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Thanh cong</span>
							</td>
							<td class="px-6 py-4 text-right">
								<button class="text-primary text-sm font-semibold" type="button">Xem</button>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">08:15 - 05/05</td>
							<td class="px-6 py-4 text-slate-600">he_thong</td>
							<td class="px-6 py-4 text-slate-600">Khoa tam thoi tai khoan SINH_VIEN</td>
							<td class="px-6 py-4">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Canh bao</span>
							</td>
							<td class="px-6 py-4 text-right">
								<button class="text-primary text-sm font-semibold" type="button">Xem</button>
							</td>
						</tr>
						<tr class="hover:bg-slate-50/50 transition-colors">
							<td class="px-6 py-4 text-slate-600">17:33 - 04/05</td>
							<td class="px-6 py-4 text-slate-600">admin@khoa.edu.vn</td>
							<td class="px-6 py-4 text-slate-600">Tao tai khoan moi GIAO_VU</td>
							<td class="px-6 py-4">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-800">Thanh cong</span>
							</td>
							<td class="px-6 py-4 text-right">
								<button class="text-primary text-sm font-semibold" type="button">Xem</button>
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
