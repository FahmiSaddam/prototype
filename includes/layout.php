<?php
require_once __DIR__ . '/../config.php';

function renderLayout($role, $activePage, $contentFn) {
    $user = currentUser();
    if (!$user) { redirect('login.php'); exit; }

    $navLinks = '';
    $sidebarItems = [];

    if ($role === 'pelanggan') {
        $navLinks = '
            <a href="pelanggan/jadwal.php">Jadwal</a>
            <a href="pelanggan/reservasi.php">Reservasi Saya</a>
            <div class="nav-user">
                <span class="user-name">' . sanitize($user['nama']) . '</span>
                <span class="user-role">Pelanggan</span>
                <form method="POST" action="proses.php" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" style="background:none;border:none;color:white;font-size:12px;cursor:pointer;">Logout</button>
                </form>
            </div>';
        $sidebarItems = [
            ['page'=>'dashboard', 'icon'=>'&#128202;', 'label'=>'Dashboard', 'href'=>'pelanggan/dashboard.php'],
            ['page'=>'jadwal', 'icon'=>'&#128197;', 'label'=>'Jadwal', 'href'=>'pelanggan/jadwal.php'],
            ['page'=>'reservasi', 'icon'=>'&#128203;', 'label'=>'Reservasi Saya', 'href'=>'pelanggan/reservasi.php'],
            ['page'=>'profil', 'icon'=>'&#128100;', 'label'=>'Profil', 'href'=>'pelanggan/profil.php'],
        ];
    } elseif ($role === 'admin') {
        $navLinks = '
            <div class="nav-user">
                <span class="user-name">' . sanitize($user['nama']) . '</span>
                <span class="user-role" style="background:#FF9800;">Admin</span>
                <form method="POST" action="proses.php" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" style="background:none;border:none;color:white;font-size:12px;cursor:pointer;">Logout</button>
                </form>
            </div>';
        $sidebarItems = [
            ['section'=>'Menu Utama'],
            ['page'=>'dashboard', 'icon'=>'&#128202;', 'label'=>'Dashboard', 'href'=>'admin/dashboard.php'],
            ['page'=>'lapangan', 'icon'=>'&#9917;', 'label'=>'Lapangan', 'href'=>'admin/lapangan.php'],
            ['page'=>'jadwal', 'icon'=>'&#128197;', 'label'=>'Jadwal', 'href'=>'admin/jadwal.php'],
            ['page'=>'pelanggan', 'icon'=>'&#128101;', 'label'=>'Pelanggan', 'href'=>'admin/pelanggan.php'],
            ['section'=>'Operasional'],
            ['page'=>'reservasi', 'icon'=>'&#128203;', 'label'=>'Reservasi', 'href'=>'admin/reservasi.php'],
            ['page'=>'verifikasi', 'icon'=>'&#128179;', 'label'=>'Verifikasi', 'href'=>'admin/verifikasi.php'],
            ['section'=>'Laporan'],
            ['page'=>'laporan-reservasi', 'icon'=>'&#128196;', 'label'=>'Laporan Reservasi', 'href'=>'admin/laporan_reservasi.php'],
            ['page'=>'laporan-pendapatan', 'icon'=>'&#128176;', 'label'=>'Laporan Pendapatan', 'href'=>'admin/laporan_pendapatan.php'],
        ];
    } elseif ($role === 'pemilik') {
        $navLinks = '
            <div class="nav-user">
                <span class="user-name">' . sanitize($user['nama']) . '</span>
                <span class="user-role" style="background:#9C27B0;">Pemilik</span>
                <form method="POST" action="proses.php" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" style="background:none;border:none;color:white;font-size:12px;cursor:pointer;">Logout</button>
                </form>
            </div>';
        $sidebarItems = [
            ['page'=>'dashboard', 'icon'=>'&#128202;', 'label'=>'Dashboard', 'href'=>'pemilik/dashboard.php'],
            ['page'=>'laporan', 'icon'=>'&#128196;', 'label'=>'Laporan', 'href'=>'pemilik/laporan.php'],
            ['page'=>'statistik', 'icon'=>'&#128200;', 'label'=>'Statistik', 'href'=>'pemilik/statistik.php'],
        ];
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Futsal Center XYZ</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <div id="toast-container" class="toast-container">
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="toast <?php echo $flash['type']; ?>">
                <?php echo ($flash['type']==='success'?'&#10003;':($flash['type']==='error'?'&#10007;':'&#8505;')); ?>
                <?php echo sanitize($flash['message']); ?>
            </div>
        <?php endif; ?>
    </div>

    <nav class="navbar">
        <div class="logo"><a href="index.php" style="color:white;text-decoration:none;">&#9917; Futsal Center <span>XYZ</span></a></div>
        <div class="nav-links"><?php echo $navLinks; ?></div>
    </nav>

    <div class="layout">
        <div class="sidebar">
            <div class="menu-section">Menu</div>
            <?php foreach ($sidebarItems as $item): ?>
                <?php if (isset($item['section'])): ?>
                    <div class="menu-section"><?php echo $item['section']; ?></div>
                <?php else: ?>
                    <div class="menu-item <?php echo ($activePage === $item['page']) ? 'active' : ''; ?>">
                        <a href="<?php echo $item['href']; ?>" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:10px;">
                            <span><?php echo $item['icon']; ?></span> <?php echo $item['label']; ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="menu-section">Akun</div>
            <div class="menu-item">
                <form method="POST" action="proses.php" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" style="background:none;border:none;cursor:pointer;font-size:14px;color:#555;display:flex;align-items:center;gap:10px;">
                        <span>&#128682;</span> Logout
                    </button>
                </form>
            </div>
        </div>
        <div class="main-content">
            <?php $contentFn(); ?>
        </div>
    </div>

    <script>
    setTimeout(function() {
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(t) { t.style.display = 'none'; });
    }, 4000);
    </script>
    </body>
    </html>
    <?php
}
