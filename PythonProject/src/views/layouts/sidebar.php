<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['user_role'] ?? 'GUEST';

$menuByRole = [
    'ADMIN' => [
        ['label' => 'Quan ly tai khoan', 'icon' => 'manage_accounts', 'href' => '/PythonProject/PythonProject/src/views/admin/quan_ly_tai_khoan.php'],
        ['label' => 'Them sinh vien', 'icon' => 'person_add', 'href' => '/PythonProject/PythonProject/src/views/admin/them_sinh_vien.php'],
        ['label' => 'Hoc vu tong quan', 'icon' => 'school', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/sinh_vien/danh_sach.php'],
    ],
    'GIAO_VU' => [
        ['label' => 'Quan ly Sinh vien', 'icon' => 'group', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/sinh_vien/danh_sach.php'],
        ['label' => 'Lop hoc phan', 'icon' => 'class', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/duyet_diem.php'],
        ['label' => 'Tong ket hoc vu', 'icon' => 'analytics', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/tong_ket_hoc_vu.php'],
        ['label' => 'Tong hop LHP', 'icon' => 'table_chart', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/ket_qua_tong_hop.php'],
        ['label' => 'Bao cao canh bao', 'icon' => 'warning', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/bao_cao/canh_bao.php'],
    ],
    'GIANG_VIEN' => [
        ['label' => 'Lop hoc phan', 'icon' => 'class', 'href' => '/PythonProject/PythonProject/src/views/giang_vien/lop_hoc_phan.php'],
    ],
    'SINH_VIEN' => [
        ['label' => 'Bang diem ca nhan', 'icon' => 'menu_book', 'href' => '/PythonProject/PythonProject/src/views/sinh_vien/bang_diem.php'],
    ],
];

$menuItems = $menuByRole[$userRole] ?? [];
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function sidebarRoleLabel(string $role): string
{
    if ($role === 'ADMIN') {
        return 'Quan tri he thong';
    }
    if ($role === 'GIAO_VU') {
        return 'Nghiep vu giao vu';
    }
    if ($role === 'GIANG_VIEN') {
        return 'Cong tac giang day';
    }
    if ($role === 'SINH_VIEN') {
        return 'Thong tin hoc tap';
    }
    return 'Truy cap he thong';
}
?>
<aside class="h-screen w-64 border-r fixed left-0 top-0 bg-white border-slate-100 shadow-[4px_0_24px_rgba(0,184,148,0.04)] z-50 flex flex-col h-full p-4 space-y-2">
    <div class="mb-8 px-2 flex items-center gap-3">
        <div class="w-10 h-10 bg-primary-container rounded-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-white" data-icon="school">school</span>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900 font-display-md leading-tight">Hoc vien Giao duc</h2>
            <p class="text-[11px] text-slate-500 uppercase tracking-wider font-semibold"><?php echo htmlspecialchars(sidebarRoleLabel($userRole)); ?></p>
        </div>
    </div>

    <nav class="flex-1 space-y-1">
        <?php if (empty($menuItems)): ?>
            <div class="px-3 py-2 text-sm text-slate-400">Khong co menu phu hop vai tro hien tai.</div>
        <?php endif; ?>
        <?php foreach ($menuItems as $item): ?>
            <?php $isActive = strpos($currentPath, $item['href']) !== false; ?>
            <a class="flex items-center gap-3 px-3 py-2 <?php echo $isActive ? 'bg-teal-50 text-teal-600 font-semibold' : 'text-slate-600 hover:text-teal-500 hover:bg-slate-50'; ?> rounded-lg transition-all duration-200 scale-100 active:scale-95 origin-left font-['Manrope'] text-sm" href="<?php echo htmlspecialchars($item['href']); ?>">
                <span class="material-symbols-outlined" data-icon="<?php echo htmlspecialchars($item['icon']); ?>"><?php echo htmlspecialchars($item['icon']); ?></span>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="pt-4 border-t border-slate-100 space-y-1">
        <button class="w-full text-left flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 rounded-lg transition-all font-['Manrope'] text-sm" type="button">
            <span class="material-symbols-outlined" data-icon="menu_book">menu_book</span>
            <span>Huong dan</span>
        </button>
        <a class="w-full text-left flex items-center gap-3 px-3 py-2 text-error hover:bg-error-container/20 rounded-lg transition-all font-['Manrope'] text-sm" href="/PythonProject/PythonProject/src/views/auth/login.php">
            <span class="material-symbols-outlined" data-icon="logout">logout</span>
            <span>Dang xuat</span>
        </a>
    </div>
</aside>