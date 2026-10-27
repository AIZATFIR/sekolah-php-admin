<?php
// api/absensi.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak didukung']);
    exit;
}

try {
    $db = Database::getConnection();

    $siswaId = isset($_POST['siswa_id']) ? (int)$_POST['siswa_id'] : null;
    $guruId = isset($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Hadir';
    $keterangan = $_POST['keterangan'] ?? null;
    $metode = $_POST['metode'] ?? 'manual';
    $waktu = date('H:i:s');

    if ($siswaId) {
        $stmt = $db->prepare("
            INSERT INTO absensi_siswa (siswa_id, tanggal, status, waktu_scan, keterangan, metode)
            VALUES (?, ?, ?, ?, ?, ?)
            ON CONFLICT(siswa_id, tanggal) DO UPDATE SET
                status = excluded.status,
                waktu_scan = excluded.waktu_scan,
                keterangan = excluded.keterangan,
                metode = excluded.metode
        ");
        $stmt->execute([$siswaId, $tanggal, $status, $waktu, $keterangan, $metode]);

        echo json_encode([
            'success' => true,
            'message' => "Presensi siswa berhasil diperbarui ke: $status",
            'waktu' => $waktu
        ]);
        exit;
    }

    if ($guruId) {
        $stmt = $db->prepare("
            INSERT INTO absensi_guru (guru_id, tanggal, status, waktu_absen, keterangan)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT(guru_id, tanggal) DO UPDATE SET
                status = excluded.status,
                waktu_absen = excluded.waktu_absen,
                keterangan = excluded.keterangan
        ");
        $stmt->execute([$guruId, $tanggal, $status, $waktu, $keterangan]);

        echo json_encode([
            'success' => true,
            'message' => "Presensi guru berhasil diperbarui ke: $status",
            'waktu' => $waktu
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Parameter siswa_id atau guru_id diperlukan']);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
