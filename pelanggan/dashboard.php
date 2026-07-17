<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pelanggan');

renderLayout('pelanggan', 'dashboard', function() {
    $user = currentUser();
    $pdo = db();

    $stmt = $pdo->prepare("SELECT * FROM reservasi WHERE pelanggan_id=? ORDER BY id DESC");
    $stmt->execute([$user['id']]);
    $myResv = $stmt->fetchAll();

    $active = array_filter($myResv, fn($r) => in_array($r['status'], ['pending_payment','waiting_verification','confirmed']));
    $total = count($myResv);
    $confirmed = count(array_filter($myResv, fn($r) => $r['status'] === 'confirmed'));
    $waiting = count(array_filter($myResv, fn($r) => $r['status'] === 'waiting_verification'));

    $jadwal = getScheduleGrid(date('Y-m-d'));
    $lapanganList = $pdo->query("SELECT * FROM lapangan WHERE status='aktif' ORDER BY id")->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Dashboard</h2>
    <p style="color:#555; margin-bottom:16px;">Selamat datang, <strong><?php echo sanitize($user['nama']); ?></strong>!</p>

    <div class="stats-grid">
        <div class="stat-card blue"><div class="stat-value"><?php echo count($active); ?></div><div class="stat-label">Reservasi Aktif</div></div>
        <div class="stat-card green"><div class="stat-value"><?php echo $confirmed; ?></div><div class="stat-label">Terkonfirmasi</div></div>
        <div class="stat-card orange"><div class="stat-value"><?php echo $waiting; ?></div><div class="stat-label">Menunggu Verifikasi</div></div>
        <div class="stat-card"><div class="stat-value"><?php echo $total; ?></div><div class="stat-label">Total Reservasi</div></div>
    </div>

    <div class="card">
        <div class="card-header">Reservasi Aktif</div>
        <?php if (empty($active)): ?>
            <p style="color:#999; text-align:center; padding:20px;">Tidak ada reservasi aktif</p>
        <?php else: ?>
            <div class="table-container"><table>
                <tr><th>Kode</th><th>Lapangan</th><th>Tanggal</th><th>Jam</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                <?php foreach ($active as $r):
                    $lap = null;
                    foreach ($lapanganList as $l) { if ($l['id'] == $r['lapangan_id']) { $lap = $l; break; } }
                ?>
                <tr>
                    <td><?php echo $r['kode']; ?></td>
                    <td><?php echo $lap ? sanitize($lap['nama']) : '-'; ?></td>
                    <td><?php echo $r['tanggal']; ?></td>
                    <td><?php echo substr($r['jam_mulai'],0,5); ?>-<?php echo substr($r['jam_selesai'],0,5); ?></td>
                    <td><?php echo formatRupiah($r['total_biaya']); ?></td>
                    <td><?php echo statusBadge($r['status']); ?></td>
                    <td>
                        <?php if ($r['status'] === 'pending_payment'): ?>
                            <a href="reservasi.php" class="btn btn-primary btn-sm">Upload Bukti</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">Jadwal Hari Ini (<?php echo date('d M Y'); ?>)</div>
        <div class="schedule-grid" style="grid-template-columns: 120px repeat(14, 1fr);">
            <div class="header"></div>
            <?php foreach ($jadwal['slots'] as $slot): ?>
                <div class="header"><?php echo $slot['label']; ?></div>
            <?php endforeach; ?>
            <?php foreach ($jadwal['lapangan'] as $field): ?>
                <div class="field-name"><?php echo sanitize($field['nama']); ?></div>
                <?php foreach ($jadwal['slots'] as $slot):
                    $status = $jadwal['grid'][$field['id']][$slot['start']] ?? 'available';
                    $label = $status === 'available' ? '&#10003;' : ($status === 'booked' ? '&#10007;' : '&mdash;');
                ?>
                    <div class="slot <?php echo $status; ?>"><?php echo $label; ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:8px; font-size:12px; color:#777;">
            <span style="color:#2e7d32;">&#9632;</span> Tersedia &nbsp;
            <span style="color:#c62828;">&#9632;</span> Terpesan &nbsp;
            <span style="color:#999;">&#9632;</span> Diblokir
        </p>
    </div>
    <?php
});
