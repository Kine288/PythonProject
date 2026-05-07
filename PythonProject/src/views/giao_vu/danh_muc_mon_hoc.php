<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'GIAO_VU') {
    header('Location: ../auth/login.php');
    exit;
}

function newId32(): string
{
    return bin2hex(random_bytes(16));
}

$notice = '';
$error = '';
$pdo = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['form_action'] ?? '');
    try {
        if (!$pdo) {
            throw new RuntimeException('Khong ket noi duoc CSDL');
        }

        if ($action === 'create_mon_hoc') {
            $maMon = trim($_POST['ma_mon'] ?? '');
            $tenMon = trim($_POST['ten_mon'] ?? '');
            $soTinChi = (int)($_POST['so_tin_chi'] ?? 0);
            $tinhGpa = isset($_POST['tinh_gpa']) ? 1 : 0;
            if ($maMon === '' || $tenMon === '' || $soTinChi <= 0) {
                throw new RuntimeException('Thieu du lieu mon hoc');
            }
            $stmt = $pdo->prepare('INSERT INTO mon_hoc (mon_hoc_id, ma_mon, ten_mon, so_tin_chi, tinh_gpa) VALUES (:id, :ma, :ten, :tc, :gpa)');
            $stmt->execute(['id' => newId32(), 'ma' => $maMon, 'ten' => $tenMon, 'tc' => $soTinChi, 'gpa' => $tinhGpa]);
            $notice = 'Da them mon hoc';
        } elseif ($action === 'update_mon_hoc') {
            $monHocId = trim($_POST['mon_hoc_id'] ?? '');
            if ($monHocId === '') {
                throw new RuntimeException('Thieu mon_hoc_id');
            }
            $stmt = $pdo->prepare('UPDATE mon_hoc SET ma_mon = :ma, ten_mon = :ten, so_tin_chi = :tc, tinh_gpa = :gpa WHERE mon_hoc_id = :id');
            $stmt->execute([
                'id' => $monHocId,
                'ma' => trim($_POST['ma_mon'] ?? ''),
                'ten' => trim($_POST['ten_mon'] ?? ''),
                'tc' => (int)($_POST['so_tin_chi'] ?? 0),
                'gpa' => isset($_POST['tinh_gpa']) ? 1 : 0,
            ]);
            $notice = 'Da cap nhat mon hoc';
        } else {
            throw new RuntimeException('Thao tac khong hop le');
        }
    } catch (Throwable $exc) {
        $error = $exc->getMessage();
    }
}

$monHocs = [];
if ($pdo) {
    $monHocs = $pdo->query('SELECT mon_hoc_id, ma_mon, ten_mon, so_tin_chi, tinh_gpa FROM mon_hoc ORDER BY ma_mon ASC')->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh muc mon hoc</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <link rel="stylesheet" href="../../../assets/css/components.css">
</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <div class="app-content-inner">
            <div class="dashboard-header">
                <div>
                    <h1>Danh muc mon hoc</h1>
                    <p class="muted-text">Quan ly danh muc mon hoc dung chung de tao Lop hoc phan.</p>
                </div>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="alert-info" style="margin-bottom:12px;"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert-danger" style="margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card" style="margin-bottom:14px;">
                <h3 style="margin-bottom:10px;">Them mon hoc</h3>
                <form method="post" style="display:grid;grid-template-columns:1fr 2fr 1fr auto;gap:12px;align-items:end;">
                    <input type="hidden" name="form_action" value="create_mon_hoc">
                    <div class="form-group" style="margin:0;">
                        <label>Ma mon</label>
                        <input name="ma_mon" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Ten mon</label>
                        <input name="ten_mon" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>So tin chi</label>
                        <input type="number" min="1" max="10" name="so_tin_chi" required>
                    </div>
                    <div>
                        <label style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                            <input type="checkbox" name="tinh_gpa" value="1" checked>
                            <span>Tinh GPA</span>
                        </label>
                        <button class="btn-primary" type="submit">Them mon</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3 style="margin-bottom:10px;">Danh sach mon hoc</h3>
                <div style="overflow-x:auto;">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Ma mon</th>
                                <th>Ten mon</th>
                                <th>So tin chi</th>
                                <th>Tinh GPA</th>
                                <th>Cap nhat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monHocs as $mon): ?>
                                <tr>
                                    <form method="post">
                                        <input type="hidden" name="form_action" value="update_mon_hoc">
                                        <input type="hidden" name="mon_hoc_id" value="<?php echo htmlspecialchars($mon['mon_hoc_id']); ?>">
                                        <td><input name="ma_mon" value="<?php echo htmlspecialchars((string)$mon['ma_mon']); ?>" required></td>
                                        <td><input name="ten_mon" value="<?php echo htmlspecialchars((string)$mon['ten_mon']); ?>" required></td>
                                        <td><input type="number" min="1" max="10" name="so_tin_chi" value="<?php echo (int)($mon['so_tin_chi'] ?? 0); ?>" required></td>
                                        <td><input type="checkbox" name="tinh_gpa" value="1" <?php echo !empty($mon['tinh_gpa']) ? 'checked' : ''; ?>></td>
                                        <td><button class="btn-secondary" type="submit">Luu</button></td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>
</body>

</html>