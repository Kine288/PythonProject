<?php
$role = $_SESSION['user_role'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$views_base = preg_replace('#/src/views/.*$#', '/src/views', $script_name);
if (!$views_base) {
	$views_base = '/src/views';
}

$menus_by_role = [
	'ADMIN' => [
		['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => $views_base . '/admin/dashboard.php'],
		['label' => 'Quan ly tai khoan', 'icon' => 'manage_accounts', 'href' => $views_base . '/admin/quan_ly_tai_khoan.php'],
		['label' => 'Reset mat khau', 'icon' => 'lock_reset', 'href' => $views_base . '/admin/reset_mat_khau.php'],
	],
	'GIAO_VU' => [
		['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => $views_base . '/giao_vu/dashboard.php'],
		['label' => 'Sinh vien', 'icon' => 'group', 'href' => $views_base . '/giao_vu/sinh_vien/danh_sach.php'],
		['label' => 'Giang vien', 'icon' => 'school', 'href' => '#'],
		['label' => 'Danh muc', 'icon' => 'inventory_2', 'href' => '#'],
		['label' => 'Lop hoc phan', 'icon' => 'class', 'href' => '#'],
		['label' => 'Phan cong', 'icon' => 'assignment_ind', 'href' => '#'],
		['label' => 'Duyet diem', 'icon' => 'fact_check', 'href' => $views_base . '/giao_vu/duyet_diem.php'],
		['label' => 'Bao cao', 'icon' => 'analytics', 'href' => '#'],
	],
	'GIANG_VIEN' => [
		['label' => 'Lop hoc phan', 'icon' => 'class', 'href' => $views_base . '/giang_vien/lop_hoc_phan.php'],
		['label' => 'Nhap diem', 'icon' => 'edit_square', 'href' => $views_base . '/giang_vien/nhap_diem.php'],
		['label' => 'Yeu cau sua diem', 'icon' => 'history_edu', 'href' => '#'],
		['label' => 'Ho so ca nhan', 'icon' => 'person', 'href' => '#'],
	],
	'SINH_VIEN' => [
		['label' => 'Bang diem ca nhan', 'icon' => 'grading', 'href' => '#'],
		['label' => 'Xep loai hoc luc', 'icon' => 'emoji_events', 'href' => '#'],
		['label' => 'Xuat phieu diem', 'icon' => 'description', 'href' => '#'],
		['label' => 'Ho so ca nhan', 'icon' => 'person', 'href' => '#'],
	],
];

$menu_items = $menus_by_role[$role] ?? [];
$logout_href = $views_base . '/auth/logout.php';
?>

<aside class="h-screen w-64 border-r fixed left-0 top-0 bg-white border-slate-100 shadow-[4px_0_24px_rgba(0,184,148,0.04)] z-50 flex flex-col h-full p-4 space-y-2">
	<div class="mb-8 px-2 flex items-center gap-3">
		<div class="w-10 h-10 bg-primary-container rounded-lg flex items-center justify-center">
			<span class="material-symbols-outlined text-white" data-icon="school">school</span>
		</div>
		<div>
			<h2 class="text-lg font-bold text-slate-900 font-display-md leading-tight">Hoc vien Giao duc</h2>
			<p class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold">Quan ly Nghiep vu</p>
		</div>
	</div>

	<nav class="flex-1 space-y-1">
		<?php foreach ($menu_items as $item): ?>
			<?php
			$is_active = $item['href'] !== '#' && $item['href'] === $script_name;
			$link_classes = $is_active
				? "flex items-center gap-3 px-3 py-2 bg-teal-50 text-teal-600 font-semibold rounded-lg scale-100 active:scale-95 origin-left font-['Manrope'] text-sm"
				: "flex items-center gap-3 px-3 py-2 text-slate-600 hover:text-teal-500 hover:bg-slate-50 rounded-lg transition-all duration-200 scale-100 active:scale-95 origin-left font-['Manrope'] text-sm";
			?>
			<a class="<?php echo $link_classes; ?>" href="<?php echo htmlspecialchars($item['href']); ?>">
				<span class="material-symbols-outlined" data-icon="<?php echo htmlspecialchars($item['icon']); ?>"><?php echo htmlspecialchars($item['icon']); ?></span>
				<span><?php echo htmlspecialchars($item['label']); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="pt-4 border-t border-slate-100 space-y-1">
		<a class="w-full text-left flex items-center gap-3 px-3 py-2 text-error hover:bg-error-container/20 rounded-lg transition-all font-['Manrope'] text-sm" href="<?php echo htmlspecialchars($logout_href); ?>">
			<span class="material-symbols-outlined" data-icon="logout">logout</span>
			<span>Dang xuat</span>
		</a>
	</div>
</aside>