<?php
require __DIR__ . '/includes/init.php';
require_level(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    try {
        if ($aksi === 'simpan') {
            $id       = (int)($_POST['id_user'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $nama     = trim($_POST['nama'] ?? '');
            $lvl      = $_POST['level'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($username === '' || $nama === '' || mb_strlen($username) > 50 || mb_strlen($nama) > 100) {
                throw new RuntimeException('Username (maks 50) dan nama (maks 100) wajib diisi.');
            }
            if (!in_array($lvl, ['Admin', 'Guru', 'Siswa'], true)) {
                throw new RuntimeException('Level tidak valid.');
            }
            if ($id === 0) {
                if (strlen($password) < 6) {
                    throw new RuntimeException('Password minimal 6 karakter.');
                }
                $pdo->prepare('INSERT INTO users (username, password, nama, level) VALUES (?, ?, ?, ?)')
                    ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nama, $lvl]);
                flash('success', 'Pengguna berhasil ditambahkan.');
            } else {
                if ($id === $_SESSION['user']['id'] && $lvl !== 'Admin') {
                    throw new RuntimeException('Anda tidak dapat menurunkan level akun Anda sendiri.');
                }
                if ($password !== '') {
                    if (strlen($password) < 6) {
                        throw new RuntimeException('Password minimal 6 karakter.');
                    }
                    $pdo->prepare('UPDATE users SET username = ?, password = ?, nama = ?, level = ? WHERE id_user = ?')
                        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nama, $lvl, $id]);
                } else {
                    $pdo->prepare('UPDATE users SET username = ?, nama = ?, level = ? WHERE id_user = ?')
                        ->execute([$username, $nama, $lvl, $id]);
                }
                if ($id === $_SESSION['user']['id']) {
                    $_SESSION['user']['username'] = $username;
                    $_SESSION['user']['nama'] = $nama;
                }
                flash('success', 'Pengguna berhasil diperbarui.');
            }
        } elseif ($aksi === 'hapus') {
            $id = (int)($_POST['id_user'] ?? 0);
            if ($id === $_SESSION['user']['id']) {
                throw new RuntimeException('Anda tidak dapat menghapus akun Anda sendiri.');
            }
            $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$id]);
            flash('success', 'Pengguna dihapus.');
        }
    } catch (RuntimeException $ex) {
        flash('error', $ex->getMessage());
    } catch (PDOException $ex) {
        flash('error', $ex->getCode() === '23000' ? 'Username sudah dipakai.' : 'Terjadi kesalahan database.');
    }
    redirect('users.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id_user = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}
$data = $pdo->query('SELECT id_user, username, nama, level FROM users ORDER BY level, nama')->fetchAll();

$judul = 'Pengguna';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Manajemen Pengguna</h1></div>

<div class="alert alert-info">Untuk akun level <strong>Siswa</strong>, isi <em>username</em> dengan NIS siswa agar akun terhubung ke data siswa.</div>

<div class="card">
    <h2><?= $edit ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h2>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id_user" value="<?= $edit ? (int)$edit['id_user'] : 0 ?>">
        <div class="form-row">
            <div><label>Username</label><input type="text" name="username" required maxlength="50" value="<?= e($edit['username'] ?? '') ?>"></div>
            <div><label>Nama</label><input type="text" name="nama" required maxlength="100" value="<?= e($edit['nama'] ?? '') ?>"></div>
            <div>
                <label>Level</label>
                <select name="level">
                    <?php foreach (['Admin', 'Guru', 'Siswa'] as $l): ?>
                        <option <?= ($edit['level'] ?? 'Siswa') === $l ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Password <?= $edit ? '<span class="muted">(kosongkan jika tidak diubah)</span>' : '' ?></label>
                <input type="password" name="password" <?= $edit ? '' : 'required' ?> minlength="6" autocomplete="new-password">
            </div>
        </div>
        <button class="btn" type="submit"><?= $edit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($edit): ?><a class="btn btn-gray" href="users.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr><th>Username</th><th>Nama</th><th>Level</th><th>Aksi</th></tr>
        <?php foreach ($data as $r): ?>
            <tr>
                <td><?= e($r['username']) ?></td>
                <td><?= e($r['nama']) ?></td>
                <td><span class="badge"><?= e($r['level'] ?: '-') ?></span></td>
                <td class="aksi">
                    <a class="btn btn-small btn-gray" href="users.php?edit=<?= (int)$r['id_user'] ?>">Edit</a>
                    <?php if ((int)$r['id_user'] !== $_SESSION['user']['id']): ?>
                        <form method="post" onsubmit="return confirm('Hapus pengguna ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id_user" value="<?= (int)$r['id_user'] ?>">
                            <button class="btn btn-small btn-danger" type="submit">Hapus</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
