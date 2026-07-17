<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pelanggan');

renderLayout('pelanggan', 'reservasi', function() {
    $user = currentUser();
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama, l.deskripsi AS lapangan_deskripsi
        FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id
        WHERE r.pelanggan_id = ? ORDER BY r.id DESC
    ");
    $stmt->execute([$user['id']]);
    $reservasiList = $stmt->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Reservasi Saya</h2>

    <?php if (empty($reservasiList)): ?>
        <div class="card">
            <p style="text-align:center; color:#999; padding:20px;">
                Belum ada reservasi. <a href="jadwal.php" style="color:#1e3a5f;">Buat reservasi baru</a>
            </p>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Durasi</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                <?php foreach ($reservasiList as $r): ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td>
                    <td><?php echo sanitize($r['lapangan_nama']); ?></td>
                    <td><?php echo $r['tanggal']; ?></td>
                    <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                    <td><?php echo $r['durasi']; ?> jam</td>
                    <td><?php echo formatRupiah($r['total_biaya']); ?></td>
                    <td><?php echo statusBadge($r['status']); ?></td>
                    <td>
                        <?php if ($r['status'] === 'pending_payment'): ?>
                            <button class="btn btn-primary btn-sm" onclick="showUploadModal(<?php echo $r['id']; ?>, '<?php echo $r['kode']; ?>', '<?php echo sanitize($r['lapangan_nama']); ?>', '<?php echo $r['tanggal']; ?>', '<?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?>', <?php echo $r['total_biaya']; ?>)">Upload Bukti</button>
                            <form method="POST" action="../proses.php" style="display:inline;" onsubmit="return confirm('Yakin batalkan reservasi?')">
                                <input type="hidden" name="action" value="cancel_reservasi">
                                <input type="hidden" name="reservasi_id" value="<?php echo $r['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Batal</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        </div>
    <?php endif; ?>

    <!-- Modal Upload Bukti -->
    <div id="upload-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <span>Upload Bukti Pembayaran</span>
                <button class="modal-close" onclick="document.getElementById('upload-modal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" action="../proses.php">
                <input type="hidden" name="action" value="upload_bukti">
                <input type="hidden" name="reservasi_id" id="upload-resv-id">
                <div class="summary-box">
                    <div class="summary-row"><span>Kode:</span><span id="upload-kode"></span></div>
                    <div class="summary-row"><span>Lapangan:</span><span id="upload-lapangan"></span></div>
                    <div class="summary-row"><span>Tanggal:</span><span id="upload-tanggal"></span></div>
                    <div class="summary-row"><span>Jam:</span><span id="upload-jam"></span></div>
                    <div class="summary-row total"><span>Total:</span><span id="upload-total"></span></div>
                </div>
                <div class="card" style="background:#f8f9fa; margin:12px 0;">
                    <strong>Transfer ke:</strong><br>
                    BCA: 1234567890 a/n Futsal Center XYZ<br>
                    Mandiri: 0987654321 a/n Futsal Center XYZ<br>
                    BRI: 1122334455 a/n Futsal Center XYZ
                </div>
                <div class="form-group">
                    <label>Bank Pengirim</label>
                    <select name="bank_pengirim" class="form-control" required>
                        <option value="BCA">BCA</option>
                        <option value="Mandiri">Mandiri</option>
                        <option value="BRI">BRI</option>
                        <option value="BNI">BNI</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Pengirim</label>
                    <input type="text" name="nama_pengirim" class="form-control" value="<?php echo sanitize($user['nama']); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('upload-modal').classList.remove('active')">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload Bukti Pembayaran</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function showUploadModal(id, kode, lapangan, tanggal, jam, total) {
        document.getElementById('upload-resv-id').value = id;
        document.getElementById('upload-kode').textContent = kode;
        document.getElementById('upload-lapangan').textContent = lapangan;
        document.getElementById('upload-tanggal').textContent = tanggal;
        document.getElementById('upload-jam').textContent = jam;
        document.getElementById('upload-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('upload-modal').classList.add('active');
    }
    </script>
    <?php
});
