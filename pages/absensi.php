<?php
// pages/absensi.php
$pageTitle = 'Presensi Harian Siswa & Guru';
require_once __DIR__ . '/../includes/header.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$role = $_GET['role'] ?? 'siswa'; // 'siswa' or 'guru'
$kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;

$message = null;

// Handle Bulk Save POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetRole = $_POST['target_role'] ?? 'siswa';
    $postTanggal = $_POST['post_tanggal'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? [];
    $keterangans = $_POST['keterangan'] ?? [];
    $currentTime = date('H:i:s');

    if ($targetRole === 'siswa') {
        $stmtSave = $db->prepare("
            INSERT INTO absensi_siswa (siswa_id, tanggal, status, waktu_scan, keterangan, metode)
            VALUES (?, ?, ?, ?, ?, 'manual')
            ON CONFLICT(siswa_id, tanggal) DO UPDATE SET
                status = excluded.status,
                keterangan = excluded.keterangan
        ");
        foreach ($statuses as $sId => $st) {
            $ket = $keterangans[$sId] ?? null;
            $stmtSave->execute([(int)$sId, $postTanggal, $st, $currentTime, $ket]);
        }
        $message = "Presensi siswa tanggal [{$postTanggal}] berhasil disimpan!";
    } else {
        $stmtSaveGuru = $db->prepare("
            INSERT INTO absensi_guru (guru_id, tanggal, status, waktu_absen, keterangan)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT(guru_id, tanggal) DO UPDATE SET
                status = excluded.status,
                keterangan = excluded.keterangan
        ");
        foreach ($statuses as $gId => $st) {
            $ket = $keterangans[$gId] ?? null;
            $stmtSaveGuru->execute([(int)$gId, $postTanggal, $st, $currentTime, $ket]);
        }
        $message = "Presensi guru tanggal [{$postTanggal}] berhasil disimpan!";
    }
}

// Fetch classes
$kelasList = $db->query("SELECT * FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();
if (!$kelasId && !empty($kelasList) && $role === 'siswa') {
    $kelasId = (int)$kelasList[0]['id'];
}

// Load List based on role
if ($role === 'siswa') {
    $sql = "
        SELECT s.id, s.nisn, s.nama, s.jenis_kelamin, k.nama_kelas,
               a.status as presensi_status, a.waktu_scan, a.metode, a.keterangan
        FROM siswa s
        JOIN kelas k ON s.kelas_id = k.id
        LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND a.tanggal = ?
        WHERE s.status = 'Aktif'
    ";
    $params = [$tanggal];
    if ($kelasId) {
        $sql .= " AND s.kelas_id = ?";
        $params[] = $kelasId;
    }
    $sql .= " ORDER BY s.nama ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
} else {
    $sql = "
        SELECT g.id, g.nip, g.nama, g.mata_pelajaran,
               a.status as presensi_status, a.waktu_absen as waktu_scan, a.keterangan
        FROM guru g
        LEFT JOIN absensi_guru a ON a.guru_id = g.id AND a.tanggal = ?
        WHERE g.status = 'Aktif'
        ORDER BY g.nama ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$tanggal]);
    $records = $stmt->fetchAll();
}

// Attendance summary counters for today
$countHadir = 0; $countIzin = 0; $countSakit = 0; $countAlpa = 0; $countBelum = 0;
foreach ($records as $r) {
    $st = $r['presensi_status'];
    if ($st === 'Hadir') $countHadir++;
    elseif ($st === 'Izin') $countIzin++;
    elseif ($st === 'Sakit') $countSakit++;
    elseif ($st === 'Alpa') $countAlpa++;
    else $countBelum++;
}
?>

<?php if ($message): ?>
<div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
    <i data-lucide="check-circle-2"></i>
    <span><?= htmlspecialchars($message) ?></span>
</div>
<?php endif; ?>

<!-- Filter & Tab Bar -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <!-- Tab Switcher -->
        <div style="display: flex; gap: 8px;">
            <a href="index.php?page=absensi&role=siswa&tanggal=<?= urlencode($tanggal) ?>&kelas_id=<?= $kelasId ?>" 
               class="btn <?= $role === 'siswa' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size: 0.85rem;">
                <i data-lucide="users"></i>
                <span>Presensi Siswa</span>
            </a>
            <a href="index.php?page=absensi&role=guru&tanggal=<?= urlencode($tanggal) ?>" 
               class="btn <?= $role === 'guru' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size: 0.85rem;">
                <i data-lucide="award"></i>
                <span>Presensi Guru</span>
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="index.php" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <input type="hidden" name="page" value="absensi">
            <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">

            <div style="display: flex; align-items: center; gap: 8px;">
                <label class="form-label" style="margin: 0; white-space: nowrap;">Tanggal:</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-input" style="width: auto; padding: 7px 12px;" onchange="this.form.submit()">
            </div>

            <?php if ($role === 'siswa'): ?>
            <div style="display: flex; align-items: center; gap: 8px;">
                <label class="form-label" style="margin: 0; white-space: nowrap;">Kelas:</label>
                <select name="kelas_id" class="form-select" style="width: auto; padding: 7px 12px;" onchange="this.form.submit()">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelasId === (int)$k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <a href="index.php?page=scanner" class="btn btn-secondary" style="background: #10b981; color: #fff; border: none;">
                <i data-lucide="qr-code"></i>
                <span>Scan QR</span>
            </a>
        </form>
    </div>
</div>

<!-- Summary Badges Bar -->
<div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 130px; background: #ffffff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">HADIR</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #10b981;"><?= $countHadir ?></div>
    </div>
    <div style="flex: 1; min-width: 130px; background: #ffffff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">IZIN</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #f59e0b;"><?= $countIzin ?></div>
    </div>
    <div style="flex: 1; min-width: 130px; background: #ffffff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; border-left: 4px solid #6366f1;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">SAKIT</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #6366f1;"><?= $countSakit ?></div>
    </div>
    <div style="flex: 1; min-width: 130px; background: #ffffff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">ALPA</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #ef4444;"><?= $countAlpa ?></div>
    </div>
    <div style="flex: 1; min-width: 130px; background: #ffffff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 12px 16px; border-left: 4px solid #94a3b8;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">BELUM DIISI</div>
        <div style="font-size: 1.35rem; font-weight: 800; color: #64748b;"><?= $countBelum ?></div>
    </div>
</div>

<!-- Table Sheet -->
<form method="POST">
    <input type="hidden" name="target_role" value="<?= htmlspecialchars($role) ?>">
    <input type="hidden" name="post_tanggal" value="<?= htmlspecialchars($tanggal) ?>">

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i data-lucide="calendar" style="color: #2563eb;"></i>
                <span>Lembar Presensi <?= $role === 'siswa' ? 'Siswa' : 'Guru' ?></span>
                <span class="badge" style="background: #eff6ff; color: #2563eb;"><?= date('d F Y', strtotime($tanggal)) ?></span>
            </div>

            <div class="card-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setAllHadir()">
                    <i data-lucide="check-check"></i>
                    <span>Tandai Semua Hadir</span>
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save"></i>
                    <span>Simpan Presensi</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th><?= $role === 'siswa' ? 'NISN / Nama Siswa' : 'NIP / Nama Guru' ?></th>
                        <th style="text-align: center; width: 340px;">Pilihan Status Presensi</th>
                        <th>Waktu</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 36px;">
                            Tidak ada anggota yang terdaftar di kelas / daftar ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($records as $r): ?>
                        <?php 
                        $currentStatus = $r['presensi_status'] ?? 'Hadir'; // default Hadir
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <div style="font-weight: 600; color: var(--color-text-main);">
                                    <?= htmlspecialchars($r['nama']) ?>
                                </div>
                                <div style="font-size: 0.775rem; color: #64748b; font-family: monospace;">
                                    <?= htmlspecialchars($role === 'siswa' ? $r['nisn'] : $r['nip']) ?>
                                    <?php if ($role === 'siswa'): ?>
                                        • <?= htmlspecialchars($r['nama_kelas']) ?>
                                    <?php else: ?>
                                        • <?= htmlspecialchars($r['mata_pelajaran']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                        <input type="radio" name="status[<?= $r['id'] ?>]" value="Hadir" <?= $currentStatus === 'Hadir' ? 'checked' : '' ?> class="radio-status radio-hadir">
                                        <span>Hadir</span>
                                    </label>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                                        <input type="radio" name="status[<?= $r['id'] ?>]" value="Izin" <?= $currentStatus === 'Izin' ? 'checked' : '' ?> class="radio-status">
                                        <span>Izin</span>
                                    </label>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe;">
                                        <input type="radio" name="status[<?= $r['id'] ?>]" value="Sakit" <?= $currentStatus === 'Sakit' ? 'checked' : '' ?> class="radio-status">
                                        <span>Sakit</span>
                                    </label>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                        <input type="radio" name="status[<?= $r['id'] ?>]" value="Alpa" <?= $currentStatus === 'Alpa' ? 'checked' : '' ?> class="radio-status">
                                        <span>Alpa</span>
                                    </label>
                                </div>
                            </td>
                            <td style="font-family: monospace; font-size: 0.825rem; color: #475569;">
                                <?= htmlspecialchars($r['waktu_scan'] ?: '-') ?>
                                <?php if (($r['metode'] ?? '') === 'qr_scan'): ?>
                                    <span title="Scan QR" style="color: #10b981;">⚡</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <input type="text" name="keterangan[<?= $r['id'] ?>]" value="<?= htmlspecialchars($r['keterangan'] ?? '') ?>" class="form-input" style="padding: 4px 10px; font-size: 0.825rem;" placeholder="Catatan opsional...">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer" style="padding: 16px 24px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; background: #f8fafc;">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="check"></i>
                <span>Simpan Seluruh Lembar Presensi</span>
            </button>
        </div>
    </div>
</form>

<script>
function setAllHadir() {
    const radioHadirs = document.querySelectorAll('.radio-hadir');
    radioHadirs.forEach(r => {
        r.checked = true;
    });
    showToast('Seluruh daftar ditandai Hadir.', 'info');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
