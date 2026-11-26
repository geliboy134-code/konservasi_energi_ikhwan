<?php
require __DIR__ . '/includes/init.php';
require_login();

$isSiswa = level() === 'Siswa';
$perangkat = $pdo->query('SELECT * FROM perangkat ORDER BY jenis, nama_perangkat')->fetchAll();

$siswaSendiri = null;
$daftarSiswa = [];
if ($isSiswa) {
    $stmt = $pdo->prepare('SELECT * FROM siswa WHERE nis = ?');
    $stmt->execute([(int)$_SESSION['user']['username']]);
    $siswaSendiri = $stmt->fetch() ?: null;
} else {
    $daftarSiswa = $pdo->query('SELECT * FROM siswa ORDER BY kelas, nama')->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $nis = $isSiswa ? (int)($siswaSendiri['nis'] ?? 0) : (int)($_POST['nis'] ?? 0);
        $tanggal = $_POST['tanggal'] ?? '';
        $jam = $_POST['jam'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || !strtotime($tanggal)) {
            throw new RuntimeException('Tanggal tidak valid.');
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $jam)) {
            throw new RuntimeException('Jam tidak valid.');
        }
        $cek = $pdo->prepare('SELECT 1 FROM siswa WHERE nis = ?');
        $cek->execute([$nis]);
        if (!$cek->fetchColumn()) {
            throw new RuntimeException('Siswa tidak ditemukan.');
        }

        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO checklist (nis, tanggal, jam) VALUES (?, ?, ?)')
            ->execute([$nis, $tanggal, $jam . ':00']);
        $idChecklist = (int)$pdo->lastInsertId();

        $ins = $pdo->prepare(
            'INSERT INTO detail_checklist (id_checklist, id_perangkat, tahapan, kondisi, status, keterangan)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $jumlah = 0;
        foreach (TAHAPAN as $t) {
            foreach ($perangkat as $p) {
                $row = $_POST['d'][$t][$p['id_perangkat']] ?? [];
                $status = $row['status'] ?? '';
                if (!in_array($status, ['Ya', 'Tidak'], true)) {
                    continue;
                }
                $kondisi = mb_substr(trim($row['kondisi'] ?? ''), 0, 100);
                $ket = trim($row['keterangan'] ?? '');
                $ins->execute([
                    $idChecklist, $p['id_perangkat'], $t,
                    $kondisi === '' ? null : $kondisi, $status, $ket === '' ? null : $ket,
                ]);
                $jumlah++;
            }
        }
        if ($jumlah === 0) {
            throw new RuntimeException('Isi minimal satu item (pilih Ya atau Tidak).');
        }
        $pdo->commit();
        flash('success', "Checklist tersimpan ($jumlah item).");
        redirect('checklist_detail.php?id=' . $idChecklist);
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', $ex->getMessage());
        redirect('checklist_tambah.php');
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('error', 'Gagal menyimpan checklist.');
        redirect('checklist_tambah.php');
    }
}

$judul = 'Isi Checklist';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Isi Checklist Baru</h1></div>

<?php if ($isSiswa && !$siswaSendiri): ?>
    <div class="alert alert-error">Akun Anda belum terhubung ke data siswa (username harus sama dengan NIS). Hubungi admin.</div>
<?php elseif (!$perangkat): ?>
    <div class="alert alert-info">Belum ada data perangkat. <?php if (level() !== 'Siswa'): ?><a href="perangkat.php">Tambahkan perangkat</a> terlebih dahulu.<?php else: ?>Hubungi admin/guru.<?php endif; ?></div>
<?php elseif (!$isSiswa && !$daftarSiswa): ?>
    <div class="alert alert-info">Belum ada data siswa. <a href="siswa.php">Tambahkan siswa</a> terlebih dahulu.</div>
<?php else: ?>
<form method="post">
    <?= csrf_field() ?>
    <div class="card">
        <div class="form-row">
            <div>
                <label>Siswa</label>
                <?php if ($isSiswa): ?>
                    <input type="text" value="<?= e($siswaSendiri['nama']) ?> (<?= e($siswaSendiri['kelas']) ?>)" disabled>
                <?php else: ?>
                    <select name="nis" required>
                        <option value="">-- pilih siswa --</option>
                        <?php foreach ($daftarSiswa as $s): ?>
                            <option value="<?= (int)$s['nis'] ?>"><?= e($s['kelas']) ?> - <?= e($s['nama']) ?> (<?= (int)$s['nis'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div><label>Tanggal</label><input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"></div>
            <div><label>Jam</label><input type="time" name="jam" required value="<?= date('H:i') ?>"></div>
        </div>
    </div>

    <p class="muted">Pilih <strong>Ya</strong> atau <strong>Tidak</strong> untuk setiap perangkat pada tiap tahapan. Baris yang tidak dipilih tidak akan disimpan.</p>

    <div class="tabs">
        <?php foreach (TAHAPAN as $i => $t): ?>
            <button type="button" class="tab-btn <?= $i === 0 ? 'active' : '' ?>" data-tab="tab-<?= e($t) ?>"><?= e($t) ?></button>
        <?php endforeach; ?>
    </div>

    <?php foreach (TAHAPAN as $i => $t): ?>
        <div class="tab-panel <?= $i === 0 ? 'active' : '' ?>" id="tab-<?= e($t) ?>">
            <div class="page-head" style="margin-bottom:8px">
                <h2 style="margin:0">Tahapan <?= e($t) ?></h2>
                <button type="button" class="btn btn-small btn-light" data-semua="<?= e($t) ?>">Tandai semua "Ya"</button>
            </div>
            <div class="table-wrap">
                <table>
                    <tr><th>Perangkat</th><th>Status</th><th>Kondisi</th><th>Keterangan</th></tr>
                    <?php foreach ($perangkat as $p): $pid = (int)$p['id_perangkat']; ?>
                        <tr>
                            <td><?= e($p['nama_perangkat']) ?><br><span class="muted"><?= e($p['jenis']) ?></span></td>
                            <td class="radio-cell">
                                <label><input type="radio" name="d[<?= e($t) ?>][<?= $pid ?>][status]" value="Ya"> Ya</label>
                                <label><input type="radio" name="d[<?= e($t) ?>][<?= $pid ?>][status]" value="Tidak"> Tidak</label>
                            </td>
                            <td><input type="text" maxlength="100" name="d[<?= e($t) ?>][<?= $pid ?>][kondisi]" placeholder="mis. Menyala / Rusak"></td>
                            <td><input type="text" name="d[<?= e($t) ?>][<?= $pid ?>][keterangan]"></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <div style="margin-top:16px">
        <button class="btn" type="submit">Simpan Checklist</button>
        <a class="btn btn-gray" href="checklist.php">Batal</a>
    </div>
</form>

<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
document.querySelectorAll('[data-semua]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('#tab-' + btn.dataset.semua + ' input[type=radio][value=Ya]').forEach(function (r) { r.checked = true; });
    });
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
