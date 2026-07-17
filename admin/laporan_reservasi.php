<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'laporan-reservasi', function() {
    $pdo = db();
    $start = $_GET['start'] ?? date('Y-m-01');
    $end = $_GET['end'] ?? date('Y-m-d');
    $fieldId = $_GET['field_id'] ?? '';

    $fields = $pdo->query("SELECT * FROM lapangan ORDER BY id")->fetchAll();

    $where = ["r.tanggal BETWEEN ? AND ?"];
    $params = [$start, $end];
    if ($fieldId) { $where[] = "r.lapangan_id = ?"; $params[] = $fieldId; }
    $whereClause = 'WHERE ' . implode(' AND ', $where);

    $stmt = $pdo->prepare("
        SELECT r.*, l.nama AS lapangan_nama, u.nama AS pelanggan_nama
        FROM reservasi r JOIN lapangan l ON r.lapangan_id = l.id JOIN users u ON r.pelanggan_id = u.id
        $whereClause ORDER BY r.tanggal, r.jam_mulai
    ");
    $stmt->execute($params);
    $list = $stmt->fetchAll();

    $totalRevenue = 0; $confirmed = 0; $cancelled = 0;
    foreach ($list as $r) {
        if (in_array($r['status'], ['confirmed','completed'])) $totalRevenue += $r['total_biaya'];
        if ($r['status'] === 'confirmed') $confirmed++;
        if ($r['status'] === 'cancelled') $cancelled++;
    }
    ?>
    <h2 style="margin-bottom:20px;">Laporan Reservasi</h2>
    <div class="card">
        <form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:flex-end;">
            <div class="form-group" style="margin-bottom:0;"><label>Dari Tanggal</label><input type="date" name="start" class="form-control" value="<?php echo $start; ?>"></div>
            <div class="form-group" style="margin-bottom:0;"><label>Sampai Tanggal</label><input type="date" name="end" class="form-control" value="<?php echo $end; ?>"></div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Lapangan</label>
                <select name="field_id" class="form-control"><option value="">Semua</option>
                    <?php foreach ($fields as $f): ?><option value="<?php echo $f['id']; ?>" <?php echo $fieldId==$f['id']?'selected':''; ?>><?php echo sanitize($f['nama']); ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Generate</button>
        </form>

        <div class="stats-grid">
            <div class="stat-card blue"><div class="stat-value"><?php echo count($list); ?></div><div class="stat-label">Total Reservasi</div></div>
            <div class="stat-card green"><div class="stat-value"><?php echo formatRupiah($totalRevenue); ?></div><div class="stat-label">Total Pendapatan</div></div>
            <div class="stat-card"><div class="stat-value"><?php echo $confirmed; ?></div><div class="stat-label">Confirmed</div></div>
            <div class="stat-card red"><div class="stat-value"><?php echo $cancelled; ?></div><div class="stat-label">Cancelled</div></div>
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
    </div>
    <?php
});
