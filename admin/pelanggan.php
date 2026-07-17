<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'pelanggan', function() {
    $customers = db()->query("
        SELECT u.*, (SELECT COUNT(*) FROM reservasi r WHERE r.pelanggan_id=u.id) as total_reservasi
        FROM users u WHERE u.role='pelanggan' ORDER BY u.id
    ")->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Kelola Pelanggan</h2>
    <div class="card">
        <div class="table-container"><table>
            <tr><th>ID</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Status</th><th>Total Reservasi</th><th>Aksi</th></tr>
            <?php foreach ($customers as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo sanitize($u['nama']); ?></td>
                <td><?php echo sanitize($u['email']); ?></td>
                <td><?php echo sanitize($u['telepon']); ?></td>
                <td><?php echo $u['status']==='active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                <td><?php echo $u['total_reservasi']; ?></td>
                <td>
                    <form method="POST" action="../proses.php" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_user">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" class="btn btn-outline btn-sm"><?php echo $u['status']==='active' ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table></div>
    </div>
    <?php
});
