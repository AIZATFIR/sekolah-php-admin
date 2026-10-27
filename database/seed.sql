-- Seed Data untuk Sistem Administrasi Sekolah Nusantara

INSERT INTO sekolah_info (id, nama_sekolah, npsn, alamat, kabupaten_kota, provinsi, kode_pos, email, no_telp, kepala_sekolah, nip_kepala_sekolah)
VALUES (1, 'SMA Negeri 1 Nusantara', '20109988', 'Jl. Pendidikan Merdeka No. 45, Kecamatan Sukamaju', 'Kota Bandung', 'Jawa Barat', '40115', 'info@sman1nusantara.sch.id', '(022) 7201988', 'Drs. H. Bambang Sudrajat, M.Pd.', '196805121994031004');

INSERT INTO kelas (id, nama_kelas, tingkat, wali_nama) VALUES
(1, 'X MIPA 1', 10, 'Siti Nurhaliza, S.Pd.'),
(2, 'X MIPA 2', 10, 'Ahmad Fauzi, M.Kom.'),
(3, 'XI MIPA 1', 11, 'Dr. Budi Santoso, M.Si.'),
(4, 'XI IPS 1', 11, 'Rina Anggraini, S.Pd.'),
(5, 'XII MIPA 1', 12, 'Hendra Gunawan, M.Pd.'),
(6, 'XII IPS 1', 12, 'Dewi Lestari, S.Pd.');

INSERT INTO guru (id, nip, nama, jenis_kelamin, mata_pelajaran, no_hp, email, status) VALUES
(1, '197501012002121001', 'Dr. Budi Santoso, M.Si.', 'L', 'Matematika Peminatan', '081234567801', 'budi.santoso@sman1nusantara.sch.id', 'Aktif'),
(2, '198003152005012003', 'Siti Nurhaliza, S.Pd.', 'P', 'Bahasa Indonesia', '081234567802', 'siti.nurhaliza@sman1nusantara.sch.id', 'Aktif'),
(3, '198507202009021005', 'Ahmad Fauzi, M.Kom.', 'L', 'Informatika & TIK', '081234567803', 'ahmad.fauzi@sman1nusantara.sch.id', 'Aktif'),
(4, '198811122011012007', 'Rina Anggraini, S.Pd.', 'P', 'Bahasa Inggris', '081234567804', 'rina.anggraini@sman1nusantara.sch.id', 'Aktif'),
(5, '197902182003121002', 'Hendra Gunawan, M.Pd.', 'L', 'Fisika Terapan', '081234567805', 'hendra.gunawan@sman1nusantara.sch.id', 'Aktif'),
(6, '198409052008012004', 'Dewi Lestari, S.Pd.', 'P', 'Sosiologi & Sejarah', '081234567806', 'dewi.lestari@sman1nusantara.sch.id', 'Aktif'),
(7, '199104102015031006', 'Rizky Pratama, S.Pd.', 'L', 'Pendidikan Jasmani & Olahraga', '081234567807', 'rizky.pratama@sman1nusantara.sch.id', 'Aktif'),
(8, '198706242010012008', 'Sri Wahyuni, M.Pd.', 'P', 'Biologi Molekuler', '081234567808', 'sri.wahyuni@sman1nusantara.sch.id', 'Aktif');

INSERT INTO siswa (id, nisn, nama, jenis_kelamin, kelas_id, tempat_lahir, tanggal_lahir, alamat, no_telp_ortu, status) VALUES
(1, '0071234501', 'Aditya Pratama Putra', 'L', 1, 'Bandung', '2008-04-12', 'Jl. Dago Asri No. 12', '081320011001', 'Aktif'),
(2, '0071234502', 'Anisa Rahmawati', 'P', 1, 'Jakarta', '2008-06-25', 'Jl. Tubagus Ismail No. 8', '081320011002', 'Aktif'),
(3, '0071234503', 'Bayu Tri Nugroho', 'L', 1, 'Cimahi', '2008-01-19', 'Jl. Cihanjuang No. 44', '081320011003', 'Aktif'),
(4, '0071234504', 'Cantika Putri Maharani', 'P', 1, 'Bandung', '2008-09-03', 'Jl. Setiabudi No. 102', '081320011004', 'Aktif'),
(5, '0071234505', 'Dimas Arya Saputra', 'L', 2, 'Sumedang', '2008-03-14', 'Jl. Ciumbuleuit No. 55', '081320011005', 'Aktif'),
(6, '0071234506', 'Elsa Maulida Azzahra', 'P', 2, 'Bandung', '2008-11-20', 'Jl. Pasirkaliki No. 78', '081320011006', 'Aktif'),
(7, '0071234507', 'Fajar Ramadhan', 'L', 2, 'Garut', '2008-08-17', 'Jl. Sukajadi No. 90', '081320011007', 'Aktif'),
(8, '0071234508', 'Gita Gutawa Putri', 'P', 3, 'Bandung', '2007-02-10', 'Jl. Buah Batu No. 15', '081320011008', 'Aktif'),
(9, '0071234509', 'Hafizh Al-Fatih', 'L', 3, 'Tasikmalaya', '2007-05-30', 'Jl. Laswi No. 23', '081320011009', 'Aktif'),
(10, '0071234510', 'Indah Permatasari', 'P', 3, 'Bandung', '2007-12-05', 'Jl. Riau No. 67', '081320011010', 'Aktif'),
(11, '0071234511', 'Jonathan Kevin Wijaya', 'L', 4, 'Surabaya', '2007-07-22', 'Jl. Naripan No. 34', '081320011011', 'Aktif'),
(12, '0071234512', 'Kirana Larasati', 'P', 4, 'Bandung', '2007-10-11', 'Jl. Gatot Subroto No. 89', '081320011012', 'Aktif'),
(13, '0071234513', 'Lukman Hakim Siregar', 'L', 5, 'Medan', '2006-03-08', 'Jl. Dipati Ukur No. 42', '081320011013', 'Aktif'),
(14, '0071234514', 'Nabila Syakieb Zahra', 'P', 5, 'Bandung', '2006-08-14', 'Jl. Pasteur No. 11', '081320011014', 'Aktif'),
(15, '0071234515', 'Oki Setiana Raharjo', 'L', 5, 'Yogyakarta', '2006-01-29', 'Jl. Cihampelas No. 120', '081320011015', 'Aktif'),
(16, '0071234516', 'Putri Ayu Wandira', 'P', 6, 'Semarang', '2006-11-09', 'Jl. Asia Afrika No. 3', '081320011016', 'Aktif'),
(17, '0071234517', 'Rendi Aditya Kurnia', 'L', 6, 'Bandung', '2006-04-16', 'Jl. Pajajaran No. 71', '081320011017', 'Aktif'),
(18, '0071234518', 'Tiara Andini Santika', 'P', 6, 'Malang', '2006-09-23', 'Jl. Sunda No. 56', '081320011018', 'Aktif');

-- Absensi Siswa untuk Hari Ini (Current Date) & 3 Hari Sebelumnya
-- Tanggal dinamis disesuaikan via script atau seed
