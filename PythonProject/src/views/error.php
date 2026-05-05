<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Loi he thong | EduAdmin</title>
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
	<div class="min-h-screen flex items-center justify-center p-6">
		<div class="max-w-2xl w-full bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
			<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-error-container/60 text-on-error-container mb-4">
				<span class="material-symbols-outlined text-3xl" data-icon="error">error</span>
			</div>
			<h1 class="text-3xl font-bold text-on-background mb-2">Loi he thong</h1>
			<p class="text-body-md text-on-surface-variant">He thong dang gap su co. Vui long thu lai sau hoac lien he quan tri.</p>
			<div class="flex flex-wrap justify-center gap-3 mt-6">
				<a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all" href="/src/views/index.php">
					<span class="material-symbols-outlined" data-icon="home">home</span>
					Ve trang chu
				</a>
				<a class="flex items-center gap-2 border border-outline-variant text-secondary px-6 py-3 rounded-lg font-semibold hover:bg-slate-50 transition-colors" href="/src/views/auth/login.php">
					<span class="material-symbols-outlined" data-icon="support_agent">support_agent</span>
					Lien he ho tro
				</a>
			</div>
		</div>
	</div>
</body>
</html>
