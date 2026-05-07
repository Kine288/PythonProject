<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'GUEST';
$userEmail = $_SESSION['user_email'] ?? 'guest';
$changePasswordUrl = '/PythonProject/PythonProject/src/views/auth/doi_mat_khau.php';
$roleLabel = 'Khach';
if ($role === 'ADMIN') {
    $roleLabel = 'Quan tri he thong';
} elseif ($role === 'GIAO_VU') {
    $roleLabel = 'Giao vu Hoc vien';
} elseif ($role === 'GIANG_VIEN') {
    $roleLabel = 'Giang vien bo mon';
} elseif ($role === 'SINH_VIEN') {
    $roleLabel = 'Sinh vien';
}
?>
<header class="app-header">
    <div class="header-left">
        <div class="header-logo">
            <span class="logo-badge">SV</span>
            <div class="logo-text">
                <span class="logo-title">Khoa CNTT</span>
                <span class="logo-subtitle">Student Management</span>
            </div>
        </div>
        <div class="header-search">
            <span class="search-icon material-symbols-outlined">search</span>
            <input placeholder="Tim kiem nhanh he thong..." type="text">
        </div>
    </div>

    <div class="header-right">
        <button class="icon-button" type="button" title="Thong bao">
            <span class="material-symbols-outlined">notifications</span>
            <span class="dot"></span>
        </button>
        <a class="icon-button" href="<?php echo htmlspecialchars($changePasswordUrl); ?>" title="Doi mat khau">
            <span class="material-symbols-outlined">settings</span>
        </a>
        <div class="header-divider"></div>
        <div class="header-profile">
            <div class="profile-meta">
                <span class="profile-name"><?php echo htmlspecialchars($userEmail); ?></span>
                <span class="profile-role"><?php echo htmlspecialchars($roleLabel); ?></span>
            </div>
            <a class="icon-button" href="<?php echo htmlspecialchars($changePasswordUrl); ?>" title="Doi mat khau">
                <span class="material-symbols-outlined">account_circle</span>
            </a>
        </div>
    </div>
</header>