<?php
// pages/kartu-pelajar.php
$pageTitle = 'Kartu Pelajar Digital Siswa';
require_once __DIR__ . '/../includes/header.php';

$filterNisn = $_GET['nisn'] ?? null;
$filterKelas = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

$kelasList = $db->query("SELECT * FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();

$query = "
    SELECT s.*, k.nama_kelas, k.tingkat
    FROM siswa s
    JOIN kelas k ON s.kelas_id = k.id
    WHERE s.status = 'Aktif'
";
$params = [];

if ($filterNisn) {
    $query .= " AND s.nisn = ? ";
    $params[] = $filterNisn;
} elseif ($filterKelas) {
    $query .= " AND s.kelas_id = ? ";
    $params[] = $filterKelas;
}

$query .= " ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama ASC LIMIT 12";
$stmt = $db->prepare($query);
$stmt->execute($params);
$siswaCards = $stmt->fetchAll();
?>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="id-card" style="color: #2563eb;"></i>
            <span>Kartu Tanda Pelajar Digital & QR Presensi</span>
        </div>
        <div class="card-actions">
            <form method="GET" action="index.php" style="display: flex; gap: 8px;">
                <input type="hidden" name="page" value="kartu-pelajar">
                <select name="kelas_id" class="form-select" style="width: auto; padding: 7px 12px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKelas === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button class="btn btn-primary" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Cetak Semua Kartu</span>
            </button>
        </div>
    </div>
    <div class="card-body" style="background: #f8fafc;">
        <p style="font-size: 0.875rem; color: #64748b;">
            Setiap kartu dilengkapi <strong>QR Code Unik</strong> berformat <code>NISN</code> yang dapat dipindai secara langsung oleh sistem Scanner Absensi Sekolah. Kartu telah dioptimasi dengan rasio standar ID Card (CR80).
        </p>
    </div>
</div>

<!-- Grid Kartu Pelajar -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px; justify-items: center;">
    <?php if (empty($siswaCards)): ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: var(--color-text-muted);">
        Tidak ada data siswa ditemukan untuk kriteria filter ini.
    </div>
    <?php else: ?>
        <?php foreach ($siswaCards as $idx => $s): ?>
        <div class="id-card" id="card-<?= $s['id'] ?>">
            <div class="id-card-header">
                <div>
                    <div class="id-school-name"><?= htmlspecialchars($school['nama_sekolah']) ?></div>
                    <div style="font-size: 0.65rem; color: #bfdbfe;">KARTU TANDA PELAJAR RESMI</div>
                </div>
                <div class="id-badge">AKTIF</div>
            </div>

            <div class="id-card-body">
                <div class="id-avatar">
                    <!-- SVG Avatar Pria / Wanita -->
                    <?php if ($s['jenis_kelamin'] === 'L'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M12 11c1.5 0 3-1 3-3s-1.5-3-3-3-3 1-3 3 1.5 3 3 3z"></path>
                        </svg>
                    <?php endif; ?>
                </div>

                <div class="id-meta">
                    <div class="id-student-name" title="<?= htmlspecialchars($s['nama']) ?>"><?= htmlspecialchars($s['nama']) ?></div>
                    <div class="id-nisn">NISN: <?= htmlspecialchars($s['nisn']) ?></div>
                    <span class="id-class"><?= htmlspecialchars($s['nama_kelas']) ?></span>
                </div>
            </div>

            <div class="id-card-footer">
                <div class="id-qr-box" id="qrcode-<?= $s['id'] ?>" data-nisn="<?= htmlspecialchars($s['nisn']) ?>">
                    <!-- QR Code auto generated via script -->
                </div>
                <div class="id-validity">
                    <div>Berlaku s/d:</div>
                    <div style="font-weight: 700; color: #fff;">30 JUNI <?= date('Y') + (13 - (int)$s['tingkat']) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Generate QR Code on each student ID card
    const qrContainers = document.querySelectorAll('.id-qr-box');
    qrContainers.forEach(container => {
        const nisn = container.getAttribute('data-nisn');
        if (nisn && window.QRCode) {
            new QRCode(container, {
                text: nisn,
                width: 60,
                height: 60,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
