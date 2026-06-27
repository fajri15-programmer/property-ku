<?php
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika pakai XAMPP default
$db   = "perumahanku";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>