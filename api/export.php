<?php
// api/export.php
require_once __DIR__ . '/../config/database.php';

$type = $_GET['type'] ?? 'siswa';
$db = Database::getConnection();

// Send CSV headers with UTF-8 BOM for Excel compatibility
$filename = "export_{$type}_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");

$output = fopen('php://output', 'w');
// Write UTF-8 BOM
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($type === 'siswa') {
    fputcsv($output, ['No', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Kelas', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'No Telp Ortu', 'Status']);

    $stmt = $db->query("
        SELECT s.*, k.nama_kelas 
        FROM siswa s 
        JOIN kelas k ON s.kelas_id = k.id 
        ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama ASC
    ");
    $no = 1;
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $no++,
            $row['nisn'],
            $row['nama'],
            $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan',
            $row['nama_kelas'],
            $row['tempat_lahir'],
            $row['tanggal_lahir'],
            $row['alamat'],
            $row['no_telp_ortu'],
            $row['status']
        ]);
    }
} elseif ($type === 'guru') {
    fputcsv($output, ['No', 'NIP', 'Nama Lengkap', 'Jenis Kelamin', 'Mata Pelajaran', 'No HP', 'Email', 'Status']);

    $stmt = $db->query("SELECT * FROM guru ORDER BY nama ASC");
    $no = 1;
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $no++,
            $row['nip'],
            $row['nama'],
            $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan',
            $row['mata_pelajaran'],
            $row['no_hp'],
            $row['email'],
            $row['status']
        ]);
    }
} elseif ($type === 'absensi') {
    $startDate = $_GET['start_date'] ?? date('Y-m-01');
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

    fputcsv($output, ['No', 'Tanggal', 'NISN', 'Nama Siswa', 'Kelas', 'Status Kehadiran', 'Waktu Presensi', 'Metode', 'Keterangan']);

    $sql = "
        SELECT a.tanggal, a.status, a.waktu_scan, a.metode, a.keterangan, s.nisn, s.nama, k.nama_kelas
        FROM absensi_siswa a
        JOIN siswa s ON a.siswa_id = s.id
        JOIN kelas k ON s.kelas_id = k.id
        WHERE a.tanggal BETWEEN ? AND ?
    ";
    $params = [$startDate, $endDate];

    if ($kelasId) {
        $sql .= " AND s.kelas_id = ?";
        $params[] = $kelasId;
    }

    $sql .= " ORDER BY a.tanggal DESC, k.nama_kelas ASC, s.nama ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $no = 1;
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $no++,
            $row['tanggal'],
            $row['nisn'],
            $row['nama'],
            $row['nama_kelas'],
            $row['status'],
            $row['waktu_scan'] ?: '-',
            $row['metode'] === 'qr_scan' ? 'Scan QR' : 'Manual',
            $row['keterangan'] ?: '-'
        ]);
    }
}

fclose($output);
exit;
