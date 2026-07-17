<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pemilik');

renderLayout('pemilik', 'statistik', function() {
    $pdo = db();
    $month = date('Y-m');
    $totalSlotsPerField = 14 * (int)date('t');

    $fields = $pdo->query("
        SELECT l.nama, l.id,
               (SELECT COUNT(*) FROM reservasi r WHERE r.lapangan_id=l.id AND DATE_FORMAT(r.tanggal,'%Y-%m')='$month' AND r.status IN ('confirmed','completed','waiting_verification')) as booked
        FROM lapangan l WHERE l.status='aktif'
    ")->fetchAll();

    $tren = [];
    for ($i = 2; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $stmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(total_biaya),0) FROM reservasi WHERE DATE_FORMAT(tanggal,'%Y-%m')=? AND status IN ('confirmed','completed')");
        $stmt->execute([$m]);
        $row = $stmt->fetch();
        $tren[] = ['bulan'=>$m, 'reservasi'=>(int)$row[0], 'pendapatan'=>(int)$row[1]];
    }
    ?>
    <h2 style="margin-bottom:20px;">Statistik Penggunaan</h2>

    <div class="card">
        <div class="card-header">Utilisasi Lapangan (Bulan Ini - <?php echo formatMonth($month); ?>)</div>
        <div class="table-container"><table>
            <tr><th>Lapangan</th><th>Slot Terpakai</th><th>Total Slot</th><th>Utilisasi</th></tr>
            <?php foreach ($fields as $f):
                $pct = $totalSlotsPerField > 0 ? round(($f['booked'] / $totalSlotsPerField) * 100) : 0;
                $badgeCls = $pct > 70 ? 'badge-success' : ($pct > 40 ? 'badge-warning' : 'badge-danger');
            ?>
            <tr>
                <td><?php echo sanitize($f['nama']); ?></td>
                <td><?php echo $f['booked']; ?></td>
                <td><?php echo $totalSlotsPerField; ?></td>
                <td><span class="badge <?php echo $badgeCls; ?>"><?php echo $pct; ?>%</span></td>
            </tr>
            <?php endforeach; ?>
        </table></div>
    </div>

    <div class="card">
        <div class="card-header">Tren Reservasi (3 Bulan Terakhir)</div>
        <div class="table-container"><table>
            <tr><th>Bulan</th><th>Total Reservasi</th><th>Pendapatan</th></tr>
            <?php foreach ($tren as $t): ?>
            <tr>
                <td><?php echo formatMonth($t['bulan']); ?></td>
                <td><?php echo $t['reservasi']; ?></td>
                <td><?php echo formatRupiah($t['pendapatan']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
