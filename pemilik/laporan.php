<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pemilik');

renderLayout('pemilik', 'laporan', function() {
    $pdo = db();
    $type = $_GET['type'] ?? 'reservasi';
    $month = $_GET['month'] ?? date('Y-m');
    ?>
    <h2 style="margin-bottom:20px;">Laporan Bisnis</h2>
    <div class="card">
        <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;">
                <label>Jenis Laporan</label>
                <select name="type" class="form-control" style="width:180px;">
                    <option value="reservasi" <?php echo $type==='reservasi'?'selected':''; ?>>Laporan Reservasi</option>
                    <option value="pendapatan" <?php echo $type==='pendapatan'?'selected':''; ?>>Laporan Pendapatan</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;"><label>Periode</label><input type="month" name="month" class="form-control" value="<?php echo $month; ?>"></div>
            <button type="submit" class="btn btn-primary">Generate</button>
        </form>

        <?php if ($type === 'reservasi'):
            $stmt = $pdo->prepare("
                SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama
                FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id JOIN users u ON r.pelanggan_id = u.id
                WHERE DATE_FORMAT(r.tanggal,'%Y-%m')=? ORDER BY r.tanggal, r.jam_mulai
            ");
            $stmt->execute([$month]);
            $list = $stmt->fetchAll();
            $berhasil = 0; $batal = 0;
            foreach ($list as $r) {
                if (in_array($r['status'], ['confirmed','completed'])) $berhasil++;
                if ($r['status'] === 'cancelled') $batal++;
            }
        ?>
            <div class="stats-grid">
                <div class="stat-card blue"><div class="stat-value"><?php echo count($list); ?></div><div class="stat-label">Total Reservasi</div></div>
                <div class="stat-card green"><div class="stat-value"><?php echo $berhasil; ?></div><div class="stat-label">Berhasil</div></div>
                <div class="stat-card red"><div class="stat-value"><?php echo $batal; ?></div><div class="stat-label">Dibatalkan</div></div>
            </div>
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th></tr>
                <?php foreach ($list as $r): ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td><td><?php echo sanitize($r['pelanggan_nama']); ?></td>
                    <td><?php echo sanitize($r['lapangan_nama']); ?></td><td><?php echo $r['tanggal']; ?></td>
                    <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                    <td><?php echo formatRupiah($r['total_biaya']); ?></td><td><?php echo statusBadge($r['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        <?php else:
            $stmt = $pdo->prepare("
                SELECT l.nama AS lapangan_nama, COUNT(*) as jumlah, SUM(r.total_biaya) as total_pendapatan
                FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id
                WHERE DATE_FORMAT(r.tanggal,'%Y-%m')=? AND r.status IN ('confirmed','completed') GROUP BY l.id
            ");
            $stmt->execute([$month]);
            $byField = $stmt->fetchAll();
            $totalRevenue = array_sum(array_column($byField, 'total_pendapatan'));
        ?>
            <div class="stats-grid">
                <div class="stat-card green"><div class="stat-value"><?php echo formatRupiah($totalRevenue); ?></div><div class="stat-label">Total Pendapatan</div></div>
                <div class="stat-card blue"><div class="stat-value"><?php echo array_sum(array_column($byField,'jumlah')); ?></div><div class="stat-label">Transaksi Berhasil</div></div>
            </div>
            <h4 style="margin:16px 0 8px;">Breakdown per Lapangan</h4>
            <div class="table-container"><table>
                <tr><th>Lapangan</th><th>Jumlah</th><th>Pendapatan</th></tr>
                <?php foreach ($byField as $bf): ?>
                <tr><td><?php echo sanitize($bf['lapangan_nama']); ?></td><td><?php echo $bf['jumlah']; ?></td><td><?php echo formatRupiah($bf['total_pendapatan']); ?></td></tr>
                <?php endforeach; ?>
            </table></div>
        <?php endif; ?>
    </div>
    <?php
});
