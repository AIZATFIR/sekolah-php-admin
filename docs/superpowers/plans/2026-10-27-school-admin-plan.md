# EduAdmin Modern Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Membangun aplikasi web PHP manajemen administrasi sekolah modern lengkap dengan absensi, CRUD siswa & guru, 3 fitur unggulan (Kartu Pelajar QR + Scanner, Rekap & Export Cetak/CSV, Dashboard Chart.js), terhubung database SQL, repo GitHub, dan konfigurasi Vercel.

**Architecture:** Single-Entry Router `index.php` memetakan rute `/` (dashboard), `/siswa`, `/guru`, `/absensi`, `/scanner`, `/kartu-pelajar`, `/laporan`, dan API `/api/*`. Layer basis data menggunakan PDO abstraction dengan auto-initialization SQLite (atau MySQL jika variabel `DB_HOST` tersedia).

**Tech Stack:** PHP 8+, PDO SQLite & MySQL, Vanilla Modern CSS + Lucide Icons + Chart.js + HTML5 QR Code, Vercel runtime.

## Global Constraints
- Tanpa emoji sebagai ikon sistem (menggunakan SVG Lucide).
- Contrast teks minimum 4.5:1 untuk aksesibilitas WCAG AA.
- Dukungan printer kop surat resmi sekolah (`@media print`).
- Zero-external PHP composer dependency agar instan jalan di Vercel dan server lokal tanpa build step rumit.

---

### Task 1: Database Layer & Seed Configuration
- Buat `config/database.php` dengan koneksi PDO pintar (SQLite & MySQL support, auto-migrasi skema).
- Buat `database/schema.sql` dan `database/seed.sql` berisi data sekolah Nusantara, kelas, siswa, guru, dan log absensi awal.

### Task 2: Core Layout & Styling System
- Buat `assets/css/style.css` berisi variable tokens, data-dense cards, buttons, table styles, responsive sidebar/navbar, dan print stylesheet.
- Buat `includes/header.php` dan `includes/footer.php` dengan navigasi dinamis, modal handler, dan toast notifications.

### Task 3: Dashboard Analytics & Chart API
- Buat `pages/dashboard.php` menampilkan ringkasan metrik (Total Siswa, Total Guru, Presensi Hari Ini, Alpa Hari Ini).
- Buat `api/stats.php` yang mengembalikan data JSON untuk Chart.js (tren kehadiran 7 hari & komposisi per kelas).

### Task 4: Manajemen Siswa & Guru (CRUD)
- Buat `pages/siswa.php` dengan tabel paginasi/search, modal Tambah, modal Edit, Hapus, dan shortcut lihat Kartu Pelajar.
- Buat `pages/guru.php` dengan tabel search, modal Tambah, modal Edit, Hapus.
- Logika penanganan POST simpan/update/delete di masing-masing page.

### Task 5: Absensi Presensi & Rekapitulasi
- Buat `pages/absensi.php` untuk input absensi harian kelas (Siswa) dan guru per tanggal, dengan penandaan Hadir, Sakit, Izin, Alpa.
- Buat `api/absensi.php` untuk AJAX update status cepat.

### Task 6: 3 Fitur Unggulan
- **Fitur 1**: Buat `pages/kartu-pelajar.php` (Kartu Pelajar Digital dengan QR Code SVG) & `pages/scanner.php` + `api/qr-scan.php` (Scanner QR Kamera + input cepat NISN + audio beep konfirmasi).
- **Fitur 2**: Buat `pages/laporan.php` & `api/export.php` (Kop Surat Resmi Cetak PDF berstandar sekolah Indonesia + Export CSV).
- **Fitur 3**: Integrasikan Chart.js pada Dashboard dengan chart interaktif responsif.

### Task 7: Git Init, GitHub Push, & Vercel Deployment
- Inisialisasi Git repository di folder `sekolah-php-admin`.
- Buat `vercel.json` dan `.gitignore`.
- Buat repo GitHub `sekolah-php-admin` via `gh repo create` dan push `main`.
- Verifikasi lokal dengan built-in server PHP `php -S 127.0.0.1:8000`.
