<?php
session_start();
require_once __DIR__ . '/../../../api/sinh_vien.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Location: ../auth/login.php');
    exit;
}

$error = '';
$notice = '';

$lopRes = sinhVienProxyRequest('GET', '/catalog/lop');
$lops = $lopRes['data'] ?? [];

if (empty($lops)) {
    $pdo = getDatabaseConnection();
    if ($pdo) {
        $stmt = $pdo->query("SELECT lop_id, ten_lop FROM lop ORDER BY ten_lop ASC");
        $lops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$hasLops = !empty($lops);

$student = [
    'msv' => '',
    'ten_sv' => '',
    'email' => '',
    'gioi_tinh' => '',
    'ngay_sinh' => '',
    'lop_id' => '',
    'mat_khau' => '123456',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasLops) {
        $error = 'Danh sach lop dang rong. Vui long tao lop truoc khi tao tai khoan sinh vien.';
    }

    $payload = [
        'msv' => trim($_POST['msv'] ?? ''),
        'ten_sv' => trim($_POST['ten_sv'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
        'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
        'lop_id' => $_POST['lop_id'] ?? '',
        'mat_khau' => trim($_POST['mat_khau'] ?? '123456'),
    ];

    if ($hasLops) {
        $res = sinhVienProxyRequest('POST', '/students', $payload);

        if (!empty($res['success'])) {
            $notice = $res['message'] ?? 'Tao tai khoan sinh vien thanh cong';
            $student = [
                'msv' => '',
                'ten_sv' => '',
                'email' => '',
                'gioi_tinh' => '',
                'ngay_sinh' => '',
                'lop_id' => '',
                'mat_khau' => '123456',
            ];
        } else {
            $error = $res['message'] ?? 'Khong the tao tai khoan sinh vien';
            $student = array_merge($student, $payload);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Them sinh vien</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="ml-64 min-h-screen">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <section class="p-6 space-y-6">
            <div>
                <h1 class="text-2xl font-bold">Tao tai khoan sinh vien (Admin)</h1>
                <p class="text-sm text-slate-500">Theo luong 1: Admin tao tai khoan + ho so sinh vien, Giao vu chi xu ly nghiep vu hoc vu.</p>
            </div>

            <?php if ($notice): ?>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!$hasLops): ?>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700">He thong chua co lop nao. Hay tao lop trong danh muc truoc, sau do quay lai tao sinh vien.</div>
            <?php endif; ?>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <form method="post" class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Ma sinh vien</label>
                        <input class="w-full rounded-lg border-slate-300" name="msv" required value="<?php echo htmlspecialchars((string)$student['msv']); ?>">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Ho ten</label>
                        <input class="w-full rounded-lg border-slate-300" name="ten_sv" required value="<?php echo htmlspecialchars((string)$student['ten_sv']); ?>">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Email dang nhap</label>
                        <input class="w-full rounded-lg border-slate-300" type="email" name="email" required value="<?php echo htmlspecialchars((string)$student['email']); ?>">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Mat khau khoi tao</label>
                        <input class="w-full rounded-lg border-slate-300" name="mat_khau" value="<?php echo htmlspecialchars((string)$student['mat_khau']); ?>">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Ngay sinh</label>
                        <input class="w-full rounded-lg border-slate-300" type="date" name="ngay_sinh" value="<?php echo htmlspecialchars((string)$student['ngay_sinh']); ?>">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Gioi tinh</label>
                        <select class="w-full rounded-lg border-slate-300" name="gioi_tinh">
                            <option value="">Khong xac dinh</option>
                            <option value="1" <?php echo (string)$student['gioi_tinh'] === '1' ? 'selected' : ''; ?>>Nam</option>
                            <option value="0" <?php echo (string)$student['gioi_tinh'] === '0' ? 'selected' : ''; ?>>Nu</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold">Lop sinh hoat</label>
                        <select class="w-full rounded-lg border-slate-300" name="lop_id" <?php echo $hasLops ? 'required' : 'disabled'; ?>>
                            <option value="">-- Chon lop --</option>
                            <?php foreach ($lops as $lop): ?>
                                <option value="<?php echo htmlspecialchars($lop['lop_id']); ?>" <?php echo (string)$student['lop_id'] === (string)$lop['lop_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lop['ten_lop']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60" <?php echo $hasLops ? '' : 'disabled'; ?>>Tao tai khoan sinh vien</button>
                        <a href="quan_ly_tai_khoan.php" class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-100">Quay lai quan ly tai khoan</a>
                    </div>
                </form>
            </div>
        </section>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </main>
</body>

</html>
