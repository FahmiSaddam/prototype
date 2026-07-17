<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pelanggan');

renderLayout('pelanggan', 'profil', function() {
    $user = currentUser();
    ?>
    <h2 style="margin-bottom:20px;">Profil Saya</h2>
    <div class="card">
        <form method="POST" action="../proses.php">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?php echo sanitize($user['nama']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="telepon" class="form-control" value="<?php echo sanitize($user['telepon']); ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" rows="3"><?php echo sanitize($user['alamat'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
    <?php
});
