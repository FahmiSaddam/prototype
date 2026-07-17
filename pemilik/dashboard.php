<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pemilik');

renderLayout('pemilik', 'dashboard', function() {
    $pdo = db();
    $month = date('Y-m');

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_biaya),0) FROM reservasi WHERE DATE_FORMAT(tanggal,'%Y-%m')=? AND status IN ('confirmed','completed')");
    $stmt->execute([$month]);
    $monthRevenue = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservasi WHERE DATE_FORMAT(tanggal,'%Y-%m')=?");
    $stmt->execute([$month]);
    $monthResv = $stmt->fetchColumn();

    $dayOfMonth = (int)date('d');
    $avgPerDay = $dayOfMonth > 0 ? round($monthRevenue / $dayOfMonth) : 0;

    $activeFields = $pdo->query("SELECT COUNT(*) FROM lapangan WHERE status='aktif'")->fetchColumn();

    $fields = $pdo->query("SELECT l.nama, l.id, (SELECT COUNT(*) FROM reservasi r WHERE r.lapangan_id=l.id AND r.status IN ('confirmed','completed')) as total_booked FROM lapangan l WHERE l.status='aktif'")->fetchAll();

    $peakHours = $pdo->query("SELECT SUBSTR(jam_mulai,1,5) as jam, COUNT(*) as cnt FROM reservasi WHERE status NOT IN ('cancelled') GROUP BY jam_mulai ORDER BY cnt DESC LIMIT 5")->fetchAll();

    $tren = [];
    for ($i = 2; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(total_biaya),0) FROM reservasi WHERE DATE_FORMAT(tanggal,'%Y-%m')=? AND status IN ('confirmed','completed')");
        $stmt->execute([$m]);
        $row = $stmt->fetch();
        $tren[] = ['bulan'=>$m, 'reservasi'=>(int)$row[0], 'pendapatan'=>(int)$row[1]];
    }
    ?>
    <h2 style="margin-bottom:20px;">Dashboard Pemilik</h2>

    <div class="stats-grid">
        <div class="stat-card green"><div class="stat-value"><?php echo formatRupiah($monthRevenue); ?></div><div class="stat-label">Pendapatan Bulan Ini</div></div>
        <div class="stat-card blue"><div class="stat-value"><?php echo $monthResv; ?></div><div class="stat-label">Reservasi Bulan Ini</div></div>
        <div class="stat-card orange"><div class="stat-value"><?php echo formatRupiah($avgPerDay); ?></div><div class="stat-label">Rata-rata/Hari</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo $activeFields; ?></div><div class="stat-label">Lapangan Aktif</div></div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="card">
            <div class="card-header">Grafik Pendapatan Bulanan</div>
            <div class="chart-placeholder">&#128202; Grafik Pendapatan</div>
        </div>
        <div class="card">
            <div class="card-header">Statistik Penggunaan Lapangan</div>
            <?php foreach ($fields as $f):
                $totalSlots = 14 * 30;
                $pct = $totalSlots > 0 ? min(100, round(($f['total_booked'] / $totalSlots) * 100)) : 0;
            ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                        <span><?php echo sanitize($f['nama']); ?></span><span><?php echo $pct; ?>%</span>
                    </div>
                    <div style="background:#eee; border-radius:4px; height:20px; overflow:hidden;">
                        <div style="background:<?php echo $pct>70?'#4CAF50':($pct>40?'#FF9800':'#f44336'); ?>; width:<?php echo $pct; ?>%; height:100%; border-radius:4px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Jam Paling Ramai</div>
        <?php if (empty($peakHours)): ?>
            <p style="color:#999;">Belum ada data</p>
        <?php else:
            $maxCnt = $peakHours[0]['cnt'];
            foreach ($peakHours as $ph):
                $pct = round(($ph['cnt']/$maxCnt)*100);
        ?>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <span style="width:80px; font-size:14px;"><?php echo $ph['jam']; ?></span>
                <div style="flex:1; background:#eee; border-radius:4px; height:20px; overflow:hidden;">
                    <div style="background:#1e3a5f; width:<?php echo $pct; ?>%; height:100%; border-radius:4px;"></div>
                </div>
                <span style="font-size:13px; color:#555;"><?php echo $ph['cnt']; ?> reservasi</span>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card">
        <div class="card-header">Tren Pendapatan (3 Bulan Terakhir)</div>
        <div class="table-container"><table>
            <tr><th>Bulan</th><th>Total Reservasi</th><th>Pendapatan</th></tr>
            <?php foreach ($tren as $t): ?>
            <tr><td><?php echo formatMonth($t['bulan']); ?></td><td><?php echo $t['reservasi']; ?></td><td><?php echo formatRupiah($t['pendapatan']); ?></td></tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
