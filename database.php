<?php
// Pengaturan koneksi database (default XAMPP)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'konservasi_energi_ikhwan';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL di XAMPP menyala, database '
        . htmlspecialchars($DB_NAME) . ' sudah diimport, dan config/database.php sudah benar.');
}
