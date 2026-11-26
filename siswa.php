<?php
require __DIR__ . '/includes/init.php';
require_level(['Admin', 'Guru']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'simpan') {
            $nis   = trim($_POST['nis'] ?? '');
            $lama  = trim($_POST['nis_lama'] ?? '');
            $nama  = trim($_POST['nama'] ?? '');
            $kelas = trim($_POST['kelas'] ?? '');
            if (!ctype_digit($nis) || strlen($nis) > 10 || (int)$nis < 1 || (int)$nis > 2147483647) {
                throw new RuntimeException('NIS harus berupa angka (maksimal 2147483647).');
            }
            if ($nama === '' || $kelas === '' || mb_strlen($nama) > 100 || mb_strlen($kelas) > 10) {
                throw new RuntimeException('Nama wajib diisi dan kelas maksimal 10 karakter.');
            }
            if ($lama === '') {
                $pdo->prepare('INSERT INTO siswa (nis, nama, kelas) VALUES (?, ?, ?)')
                    ->execute([(int)$nis, $nama, $kelas]);
                flash('success', 'Siswa berhasil ditambahkan.');
            } else {
                $pdo->prepare('UPDATE siswa SET nis = ?, nama = ?, kelas = ? WHERE nis = ?')
                    ->execute([(int)$nis, $nama, $kelas, (int)$lama]);
                flash('success', 'Data siswa berhasil diperbarui.');
            }
        } elseif ($aksi === 'hapus') {
            $pdo->prepare('DELETE FROM siswa WHERE nis = ?')->execute([(int)($_POST['nis'] ?? 0)]);
            flash('success', 'Siswa dihapus beserta checklist miliknya.');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    } catch (PDOException $ex) {
        flash('error', $ex->getCode() === '23000' ? 'NIS sudah terdaftar.' : 'Terjadi kesalahan database.');
    }
    redirect('siswa.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM siswa WHERE nis = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare('SELECT * FROM siswa WHERE nama LIKE ? OR kelas LIKE ? OR nis LIKE ? ORDER BY kelas, nama');
    $like = "%$q%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM siswa ORDER BY kelas, nama');
}
$data = $stmt->fetchAll();

$judul = 'Data Siswa';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Data Siswa</h1></div>

<div class="card">
    <h2><?= $edit ? 'Edit Siswa' : 'Tambah Siswa' ?></h2>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="nis_lama" value="<?= $edit ? (int)$edit['nis'] : '' ?>">
        <div class="form-row">
            <div><label>NIS</label><input type="text" name="nis" inputmode="numeric" required value="<?= e($edit['nis'] ?? '') ?>"></div>
            <div><label>Nama</label><input type="text" name="nama" required maxlength="100" value="<?= e($edit['nama'] ?? '') ?>"></div>
            <div><label>Kelas</label><input type="text" name="kelas" required maxlength="10" placeholder="mis. X-1" value="<?= e($edit['kelas'] ?? '') ?>"></div>
        </div>
        <button class="btn" type="submit"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($edit): ?><a class="btn btn-gray" href="siswa.php">Batal</a><?php endif; ?>
    </form>
</div>

<form method="get" class="filter">
    <div><label>Cari</label><input type="text" name="q" value="<?= e($q) ?>" placeholder="NIS / nama / kelas"></div>
    <button class="btn" type="submit">Cari</button>
    <?php if ($q !== ''): ?><a class="btn btn-gray" href="siswa.php">Reset</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Aksi</th></tr>
        <?php if (!$data): ?><tr><td colspan="4" class="muted">Belum ada data.</td></tr><?php endif; ?>
        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= (int)$r['nis'] ?></td>
                <td><?= e($r['nama']) ?></td>
                <td><?= e($r['kelas']) ?></td>
                <td class="aksi">
                    <a class="btn btn-small btn-gray" href="siswa.php?edit=<?= (int)$r['nis'] ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Hapus siswa ini beserta seluruh checklist-nya?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="nis" value="<?= (int)$r['nis'] ?>">
                        <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
