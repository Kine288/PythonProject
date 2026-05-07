<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['user_role'] ?? 'GUEST';

$menuByRole = [
    'ADMIN' => [
        ['label' => 'Tong quan he thong', 'icon' => 'dashboard', 'href' => '/PythonProject/PythonProject/src/views/admin/dashboard.php'],
        ['label' => 'Quan ly tai khoan', 'icon' => 'manage_accounts', 'href' => '/PythonProject/PythonProject/src/views/admin/quan_ly_tai_khoan.php'],
        ['label' => 'Log he thong', 'icon' => 'receipt_long', 'href' => '/PythonProject/PythonProject/src/views/admin/log_he_thong.php'],
    ],
    'GIAO_VU' => [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/dashboard.php'],
        ['label' => 'Danh muc co so', 'icon' => 'folder_open', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/danh_muc.php'],
        ['label' => 'Sinh vien', 'icon' => 'group', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/sinh_vien/danh_sach.php'],
        ['label' => 'Lop hoc phan', 'icon' => 'class', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/duyet_diem.php'],
        ['label' => 'Duyet diem', 'icon' => 'fact_check', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/duyet_diem.php'],
        ['label' => 'Tong ket hoc vu', 'icon' => 'analytics', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/tong_ket_hoc_vu.php'],
        ['label' => 'Bao cao', 'icon' => 'summarize', 'href' => '/PythonProject/PythonProject/src/views/giao_vu/bao_cao/canh_bao.php'],
    ],
    'GIANG_VIEN' => [
        ['label' => 'Lop phu trach', 'icon' => 'class', 'href' => '/PythonProject/PythonProject/src/views/giang_vien/lop_hoc_phan.php'],
        ['label' => 'Nhap diem', 'icon' => 'edit_note', 'href' => '/PythonProject/PythonProject/src/views/giang_vien/nhap_diem.php'],
        ['label' => 'Yeu cau sua diem', 'icon' => 'request_quote', 'href' => '/PythonProject/PythonProject/src/views/giang_vien/yeu_cau_sua_diem.php'],
    ],
    'SINH_VIEN' => [
        ['label' => 'Thong tin ca nhan', 'icon' => 'person', 'href' => '/PythonProject/PythonProject/src/views/sinh_vien/thong_tin.php'],
        ['label' => 'Bang diem', 'icon' => 'menu_book', 'href' => '/PythonProject/PythonProject/src/views/sinh_vien/bang_diem.php'],
        ['label' => 'Xuat phieu diem', 'icon' => 'picture_as_pdf', 'href' => '/PythonProject/PythonProject/src/views/sinh_vien/xuat_phieu_diem.php'],
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
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">SV</div>
        <div class="brand-text">
            <span class="brand-title">QL Sinh Vien</span>
            <span class="brand-subtitle"><?php echo htmlspecialchars(sidebarRoleLabel($userRole)); ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if (empty($menuItems)): ?>
            <div class="muted-text">Khong co menu phu hop vai tro hien tai.</div>
        <?php endif; ?>
        <?php foreach ($menuItems as $item): ?>
            <?php $isActive = strpos($currentPath, $item['href']) !== false; ?>
            <a class="nav-link <?php echo $isActive ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($item['href']); ?>">
                <span class="material-symbols-outlined"><?php echo htmlspecialchars($item['icon']); ?></span>
                <span><?php echo htmlspecialchars($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <button class="support-button" type="button">
            <span class="material-symbols-outlined">help</span>
            <span>Tro giup</span>
        </button>
        <a class="account-button" href="/PythonProject/PythonProject/src/views/auth/doi_mat_khau.php">
            <span class="material-symbols-outlined">lock_reset</span>
            <span>Doi mat khau</span>
        </a>
        <a class="logout-button" href="/PythonProject/PythonProject/src/views/auth/logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Dang xuat</span>
        </a>
    </div>
</aside>