# Aplikasi Checklist Konservasi Energi

Aplikasi web PHP + MySQL (PDO) untuk database `konservasi_energi_ikhwan`.

## Cara pasang (XAMPP)
1. Ekstrak folder `konservasi_energi_ikhwan` ke `C:\xampp\htdocs\`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Buka phpMyAdmin -> tab **Import** -> pilih `database/konservasi_energi_ikhwan.sql` -> Go.
   (File ini sudah berisi `CREATE DATABASE`, jadi tidak perlu membuat database dulu.)
4. Buka http://localhost/konservasi_energi_ikhwan/
5. Login: username `danish`, password `danish123` (segera ganti lewat menu Pengguna).

Jika MySQL Anda memakai user/password lain, ubah `config/database.php`.

## Fitur
- Login 3 level: **Admin**, **Guru**, **Siswa** (password disimpan ter-hash)
- Dashboard ringkasan
- CRUD Siswa, Perangkat, dan Pengguna (Pengguna hanya Admin)
- Isi checklist per tahapan SEBELUM / SELAMA / SESUDAH / 5R, status Ya/Tidak, kondisi, keterangan
- Detail checklist + cetak
- Laporan per periode & kelas, rekap per perangkat, unduh CSV, cetak

## Catatan
- Akun level **Siswa**: isi *username* dengan **NIS** siswa agar akun terhubung ke data siswa.
  Siswa hanya bisa melihat dan mengisi checklist miliknya sendiri.
- Kolom `nis` bertipe `int(10)`, sehingga NIS maksimal 2147483647.
- Menghapus siswa/perangkat ikut menghapus checklist/detail terkait (sesuai relasi di database).

## Struktur
```
config/database.php   koneksi
includes/             init, header, footer
assets/style.css      tampilan
database/*.sql        database + data awal
*.php                 halaman (login, dashboard, siswa, perangkat, users, checklist*, laporan)
```
