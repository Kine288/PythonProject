<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/constants.php';

$role = $_SESSION['user_role'] ?? 'GUEST';
$userEmail = $_SESSION['user_email'] ?? 'guest';
$baseUrl = rtrim(APP_BASE_URL, '/');
$profileUrl = $baseUrl . '/src/views/auth/thong_tin_ca_nhan.php';
$changePasswordUrl = $baseUrl . '/src/views/auth/doi_mat_khau.php';
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
        <div class="header-profile" style="position:relative;">
            <div class="profile-meta">
                <span class="profile-name"><?php echo htmlspecialchars($userEmail); ?></span>
                <span class="profile-role"><?php echo htmlspecialchars($roleLabel); ?></span>
            </div>
            <button class="icon-button" id="profile-toggle" type="button" title="Thong tin tai khoan">
                <span class="material-symbols-outlined">account_circle</span>
            </button>

            <div id="profile-popover" style="display:none;position:absolute;top:46px;right:0;min-width:260px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(15,23,42,.14);padding:12px;z-index:300;">
                <div style="display:flex;gap:10px;align-items:center;padding-bottom:10px;border-bottom:1px solid #eef2f7;">
                    <span class="material-symbols-outlined" style="font-size:34px;color:#0f766e;">account_circle</span>
                    <div>
                        <div style="font-weight:700;color:#1f2937;"><?php echo htmlspecialchars($userEmail); ?></div>
                        <div style="font-size:12px;color:#64748b;"><?php echo htmlspecialchars($roleLabel); ?></div>
                    </div>
                </div>
                <div style="padding-top:10px;display:flex;flex-direction:column;gap:8px;">
                    <a href="<?php echo htmlspecialchars($profileUrl); ?>" style="display:flex;align-items:center;gap:8px;color:#0f766e;text-decoration:none;font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:18px;">badge</span>
                        <span>Thong tin ca nhan</span>
                    </a>
                    <a href="<?php echo htmlspecialchars($changePasswordUrl); ?>" style="display:flex;align-items:center;gap:8px;color:#0f766e;text-decoration:none;font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:18px;">lock_reset</span>
                        <span>Doi mat khau</span>
                    </a>
                    <a href="<?php echo htmlspecialchars($baseUrl . '/src/views/auth/logout.php'); ?>" style="display:flex;align-items:center;gap:8px;color:#dc2626;text-decoration:none;font-weight:600;">
                        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
                        <span>Dang xuat</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    (function() {
        const toggle = document.getElementById('profile-toggle');
        const popover = document.getElementById('profile-popover');
        if (!toggle || !popover) return;

        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            popover.style.display = popover.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', function(e) {
            if (!popover.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                popover.style.display = 'none';
            }
        });
    })();
</script>