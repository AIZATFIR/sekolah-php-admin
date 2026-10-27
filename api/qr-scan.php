<?php
// api/qr-scan.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak didukung']);
    exit;
}

try {
    $db = Database::getConnection();
    $qrData = trim($_POST['qr_data'] ?? '');

    if (empty($qrData)) {
        echo json_encode(['success' => false, 'message' => 'Data QR / NISN kosong']);
        exit;
    }

    // Clean up QR prefix if any (e.g. "NISN:0071234501" or "EDU-0071234501")
    $nisn = preg_replace('/^(NISN:|EDU-|SISWA:)/i', '', $qrData);
    $nisn = trim($nisn);

    // Look up student
    $stmt = $db->prepare("
        SELECT s.id, s.nisn, s.nama, s.jenis_kelamin, k.nama_kelas
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        WHERE s.nisn = ? AND s.status = 'Aktif'
        LIMIT 1
    ");
    $stmt->execute([$nisn]);
    $siswa = $stmt->fetch();

    if (!$siswa) {
        echo json_encode([
            'success' => false,
            'message' => "Siswa dengan NISN [{$nisn}] tidak ditemukan atau sudah tidak aktif."
        ]);
        exit;
    }

    $today = date('Y-m-d');
    $time = date('H:i:s');

    // Check if already checked in today
    $checkStmt = $db->prepare("SELECT status, waktu_scan FROM absensi_siswa WHERE siswa_id = ? AND tanggal = ?");
    $checkStmt->execute([$siswa['id'], $today]);
    $existing = $checkStmt->fetch();

    $saveStmt = $db->prepare("
        INSERT INTO absensi_siswa (siswa_id, tanggal, status, waktu_scan, keterangan, metode)
        VALUES (?, ?, 'Hadir', ?, 'Presensi via QR Scanner', 'qr_scan')
        ON CONFLICT(siswa_id, tanggal) DO UPDATE SET
            status = 'Hadir',
            waktu_scan = excluded.waktu_scan,
            metode = 'qr_scan'
    ");
    $saveStmt->execute([$siswa['id'], $today, $time]);

    $isUpdate = ($existing !== false);

    echo json_encode([
        'success' => true,
        'message' => $isUpdate ? "Presensi diperbarui: Hadir pukul {$time}" : "Presensi berhasil dicatat: Hadir pukul {$time}",
        'data' => [
            'id' => $siswa['id'],
            'nama' => $siswa['nama'],
            'nisn' => $siswa['nisn'],
            'kelas' => $siswa['nama_kelas'],
            'jenis_kelamin' => $siswa['jenis_kelamin'],
            'waktu' => $time,
            'status' => 'Hadir'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
