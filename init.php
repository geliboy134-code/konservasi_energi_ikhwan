<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
require __DIR__ . '/../config/database.php';

const TAHAPAN = ['SEBELUM', 'SELAMA', 'SESUDAH', '5R'];

function e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function is_login(): bool
{
    return isset($_SESSION['user']);
}

function level(): string
{
    return $_SESSION['user']['level'] ?? '';
}

function require_login(): void
{
    if (!is_login()) {
        header('Location: login.php');
        exit;
    }
}

function require_level(array $levels): void
{
    require_login();
    if (!in_array(level(), $levels, true)) {
        http_response_code(403);
        exit('Akses ditolak. <a href="dashboard.php">Kembali</a>');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Token tidak valid. Silakan muat ulang halaman.');
    }
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = [$type, $msg];
}

function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function tgl_id(string $date): string
{
    $t = strtotime($date);
    return $t ? date('d/m/Y', $t) : $date;
}
