<?php
require_once 'config.php';
if (isLoggedIn()) redirect('index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Futsal Center XYZ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">&#9917;</div>
            <h1>Daftar Akun Baru</h1>
            <p>Futsal Center XYZ</p>
        </div>
        <?php renderFlash(); ?>
        <form method="POST" action="proses.php">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="telepon" class="form-control" placeholder="08xx-xxxx-xxxx" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px;">DAFTAR</button>
            </div>
        </form>
        <p style="text-align:center; font-size:13px; color:#777; margin-top:16px;">
            Sudah punya akun? <a href="login.php" style="color:#1e3a5f; font-weight:600;">Login di sini</a>
        </p>
    </div>
</div>
</body>
</html>
