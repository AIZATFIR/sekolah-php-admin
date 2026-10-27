# EduAdmin - Sistem Manajemen Administrasi Sekolah Modern (PHP + SQL)

Aplikasi web manajemen administrasi sekolah berbasis PHP murni dengan antarmuka modern, responsif, dan bebas bloatware (*anti-slop*). Sistem ini mengintegrasikan presensi harian, manajemen siswa & guru, analitik visual, serta kartu pelajar digital interaktif.

![EduAdmin Dashboard Preview](assets/css/style.css)

---

## Fitur Utama

### 1. Manajemen Siswa (CRUD Lengkap)
- Nomor Induk Siswa Nasional (NISN) unik.
- Filter berdasarkan rombel kelas (Tingkat 10, 11, 12).
- Pencarian instan real-time tanpa reload halaman.
- Informasi lengkap: NISN, Nama Lengkap, Jenis Kelamin, TTL, Alamat, dan Kontak Orang Tua.
- Pintasan langsung untuk melihat dan mencetak Kartu Pelajar Digital.

### 2. Manajemen Tenaga Pendidik / Guru (CRUD Lengkap)
- Nomor Induk Pegawai (NIP), Gelar, Mata Pelajaran yang diampu.
- Status keaktifan guru (Aktif / Cuti).
- Kontak telepon / WhatsApp dan Email resmi.

### 3. Presensi Harian Siswa & Guru
- Perekaman status kehadiran harian: **Hadir**, **Izin**, **Sakit**, dan **Alpa**.
- Penanda waktu presensi otomatis.
- Tombol satu-klik "Tandai Semua Hadir".
- Dukungan input manual dan pemindaian cepat.

---

## 3 Fitur Unggulan Nice & Bermanfaat

### Fitur 1: Kartu Pelajar Digital + Scanner QR Code Real-Time
- **Kartu Pelajar Digital**: Mengikuti rasio standar kartu fisik (CR80) dengan warna gradien korporat, logo sekolah, identitas siswa, dan **QR Code Unik** yang dirender otomatis.
- **Scanner Kamera QR**: Mengakses webcam/kamera laptop/ponsel untuk memindai QR Code kartu pelajar siswa secara real-time.
- **Audio Feedback**: Menggunakan Web Audio API untuk menghasilkan nada chime positif saat presensi berhasil tercatat.
- **Dukungan Barcode Scanner Fisik**: Menyediakan input cepat untuk barcode scanner USB/Bluetooth tipe keyboard wedge.

### Fitur 2: Rekapitulasi & Cetak Kop Surat Resmi + Export CSV
- **Kop Surat Standar Sekolah Indonesia**: Tampilan kop surat resmi instansi dengan garis ganda (*double border*), data NPSN, alamat, dan pejabat berwenang.
- **Siap Cetak ke PDF**: Pengaturan `@media print` yang rapi, otomatis menyembunyikan navigasi dan sidebar saat tombol Cetak ditekan.
- **Lembar Tanda Tangan**: Kolom tanda tangan Wali Kelas dan Kepala Sekolah lengkap dengan NIP.
- **Ekspor CSV**: Unduh data siswa, guru, dan rekapitulasi kehadiran per periode ke format file spreadsheet dengan encoding UTF-8 BOM.

### Fitur 3: Dashboard Analitik & Visualisasi Data (Chart.js)
- **Tren Presensi 7 Hari**: Grafik area dinamis yang memperlihatkan tren naik/turun kehadiran siswa.
- **Rasio Presensi Hari Ini**: Diagram donat persentase Hadir, Izin, Sakit, dan Alpa hari ini.
- **Metrik Utama (KPI)**: Total Siswa Aktif, Guru Terdaftar, Persentase Kehadiran Hari Ini, dan Total Ketidakhadiran.
- **Log Aktivitas Terkini**: Tabel pemantauan langsung siswa yang baru saja presensi.

---

## Basis Data (Dual-Driver SQL)

Sistem menggunakan **PHP PDO** yang mendukung dua jenis basis data:

1. **SQLite (Default - Zero Configuration)**
   - Tidak memerlukan instalasi database server terpisah.
   - Database dibuat otomatis di `database/school.db` (atau di `/tmp/sekolah.db` saat berjalan di serverless Vercel).
   - Skema tabel dan data awal diinisialisasi otomatis pada akses pertama.

2. **MySQL / MariaDB (Opsional untuk Produksi)**
   - Cukup konfigurasikan variabel lingkungan (*environment variables*):
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=sekolah_db
     DB_USERNAME=root
     DB_PASSWORD=rahasia
     ```

---

## Cara Menjalankan Secara Lokal

1. Pastikan PHP (versi 8.0 atau lebih baru) telah terpasang di komputer Anda.
2. Buka terminal di folder proyek ini (`/home/aizatfir/Project/sekolah-php-admin`):
   ```bash
   php -S 127.0.0.1:8000
   ```
3. Buka browser dan akses alamat:
   ```
   http://127.0.0.1:8000
   ```

---

## Panduan Deployment ke Vercel

Proyek ini telah dikonfigurasi dengan file `vercel.json` menggunakan runtime serverless `vercel-php`:

```bash
# Melalui npx Vercel CLI
npx vercel
```

Atau cukup sambungkan repositori GitHub ini langsung melalui dashboard [Vercel](https://vercel.com) dengan memilih preset build default.
