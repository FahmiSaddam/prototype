<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'verifikasi', function() {
    $pdo = db();
    $stmt = $pdo->query("
        SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama, u.telepon AS pel_telepon,
               p.bank_pengirim, p.nama_pengirim, p.bukti_pembayaran
        FROM reservasi r
        JOIN lapangan l ON r.lapangan_id = l.id
        JOIN users u ON r.pelanggan_id = u.id
        LEFT JOIN pembayaran p ON r.id = p.reservasi_id
        WHERE r.status = 'waiting_verification' ORDER BY r.id DESC
    ");
    $list = $stmt->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Verifikasi Pembayaran</h2>
    <div class="card">
        <div class="card-header">Menunggu Verifikasi</div>
        <?php if (empty($list)): ?>
            <p style="color:#999; text-align:center; padding:20px;">Tidak ada pembayaran menunggu verifikasi</p>
        <?php else: ?>
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Jumlah</th><th>Bank</th><th>Pengirim</th><th>Aksi</th></tr>
                <?php foreach ($list as $r): ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td>
                    <td><?php echo sanitize($r['pelanggan_nama']); ?></td>
                    <td><?php echo sanitize($r['lapangan_nama']); ?></td>
                    <td><?php echo $r['tanggal']; ?></td>
                    <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                    <td><?php echo formatRupiah($r['total_biaya']); ?></td>
                    <td><?php echo sanitize($r['bank_pengirim'] ?? '-'); ?></td>
                    <td><?php echo sanitize($r['nama_pengirim'] ?? '-'); ?></td>
                    <td style="display:flex; gap:4px;">
                        <form method="POST" action="../proses.php">
                            <input type="hidden" name="action" value="verify_payment">
                            <input type="hidden" name="reservasi_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">&#10003; Verifikasi</button>
                        </form>
                        <form method="POST" action="../proses.php">
                            <input type="hidden" name="action" value="reject_payment">
                            <input type="hidden" name="reservasi_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">&#10007; Tolak</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        <?php endif; ?>
    </div>
    <?php
});
