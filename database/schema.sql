-- Schema Database Sistem Administrasi Sekolah (EduAdmin Modern)

CREATE TABLE IF NOT EXISTS sekolah_info (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_sekolah VARCHAR(150) NOT NULL,
    npsn VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    kabupaten_kota VARCHAR(100) NOT NULL,
    provinsi VARCHAR(100) NOT NULL,
    kode_pos VARCHAR(10),
    email VARCHAR(100),
    no_telp VARCHAR(30),
    kepala_sekolah VARCHAR(150) NOT NULL,
    nip_kepala_sekolah VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS kelas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama_kelas VARCHAR(50) NOT NULL UNIQUE,
    tingkat INTEGER NOT NULL,
    wali_nama VARCHAR(150)
);

CREATE TABLE IF NOT EXISTS guru (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nip VARCHAR(30) UNIQUE NOT NULL,
    nama VARCHAR(150) NOT NULL,
    jenis_kelamin VARCHAR(1) NOT NULL, -- L / P
    mata_pelajaran VARCHAR(100) NOT NULL,
    no_hp VARCHAR(25),
    email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'Aktif', -- Aktif, Cuti
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS siswa (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nisn VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(150) NOT NULL,
    jenis_kelamin VARCHAR(1) NOT NULL, -- L / P
    kelas_id INTEGER NOT NULL,
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    alamat TEXT,
    no_telp_ortu VARCHAR(25),
    status VARCHAR(20) DEFAULT 'Aktif', -- Aktif, Lulus, Pindah
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS absensi_siswa (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    siswa_id INTEGER NOT NULL,
    tanggal DATE NOT NULL,
    status VARCHAR(10) NOT NULL, -- Hadir, Sakit, Izin, Alpa
    waktu_scan TIME,
    keterangan TEXT,
    metode VARCHAR(20) DEFAULT 'manual', -- manual, qr_scan
    UNIQUE(siswa_id, tanggal),
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS absensi_guru (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    guru_id INTEGER NOT NULL,
    tanggal DATE NOT NULL,
    status VARCHAR(10) NOT NULL, -- Hadir, Sakit, Izin, Alpa
    waktu_absen TIME,
    keterangan TEXT,
    UNIQUE(guru_id, tanggal),
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE
);
