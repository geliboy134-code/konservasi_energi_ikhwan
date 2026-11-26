<?php
require __DIR__ . '/includes/init.php';
require_login();

$isSiswa = level() === 'Siswa';
$nisSiswa = $isSiswa ? (int)$_SESSION['user']['username'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $id = (int)($_POST['id_checklist'] ?? 0);
        if ($isSiswa) {
            $stmt = $pdo->prepare('DELETE FROM checklist WHERE id_checklist = ? AND nis = ?');
            $stmt->execute([$id, $nisSiswa]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM checklist WHERE id_checklist = ?');
            $stmt->execute([$id]);
        }
        flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Checklist dihapus.' : 'Checklist tidak ditemukan.');
    }
    redirect('checklist.php');
}

$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';
$cari   = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($isSiswa) {
    $where[] = 'c.nis = ?';
    $params[] = $nisSiswa;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
    $where[] = 'c.tanggal >= ?';
    $params[] = $dari;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    $where[] = 'c.tanggal <= ?';
    $params[] = $sampai;
}
if ($cari !== '' && !$isSiswa) {
    $where[] = '(s.nama LIKE ? OR s.kelas LIKE ? OR s.nis LIKE ?)';
    array_push($params, "%$cari%", "%$cari%", "%$cari%");
}
$sql = 'SELECT c.id_checklist, c.tanggal, c.jam, s.nis, s.nama, s.kelas,
               SUM(d.status = "Ya") ya, SUM(d.status = "Tidak") tidak
        FROM checklist c
        JOIN siswa s ON s.nis = c.nis
        LEFT JOIN detail_checklist d ON d.id_checklist = c.id_checklist'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' GROUP BY c.id_checklist, c.tanggal, c.jam, s.nis, s.nama, s.kelas
        ORDER BY c.tanggal DESC, c.jam DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$judul = 'Checklist';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Checklist Konservasi Energi</h1>
    <a class="btn" href="checklist_tambah.php">+ Isi Checklist Baru</a>
</div>

<form method="get" class="filter">
    <div><label>Dari tanggal</label><input type="date" name="dari" value="<?= e($dari) ?>"></div>
    <div><label>Sampai tanggal</label><input type="date" name="sampai" value="<?= e($sampai) ?>"></div>
    <?php if (!$isSiswa): ?>
        <div><label>Cari siswa</label><input type="text" name="q" value="<?= e($cari) ?>" placeholder="NIS / nama / kelas"></div>
    <?php endif; ?>
    <button class="btn" type="submit">Filter</button>
    <a class="btn btn-gray" href="checklist.php">Reset</a>
</form>

<div class="table-wrap">
    <table>
        <tr><th>Tanggal</th><th>Jam</th><th>Siswa</th><th>Kelas</th><th>Ya</th><th>Tidak</th><th>Aksi</th></tr>
        <?php if (!$data): ?><tr><td colspan="7" class="muted">Belum ada checklist.</td></tr><?php endif; ?>
        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= tgl_id($r['tanggal']) ?></td>
                <td><?= e(substr($r['jam'], 0, 5)) ?></td>
                <td><?= e($r['nama']) ?> <span class="muted">(<?= (int)$r['nis'] ?>)</span></td>
                <td><?= e($r['kelas']) ?></td>
                <td><span class="badge badge-ya"><?= (int)$r['ya'] ?></span></td>
                <td><span class="badge badge-tidak"><?= (int)$r['tidak'] ?></span></td>
                <td class="aksi">
                    <a class="btn btn-small" href="checklist_detail.php?id=<?= (int)$r['id_checklist'] ?>">Detail</a>
                    <form method="post" onsubmit="return confirm('Hapus checklist ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_checklist" value="<?= (int)$r['id_checklist'] ?>">
                        <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
