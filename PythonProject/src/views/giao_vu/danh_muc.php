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
    <title>Danh muc co so</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;600;700&display=swap" rel="stylesheet">

</head>

<body><?php include __DIR__ . '/../layouts/sidebar.php'; ?><div class="app-content"><?php include __DIR__ . '/../layouts/header.php'; ?><div class="app-content-inner">
            <h1>Danh muc co so</h1>
            <p class="muted-text">Quan ly khoa, lop, hoc ky, mon hoc (se tiep tuc hoan thien).</p>
        </div><?php include __DIR__ . '/../layouts/footer.php'; ?></div>
</body>

</html>