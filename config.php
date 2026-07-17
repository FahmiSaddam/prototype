<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'futsal_xyz');
define('DB_USER', 'root');
define('DB_PASS', '');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $e) {
            die("Koneksi database gagal. Pastikan MySQL berjalan dan database 'futsal_xyz' sudah diimport.<br>Error: " . $e->getMessage());
        }
    }
    return $pdo;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['user_role'], (array)$roles)) {
        header('Location: index.php');
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function formatRupiah($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function formatDate($dateStr) {
    $months = [1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    $d = strtotime($dateStr);
    return date('d', $d) . ' ' . $months[(int)date('m', $d)] . ' ' . date('Y', $d);
}

function formatMonth($monthStr) {
    $months = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    [$y, $m] = explode('-', $monthStr);
    return $months[(int)$m] . ' ' . $y;
}

function getDayType($dateStr) {
    $day = date('w', strtotime($dateStr));
    return ($day == 0 || $day == 6) ? 'weekend' : 'weekday';
}

function getTimeSlots() {
    $slots = [];
    for ($h = 8; $h < 22; $h++) {
        $slots[] = [
            'start' => sprintf('%02d:00', $h),
            'end'   => sprintf('%02d:00', $h + 1),
            'label' => sprintf('%02d.00-%02d.00', $h, $h + 1)
        ];
    }
    return $slots;
}

function statusBadge($status) {
    $map = [
        'pending_payment'      => ['badge-warning', 'Pending Payment'],
        'waiting_verification' => ['badge-info', 'Waiting Verification'],
        'confirmed'            => ['badge-success', 'Confirmed'],
        'completed'            => ['badge-success', 'Completed'],
        'cancelled'            => ['badge-danger', 'Cancelled'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-secondary', $status];
    return "<span class=\"badge {$cls}\">{$label}</span>";
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        $icon = $flash['type'] === 'success' ? '&#10003;' : ($flash['type'] === 'error' ? '&#10007;' : '&#8505;');
        echo "<div class=\"alert alert-{$flash['type']}\">{$icon} " . sanitize($flash['message']) . "</div>";
    }
}

function getScheduleGrid($tanggal) {
    $pdo = db();
    $lapangan = $pdo->query("SELECT * FROM lapangan WHERE status='aktif' ORDER BY id")->fetchAll();

    $stmt = $pdo->prepare("SELECT lapangan_id, jam_mulai, jam_selesai FROM reservasi WHERE tanggal=? AND status NOT IN ('cancelled')");
    $stmt->execute([$tanggal]);
    $reservasi = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT lapangan_id, jam_mulai, jam_selesai FROM slot_blocked WHERE tanggal=?");
    $stmt->execute([$tanggal]);
    $blocked = $stmt->fetchAll();

    $slots = getTimeSlots();
    $grid = [];

    foreach ($lapangan as $field) {
        $fid = $field['id'];
        $grid[$fid] = [];
        foreach ($slots as $slot) {
            $status = 'available';
            foreach ($blocked as $b) {
                if ($b['lapangan_id'] == $fid && $slot['start'] >= substr($b['jam_mulai'],0,5) && $slot['start'] < substr($b['jam_selesai'],0,5)) {
                    $status = 'blocked'; break;
                }
            }
            if ($status === 'available') {
                foreach ($reservasi as $r) {
                    if ($r['lapangan_id'] == $fid && $slot['start'] >= substr($r['jam_mulai'],0,5) && $slot['start'] < substr($r['jam_selesai'],0,5)) {
                        $status = 'booked'; break;
                    }
                }
            }
            $grid[$fid][$slot['start']] = $status;
        }
    }

    return ['lapangan' => $lapangan, 'slots' => $slots, 'grid' => $grid];
}
