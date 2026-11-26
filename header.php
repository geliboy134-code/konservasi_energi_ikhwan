<?php
$judul = $judul ?? 'Konservasi Energi';
$cur = basename($_SERVER['PHP_SELF']);
$menu = [
    'dashboard.php' => ['Dashboard', ['Admin', 'Guru', 'Siswa'],
        '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>'],
    'checklist.php' => ['Checklist', ['Admin', 'Guru', 'Siswa'],
        '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
    'siswa.php'     => ['Siswa', ['Admin', 'Guru'],
        '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>'],
    'perangkat.php' => ['Perangkat', ['Admin', 'Guru'],
        '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>'],
    'laporan.php'   => ['Laporan', ['Admin', 'Guru'],
        '<path d="M3 3v18h18"/><path d="M7 14v3M12 9v8M17 5v12"/>'],
    'users.php'     => ['Pengguna', ['Admin'],
        '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
];
$nama = $_SESSION['user']['nama'] ?? '';
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - Konservasi Energi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= is_login() ? 'app' : 'guest' ?>">
<?php if (is_login()): ?>
<aside class="sidebar no-print">
    <div class="brand">
        <div class="brand-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6z"/></svg></div>
        <div class="brand-text"><strong>Konservasi Energi</strong><span>Checklist sekolah</span></div>
    </div>
    <nav class="side-nav">
        <?php foreach ($menu as $file => [$label, $levels, $ikon]): ?>
            <?php if (in_array(level(), $levels, true)): ?>
                <a href="<?= $file ?>" class="<?= $cur === $file ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><?= $ikon ?></svg><?= $label ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="side-user">
        <div class="avatar"><?= e(mb_strtoupper(mb_substr($nama, 0, 1))) ?></div>
        <div class="info"><strong><?= e($nama) ?></strong><span><?= e(level()) ?></span></div>
        <a href="logout.php" class="keluar">Keluar</a>
    </div>
</aside>
<?php endif; ?>
<main class="container">
<?php foreach (flash_get() as [$type, $msg]): ?>
    <div class="alert alert-<?= e($type) ?>"><?= e($msg) ?></div>
<?php endforeach; ?>
