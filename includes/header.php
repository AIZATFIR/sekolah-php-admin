<?php
// includes/header.php
if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();

// Fetch school profile
$schoolStmt = $db->query("SELECT * FROM sekolah_info LIMIT 1");
$school = $schoolStmt->fetch() ?: [
    'nama_sekolah' => 'SMA Negeri 1 Nusantara',
    'npsn' => '20109988',
    'alamat' => 'Jl. Pendidikan Merdeka No. 45',
    'kabupaten_kota' => 'Kota Bandung'
];

$currentPage = $_GET['page'] ?? 'dashboard';

// Quick counters for sidebar badge
$totalSiswa = (int)$db->query("SELECT count(*) FROM siswa WHERE status = 'Aktif'")->fetchColumn();
$totalGuru = (int)$db->query("SELECT count(*) FROM guru WHERE status = 'Aktif'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Sistem Administrasi Sekolah') ?> - <?= htmlspecialchars($school['nama_sekolah']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- HTML5 QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>

<!-- Sidebar Navigation -->
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i data-lucide="graduation-cap"></i>
        </div>
        <div>
            <div class="brand-title">EduAdmin</div>
            <div class="brand-subtitle">Sistem Sekolah</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        <a href="index.php?page=dashboard" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <a href="index.php?page=siswa" class="nav-link <?= $currentPage === 'siswa' ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Data Siswa</span>
            <span class="badge-counter"><?= $totalSiswa ?></span>
        </a>
        <a href="index.php?page=guru" class="nav-link <?= $currentPage === 'guru' ? 'active' : '' ?>">
            <i data-lucide="user-check"></i>
            <span>Data Guru</span>
            <span class="badge-counter"><?= $totalGuru ?></span>
        </a>
        <a href="index.php?page=absensi" class="nav-link <?= $currentPage === 'absensi' ? 'active' : '' ?>">
            <i data-lucide="calendar-check-2"></i>
            <span>Presensi Harian</span>
        </a>

        <div class="nav-section-label">Fitur Unggulan</div>
        <a href="index.php?page=scanner" class="nav-link <?= $currentPage === 'scanner' ? 'active' : '' ?>">
            <i data-lucide="qr-code"></i>
            <span>Scan QR Absensi</span>
        </a>
        <a href="index.php?page=kartu-pelajar" class="nav-link <?= $currentPage === 'kartu-pelajar' ? 'active' : '' ?>">
            <i data-lucide="id-card"></i>
            <span>Kartu Pelajar</span>
        </a>
        <a href="index.php?page=laporan" class="nav-link <?= $currentPage === 'laporan' ? 'active' : '' ?>">
            <i data-lucide="file-spreadsheet"></i>
            <span>Rekap & Laporan</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar">AD</div>
        <div class="user-info">
            <div class="user-name">Administrator</div>
            <div class="user-role">Tata Usaha Sekolah</div>
        </div>
    </div>
</aside>

<!-- Main Workspace -->
<div class="app-main">
    <header class="top-navbar no-print">
        <div style="display: flex; align-items: center; gap: 16px;">
            <button id="sidebar-toggle" class="btn btn-icon-only" style="display: none;" title="Toggle Sidebar">
                <i data-lucide="menu"></i>
            </button>
            <div class="page-title-box">
                <h1><?= htmlspecialchars($pageTitle ?? 'Sistem Administrasi') ?></h1>
            </div>
        </div>

        <div class="top-actions">
            <div class="school-tag">
                <i data-lucide="building-2" style="width: 14px; height: 14px;"></i>
                <span><?= htmlspecialchars($school['nama_sekolah']) ?> (NPSN: <?= htmlspecialchars($school['npsn']) ?>)</span>
            </div>
            <div class="current-datetime">
                <?= date('d M Y') ?>
            </div>
        </div>
    </header>

    <main class="page-container">
