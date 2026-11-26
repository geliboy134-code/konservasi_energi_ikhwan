<?php
require __DIR__ . '/includes/init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT c.*, s.nama, s.kelas FROM checklist c JOIN siswa s ON s.nis = c.nis WHERE c.id_checklist = ?'
);
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c || (level() === 'Siswa' && (int)$c['nis'] !== (int)$_SESSION['user']['username'])) {
    flash('error', 'Checklist tidak ditemukan.');
    redirect('checklist.php');
}

$stmt = $pdo->prepare(
    'SELECT d.*, p.nama_perangkat, p.jenis
     FROM detail_checklist d JOIN perangkat p ON p.id_perangkat = d.id_perangkat
     WHERE d.id_checklist = ? ORDER BY p.jenis, p.nama_perangkat'
);
$stmt->execute([$id]);
$per = [];
foreach ($stmt->fetchAll() as $r) {
    $per[$r['tahapan']][] = $r;
}

$judul = 'Detail Checklist';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Detail Checklist #<?= (int)$c['id_checklist'] ?></h1>
    <div class="no-print">
        <button class="btn btn-gray" onclick="window.print()">Cetak</button>
        <a class="btn btn-light" href="checklist.php">Kembali</a>
    </div>
</div>

<div class="card">
    <div class="grid grid-4">
        <div><span class="muted">Siswa</span><br><strong><?= e($c['nama']) ?></strong></div>
        <div><span class="muted">NIS / Kelas</span><br><strong><?= (int)$c['nis'] ?> / <?= e($c['kelas']) ?></strong></div>
        <div><span class="muted">Tanggal</span><br><strong><?= tgl_id($c['tanggal']) ?></strong></div>
        <div><span class="muted">Jam</span><br><strong><?= e(substr($c['jam'], 0, 5)) ?></strong></div>
    </div>
</div>

<?php foreach (TAHAPAN as $t): ?>
    <?php if (empty($per[$t])) continue; ?>
    <h2>Tahapan <?= e($t) ?></h2>
    <div class="table-wrap" style="margin-bottom:18px">
        <table>
            <tr><th>Perangkat</th><th>Jenis</th><th>Status</th><th>Kondisi</th><th>Keterangan</th></tr>
            <?php foreach ($per[$t] as $r): ?>
                <tr>
                    <td><?= e($r['nama_perangkat']) ?></td>
                    <td><?= e($r['jenis']) ?></td>
                    <td><span class="badge badge-<?= $r['status'] === 'Ya' ? 'ya' : 'tidak' ?>"><?= e($r['status']) ?></span></td>
                    <td><?= e($r['kondisi']) ?></td>
                    <td><?= e($r['keterangan']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endforeach; ?>
<?php if (!$per): ?><p class="muted">Checklist ini belum memiliki detail.</p><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
