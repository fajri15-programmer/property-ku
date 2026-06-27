<?php
session_start();
include 'koneksi.php';

// --- SISTEM KEAMANAN UTAMA (Mencegah Tembakan URL Langsung) ---
// Jika tidak ada session 'logged_in' atau nilainya bukan true, langsung block aksesnya!
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Kirim status kode error 403 (Akses Dilarang) ke browser
    header('HTTP/1.1 403 Forbidden');
    
    // Jika diakses lewat tombol hapus (AJAX/Fetch), beri respon JSON error
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak! Anda harus login sebagai admin untuk melakukan aksi ini.'
    ]);
    exit(); // Hentikan paksa script di sini agar kode di bawahnya TIDAK DIEKSEKUSI
}
// -------------------------------------------------------------


$aksi = $_GET['aksi'] ?? '';

// 1. PROSES TAMBAH DATA
if ($aksi == 'tambah') {
    $nama_properti = mysqli_real_escape_string($koneksi, $_POST['nama_properti']);
    $lokasi        = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $harga         = mysqli_real_escape_string($koneksi, $_POST['harga']);
    
    $nama_file = $_FILES['foto_properti']['name'];
    $tmp_name  = $_FILES['foto_properti']['tmp_name'];
    $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
    $nama_gambar_baru = 'rumah_' . time() . '.' . $ekstensi;
    $target_dir = "resource/" . $nama_gambar_baru;
    
    if (move_uploaded_file($tmp_name, $target_dir)) {
        $query = "INSERT INTO properties (nama_properti, lokasi, harga, status, gambar_url) 
                  VALUES ('$nama_properti', '$lokasi', '$harga', 'Tersedia', '$target_dir')";
    } else {
        $query = "INSERT INTO properties (nama_properti, lokasi, harga, status, gambar_url) 
                  VALUES ('$nama_properti', '$lokasi', '$harga', 'Tersedia', 'resource/rumah1.jpg')";
    }
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}

// 2. PROSES HAPUS DATA
if ($aksi == 'hapus') {
    $id = intval($_GET['id'] ?? 0);
    
    $cek_query = "SELECT gambar_url FROM properties WHERE id = $id";
    $cek_result = mysqli_query($koneksi, $cek_query);
    if ($row = mysqli_fetch_assoc($cek_result)) {
        if (strpos($row['gambar_url'], 'rumah_') !== false && file_exists($row['gambar_url'])) {
            unlink($row['gambar_url']);
        }
    }
    
    $query = "DELETE FROM properties WHERE id = $id";
    if (mysqli_query($koneksi, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Properti berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit();
}

// 3. PROSES EDIT DATA
if ($aksi == 'edit') {
    $id            = intval($_POST['id']);
    $nama_properti = mysqli_real_escape_string($koneksi, $_POST['nama_properti']);
    $lokasi        = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $harga         = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $status        = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    if (!empty($_FILES['foto_properti']['name'])) {
        $nama_file = $_FILES['foto_properti']['name'];
        $tmp_name  = $_FILES['foto_properti']['tmp_name'];
        $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_gambar_baru = 'rumah_' . time() . '.' . $ekstensi;
        $target_dir = "resource/" . $nama_gambar_baru;
        
        if (move_uploaded_file($tmp_name, $target_dir)) {
            $cek_old = mysqli_query($koneksi, "SELECT gambar_url FROM properties WHERE id = $id");
            if ($old = mysqli_fetch_assoc($cek_old)) {
                if (strpos($old['gambar_url'], 'rumah_') !== false && file_exists($old['gambar_url'])) {
                    unlink($old['gambar_url']);
                }
            }
            
            $query = "UPDATE properties SET 
                      nama_properti = '$nama_properti', 
                      lokasi = '$lokasi', 
                      harga = '$harga', 
                      status = '$status',
                      gambar_url = '$target_dir' 
                      WHERE id = $id";
        }
    } else {
        $query = "UPDATE properties SET 
                  nama_properti = '$nama_properti', 
                  lokasi = '$lokasi', 
                  harga = '$harga', 
                  status = '$status' 
                  WHERE id = $id";
    }
              
    if (mysqli_query($koneksi, $query)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}
?>