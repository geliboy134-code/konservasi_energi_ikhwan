<?php
require __DIR__ . '/includes/init.php';

if (is_login()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    $ok = false;
    if ($u) {
        if (password_verify($password, $u['password'])) {
            $ok = true;
            if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE users SET password = ? WHERE id_user = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $u['id_user']]);
            }
        } elseif (hash_equals($u['password'], $password)) {
            // Password lama yang masih teks biasa: terima sekali lalu ubah menjadi hash
            $ok = true;
            $pdo->prepare('UPDATE users SET password = ? WHERE id_user = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $u['id_user']]);
        }
    }

    if ($ok) {
        // Akun pertama (id 1) pada dump lama punya level kosong -> jadikan Admin
        if ($u['level'] === '' && (int)$u['id_user'] === 1) {
            $u['level'] = 'Admin';
            $pdo->prepare("UPDATE users SET level = 'Admin' WHERE id_user = 1")->execute();
        }
        if (!in_array($u['level'], ['Admin', 'Guru', 'Siswa'], true)) {
            flash('error', 'Level akun belum diatur. Hubungi admin.');
            redirect('login.php');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int)$u['id_user'],
            'username' => $u['username'],
            'nama'     => $u['nama'],
            'level'    => $u['level'],
        ];
        redirect('dashboard.php');
    }
    flash('error', 'Username atau password salah.');
    redirect('login.php');
}

$judul = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-box card">
    <h1>⚡ Konservasi Energi</h1>
    <p class="muted" style="text-align:center">Silakan masuk untuk melanjutkan</p>
    <form method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button class="btn" style="width:100%" type="submit">Masuk</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
