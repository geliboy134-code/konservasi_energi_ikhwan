<?php
require __DIR__ . '/includes/init.php';
require_login();

$isSiswa = level() === 'Siswa';
$nisSiswa = $isSiswa ? (int)$_SESSION['user']['username'] : null;

if ($isSiswa) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM checklist WHERE nis = ?');
    $stmt->execute([$nisSiswa]);
    $totalChecklist = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM checklist WHERE nis = ? AND tanggal = CURDATE()');
    $stmt->execute([$nisSiswa]);
    $hariIni = (int)$stmt->fetchColumn();
    $stmt = $pdo->prepare(
        'SELECT d.tahapan, SUM(d.status = "Ya") ya, SUM(d.status = "Tidak") tidak
         FROM detail_checklist d JOIN checklist c ON c.id_checklist = d.id_checklist
         WHERE c.nis = ? GROUP BY d.tahapan'
    );
    $stmt->execute([$nisSiswa]);
    $perTahap = $stmt->fetchAll(PDO::FETCH_UNIQUE);
    $stmt = $pdo->prepare(
        'SELECT c.id_checklist, c.tanggal, c.jam, s.nama, s.kelas
         FROM checklist c JOIN siswa s ON s.nis = c.nis WHERE c.nis = ?
         ORDER BY c.tanggal DESC, c.jam DESC LIMIT 8'
    );
    $stmt->execute([$nisSiswa]);
    $terbaru = $stmt->fetchAll();
} else {
    $totalSiswa = (int)$pdo->query('SELECT COUNT(*) FROM siswa')->fetchColumn();
    $totalPerangkat = (int)$pdo->query('SELECT COUNT(*) FROM perangkat')->fetchColumn();
    $totalChecklist = (int)$pdo->query('SELECT COUNT(*) FROM checklist')->fetchColumn();
    $hariIni = (int)$pdo->query('SELECT COUNT(*) FROM checklist WHERE tanggal = CURDATE()')->fetchColumn();
    $perTahap = $pdo->query(
        'SELECT tahapan, SUM(status = "Ya") ya, SUM(status = "Tidak") tidak
         FROM detail_checklist GROUP BY tahapan'
    )->fetchAll(PDO::FETCH_UNIQUE);
    $terbaru = $pdo->query(
        'SELECT c.id_checklist, c.tanggal, c.jam, s.nama, s.kelas
         FROM checklist c JOIN siswa s ON s.nis = c.nis
         ORDER BY c.tanggal DESC, c.jam DESC LIMIT 8'
    )->fetchAll();
}

$judul = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Halo, <?= e($_SESSION['user']['nama']) ?> 👋</h1>
    <a class="btn" href="checklist_tambah.php">+ Isi Checklist Baru</a>
</div>

<div class="grid grid-4" style="margin-bottom:18px">
    <?php if (!$isSiswa): ?>
        <div class="card stat"><div class="angka"><?= $totalSiswa ?></div><div class="label">Siswa</div></div>
        <div class="card stat"><div class="angka"><?= $totalPerangkat ?></div><div class="label">Perangkat</div></div>
    <?php endif; ?>
    <div class="card stat"><div class="angka"><?= $totalChecklist ?></div><div class="label">Total Checklist</div></div>
    <div class="card stat"><div class="angka"><?= $hariIni ?></div><div class="label">Checklist Hari Ini</div></div>
</div>

<div class="grid grid-2">
    <div class="card">
        <h2>Rekap Jawaban per Tahapan</h2>
        <div class="table-wrap">
            <table>
                <tr><th>Tahapan</th><th>Ya</th><th>Tidak</th></tr>
                <?php foreach (TAHAPAN as $t): ?>
                    <tr>
                        <td><?= e($t) ?></td>
                        <td><span class="badge badge-ya"><?= (int)($perTahap[$t]['ya'] ?? 0) ?></span></td>
                        <td><span class="badge badge-tidak"><?= (int)($perTahap[$t]['tidak'] ?? 0) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="card">
        <h2>Checklist Terbaru</h2>
        <?php if (!$terbaru): ?>
            <p class="muted">Belum ada checklist.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <tr><th>Tanggal</th><th>Siswa</th><th></th></tr>
                    <?php foreach ($terbaru as $r): ?>
                        <tr>
                            <td><?= tgl_id($r['tanggal']) ?> <span class="muted"><?= e(substr($r['jam'], 0, 5)) ?></span></td>
                            <td><?= e($r['nama']) ?> <span class="muted">(<?= e($r['kelas']) ?>)</span></td>
                            <td><a href="checklist_detail.php?id=<?= (int)$r['id_checklist'] ?>">Lihat</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
