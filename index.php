<?php
/**
 * EduAdmin Modern - Main Entry Point & Router
 * Sistem Manajemen Administrasi Sekolah
 */

define('APP_INIT', true);

// Parse page request
$page = $_GET['page'] ?? 'dashboard';

// Whitelisted pages
$allowedPages = [
    'dashboard'     => 'pages/dashboard.php',
    'siswa'         => 'pages/siswa.php',
    'guru'          => 'pages/guru.php',
    'absensi'       => 'pages/absensi.php',
    'kartu-pelajar' => 'pages/kartu-pelajar.php',
    'scanner'       => 'pages/scanner.php',
    'laporan'       => 'pages/laporan.php',
];

if (array_key_exists($page, $allowedPages)) {
    $targetFile = __DIR__ . '/' . $allowedPages[$page];
    if (file_exists($targetFile)) {
        require_once $targetFile;
        exit;
    }
}

// Fallback to dashboard
require_once __DIR__ . '/pages/dashboard.php';
