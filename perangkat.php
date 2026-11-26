<?php
require __DIR__ . '/includes/init.php';
require_level(['Admin', 'Guru']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'simpan') {
            $id    = (int)($_POST['id_perangkat'] ?? 0);
            $nama  = trim($_POST['nama_perangkat'] ?? '');
            $jenis = trim($_POST['jenis'] ?? '');
            $ket   = trim($_POST['keterangan'] ?? '');
            if ($nama === '' || $jenis === '' || mb_strlen($nama) > 100 || mb_strlen($jenis) > 50) {
                throw new RuntimeException('Nama (maks 100) dan jenis (maks 50) wajib diisi.');
            }
            $ket = $ket === '' ? null : $ket;
            if ($id === 0) {
                $pdo->prepare('INSERT INTO perangkat (nama_perangkat, jenis, keterangan) VALUES (?, ?, ?)')
                    ->execute([$nama, $jenis, $ket]);
                flash('success', 'Perangkat berhasil ditambahkan.');
            } else {
                $pdo->prepare('UPDATE perangkat SET nama_perangkat = ?, jenis = ?, keterangan = ? WHERE id_perangkat = ?')
                    ->execute([$nama, $jenis, $ket, $id]);
                flash('success', 'Perangkat berhasil diperbarui.');
            }
        } elseif ($aksi === 'hapus') {
            $pdo->prepare('DELETE FROM perangkat WHERE id_perangkat = ?')->execute([(int)($_POST['id_perangkat'] ?? 0)]);
            flash('success', 'Perangkat dihapus (beserta detail checklist yang memakainya).');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    } catch (PDOException $ex) {
        flash('error', 'Terjadi kesalahan database.');
    }
    redirect('perangkat.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM perangkat WHERE id_perangkat = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$data = $pdo->query('SELECT * FROM perangkat ORDER BY jenis, nama_perangkat')->fetchAll();

$judul = 'Data Perangkat';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Data Perangkat</h1></div>

<div class="card">
    <h2><?= $edit ? 'Edit Perangkat' : 'Tambah Perangkat' ?></h2>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id_perangkat" value="<?= $edit ? (int)$edit['id_perangkat'] : 0 ?>">
        <div class="form-row">
            <div><label>Nama Perangkat</label><input type="text" name="nama_perangkat" required maxlength="100" value="<?= e($edit['nama_perangkat'] ?? '') ?>"></div>
            <div><label>Jenis</label><input type="text" name="jenis" required maxlength="50" placeholder="mis. Penerangan" value="<?= e($edit['jenis'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Keterangan</label><textarea name="keterangan"><?= e($edit['keterangan'] ?? '') ?></textarea></div>
        <button class="btn" type="submit"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($edit): ?><a class="btn btn-gray" href="perangkat.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr><th>#</th><th>Nama</th><th>Jenis</th><th>Keterangan</th><th>Aksi</th></tr>
        <?php if (!$data): ?><tr><td colspan="5" class="muted">Belum ada perangkat.</td></tr><?php endif; ?>
        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= (int)$r['id_perangkat'] ?></td>
                <td><?= e($r['nama_perangkat']) ?></td>
                <td><?= e($r['jenis']) ?></td>
                <td><?= e($r['keterangan']) ?></td>
                <td class="aksi">
                    <a class="btn btn-small btn-gray" href="perangkat.php?edit=<?= (int)$r['id_perangkat'] ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Hapus perangkat ini? Detail checklist yang memakainya ikut terhapus.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_perangkat" value="<?= (int)$r['id_perangkat'] ?>">
                        <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
