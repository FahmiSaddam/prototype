<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'laporan-pendapatan', function() {
    $pdo = db();
    $month = $_GET['month'] ?? date('Y-m');

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id
        WHERE DATE_FORMAT(r.tanggal,'%Y-%m') = ? AND r.status IN ('confirmed','completed') ORDER BY r.tanggal
    ");
    $stmt->execute([$month]);
    $list = $stmt->fetchAll();

    $total = 0; $byField = [];
    foreach ($list as $r) {
        $total += $r['total_biaya'];
        $fn = $r['lapangan_nama'];
        if (!isset($byField[$fn])) $byField[$fn] = ['jumlah'=>0,'total'=>0];
        $byField[$fn]['jumlah']++;
        $byField[$fn]['total'] += $r['total_biaya'];
    }
    ?>
    <h2 style="margin-bottom:20px;">Laporan Pendapatan</h2>
    <div class="card">
        <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;"><label>Bulan</label><input type="month" name="month" class="form-control" value="<?php echo $month; ?>"></div>
            <button type="submit" class="btn btn-primary">Generate</button>
        </form>

        <div class="stats-grid">
            <div class="stat-card green"><div class="stat-value"><?php echo formatRupiah($total); ?></div><div class="stat-label">Total Pendapatan</div></div>
            <div class="stat-card blue"><div class="stat-value"><?php echo count($list); ?></div><div class="stat-label">Reservasi Confirmed</div></div>
            <div class="stat-card"><div class="stat-value"><?php echo formatRupiah(count($list)>0 ? round($total/count($list)) : 0); ?></div><div class="stat-label">Rata-rata per Reservasi</div></div>
        </div>

        <h4 style="margin:16px 0 8px;">Pendapatan per Lapangan</h4>
        <div class="table-container"><table>
            <tr><th>Lapangan</th><th>Jumlah Reservasi</th><th>Total Pendapatan</th></tr>
            <?php foreach ($byField as $name => $data): ?>
            <tr><td><?php echo sanitize($name); ?></td><td><?php echo $data['jumlah']; ?></td><td><?php echo formatRupiah($data['total']); ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
