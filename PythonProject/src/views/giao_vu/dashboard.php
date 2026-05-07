<?php session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
} ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giao vu Dashboard</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
</head>

<body><?php include __DIR__ . '/../layouts/sidebar.php'; ?><div class="app-content"><?php include __DIR__ . '/../layouts/header.php'; ?><div class="app-content-inner">
            <h1>Dashboard Giao vu</h1>
            <p class="muted-text">Tong hop nhanh nghiep vu hoc ky hien tai.</p>
        </div><?php include __DIR__ . '/../layouts/footer.php'; ?></div>
</body>

</html>