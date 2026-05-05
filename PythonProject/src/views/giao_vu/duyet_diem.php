<?php
session_start();
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/constants.php';

$giao_vu_id = $_SESSION['user_id'] ?? '';
$pdo = getDatabaseConnection();
$ds_lhp = [];

if ($pdo) {
  $stmt = $pdo->prepare("
    SELECT lhp_id, ma_lhp, mon_hoc_id, hoc_ky_id,
         trang_thai_giao_vu, trang_thai_giang_vien
    FROM lop_hoc_phan
    WHERE trang_thai_giang_vien = 1 AND trang_thai_giao_vu = 1
    ORDER BY ma_lhp
  ");
  $stmt->execute();
  $ds_lhp = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="p-6">
  <h2 class="text-lg font-semibold text-slate-900 mb-4">Duyet diem LHP</h2>

  <div class="overflow-x-auto rounded-lg border border-slate-200">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="px-3 py-2 text-left">Ma LHP</th>
          <th class="px-3 py-2 text-left">Mon hoc</th>
          <th class="px-3 py-2 text-left">Hoc ky</th>
          <th class="px-3 py-2 text-left">Trang thai GV</th>
          <th class="px-3 py-2 text-left">Trang thai GVU</th>
          <th class="px-3 py-2 text-left">Thao tac</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ds_lhp as $lhp): ?>
          <tr class="border-t">
            <td class="px-3 py-2"><?php echo htmlspecialchars($lhp['ma_lhp']); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($lhp['mon_hoc_id']); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($lhp['hoc_ky_id']); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($lhp['trang_thai_giang_vien']); ?></td>
            <td class="px-3 py-2"><?php echo htmlspecialchars($lhp['trang_thai_giao_vu']); ?></td>
            <td class="px-3 py-2">
              <button class="btn-duyet bg-teal-500 text-white px-3 py-1 rounded"
                data-id="<?php echo htmlspecialchars($lhp['lhp_id']); ?>">
                Duyet
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  const PY_API = '<?php echo rtrim(PYTHON_API_URL, '/'); ?>';

  document.querySelectorAll('.btn-duyet').forEach(btn => {
    btn.addEventListener('click', function() {
      if (!confirm('Duyet diem LHP nay?')) return;
      fetch(`${PY_API}/diem/duyet`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            lhp_id: this.dataset.id,
            giao_vu_id: '<?php echo htmlspecialchars($giao_vu_id); ?>'
          })
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            alert('Da duyet: ' + (res.so_luong || 0) + ' ban ghi');
            location.reload();
          } else {
            alert('Loi: ' + res.error);
          }
        });
    });
  });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>