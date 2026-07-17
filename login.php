<?php
require_once 'config.php';
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') redirect('admin/dashboard.php');
    elseif ($role === 'pemilik') redirect('pemilik/dashboard.php');
    else redirect('pelanggan/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Futsal Center XYZ</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">&#9917;</div>
            <h1>Futsal Center XYZ</h1>
            <p>Sistem Reservasi Online</p>
        </div>
        <?php renderFlash(); ?>
        <form method="POST" action="proses.php">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px;">LOGIN</button>
            </div>
        </form>
        <p style="text-align:center; font-size:13px; color:#777; margin-top:16px;">
            Belum punya akun? <a href="register.php" style="color:#1e3a5f; font-weight:600;">Daftar di sini</a>
        </p>
        <p style="text-align:center; font-size:12px; color:#999; margin-top:12px;">
            <a href="index.php" style="color:#999;">&larr; Kembali ke Beranda</a>
        </p>
        <div style="margin-top:20px; padding-top:16px; border-top:1px solid #eee;">
            <p style="text-align:center; font-size:12px; color:#999; margin-bottom:8px;">Demo Akun:</p>
            <table style="width:100%; font-size:11px;">
                <tr><td><b>Pelanggan:</b></td><td>budi@email.com / password</td></tr>
                <tr><td><b>Admin:</b></td><td>admin@futsal.com / admin123</td></tr>
                <tr><td><b>Pemilik:</b></td><td>pemilik@futsal.com / pemilik123</td></tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
