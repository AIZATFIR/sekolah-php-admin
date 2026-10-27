<?php
// pages/dashboard.php
$pageTitle = 'Dashboard Ringkasan Sekolah';
require_once __DIR__ . '/../includes/header.php';

$today = date('Y-m-d');

// Metrik Utama
$totalSiswa = (int)$db->query("SELECT count(*) FROM siswa WHERE status = 'Aktif'")->fetchColumn();
$totalGuru = (int)$db->query("SELECT count(*) FROM guru WHERE status = 'Aktif'")->fetchColumn();

// Presensi Hari Ini
$stmtPresensi = $db->prepare("
    SELECT 
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'Alpa' THEN 1 ELSE 0 END) as alpa,
        count(*) as total
    FROM absensi_siswa
    WHERE tanggal = ?
");
$stmtPresensi->execute([$today]);
$presensiHariIni = $stmtPresensi->fetch() ?: ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0];

$hadirCount = (int)($presensiHariIni['hadir'] ?? 0);
$persenHadir = $totalSiswa > 0 ? round(($hadirCount / $totalSiswa) * 100, 1) : 0;
$tidakHadirCount = (int)(($presensiHariIni['izin'] ?? 0) + ($presensiHariIni['sakit'] ?? 0) + ($presensiHariIni['alpa'] ?? 0));

// Daftar siswa yang baru saja presensi hari ini
$stmtRecent = $db->prepare("
    SELECT a.waktu_scan, a.status, a.metode, s.nisn, s.nama, k.nama_kelas
    FROM absensi_siswa a
    JOIN siswa s ON a.siswa_id = s.id
    JOIN kelas k ON s.kelas_id = k.id
    WHERE a.tanggal = ?
    ORDER BY a.waktu_scan DESC
    LIMIT 6
");
$stmtRecent->execute([$today]);
$recentPresensi = $stmtRecent->fetchAll();
?>

<!-- KPI Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-details">
            <div class="stat-label">Total Siswa Aktif</div>
            <div class="stat-value"><?= number_format($totalSiswa) ?></div>
            <div class="stat-subtext">Terdaftar di 6 rombel kelas</div>
        </div>
        <div class="stat-icon primary">
            <i data-lucide="graduation-cap"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <div class="stat-label">Tenaga Pengajar (Guru)</div>
            <div class="stat-value"><?= number_format($totalGuru) ?></div>
            <div class="stat-subtext">Guru aktif mengajar</div>
        </div>
        <div class="stat-icon success">
            <i data-lucide="award"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <div class="stat-label">Kehadiran Hari Ini</div>
            <div class="stat-value"><?= $persenHadir ?>%</div>
            <div class="stat-subtext"><?= $hadirCount ?> dari <?= $totalSiswa ?> siswa hadir</div>
        </div>
        <div class="stat-icon warning">
            <i data-lucide="check-circle-2"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-details">
            <div class="stat-label">Absen / Tidak Masuk</div>
            <div class="stat-value"><?= $tidakHadirCount ?></div>
            <div class="stat-subtext"><?= (int)$presensiHariIni['izin'] ?> Izin, <?= (int)$presensiHariIni['sakit'] ?> Sakit, <?= (int)$presensiHariIni['alpa'] ?> Alpa</div>
        </div>
        <div class="stat-icon danger">
            <i data-lucide="alert-circle"></i>
        </div>
    </div>
</div>

<!-- Quick Action Banner -->
<div class="card" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #ffffff; border: none;">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 6px;">Pusat Kontrol Cepat Administrasi</h2>
            <p style="color: #94a3b8; font-size: 0.875rem;">Akses instan pemindaian QR Code, cetak kartu pelajar, dan rekapitulasi data presensi.</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="index.php?page=scanner" class="btn btn-primary" style="background: #10b981; border: none;">
                <i data-lucide="qr-code"></i>
                <span>Buka Scanner QR</span>
            </a>
            <a href="index.php?page=kartu-pelajar" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.2);">
                <i data-lucide="id-card"></i>
                <span>Cetak Kartu Pelajar</span>
            </a>
            <a href="index.php?page=laporan" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.2);">
                <i data-lucide="printer"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>
</div>

<!-- Fitur 3: Dashboard Analytics & Charts Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px;">
    <!-- Tren Presensi 7 Hari -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="trending-up" style="color: #3b82f6;"></i>
                <span>Tren Kehadiran Siswa (7 Hari Terakhir)</span>
            </div>
            <span class="badge badge-hadir">Data Real-Time</span>
        </div>
        <div class="card-body">
            <div style="height: 280px; position: relative;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Rasio Kehadiran Hari Ini -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="pie-chart" style="color: #10b981;"></i>
                <span>Status Hari Ini</span>
            </div>
        </div>
        <div class="card-body" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="height: 220px; width: 100%; position: relative;">
                <canvas id="donutChart"></canvas>
            </div>
            <div style="display: flex; gap: 14px; margin-top: 14px; font-size: 0.775rem;">
                <span style="color: #10b981; font-weight: 600;">● Hadir: <?= $hadirCount ?></span>
                <span style="color: #f59e0b; font-weight: 600;">● Izin: <?= (int)$presensiHariIni['izin'] ?></span>
                <span style="color: #6366f1; font-weight: 600;">● Sakit: <?= (int)$presensiHariIni['sakit'] ?></span>
                <span style="color: #ef4444; font-weight: 600;">● Alpa: <?= (int)$presensiHariIni['alpa'] ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Aktivitas Presensi Terbaru Hari Ini -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="clock" style="color: #6366f1;"></i>
            <span>Log Presensi Terbaru Hari Ini</span>
        </div>
        <a href="index.php?page=absensi" class="btn btn-secondary btn-sm">
            <span>Lihat Semua Presensi</span>
            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentPresensi)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 32px;">
                        Belum ada rekaman presensi untuk hari ini.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recentPresensi as $log): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; color: #475569;">
                            <?= htmlspecialchars($log['waktu_scan'] ?: '-') ?>
                        </td>
                        <td style="font-family: monospace;">
                            <?= htmlspecialchars($log['nisn']) ?>
                        </td>
                        <td style="font-weight: 600;">
                            <?= htmlspecialchars($log['nama']) ?>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #334155;">
                                <?= htmlspecialchars($log['nama_kelas']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($log['metode'] === 'qr_scan'): ?>
                                <span class="badge badge-hadir" style="font-size: 0.7rem;">
                                    <i data-lucide="qr-code" style="width: 12px; height: 12px;"></i> Scan QR
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.7rem;">
                                    Manual
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $st = strtolower($log['status']);
                            $badgeClass = 'badge-hadir';
                            if ($st === 'izin') $badgeClass = 'badge-izin';
                            if ($st === 'sakit') $badgeClass = 'badge-sakit';
                            if ($st === 'alpa') $badgeClass = 'badge-alpa';
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= htmlspecialchars($log['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('api/stats.php');
        const data = await res.json();
        if (!data.success) return;

        // 1. Line/Area Chart for 7-day trend
        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        const labels = data.trend.map(item => item.tanggal);
        const hadirData = data.trend.map(item => item.hadir);
        const izinData = data.trend.map(item => item.izin);
        const sakitData = data.trend.map(item => item.sakit);
        const alpaData = data.trend.map(item => item.alpa);

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: hadirData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Izin & Sakit',
                        data: labels.map((_, i) => (Number(izinData[i]) + Number(sakitData[i]))),
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        borderWidth: 2
                    },
                    {
                        label: 'Alpa',
                        data: alpaData,
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 12 } } },
                    tooltip: { padding: 12, cornerRadius: 8 }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Donut Chart for Today's Breakdown
        const ctxDonut = document.getElementById('donutChart').getContext('2d');
        const today = data.today;
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                datasets: [{
                    data: [today.hadir || 0, today.izin || 0, today.sakit || 0, today.alpa || 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#6366f1', '#ef4444'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { padding: 10, cornerRadius: 8 }
                }
            }
        });
    } catch (e) {
        console.error('Gagal memuat analitik chart', e);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
