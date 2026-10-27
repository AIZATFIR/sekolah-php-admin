<?php
// pages/guru.php
$pageTitle = 'Manajemen Tenaga Pendidik (Guru)';
require_once __DIR__ . '/../includes/header.php';

$message = null;
$messageType = 'success';

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nip = trim($_POST['nip'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? 'L';
        $mapel = trim($_POST['mata_pelajaran'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nip && $nama && $mapel) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO guru (nip, nama, jenis_kelamin, mata_pelajaran, no_hp, email, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'Aktif')
                ");
                $stmt->execute([$nip, $nama, $jk, $mapel, $noHp, $email]);
                $message = "Guru [{$nama}] berhasil ditambahkan!";
            } catch (Exception $e) {
                $message = "Gagal menambah guru: NIP mungkin sudah terdaftar.";
                $messageType = 'error';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? 'L';
        $mapel = trim($_POST['mata_pelajaran'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = $_POST['status'] ?? 'Aktif';

        if ($id && $nama && $mapel) {
            try {
                $stmt = $db->prepare("
                    UPDATE guru 
                    SET nama = ?, jenis_kelamin = ?, mata_pelajaran = ?, no_hp = ?, email = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nama, $jk, $mapel, $noHp, $email, $status, $id]);
                $message = "Data guru [{$nama}] berhasil diperbarui!";
            } catch (Exception $e) {
                $message = "Gagal memperbarui guru: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare("DELETE FROM guru WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Data guru berhasil dihapus!";
        }
    }
}

$stmtGuru = $db->query("SELECT * FROM guru ORDER BY nama ASC");
$guruList = $stmtGuru->fetchAll();
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
            <i data-lucide="award" style="color: #10b981;"></i>
            <span>Daftar Guru Pengajar</span>
            <span class="badge" style="background: #ecfdf5; color: #10b981;"><?= count($guruList) ?> Guru</span>
        </div>

        <div class="card-actions">
            <!-- Search Box -->
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" class="form-input" data-table-search="guru-table" placeholder="Cari nama, NIP, mapel...">
            </div>

            <!-- Export CSV -->
            <a href="api/export.php?type=guru" class="btn btn-secondary" title="Export CSV">
                <i data-lucide="download"></i>
                <span>Export CSV</span>
            </a>

            <!-- Tombol Tambah Guru -->
            <button class="btn btn-primary" onclick="openModal('modal-tambah-guru')">
                <i data-lucide="user-plus"></i>
                <span>Tambah Guru</span>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="guru-table">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Mata Pelajaran</th>
                    <th>Kontak (HP/Email)</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($guruList)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 36px;">
                        Belum ada data guru terdaftar.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($guruList as $g): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; color: #1e3a8a;">
                            <?= htmlspecialchars($g['nip']) ?>
                        </td>
                        <td style="font-weight: 600;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                    <?= substr($g['nama'], 0, 1) ?>
                                </div>
                                <div>
                                    <?= htmlspecialchars($g['nama']) ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= $g['jenis_kelamin'] === 'L' ? '#eff6ff' : '#fdf2f8' ?>; color: <?= $g['jenis_kelamin'] === 'L' ? '#1d4ed8' : '#be185d' ?>;">
                                <?= $g['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-weight: 600;">
                                <?= htmlspecialchars($g['mata_pelajaran']) ?>
                            </span>
                        </td>
                        <td style="font-size: 0.825rem;">
                            <div><?= htmlspecialchars($g['no_hp'] ?: '-') ?></div>
                            <div style="color: #64748b;"><?= htmlspecialchars($g['email'] ?: '-') ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $g['status'] === 'Aktif' ? 'badge-aktif' : 'badge-cuti' ?>">
                                <?= htmlspecialchars($g['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn btn-secondary btn-sm" title="Edit Guru" onclick="editGuru(<?= htmlspecialchars(json_encode($g)) ?>)">
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Hapus guru <?= htmlspecialchars(addslashes($g['nama'])) ?>?');">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Guru">
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

<!-- Modal Tambah Guru -->
<div class="modal-backdrop" id="modal-tambah-guru">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Tenaga Pendidik Baru</h3>
            <button class="btn btn-icon-only" onclick="closeModal('modal-tambah-guru')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nomor Induk Pegawai (NIP) *</label>
                    <input type="text" name="nip" class="form-input" required placeholder="Contoh: 198001012005011001" maxlength="25">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama" class="form-input" required placeholder="Contoh: Drs. Ahmad Dahlan, M.Pd.">
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
                        <label class="form-label">Mata Pelajaran Diampu *</label>
                        <input type="text" name="mata_pelajaran" class="form-input" required placeholder="Contoh: Fisika Terapan">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-input" placeholder="guru@sekolah.sch.id">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah-guru')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Guru</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Guru -->
<div class="modal-backdrop" id="modal-edit-guru">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Guru</h3>
            <button class="btn btn-icon-only" onclick="closeModal('modal-edit-guru')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-guru-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">NIP (Tidak dapat diubah)</label>
                    <input type="text" id="edit-guru-nip" class="form-input" readonly style="background: #f1f5f9; cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap & Gelar *</label>
                    <input type="text" name="nama" id="edit-guru-nama" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" id="edit-guru-jk" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mata Pelajaran *</label>
                        <input type="text" name="mata_pelajaran" id="edit-guru-mapel" class="form-input" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" id="edit-guru-hp" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" id="edit-guru-email" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Keaktifan</label>
                    <select name="status" id="edit-guru-status" class="form-select">
                        <option value="Aktif">Aktif</option>
                        <option value="Cuti">Cuti</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-guru')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editGuru(guru) {
    document.getElementById('edit-guru-id').value = guru.id;
    document.getElementById('edit-guru-nip').value = guru.nip;
    document.getElementById('edit-guru-nama').value = guru.nama;
    document.getElementById('edit-guru-jk').value = guru.jenis_kelamin;
    document.getElementById('edit-guru-mapel').value = guru.mata_pelajaran;
    document.getElementById('edit-guru-hp').value = guru.no_hp || '';
    document.getElementById('edit-guru-email').value = guru.email || '';
    document.getElementById('edit-guru-status').value = guru.status || 'Aktif';
    openModal('modal-edit-guru');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
