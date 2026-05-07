<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/constants.php';

$baseUrl = rtrim(APP_BASE_URL, '/');
$userRole = $_SESSION['user_role'] ?? 'GUEST';

$menuByRole = [
    'ADMIN' => [
        ['label' => 'Tong quan he thong', 'icon' => 'dashboard', 'href' => $baseUrl . '/src/views/admin/dashboard.php'],
        ['label' => 'Quan ly tai khoan', 'icon' => 'manage_accounts', 'href' => $baseUrl . '/src/views/admin/quan_ly_tai_khoan.php'],
        ['label' => 'Log he thong', 'icon' => 'receipt_long', 'href' => $baseUrl . '/src/views/admin/log_he_thong.php'],
    ],
    'GIAO_VU' => [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => $baseUrl . '/src/views/giao_vu/dashboard.php'],
        ['label' => 'Danh muc co so', 'icon' => 'folder_open', 'href' => $baseUrl . '/src/views/giao_vu/danh_muc.php'],
        ['label' => 'Danh muc mon hoc', 'icon' => 'library_books', 'href' => $baseUrl . '/src/views/giao_vu/danh_muc_mon_hoc.php'],
        ['label' => 'Sinh vien', 'icon' => 'group', 'href' => $baseUrl . '/src/views/giao_vu/sinh_vien/danh_sach.php'],
        ['label' => 'Duyet diem', 'icon' => 'fact_check', 'href' => $baseUrl . '/src/views/giao_vu/duyet_diem.php'],
        ['label' => 'Tong ket hoc vu', 'icon' => 'analytics', 'href' => $baseUrl . '/src/views/giao_vu/tong_ket_hoc_vu.php'],
        ['label' => 'Canh bao hoc vu', 'icon' => 'summarize', 'href' => $baseUrl . '/src/views/giao_vu/bao_cao/canh_bao.php'],
    ],
    'GIANG_VIEN' => [
        ['label' => 'Lop phu trach', 'icon' => 'class', 'href' => $baseUrl . '/src/views/giang_vien/lop_hoc_phan.php'],
        ['label' => 'Nhap diem', 'icon' => 'edit_note', 'href' => $baseUrl . '/src/views/giang_vien/nhap_diem.php'],
        ['label' => 'Yeu cau sua diem', 'icon' => 'request_quote', 'href' => $baseUrl . '/src/views/giang_vien/yeu_cau_sua_diem.php'],
    ],
    'SINH_VIEN' => [
        ['label' => 'Thong tin ca nhan', 'icon' => 'person', 'href' => $baseUrl . '/src/views/sinh_vien/thong_tin.php'],
        ['label' => 'Bang diem', 'icon' => 'menu_book', 'href' => $baseUrl . '/src/views/sinh_vien/bang_diem.php'],
        ['label' => 'Xuat phieu diem', 'icon' => 'picture_as_pdf', 'href' => $baseUrl . '/src/views/sinh_vien/xuat_phieu_diem.php'],
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
<style>
    .app-sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        z-index: 100;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02);
    }

    .sidebar-brand {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .brand-icon {
        width: 40px;
        height: 40px;
        background: #0d9488;
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
    }

    .brand-title {
        font-weight: 700;
        color: #0f766e;
        font-size: 16px;
    }

    .brand-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    .sidebar-nav {
        padding: 20px 12px;
        flex-grow: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #475569;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .nav-link:hover {
        background: #f1f5f9;
        color: #0d9488;
    }

    .nav-link.active {
        background: #ccfbf1;
        color: #0f766e;
    }

    .sidebar-footer {
        padding: 20px 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .support-button,
    .account-button,
    .logout-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        border: none;
        font-size: 14px;
        transition: all 0.2s;
    }

    .support-button {
        background: #0f766e;
        color: #fff;
        width: 100%;
    }

    .support-button:hover {
        background: #0d9488;
    }

    .account-button {
        background: #ecfdf5;
        color: #047857;
    }

    .account-button:hover {
        background: #d1fae5;
    }

    .logout-button {
        background: transparent;
        color: #64748b;
        margin-top: 4px;
    }

    .logout-button:hover {
        color: #ef4444;
        background: #fee2e2;
    }
</style>

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
            <div style="color: #64748b; font-size: 13px; text-align: center; padding: 16px;">Khong co menu phu hop vai tro hien tai.</div>
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

        <a class="account-button" href="<?php echo htmlspecialchars($baseUrl . '/src/views/auth/doi_mat_khau.php'); ?>">
            <span class="material-symbols-outlined">lock_reset</span>
            <span>Doi mat khau</span>
        </a>
        <a class="logout-button" href="<?php echo htmlspecialchars($baseUrl . '/src/views/auth/logout.php'); ?>">
            <span class="material-symbols-outlined">logout</span>
            <span>Dang xuat</span>
        </a>
    </div>
</aside>