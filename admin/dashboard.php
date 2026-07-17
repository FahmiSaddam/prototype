<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'dashboard', function() {
    $pdo = db();
    $today = date('Y-m-d');

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservasi WHERE tanggal=?");
    $stmt->execute([$today]);
    $todayCount = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_biaya),0) FROM reservasi WHERE tanggal=? AND status IN ('confirmed','completed')");
    $stmt->execute([$today]);
    $todayRevenue = $stmt->fetchColumn();

    $pendingVerif = $pdo->query("SELECT COUNT(*) FROM reservasi WHERE status='waiting_verification'")->fetchColumn();
    $activeFields = $pdo->query("SELECT COUNT(*) FROM lapangan WHERE status='aktif'")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama, u.telepon AS pelanggan_telepon,
               p.bank_pengirim, p.nama_pengirim, p.bukti_pembayaran
        FROM reservasi r
        JOIN lapangan l ON r.lapangan_id = l.id
        JOIN users u ON r.pelanggan_id = u.id
        LEFT JOIN pembayaran p ON r.id = p.reservasi_id
        WHERE r.status='waiting_verification' ORDER BY r.id DESC
    ");
    $pendingList = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama
        FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id JOIN users u ON r.pelanggan_id = u.id
        WHERE r.tanggal = ? ORDER BY r.jam_mulai
    ");
    $stmt->execute([$today]);
    $todayList = $stmt->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Dashboard Admin</h2>

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-value"><?php echo $todayCount; ?></div><div class="stat-label">Reservasi Hari Ini</div></div>
        <div class="stat-card green"><div class="stat-value"><?php echo formatRupiah($todayRevenue); ?></div><div class="stat-label">Pendapatan Hari Ini</div></div>
        <div class="stat-card orange"><div class="stat-value"><?php echo $pendingVerif; ?></div><div class="stat-label">Menunggu Verifikasi</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo $activeFields; ?></div><div class="stat-label">Lapangan Aktif</div></div>
    </div>

    <div class="card">
        <div class="card-header">Verifikasi Pembayaran Menunggu</div>
        <?php if (empty($pendingList)): ?>
            <p style="color:#999; text-align:center; padding:16px;">Tidak ada verifikasi menunggu</p>
        <?php else: ?>
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Jumlah</th><th>Aksi</th></tr>
                <?php foreach ($pendingList as $r): ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td>
                    <td><?php echo sanitize($r['pelanggan_nama']); ?></td>
                    <td><?php echo sanitize($r['lapangan_nama']); ?></td>
                    <td><?php echo $r['tanggal']; ?></td>
                    <td><?php echo formatRupiah($r['total_biaya']); ?></td>
                    <td><a href="verifikasi.php" class="btn btn-success btn-sm">Verifikasi</a></td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">Reservasi Hari Ini</div>
        <?php if (empty($todayList)): ?>
            <p style="color:#999; text-align:center; padding:16px;">Tidak ada reservasi hari ini</p>
        <?php else: ?>
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Pelanggan</th><th>Lapangan</th><th>Jam</th><th>Status</th></tr>
                <?php foreach ($todayList as $r): ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td>
                    <td><?php echo sanitize($r['pelanggan_nama']); ?></td>
                    <td><?php echo sanitize($r['lapangan_nama']); ?></td>
                    <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                    <td><?php echo statusBadge($r['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        <?php endif; ?>
    </div>
    <?php
});
