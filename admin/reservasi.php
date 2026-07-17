<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'reservasi', function() {
    $pdo = db();
    $statusFilter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';

    $where = [];
    $params = [];
    if ($statusFilter) { $where[] = "r.status = ?"; $params[] = $statusFilter; }
    if ($search) { $where[] = "(r.kode LIKE ? OR u.nama LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama
        FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id JOIN users u ON r.pelanggan_id = u.id
        $whereClause ORDER BY r.id DESC
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Kelola Reservasi</h2>
    <div class="card">
        <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
            <select name="status" class="form-control" style="width:180px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <?php foreach (['pending_payment','waiting_verification','confirmed','completed','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $statusFilter===$s ? 'selected' : ''; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" class="form-control" placeholder="Cari kode/pelanggan..." style="width:220px;" value="<?php echo sanitize($search); ?>">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        </form>

        <div class="table-container"><table>
            <tr><th>Kode</th><th>Pelanggan</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            <?php if (empty($list)): ?>
                <tr><td colspan="8" style="text-align:center; color:#999; padding:20px;">Tidak ada data</td></tr>
            <?php endif; ?>
            <?php foreach ($list as $r): ?>
            <tr>
                <td><?php echo $r['kode']; ?></td>
                <td><?php echo sanitize($r['pelanggan_nama']); ?></td>
                <td><?php echo sanitize($r['lapangan_nama']); ?></td>
                <td><?php echo $r['tanggal']; ?></td>
                <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                <td><?php echo formatRupiah($r['total_biaya']); ?></td>
                <td><?php echo statusBadge($r['status']); ?></td>
                <td>
                    <?php if ($r['status'] === 'confirmed'): ?>
                        <form method="POST" action="../proses.php" style="display:inline;">
                            <input type="hidden" name="action" value="complete_reservasi">
                            <input type="hidden" name="reservasi_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">Complete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
