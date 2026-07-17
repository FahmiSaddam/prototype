<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('admin');

renderLayout('admin', 'jadwal', function() {
    $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
    $jadwal = getScheduleGrid($tanggal);
    $lapanganList = db()->query("SELECT * FROM lapangan WHERE status='aktif' ORDER BY id")->fetchAll();
    ?>
    <h2 style="margin-bottom:20px;">Kelola Jadwal</h2>
    <div class="card">
        <form method="GET" class="date-picker" style="margin-bottom:16px;">
            <label>Tanggal:</label>
            <input type="date" name="tanggal" class="form-control" style="width:200px;" value="<?php echo $tanggal; ?>">
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
            <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('block-modal').classList.add('active')">&#128683; Blokir Slot</button>
        </form>

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
    </div>

    <!-- Modal Blokir -->
    <div id="block-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <span>Blokir Jadwal</span>
                <button class="modal-close" onclick="document.getElementById('block-modal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" action="../proses.php">
                <input type="hidden" name="action" value="block_slot">
                <div class="form-group">
                    <label>Lapangan</label>
                    <select name="lapangan_id" class="form-control" required>
                        <?php foreach ($lapanganList as $l): ?>
                            <option value="<?php echo $l['id']; ?>"><?php echo sanitize($l['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>" required>
                </div>
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <select name="jam_mulai" class="form-control" required>
                        <?php foreach ($jadwal['slots'] as $slot): ?>
                            <option value="<?php echo $slot['start']; ?>"><?php echo $slot['start']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <select name="jam_selesai" class="form-control" required>
                        <?php foreach ($jadwal['slots'] as $slot): ?>
                            <option value="<?php echo $slot['end']; ?>"><?php echo $slot['end']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alasan</label>
                    <input type="text" name="alasan" class="form-control" placeholder="Contoh: Maintenance" value="Maintenance">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('block-modal').classList.remove('active')">Batal</button>
                    <button type="submit" class="btn btn-warning">Blokir</button>
                </div>
            </form>
        </div>
    </div>
    <?php
});
