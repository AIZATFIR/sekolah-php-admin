<?php
/**
 * Database Configuration & Connection Helper
 * Mendukung Dual-Driver: SQLite (Zero-config) & MySQL (Production)
 */

class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $connection = getenv('DB_CONNECTION') ?: 'sqlite';

        if ($connection === 'mysql') {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_DATABASE') ?: 'sekolah_db';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            // SQLite (Default)
            $isVercel = getenv('VERCEL') !== false || isset($_ENV['VERCEL']);
            if ($isVercel) {
                $dbPath = '/tmp/sekolah.db';
            } else {
                $dbDir = dirname(__DIR__) . '/database';
                if (!is_dir($dbDir)) {
                    @mkdir($dbDir, 0777, true);
                }
                $dbPath = $dbDir . '/school.db';
            }

            $isNewDb = !file_exists($dbPath) || filesize($dbPath) === 0;
            self::$pdo = new PDO("sqlite:" . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Enable foreign keys
            self::$pdo->exec("PRAGMA foreign_keys = ON;");

            if ($isNewDb) {
                self::initializeDatabase(self::$pdo);
            }
        }

        return self::$pdo;
    }

    private static function initializeDatabase(PDO $pdo): void {
        $schemaPath = dirname(__DIR__) . '/database/schema.sql';
        $seedPath = dirname(__DIR__) . '/database/seed.sql';

        if (file_exists($schemaPath)) {
            $schemaSql = file_get_contents($schemaPath);
            $pdo->exec($schemaSql);
        }

        if (file_exists($seedPath)) {
            $seedSql = file_get_contents($seedPath);
            $pdo->exec($seedSql);
        }

        // Generate dynamic attendance for the last 7 days including today
        self::seedDynamicAttendance($pdo);
    }

    private static function seedDynamicAttendance(PDO $pdo): void {
        $stmt = $pdo->query("SELECT id FROM siswa");
        $siswaIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($siswaIds)) return;

        $statuses = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Izin', 'Sakit', 'Alpa'];
        $insertStmt = $pdo->prepare("
            INSERT OR IGNORE INTO absensi_siswa (siswa_id, tanggal, status, waktu_scan, keterangan, metode)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $today = new DateTime();
        for ($i = 0; $i < 7; $i++) {
            $date = clone $today;
            $date->modify("-{$i} days");
            $dayOfWeek = (int)$date->format('N');
            if ($dayOfWeek >= 6) continue; // Lewati Sabtu & Minggu

            $dateStr = $date->format('Y-m-d');
            foreach ($siswaIds as $sId) {
                // Sebagian besar hadir, sedikit izin/sakit/alpa acak
                $randKey = array_rand($statuses);
                $status = $statuses[$randKey];
                $time = sprintf("06:%02d:%02d", rand(40, 59), rand(10, 59));
                $metode = (rand(1, 10) > 3) ? 'qr_scan' : 'manual';
                $keterangan = ($status === 'Sakit') ? 'Flu & demam' : (($status === 'Izin') ? 'Keperluan keluarga' : null);

                $insertStmt->execute([$sId, $dateStr, $status, $time, $keterangan, $metode]);
            }
        }

        // Seed Guru Attendance
        $stmtGuru = $pdo->query("SELECT id FROM guru");
        $guruIds = $stmtGuru->fetchAll(PDO::FETCH_COLUMN);
        $insertGuru = $pdo->prepare("
            INSERT OR IGNORE INTO absensi_guru (guru_id, tanggal, status, waktu_absen, keterangan)
            VALUES (?, ?, ?, ?, ?)
        ");

        for ($i = 0; $i < 7; $i++) {
            $date = clone $today;
            $date->modify("-{$i} days");
            $dayOfWeek = (int)$date->format('N');
            if ($dayOfWeek >= 6) continue;

            $dateStr = $date->format('Y-m-d');
            foreach ($guruIds as $gId) {
                $status = (rand(1, 15) === 1) ? 'Izin' : 'Hadir';
                $time = sprintf("06:%02d:%02d", rand(30, 55), rand(10, 59));
                $insertGuru->execute([$gId, $dateStr, $status, $time, $status === 'Izin' ? 'Dinas luar' : null]);
            }
        }
    }
}
