<?php
// pages/laporan.php
$pageTitle = 'Rekapitulasi & Laporan Presensi';
require_once __DIR__ . '/../includes/header.php';

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

$kelasList = $db->query("SELECT * FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();

// Query rekapitulasi siswa
$sql = "
    SELECT 
        s.id, s.nisn, s.nama, s.jenis_kelamin, k.nama_kelas, k.wali_nama,
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN a.status = 'Alpa' THEN 1 ELSE 0 END) as alpa,
        count(a.id) as total_hari
    FROM siswa s
    JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND (a.tanggal BETWEEN ? AND ?)
    WHERE s.status = 'Aktif'
";
$params = [$startDate, $endDate];

if ($kelasId) {
    $sql .= " AND s.kelas_id = ? ";
    $params[] = $kelasId;
}

$sql .= " GROUP BY s.id ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama ASC ";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rekapList = $stmt->fetchAll();

// Total summary
$grandHadir = 0; $grandIzin = 0; $grandSakit = 0; $grandAlpa = 0;
foreach ($rekapList as $r) {
    $grandHadir += $r['hadir'];
    $grandIzin += $r['izin'];
    $grandSakit += $r['sakit'];
    $grandAlpa += $r['alpa'];
}

$selectedKelasNama = 'Semua Kelas';
$selectedWali = 'Wali Kelas Terkait';
if ($kelasId) {
    foreach ($kelasList as $k) {
        if ((int)$k['id'] === $kelasId) {
            $selectedKelasNama = $k['nama_kelas'];
            $selectedWali = $k['wali_nama'];
            break;
        }
    }
}
?>

<!-- Filter Card (Hidden in Print) -->
<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="filter" style="color: #2563eb;"></i>
            <span>Filter Periode Rekapitulasi</span>
        </div>
        <div class="card-actions">
            <a href="api/export.php?type=absensi&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&kelas_id=<?= $kelasId ?>" class="btn btn-secondary">
                <i data-lucide="download"></i>
                <span>Download File CSV</span>
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Cetak Lembar Resmi (PDF)</span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="index.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 120px; gap: 16px; align-items: flex-end;">
            <input type="hidden" name="page" value="laporan">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tanggal Mulai:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-input">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tanggal Akhir:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-input">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Filter Kelas:</label>
                <select name="kelas_id" class="form-select">
                    <option value="">Semua Rombel Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelasId === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 42px;">
                <i data-lucide="search"></i>
                <span>Filter</span>
            </button>
        </form>
    </div>
</div>

<!-- Lembar Laporan Cetak Resmi -->
<div class="card" style="background: #ffffff; padding: 24px; border: 1px solid var(--color-border);">
    <!-- Kop Surat Resmi Berstandar Sekolah Indonesia -->
    <div class="kop-surat">
        <svg class="kop-logo" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
        </svg>
        <div class="kop-header">
            <h2>PEMERINTAH DAERAH PROVINSI <?= strtoupper(htmlspecialchars($school['provinsi'])) ?></h2>
            <h2>DINAS PENDIDIKAN & KEBUDAYAAN</h2>
            <h1><?= strtoupper(htmlspecialchars($school['nama_sekolah'])) ?></h1>
            <p><?= htmlspecialchars($school['alamat']) ?>, <?= htmlspecialchars($school['kabupaten_kota']) ?> | Telp: <?= htmlspecialchars($school['no_telp']) ?> | NPSN: <?= htmlspecialchars($school['npsn']) ?></p>
        </div>
    </div>

    <!-- Judul Laporan -->
    <div style="text-align: center; margin-bottom: 24px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: underline;">
            LAPORAN REKAPITULASI KEHADIRAN SISWA
        </h3>
        <p style="font-size: 0.85rem; color: #475569; margin-top: 4px;">
            Periode: <strong><?= date('d/m/Y', strtotime($startDate)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($endDate)) ?></strong> 
            • Kelas: <strong><?= htmlspecialchars($selectedKelasNama) ?></strong>
        </p>
    </div>

    <!-- Tabel Rekapitulasi -->
    <table class="data-table" style="width: 100%; border: 1px solid #000; margin-bottom: 32px;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="border: 1px solid #000; text-align: center; width: 40px;">No</th>
                <th style="border: 1px solid #000;">NISN</th>
                <th style="border: 1px solid #000;">Nama Lengkap Siswa</th>
                <th style="border: 1px solid #000; text-align: center;">Kelas</th>
                <th style="border: 1px solid #000; text-align: center; width: 50px;">H</th>
                <th style="border: 1px solid #000; text-align: center; width: 50px;">I</th>
                <th style="border: 1px solid #000; text-align: center; width: 50px;">S</th>
                <th style="border: 1px solid #000; text-align: center; width: 50px;">A</th>
                <th style="border: 1px solid #000; text-align: center; width: 90px;">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rekapList)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 24px; border: 1px solid #000;">
                    Tidak ada rekaman presensi pada rentang tanggal ini.
                </td>
            </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($rekapList as $r): ?>
                <?php 
                $totalHadir = (int)$r['hadir'];
                $totalAbsen = (int)($r['hadir'] + $r['izin'] + $r['sakit'] + $r['alpa']);
                $pct = $totalAbsen > 0 ? round(($totalHadir / $totalAbsen) * 100, 1) : 0;
                ?>
                <tr>
                    <td style="border: 1px solid #000; text-align: center;"><?= $no++ ?></td>
                    <td style="border: 1px solid #000; font-family: monospace;"><?= htmlspecialchars($r['nisn']) ?></td>
                    <td style="border: 1px solid #000; font-weight: 600;"><?= htmlspecialchars($r['nama']) ?></td>
                    <td style="border: 1px solid #000; text-align: center;"><?= htmlspecialchars($r['nama_kelas']) ?></td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: 700; color: #059669;"><?= $r['hadir'] ?></td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: 700; color: #d97706;"><?= $r['izin'] ?></td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: 700; color: #4f46e5;"><?= $r['sakit'] ?></td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: 700; color: #dc2626;"><?= $r['alpa'] ?></td>
                    <td style="border: 1px solid #000; text-align: center; font-weight: 700;">
                        <?= $pct ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
                <!-- Baris Total -->
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td colspan="4" style="border: 1px solid #000; text-align: right; padding-right: 14px;">TOTAL KESELURUHAN:</td>
                    <td style="border: 1px solid #000; text-align: center; color: #059669;"><?= $grandHadir ?></td>
                    <td style="border: 1px solid #000; text-align: center; color: #d97706;"><?= $grandIzin ?></td>
                    <td style="border: 1px solid #000; text-align: center; color: #4f46e5;"><?= $grandSakit ?></td>
                    <td style="border: 1px solid #000; text-align: center; color: #dc2626;"><?= $grandAlpa ?></td>
                    <td style="border: 1px solid #000; text-align: center;">-</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 40px;">
        Keterangan: <strong>H</strong> = Hadir, <strong>I</strong> = Izin, <strong>S</strong> = Sakit, <strong>A</strong> = Alpa (Tanpa Keterangan).
    </div>

    <!-- Lembar Tanda Tangan Resmi -->
    <div style="display: flex; justify-content: space-between; padding: 0 40px; page-break-inside: avoid;">
        <div style="text-align: center;">
            <p style="font-size: 0.85rem; margin-bottom: 60px;">
                Mengetahui,<br>
                Wali Kelas
            </p>
            <p style="font-size: 0.85rem; font-weight: 700; text-decoration: underline; margin-bottom: 2px;">
                <?= htmlspecialchars($selectedWali) ?>
            </p>
            <p style="font-size: 0.775rem; color: #475569;">Tenaga Pendidik</p>
        </div>

        <div style="text-align: center;">
            <p style="font-size: 0.85rem; margin-bottom: 60px;">
                <?= htmlspecialchars($school['kabupaten_kota']) ?>, <?= date('d F Y') ?><br>
                Kepala <?= htmlspecialchars($school['nama_sekolah']) ?>
            </p>
            <p style="font-size: 0.85rem; font-weight: 700; text-decoration: underline; margin-bottom: 2px;">
                <?= htmlspecialchars($school['kepala_sekolah']) ?>
            </p>
            <p style="font-size: 0.775rem; color: #475569;">NIP. <?= htmlspecialchars($school['nip_kepala_sekolah']) ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
