<?php
require_once __DIR__ . '/../includes/layout.php';
requireRole('pelanggan');

renderLayout('pelanggan', 'jadwal', function() {
    $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
    $jadwal = getScheduleGrid($tanggal);
    $lapanganList = $jadwal['lapangan'];
    ?>
    <h2 style="margin-bottom:20px;">Jadwal Ketersediaan</h2>
    <div class="card">
        <form method="GET" class="date-picker" style="margin-bottom:16px;">
            <label>Pilih Tanggal:</label>
            <input type="date" name="tanggal" class="form-control" style="width:200px;" value="<?php echo $tanggal; ?>">
            <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
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
                    $onclick = '';
                    if ($status === 'available') {
                        $onclick = "onclick=\"document.getElementById('modal-field').value='{$field['id']}';document.getElementById('modal-date').value='{$tanggal}';document.getElementById('modal-start').value='{$slot['start']}';document.getElementById('modal-end').value='{$slot['end']}';document.getElementById('modal-daytype').value='" . getDayType($tanggal) . "';document.getElementById('modal-tarif').value='" . (getDayType($tanggal)==='weekend' ? $field['tarif_weekend'] : $field['tarif_weekday']) . "';document.getElementById('reservasi-modal').classList.add('active');\"";
                    }
                ?>
                    <div class="slot <?php echo $status; ?>" <?php echo $onclick; ?>><?php echo $label; ?></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:12px; font-size:12px; color:#777;">
            <span style="color:#2e7d32;">&#9632;</span> Tersedia (klik untuk memesan) &nbsp;
            <span style="color:#c62828;">&#9632;</span> Terpesan &nbsp;
            <span style="color:#999;">&#9632;</span> Diblokir
        </p>
    </div>

    <!-- Modal Reservasi -->
    <div id="reservasi-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <span>Konfirmasi Reservasi</span>
                <button class="modal-close" onclick="document.getElementById('reservasi-modal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" action="../proses.php">
                <input type="hidden" name="action" value="create_reservasi">
                <input type="hidden" name="lapangan_id" id="modal-field">
                <input type="hidden" name="tanggal" id="modal-date">
                <input type="hidden" name="jam_mulai" id="modal-start">
                <input type="hidden" name="jam_selesai" id="modal-end">

                <div class="summary-box">
                    <div class="summary-row"><span>Tanggal:</span><span id="modal-date-display"></span></div>
                    <div class="summary-row"><span>Jam:</span><span id="modal-time-display"></span></div>
                    <div class="summary-row"><span>Tipe Hari:</span><span id="modal-daytype-display"></span></div>
                    <div class="summary-row"><span>Durasi:</span><span>1 jam</span></div>
                    <div class="summary-row total"><span>TOTAL:</span><span id="modal-total-display"></span></div>
                </div>
                <div class="alert alert-info">&#8505; Setelah reservasi dibuat, Anda memiliki waktu 1x24 jam untuk melakukan pembayaran.</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('reservasi-modal').classList.remove('active')">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi Reservasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Override onclick to also update display
    document.querySelectorAll('.slot.available').forEach(function(el) {
        el.addEventListener('click', function() {
            var date = document.getElementById('modal-date').value;
            var start = document.getElementById('modal-start').value;
            var end = document.getElementById('modal-end').value;
            var tarif = parseInt(document.getElementById('modal-tarif').value);
            var daytype = document.getElementById('modal-daytype').value;
            document.getElementById('modal-date-display').textContent = date;
            document.getElementById('modal-time-display').textContent = start + ' - ' + end;
            document.getElementById('modal-daytype-display').textContent = daytype === 'weekend' ? 'Weekend' : 'Weekday';
            document.getElementById('modal-total-display').textContent = 'Rp ' + tarif.toLocaleString('id-ID');
        });
    });
    </script>
    <?php
});
