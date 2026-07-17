<?php
require_once 'config.php';

if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') redirect('admin/dashboard.php');
    elseif ($role === 'pemilik') redirect('pemilik/dashboard.php');
    else redirect('pelanggan/dashboard.php');
}

$jadwal = getScheduleGrid(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Center XYZ - Sistem Reservasi</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar">
    <div class="logo">&#9917; Futsal Center <span>XYZ</span></div>
    <div class="nav-links">
        <a href="#jadwal">Jadwal</a>
        <a href="#lapangan">Lapangan</a>
        <a href="login.php">Login</a>
        <a class="btn btn-success btn-sm" href="register.php">Daftar</a>
    </div>
</nav>

<div class="hero">
    <h1>Selamat Datang di Futsal Center XYZ</h1>
    <p>Reservasi Lapangan Futsal Online &mdash; Mudah, Cepat, dan Transparan</p>
    <div class="hero-buttons">
        <a class="btn btn-white" href="#jadwal">&#128197; Lihat Jadwal</a>
        <a class="btn btn-green-outline" href="register.php">Daftar Sekarang</a>
    </div>
</div>

<div class="section-title" id="lapangan">
    <h2>Lapangan Kami</h2>
    <p>Tiga lapangan berkualitas untuk kebutuhan futsal Anda</p>
</div>
<div class="features">
    <div class="feature-card">
        <div class="icon">&#9917;</div>
        <h3>Lapangan A &mdash; Indoor</h3>
        <p>Rumput sintetis premium, pencahayaan LED, ventilasi baik</p>
        <p style="margin-top:8px; font-weight:600; color:#1e3a5f;"><?php echo formatRupiah(100000); ?>/jam (Weekday) | <?php echo formatRupiah(120000); ?>/jam (Weekend)</p>
    </div>
    <div class="feature-card">
        <div class="icon">&#9917;</div>
        <h3>Lapangan B &mdash; Indoor</h3>
        <p>Rumput sintetis standar, pencahayaan LED, area tribun</p>
        <p style="margin-top:8px; font-weight:600; color:#1e3a5f;"><?php echo formatRupiah(100000); ?>/jam (Weekday) | <?php echo formatRupiah(120000); ?>/jam (Weekend)</p>
    </div>
    <div class="feature-card">
        <div class="icon">&#9917;</div>
        <h3>Lapangan C &mdash; Outdoor</h3>
        <p>Rumput sintetis, area terbuka, cocok untuk sore hari</p>
        <p style="margin-top:8px; font-weight:600; color:#1e3a5f;"><?php echo formatRupiah(80000); ?>/jam (Weekday) | <?php echo formatRupiah(100000); ?>/jam (Weekend)</p>
    </div>
</div>

<div class="section-title">
    <h2>Cara Reservasi</h2>
    <p>Empat langkah mudah untuk bermain futsal</p>
</div>
<div class="features" style="padding-top:0;">
    <div class="feature-card"><div class="icon">&#128100;</div><h3>1. Daftar / Login</h3><p>Buat akun atau login ke sistem</p></div>
    <div class="feature-card"><div class="icon">&#128197;</div><h3>2. Pilih Jadwal</h3><p>Lihat ketersediaan dan pilih slot waktu</p></div>
    <div class="feature-card"><div class="icon">&#128179;</div><h3>3. Bayar</h3><p>Transfer dan upload bukti pembayaran</p></div>
    <div class="feature-card"><div class="icon">&#9917;</div><h3>4. Main!</h3><p>Datang dan nikmati permainan futsal</p></div>
</div>

<div class="section-title" id="jadwal">
    <h2>Jadwal Ketersediaan Hari Ini</h2>
    <p>Login untuk melakukan reservasi</p>
</div>
<div style="max-width:1200px; margin:0 auto; padding:0 24px 48px;">
    <div class="card">
        <div class="schedule-grid" style="grid-template-columns: 120px repeat(14, 1fr);">
            <div class="header"></div>
            <?php foreach ($jadwal['slots'] as $slot): ?>
                <div class="header"><?php echo $slot['label']; ?></div>
            <?php endforeach; ?>
            <?php foreach ($jadwal['lapangan'] as $field): ?>
                <div class="field-name"><?php echo sanitize($field['nama']); ?></div>
                <?php foreach ($jadwal['slots'] as $slot):
                    $status = $jadwal['grid'][$field['id']][$slot['start']] ?? 'available';
                    $cls = $status;
                    $label = $status === 'available' ? '&#10003;' : ($status === 'booked' ? '&#10007;' : '&mdash;');
                ?>
                    <div class="slot <?php echo $cls; ?>"><?php echo $label; ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <p style="text-align:center; margin-top:12px; font-size:13px;">
        <span style="color:#2e7d32;">&#9632;</span> Tersedia &nbsp;&nbsp;
        <span style="color:#c62828;">&#9632;</span> Terpesan &nbsp;&nbsp;
        <span style="color:#999;">&#9632;</span> Diblokir
    </p>
</div>

<div class="footer">
    &copy; 2026 Futsal Center XYZ | Hubungi Kami: 0812-xxxx-xxxx | Jl. Contoh No. 123, Jakarta
</div>
</body>
</html>
