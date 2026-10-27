# Sistem Administrasi Sekolah (EduAdmin Modern) - Specification

## Ringkasan Proyek
Sistem Manajemen Administrasi Sekolah berbasis PHP murni dengan PDO multi-driver (SQLite zero-config out-of-the-box dan MySQL kompatibel), antarmuka modern anti-slop, dilengkapi absensi, manajemen siswa & guru, serta 3 fitur unggulan:
1. Kartu Pelajar Digital + QR Code Scanner Absensi Cepat dengan Audio Feedback.
2. Rekap & Export Laporan Multi-Format (Cetak Kop Surat Resmi Berstandar PDF & Export CSV).
3. Dashboard Analitik & Visualisasi Data Interaktif (Chart.js tren kehadiran dan demografi).

## Arsitektur & Teknologi
- **Bahasa Backend**: PHP 8.x (Clean, Vanilla, No bloated vendor dependencies, fast execution)
- **Basis Data**: Dual-Driver PDO (SQLite untuk serverless/local zero-config, MySQL untuk deployment skala penuh)
- **Frontend & Gaya**:
  - CSS kustom dengan token modern (Data-Dense Dashboard design system)
  - Tipografi: Plus Jakarta Sans / Inter
  - Ikon: Lucide SVG Icons (tanpa emoji struktural)
  - Charting: Chart.js untuk visualisasi data
  - QR Code: dynamic client-side QR renderer & HTML5 QR Scanner
- **Deployment**:
  - Konfigurasi `vercel.json` dengan community runtime `vercel-php@0.9.0`
  - Git repository & GitHub remote via `gh` CLI

## Skema Basis Data
- `users`: id, username, password_hash, nama, role ('admin', 'petugas')
- `sekolah_info`: nama_sekolah, npsn, alamat, kota, provinsi, kepala_sekolah, nip_kepsek, kontak
- `kelas`: id, nama_kelas, tingkat, wali_guru_id
- `siswa`: id, nisn, nama, jenis_kelamin, kelas_id, tempat_lahir, tanggal_lahir, alamat, no_telp_ortu, status, created_at
- `guru`: id, nip, nama, jenis_kelamin, mata_pelajaran, no_hp, email, status, created_at
- `absensi_siswa`: id, siswa_id, tanggal, status ('Hadir', 'Izin', 'Sakit', 'Alpa'), waktu_scan, keterangan, metode ('manual', 'qr_scan')
- `absensi_guru`: id, guru_id, tanggal, status ('Hadir', 'Izin', 'Sakit', 'Alpa'), waktu_absen, keterangan
