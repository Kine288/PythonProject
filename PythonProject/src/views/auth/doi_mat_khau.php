<?php
session_start();

if (!isset($_SESSION['user_id'])) {
	header('Location: login.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Doi mat khau | EduAdmin</title>
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
					<h1 class="font-display-lg text-display-lg text-on-background mb-2">Doi mat khau</h1>
					<p class="text-body-md text-on-surface-variant max-w-2xl">Cap nhat mat khau ca nhan de bao ve tai khoan va thong tin he thong.</p>
				</div>
				<button class="flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
					<span class="material-symbols-outlined" data-icon="lock_reset">lock_reset</span>
					Cap nhat
				</button>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-3 gap-sm">
				<div class="xl:col-span-2 bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<h2 class="font-title-lg text-title-lg text-on-background mb-4">Thong tin doi mat khau</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
						<div class="md:col-span-2">
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Mat khau hien tai</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Nhap mat khau hien tai" type="password">
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Mat khau moi</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Mat khau moi" type="password">
						</div>
						<div>
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Xac nhan mat khau moi</label>
							<input class="w-full h-11 border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Nhap lai mat khau moi" type="password">
						</div>
						<div class="md:col-span-2">
							<label class="block text-label-md font-label-md text-secondary mb-1.5 ml-1">Ghi chu</label>
							<textarea class="w-full min-h-[90px] border-outline-variant rounded-lg focus:border-primary focus:ring-1 focus:ring-primary text-body-md" placeholder="Ghi chu ly do doi mat khau neu can"></textarea>
						</div>
					</div>
					<div class="flex flex-wrap gap-2 mt-5">
						<button class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-title-lg hover:shadow-lg transition-all active:scale-[0.98]" type="button">
							<span class="material-symbols-outlined" data-icon="check_circle">check_circle</span>
							Luu thay doi
						</button>
						<button class="flex items-center gap-2 border border-outline-variant text-secondary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-colors" type="button">
							<span class="material-symbols-outlined" data-icon="refresh">refresh</span>
							Nhap lai
						</button>
					</div>
				</div>
				<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
					<h2 class="font-title-lg text-title-lg text-on-background mb-4">Yeu cau bao mat</h2>
					<ul class="space-y-3 text-body-sm text-on-surface-variant">
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Mat khau toi thieu 8 ky tu, bao gom chu va so.
						</li>
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Khong su dung lai 3 mat khau gan nhat.
						</li>
						<li class="flex gap-2">
							<span class="material-symbols-outlined text-primary" data-icon="verified">verified</span>
							Kich hoat xac minh 2 buoc neu co.
						</li>
					</ul>
					<div class="mt-6 p-4 rounded-xl border border-outline-variant/30 bg-surface-container-lowest">
						<p class="text-sm font-semibold">Lan cap nhat gan nhat</p>
						<p class="text-xs text-slate-500 mt-1">02/05/2026 - 08:15</p>
					</div>
				</div>
			</div>
		</div>

		<?php include __DIR__ . '/../layouts/footer.php'; ?>
	</main>
</body>
</html>
