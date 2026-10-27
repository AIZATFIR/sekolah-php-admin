<?php
// pages/siswa.php
$pageTitle = 'Manajemen Data Siswa';
require_once __DIR__ . '/../includes/header.php';

$message = null;
$messageType = 'success';

// Handle Add / Edit / Delete POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nisn = trim($_POST['nisn'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? 'L';
        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $tempatLahir = trim($_POST['tempat_lahir'] ?? '');
        $tanggalLahir = $_POST['tanggal_lahir'] ?: null;
        $alamat = trim($_POST['alamat'] ?? '');
        $noTelp = trim($_POST['no_telp_ortu'] ?? '');

        if ($nisn && $nama && $kelasId) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO siswa (nisn, nama, jenis_kelamin, kelas_id, tempat_lahir, tanggal_lahir, alamat, no_telp_ortu, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')
                ");
                $stmt->execute([$nisn, $nama, $jk, $kelasId, $tempatLahir, $tanggalLahir, $alamat, $noTelp]);
                $message = "Siswa [{$nama}] berhasil ditambahkan!";
            } catch (Exception $e) {
                $message = "Gagal menambah siswa: NISN mungkin sudah terdaftar.";
                $messageType = 'error';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? 'L';
        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $tempatLahir = trim($_POST['tempat_lahir'] ?? '');
        $tanggalLahir = $_POST['tanggal_lahir'] ?: null;
        $alamat = trim($_POST['alamat'] ?? '');
        $noTelp = trim($_POST['no_telp_ortu'] ?? '');
        $status = $_POST['status'] ?? 'Aktif';

        if ($id && $nama && $kelasId) {
            try {
                $stmt = $db->prepare("
                    UPDATE siswa 
                    SET nama = ?, jenis_kelamin = ?, kelas_id = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, no_telp_ortu = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nama, $jk, $kelasId, $tempatLahir, $tanggalLahir, $alamat, $noTelp, $status, $id]);
                $message = "Data siswa [{$nama}] berhasil diperbarui!";
            } catch (Exception $e) {
                $message = "Gagal memperbarui data siswa: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM siswa WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Data siswa berhasil dihapus!";
        }
    }
}

// Fetch Classes for dropdown
$kelasList = $db->query("SELECT * FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();

// Filter Kelas
$selectedKelas = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

$query = "
    SELECT s.*, k.nama_kelas 
    FROM siswa s 
    JOIN kelas k ON s.kelas_id = k.id 
";
$params = [];
if ($selectedKelas) {
    $query .= " WHERE s.kelas_id = ? ";
    $params[] = $selectedKelas;
}
$query .= " ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama ASC ";

$stmtSiswa = $db->prepare($query);
$stmtSiswa->execute($params);
$siswaList = $stmtSiswa->fetchAll();
?>

<?php if ($message): ?>
<div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; background: <?= $messageType === 'success' ? '#ecfdf5' : '#fef2f2' ?>; color: <?= $messageType === 'success' ? '#065f46' : '#991b1b' ?>; border: 1px solid <?= $messageType === 'success' ? '#a7f3d0' : '#fecaca' ?>; display: flex; align-items: center; gap: 10px;">
    <i data-lucide="<?= $messageType === 'success' ? 'check-circle-2' : 'alert-circle' ?>"></i>
    <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i data-lucide="users" style="color: #2563eb;"></i>
            <span>Daftar Siswa Sekolah</span>
            <span class="badge" style="background: #eff6ff; color: #2563eb;"><?= count($siswaList) ?> Siswa</span>
        </div>

        <div class="card-actions">
            <!-- Filter Kelas -->
            <form method="GET" action="index.php" style="display: flex; gap: 8px;">
                <input type="hidden" name="page" value="siswa">
                <select name="kelas_id" class="form-select" style="width: auto; padding: 7px 12px; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">Semua Rombel Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selectedKelas === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <!-- Search Box -->
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" class="form-input" data-table-search="siswa-table" placeholder="Cari nama / NISN...">
            </div>

            <!-- Export CSV -->
            <a href="api/export.php?type=siswa" class="btn btn-secondary" title="Export CSV">
                <i data-lucide="download"></i>
                <span>Export CSV</span>
            </a>

            <!-- Tombol Tambah Siswa -->
            <button class="btn btn-primary" onclick="openModal('modal-tambah-siswa')">
                <i data-lucide="user-plus"></i>
                <span>Tambah Siswa</span>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="siswa-table">
            <thead>
                <tr>
                    <th>NISN</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Kelas</th>
                    <th>Tempat, Tgl Lahir</th>
                    <th>No Telp Ortu</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaList)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--color-text-muted); padding: 36px;">
                        Tidak ada data siswa yang cocok dengan filter.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($siswaList as $s): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; color: #1e3a8a;">
                            <?= htmlspecialchars($s['nisn']) ?>
                        </td>
                        <td style="font-weight: 600;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= $s['jenis_kelamin'] === 'L' ? '#eff6ff' : '#fdf2f8' ?>; color: <?= $s['jenis_kelamin'] === 'L' ? '#2563eb' : '#db2777' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                    <?= substr($s['nama'], 0, 1) ?>
                                </div>
                                <div>
                                    <?= htmlspecialchars($s['nama']) ?>
                                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 400;"><?= htmlspecialchars($s['alamat'] ?: '-') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= $s['jenis_kelamin'] === 'L' ? '#eff6ff' : '#fdf2f8' ?>; color: <?= $s['jenis_kelamin'] === 'L' ? '#1d4ed8' : '#be185d' ?>;">
                                <?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #334155;">
                                <?= htmlspecialchars($s['nama_kelas']) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.825rem; color: #475569;">
                            <?= htmlspecialchars($s['tempat_lahir'] ?: '-') ?>, <?= $s['tanggal_lahir'] ? date('d/m/Y', strtotime($s['tanggal_lahir'])) : '-' ?>
                        </td>
                        <td style="font-family: monospace; font-size: 0.825rem;">
                            <?= htmlspecialchars($s['no_telp_ortu'] ?: '-') ?>
                        </td>
                        <td>
                            <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-aktif' : 'badge-cuti' ?>">
                                <?= htmlspecialchars($s['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="index.php?page=kartu-pelajar&nisn=<?= urlencode($s['nisn']) ?>" class="btn btn-secondary btn-sm" title="Kartu Pelajar">
                                    <i data-lucide="id-card" style="width: 14px; height: 14px;"></i>
                                </a>
                                <button class="btn btn-secondary btn-sm" title="Edit Siswa" onclick="editSiswa(<?= htmlspecialchars(json_encode($s)) ?>)">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus siswa <?= htmlspecialchars(addslashes($s['nama'])) ?>?');">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Siswa">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div class="modal-backdrop" id="modal-tambah-siswa">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Data Siswa Baru</h3>
            <button class="btn btn-icon-only" onclick="closeModal('modal-tambah-siswa')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nomor Induk Siswa Nasional (NISN) *</label>
                    <input type="text" name="nisn" class="form-input" required placeholder="Contoh: 0071234567" maxlength="15">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap Siswa *</label>
                    <input type="text" name="nama" class="form-input" required placeholder="Nama lengkap siswa">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rombel Kelas *</label>
                        <select name="kelas_id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-input" placeholder="Kota lahir">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Telepon Orang Tua / Wali</label>
                    <input type="text" name="no_telp_ortu" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Domisili</label>
                    <textarea name="alamat" class="form-textarea" rows="2" placeholder="Alamat lengkap tempat tinggal siswa"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah-siswa')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal-backdrop" id="modal-edit-siswa">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Siswa</h3>
            <button class="btn btn-icon-only" onclick="closeModal('modal-edit-siswa')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">NISN (Tidak dapat diubah)</label>
                    <input type="text" id="edit-nisn" class="form-input" readonly style="background: #f1f5f9; cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap Siswa *</label>
                    <input type="text" name="nama" id="edit-nama" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" id="edit-jk" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rombel Kelas *</label>
                        <select name="kelas_id" id="edit-kelas-id" class="form-select" required>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="edit-tempat-lahir" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="edit-tanggal-lahir" class="form-input">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">No Telp Ortu</label>
                        <input type="text" name="no_telp_ortu" id="edit-telp" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Siswa</label>
                        <select name="status" id="edit-status" class="form-select">
                            <option value="Aktif">Aktif</option>
                            <option value="Lulus">Lulus</option>
                            <option value="Pindah">Pindah</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Domisili</label>
                    <textarea name="alamat" id="edit-alamat" class="form-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-siswa')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSiswa(siswa) {
    document.getElementById('edit-id').value = siswa.id;
    document.getElementById('edit-nisn').value = siswa.nisn;
    document.getElementById('edit-nama').value = siswa.nama;
    document.getElementById('edit-jk').value = siswa.jenis_kelamin;
    document.getElementById('edit-kelas-id').value = siswa.kelas_id;
    document.getElementById('edit-tempat-lahir').value = siswa.tempat_lahir || '';
    document.getElementById('edit-tanggal-lahir').value = siswa.tanggal_lahir || '';
    document.getElementById('edit-telp').value = siswa.no_telp_ortu || '';
    document.getElementById('edit-status').value = siswa.status || 'Aktif';
    document.getElementById('edit-alamat').value = siswa.alamat || '';
    openModal('modal-edit-siswa');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
