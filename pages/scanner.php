<?php
// pages/scanner.php
$pageTitle = 'Scanner QR Presensi Real-Time';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
    <!-- Sisi Kiri: Scanner Kamera & Input Cepat -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="camera" style="color: #10b981;"></i>
                    <span>Kamera Pemindai QR Presensi</span>
                </div>
                <span class="badge badge-hadir" id="scanner-status">Siap Memindai</span>
            </div>
            <div class="card-body">
                <div class="scanner-viewport" id="reader">
                    <!-- Scanner Viewport injected by Html5Qrcode -->
                    <div class="scan-laser" id="scan-laser"></div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 16px; justify-content: center;">
                    <button class="btn btn-secondary btn-sm" id="btn-toggle-camera">
                        <i data-lucide="refresh-cw"></i>
                        <span>Restart Kamera</span>
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="playBeep(true)">
                        <i data-lucide="volume-2"></i>
                        <span>Tes Audio Chime</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Manual Fast NISN Barcode Input -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="barcode" style="color: #2563eb;"></i>
                    <span>Input Cepat / Barcode Scanner Fisik</span>
                </div>
            </div>
            <div class="card-body">
                <form id="manual-scan-form" onsubmit="handleManualScan(event)">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="manual-nisn" class="form-input" placeholder="Ketik atau scan barcode NISN (cth: 0071234501)" autofocus autocomplete="off">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="check"></i>
                            <span>Catat</span>
                        </button>
                    </div>
                </form>
                <div style="font-size: 0.775rem; color: #64748b; margin-top: 8px;">
                    Mendukung scanner barcode USB / Bluetooth tipe keyboard wedge secara otomatis.
                </div>
            </div>
        </div>
    </div>

    <!-- Sisi Kanan: Hasil Scan Terbaru & Riwayat Sesi -->
    <div>
        <!-- Kartu Hasil Scan Terkini -->
        <div class="card" id="scan-result-card" style="display: none; border-left: 5px solid #10b981; animation: toastSlide 0.3s ease;">
            <div class="card-body" style="display: flex; align-items: center; gap: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; flex-shrink: 0;">
                    <i data-lucide="check"></i>
                </div>
                <div style="flex: 1;">
                    <span class="badge badge-hadir" style="margin-bottom: 4px;">Presensi Berhasil</span>
                    <h3 id="res-nama" style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 2px;">-</h3>
                    <div style="font-size: 0.85rem; color: #64748b;">
                        NISN: <span id="res-nisn" style="font-family: monospace; font-weight: 600;">-</span> • 
                        Kelas: <span id="res-kelas" style="font-weight: 600; color: #334155;">-</span> • 
                        Waktu: <span id="res-waktu" style="font-family: monospace;">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Pemindaian Sesi Ini -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i data-lucide="list-ordered" style="color: #6366f1;"></i>
                    <span>Aktivitas Scan Sesi Ini</span>
                </div>
                <span class="badge" style="background: #f1f5f9; color: #334155;" id="session-counter">0 Siswa</span>
            </div>
            <div class="table-responsive">
                <table class="data-table" id="session-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="session-log-body">
                        <tr id="empty-session-row">
                            <td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 32px;">
                                Belum ada siswa yang dipindai pada sesi ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrCode = null;
let isProcessing = false;
let sessionCount = 0;

async function sendScanData(nisn) {
    if (isProcessing) return;
    isProcessing = true;

    try {
        const formData = new FormData();
        formData.append('qr_data', nisn);

        const res = await fetch('api/qr-scan.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            playBeep(true);
            showToast(result.message, 'success');

            const data = result.data;
            // Update display card
            document.getElementById('scan-result-card').style.display = 'block';
            document.getElementById('res-nama').textContent = data.nama;
            document.getElementById('res-nisn').textContent = data.nisn;
            document.getElementById('res-kelas').textContent = data.kelas;
            document.getElementById('res-waktu').textContent = data.waktu;

            // Prepend to session table
            const emptyRow = document.getElementById('empty-session-row');
            if (emptyRow) emptyRow.remove();

            const tbody = document.getElementById('session-log-body');
            const newRow = document.createElement('tr');
            newRow.style.animation = 'toastSlide 0.3s ease';
            newRow.innerHTML = `
                <td style="font-family: monospace; font-weight: 600; color: #475569;">${data.waktu}</td>
                <td style="font-family: monospace;">${data.nisn}</td>
                <td style="font-weight: 600;">${data.nama}</td>
                <td><span class="badge" style="background: #f1f5f9; color: #334155;">${data.kelas}</span></td>
                <td><span class="badge badge-hadir">Hadir</span></td>
            `;
            tbody.insertBefore(newRow, tbody.firstChild);

            sessionCount++;
            document.getElementById('session-counter').textContent = `${sessionCount} Siswa`;
        } else {
            playBeep(false);
            showToast(result.message || 'QR / NISN tidak valid', 'error');
        }
    } catch (e) {
        playBeep(false);
        showToast('Terjadi kesalahan saat memproses data presensi', 'error');
        console.error(e);
    } finally {
        setTimeout(() => {
            isProcessing = false;
        }, 1500); // 1.5s debounce to avoid double scan
    }
}

function handleManualScan(e) {
    e.preventDefault();
    const input = document.getElementById('manual-nisn');
    const val = input.value.trim();
    if (val) {
        sendScanData(val);
        input.value = '';
    }
}

// Initialize Html5Qrcode
document.addEventListener('DOMContentLoaded', () => {
    if (window.Html5Qrcode) {
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
            sendScanData(decodedText);
        }).catch(err => {
            console.warn("Camera stream cannot be started or permission denied", err);
            document.getElementById('scanner-status').textContent = 'Kamera Standby (Input Manual Aktif)';
            document.getElementById('scanner-status').className = 'badge';
        });
    }

    const toggleBtn = document.getElementById('btn-toggle-camera');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            location.reload();
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
