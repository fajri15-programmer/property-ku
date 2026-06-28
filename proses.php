<?php
session_start();
include 'koneksi.php';

// --- SISTEM KEAMANAN UTAMA (Mencegah Tembakan URL Langsung) ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak! Anda harus login sebagai admin untuk melakukan aksi ini.'
    ]);
    exit();
}

// --- FUNGSI KEAMANAN: Validasi CSRF Token ---
function verifikasi_csrf() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// --- FUNGSI KEAMANAN: Validasi File Upload ---
function validasi_upload($file) {
    $errors = [];
    
    // Daftar ekstensi yang diizinkan
    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Daftar MIME type yang diizinkan
    $mime_diizinkan = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    // Ukuran maksimal: 5MB
    $max_size = 5 * 1024 * 1024;
    
    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat upload file.';
        return $errors;
    }
    
    // Cek ekstensi file
    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        $errors[] = 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $ekstensi_diizinkan);
    }
    
    // Cek MIME type asli dari konten file (bukan dari header HTTP yang bisa dipalsukan)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_asli = $finfo->file($file['tmp_name']);
    if (!in_array($mime_asli, $mime_diizinkan)) {
        $errors[] = 'Konten file bukan gambar yang valid.';
    }
    
    // Cek ukuran file
    if ($file['size'] > $max_size) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal 5MB.';
    }
    
    // Cek apakah benar-benar gambar dengan getimagesize
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        $errors[] = 'File yang diupload bukan gambar yang valid.';
    }
    
    return $errors;
}

// --- FUNGSI: Proses upload dan return nama file baru ---
function proses_upload($file) {
    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    // Gunakan random bytes agar nama file tidak bisa ditebak
    $nama_gambar_baru = 'rumah_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
    $target_dir = "resource/" . $nama_gambar_baru;
    
    if (move_uploaded_file($file['tmp_name'], $target_dir)) {
        return $target_dir;
    }
    return false;
}

$aksi = $_GET['aksi'] ?? '';

// =====================================================================
// 1. PROSES TAMBAH DATA
// =====================================================================
if ($aksi == 'tambah') {
    // KEAMANAN: Verifikasi CSRF token
    if (!verifikasi_csrf()) {
        echo "<script>alert('Permintaan tidak valid. Silakan coba lagi.'); window.location='dashboard.php';</script>";
        exit();
    }
    
    $nama_properti = trim($_POST['nama_properti'] ?? '');
    $lokasi        = trim($_POST['lokasi'] ?? '');
    $harga         = intval($_POST['harga'] ?? 0);
    
    // Validasi input dasar
    if (empty($nama_properti) || empty($lokasi) || $harga <= 0) {
        echo "<script>alert('Semua field harus diisi dengan benar.'); window.location='dashboard.php';</script>";
        exit();
    }
    
    $target_dir = 'resource/rumah1.jpg'; // Default
    
    // Validasi dan proses upload file
    if (!empty($_FILES['foto_properti']['name'])) {
        $upload_errors = validasi_upload($_FILES['foto_properti']);
        if (!empty($upload_errors)) {
            $pesan_error = implode('\n', $upload_errors);
            echo "<script>alert('Upload gagal:\\n$pesan_error'); window.location='dashboard.php';</script>";
            exit();
        }
        
        $hasil_upload = proses_upload($_FILES['foto_properti']);
        if ($hasil_upload) {
            $target_dir = $hasil_upload;
        }
    }
    
    // KEAMANAN: Insert dengan Prepared Statement
    $stmt = mysqli_prepare($koneksi, "INSERT INTO properties (nama_properti, lokasi, harga, status, gambar_url) VALUES (?, ?, ?, 'Tersedia', ?)");
    mysqli_stmt_bind_param($stmt, "ssis", $nama_properti, $lokasi, $harga, $target_dir);
    
    if (mysqli_stmt_execute($stmt)) {
        // Regenerate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: dashboard.php");
        exit();
    } else {
        error_log("Gagal menyimpan data: " . mysqli_error($koneksi));
        echo "<script>alert('Gagal menyimpan data. Silakan coba lagi.'); window.location='dashboard.php';</script>";
    }
    mysqli_stmt_close($stmt);
}

// =====================================================================
// 2. PROSES HAPUS DATA (Diubah ke POST untuk keamanan)
// =====================================================================
if ($aksi == 'hapus') {
    header('Content-Type: application/json; charset=utf-8');
    
    // KEAMANAN: Hanya terima method POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
        exit();
    }
    
    // KEAMANAN: Verifikasi CSRF token dari POST body
    $input = json_decode(file_get_contents('php://input'), true);
    $csrf_token = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
    
    if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Token keamanan tidak valid.']);
        exit();
    }
    
    $id = intval($input['id'] ?? ($_GET['id'] ?? 0));
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
        exit();
    }
    
    // Hapus file gambar terkait (dengan prepared statement)
    $stmt_cek = mysqli_prepare($koneksi, "SELECT gambar_url FROM properties WHERE id = ?");
    mysqli_stmt_bind_param($stmt_cek, "i", $id);
    mysqli_stmt_execute($stmt_cek);
    $cek_result = mysqli_stmt_get_result($stmt_cek);
    
    if ($row = mysqli_fetch_assoc($cek_result)) {
        if (strpos($row['gambar_url'], 'rumah_') !== false && file_exists($row['gambar_url'])) {
            unlink($row['gambar_url']);
        }
    }
    mysqli_stmt_close($stmt_cek);
    
    // Hapus data dengan prepared statement
    $stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM properties WHERE id = ?");
    mysqli_stmt_bind_param($stmt_hapus, "i", $id);
    
    if (mysqli_stmt_execute($stmt_hapus)) {
        echo json_encode(['status' => 'success', 'message' => 'Properti berhasil dihapus!']);
    } else {
        error_log("Gagal menghapus data: " . mysqli_error($koneksi));
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    mysqli_stmt_close($stmt_hapus);
    exit();
}

// =====================================================================
// 3. PROSES EDIT DATA
// =====================================================================
if ($aksi == 'edit') {
    // KEAMANAN: Verifikasi CSRF token
    if (!verifikasi_csrf()) {
        echo "<script>alert('Permintaan tidak valid. Silakan coba lagi.'); window.location='dashboard.php';</script>";
        exit();
    }
    
    $id            = intval($_POST['id'] ?? 0);
    $nama_properti = trim($_POST['nama_properti'] ?? '');
    $lokasi        = trim($_POST['lokasi'] ?? '');
    $harga         = intval($_POST['harga'] ?? 0);
    $status        = $_POST['status'] ?? '';
    
    // Validasi input
    if ($id <= 0 || empty($nama_properti) || empty($lokasi) || $harga <= 0) {
        echo "<script>alert('Semua field harus diisi dengan benar.'); window.location='dashboard.php';</script>";
        exit();
    }
    
    // Validasi status hanya nilai yang diizinkan
    $status_diizinkan = ['Tersedia', 'Terjual'];
    if (!in_array($status, $status_diizinkan)) {
        $status = 'Tersedia';
    }
    
    if (!empty($_FILES['foto_properti']['name'])) {
        // Validasi file upload
        $upload_errors = validasi_upload($_FILES['foto_properti']);
        if (!empty($upload_errors)) {
            $pesan_error = implode('\n', $upload_errors);
            echo "<script>alert('Upload gagal:\\n$pesan_error'); window.location='dashboard.php';</script>";
            exit();
        }
        
        $hasil_upload = proses_upload($_FILES['foto_properti']);
        
        if ($hasil_upload) {
            // Hapus gambar lama
            $stmt_old = mysqli_prepare($koneksi, "SELECT gambar_url FROM properties WHERE id = ?");
            mysqli_stmt_bind_param($stmt_old, "i", $id);
            mysqli_stmt_execute($stmt_old);
            $old_result = mysqli_stmt_get_result($stmt_old);
            if ($old = mysqli_fetch_assoc($old_result)) {
                if (strpos($old['gambar_url'], 'rumah_') !== false && file_exists($old['gambar_url'])) {
                    unlink($old['gambar_url']);
                }
            }
            mysqli_stmt_close($stmt_old);
            
            // Update dengan gambar baru (Prepared Statement)
            $stmt = mysqli_prepare($koneksi, "UPDATE properties SET nama_properti = ?, lokasi = ?, harga = ?, status = ?, gambar_url = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssissi", $nama_properti, $lokasi, $harga, $status, $hasil_upload, $id);
        } else {
            echo "<script>alert('Gagal mengupload gambar.'); window.location='dashboard.php';</script>";
            exit();
        }
    } else {
        // Update tanpa ganti gambar (Prepared Statement)
        $stmt = mysqli_prepare($koneksi, "UPDATE properties SET nama_properti = ?, lokasi = ?, harga = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssisi", $nama_properti, $lokasi, $harga, $status, $id);
    }
              
    if (mysqli_stmt_execute($stmt)) {
        // Regenerate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: dashboard.php");
        exit();
    } else {
        error_log("Gagal memperbarui data: " . mysqli_error($koneksi));
        echo "<script>alert('Gagal memperbarui data. Silakan coba lagi.'); window.location='dashboard.php';</script>";
    }
    mysqli_stmt_close($stmt);
}
?>