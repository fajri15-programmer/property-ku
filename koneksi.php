<?php
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika pakai XAMPP default
$db   = "perumahanku";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    // KEAMANAN: Jangan tampilkan detail error ke user
    error_log("Koneksi ke database gagal: " . mysqli_connect_error());
    die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
}

// KEAMANAN: Set charset untuk mencegah encoding-based SQL injection
mysqli_set_charset($koneksi, "utf8mb4");
?>