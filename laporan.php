<?php
require __DIR__ . '/includes/init.php';
require_level(['Admin', 'Guru']);

$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$kelas  = trim($_GET['kelas'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   { $dari = date('Y-m-01'); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) { $sampai = date('Y-m-d'); }

$sql = 'SELECT c.id_checklist, c.tanggal, c.jam, s.nis, s.nama, s.kelas,
               COUNT(d.id_detail) total, SUM(d.status = "Ya") ya, SUM(d.status = "Tidak") tidak
        FROM checklist c
        JOIN siswa s ON s.nis = c.nis
        LEFT JOIN detail_checklist d ON d.id_checklist = c.id_checklist
        WHERE c.tanggal BETWEEN ? AND ?';
$params = [$dari, $sampai];
if ($kelas !== '') {
    $sql .= ' AND s.kelas = ?';
    $params[] = $kelas;
}
$sql .= ' GROUP BY c.id_checklist, c.tanggal, c.jam, s.nis, s.nama, s.kelas
          ORDER BY c.tanggal DESC, c.jam DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Rekap per perangkat pada rentang yang sama
$sql2 = 'SELECT p.nama_perangkat, SUM(d.status = "Ya") ya, SUM(d.status = "Tidak") tidak
         FROM detail_checklist d
         JOIN checklist c ON c.id_checklist = d.id_checklist
         JOIN siswa s ON s.nis = c.nis
         JOIN perangkat p ON p.id_perangkat = d.id_perangkat
         WHERE c.tanggal BETWEEN ? AND ?' . ($kelas !== '' ? ' AND s.kelas = ?' : '') . '
         GROUP BY p.id_perangkat, p.nama_perangkat ORDER BY p.nama_perangkat';
$stmt = $pdo->prepare($sql2);
$stmt->execute($params);
$rekap = $stmt->fetchAll();

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_checklist_' . $dari . '_' . $sampai . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Tanggal', 'Jam', 'NIS', 'Nama', 'Kelas', 'Total Item', 'Ya', 'Tidak']);
    foreach ($data as $r) {
        fputcsv($out, [$r['tanggal'], substr($r['jam'], 0, 5), $r['nis'], $r['nama'], $r['kelas'], $r['total'], (int)$r['ya'], (int)$r['tidak']]);
    }
    fclose($out);
    exit;
}

$daftarKelas = $pdo->query('SELECT DISTINCT kelas FROM siswa ORDER BY kelas')->fetchAll(PDO::FETCH_COLUMN);

$judul = 'Laporan';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Laporan Checklist</h1>
    <div class="no-print">
        <a class="btn btn-light" href="?<?= e(http_build_query(['dari' => $dari, 'sampai' => $sampai, 'kelas' => $kelas, 'export' => 'csv'])) ?>">Unduh CSV</a>
        <button class="btn btn-gray" onclick="window.print()">Cetak</button>
    </div>
</div>

<form method="get" class="filter no-print">
    <div><label>Dari</label><input type="date" name="dari" value="<?= e($dari) ?>"></div>
    <div><label>Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>"></div>
    <div>
        <label>Kelas</label>
        <select name="kelas">
            <option value="">Semua kelas</option>
            <?php foreach ($daftarKelas as $k): ?>
                <option <?= $k === $kelas ? 'selected' : '' ?>><?= e($k) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn" type="submit">Tampilkan</button>
</form>

<p class="muted">Periode <?= tgl_id($dari) ?> s/d <?= tgl_id($sampai) ?><?= $kelas !== '' ? ' &middot; Kelas ' . e($kelas) : '' ?> &middot; <?= count($data) ?> checklist</p>

<h2>Rekap per Perangkat</h2>
<div class="table-wrap" style="margin-bottom:18px">
    <table>
        <tr><th>Perangkat</th><th>Ya</th><th>Tidak</th></tr>
        <?php if (!$rekap): ?><tr><td colspan="3" class="muted">Tidak ada data.</td></tr><?php endif; ?>
        <?php foreach ($rekap as $r): ?>
            <tr>
                <td><?= e($r['nama_perangkat']) ?></td>
                <td><span class="badge badge-ya"><?= (int)$r['ya'] ?></span></td>
                <td><span class="badge badge-tidak"><?= (int)$r['tidak'] ?></span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<h2>Daftar Checklist</h2>
<div class="table-wrap">
    <table>
        <tr><th>Tanggal</th><th>Jam</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Item</th><th>Ya</th><th>Tidak</th></tr>
        <?php if (!$data): ?><tr><td colspan="8" class="muted">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= tgl_id($r['tanggal']) ?></td>
                <td><?= e(substr($r['jam'], 0, 5)) ?></td>
                <td><?= (int)$r['nis'] ?></td>
                <td><a href="checklist_detail.php?id=<?= (int)$r['id_checklist'] ?>"><?= e($r['nama']) ?></a></td>
                <td><?= e($r['kelas']) ?></td>
                <td><?= (int)$r['total'] ?></td>
                <td><?= (int)$r['ya'] ?></td>
                <td><?= (int)$r['tidak'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
