<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'lapangan', function() {
    $fields = db()->query("SELECT * FROM lapangan ORDER BY id")->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Kelola Lapangan</h2>
    <div class="card">
        <div class="card-header">Daftar Lapangan</div>
        <div class="table-container"><table>
            <tr><th>ID</th><th>Nama</th><th>Deskripsi</th><th>Tipe</th><th>Tarif Weekday</th><th>Tarif Weekend</th><th>Status</th><th>Aksi</th></tr>
            <?php foreach ($fields as $f): ?>
            <tr>
                <td><?php echo $f['id']; ?></td>
                <td><?php echo sanitize($f['nama']); ?></td>
                <td><?php echo sanitize($f['deskripsi']); ?></td>
                <td><?php echo ucfirst($f['tipe']); ?></td>
                <td><?php echo formatRupiah($f['tarif_weekday']); ?></td>
                <td><?php echo formatRupiah($f['tarif_weekend']); ?></td>
                <td><?php echo $f['status']==='aktif' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>'; ?></td>
                <td>
                    <form method="POST" action="../proses.php" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_field">
                        <input type="hidden" name="field_id" value="<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-outline btn-sm"><?php echo $f['status']==='aktif' ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
