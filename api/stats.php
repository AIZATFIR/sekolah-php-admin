<?php
// api/stats.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    $today = date('Y-m-d');

    // 1. Status presensi hari ini
    $todayStatusQuery = "
        SELECT 
            SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa,
            count(*) as total_recorded
        FROM absensi_siswa
        WHERE tanggal = ?
    ";
    $stmt = $db->prepare($todayStatusQuery);
    $stmt->execute([$today]);
    $todayStatus = $stmt->fetch() ?: ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total_recorded' => 0];

    // 2. Tren presensi 7 hari terakhir
    $trendQuery = "
        SELECT 
            tanggal,
            SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa
        FROM absensi_siswa
        GROUP BY tanggal
        ORDER BY tanggal DESC
        LIMIT 7
    ";
    $trendStmt = $db->query($trendQuery);
    $trendData = array_reverse($trendStmt->fetchAll());

    // 3. Distribusi siswa per kelas
    $classQuery = "
        SELECT k.nama_kelas, count(s.id) as total_siswa
        FROM kelas k
        LEFT JOIN siswa s ON s.kelas_id = k.id AND s.status = 'Aktif'
        GROUP BY k.id, k.nama_kelas
        ORDER BY k.tingkat ASC, k.nama_kelas ASC
    ";
    $classStmt = $db->query($classQuery);
    $classDistribution = $classStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'today' => $todayStatus,
        'trend' => $trendData,
        'class_distribution' => $classDistribution
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
