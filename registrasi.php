<?php
session_start();
include 'koneksi.php';

// PROTEKSI: Pastikan hanya admin yang sudah login yang bisa mendaftarkan admin baru
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = "";
$status_pesan = "";

// Proses ketika tombol Daftar diklik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    
    // KEAMANAN: Verifikasi CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $pesan = "Permintaan tidak valid. Silakan coba lagi.";
        $status_pesan = "danger";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validasi panjang minimum
        if (strlen($username) < 3 || strlen($username) > 50) {
            $pesan = "Username harus antara 3-50 karakter.";
            $status_pesan = "danger";
        } elseif (strlen($password) < 6) {
            $pesan = "Password minimal 6 karakter.";
            $status_pesan = "danger";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $pesan = "Username hanya boleh berisi huruf, angka, dan underscore.";
            $status_pesan = "danger";
        } else {
            // KEAMANAN: Cek username dengan Prepared Statement
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt_cek, "s", $username);
            mysqli_stmt_execute($stmt_cek);
            $cek_result = mysqli_stmt_get_result($stmt_cek);
            
            if (mysqli_num_rows($cek_result) > 0) {
                $pesan = "Username sudah digunakan! Silakan pilih username lain.";
                $status_pesan = "danger";
            } else {
                // KEAMANAN: Hash password dengan password_hash
                $password_aman = password_hash($password, PASSWORD_DEFAULT);
                
                // KEAMANAN: Insert dengan Prepared Statement
                $stmt_tambah = mysqli_prepare($koneksi, "INSERT INTO users (username, password) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt_tambah, "ss", $username, $password_aman);
                
                if (mysqli_stmt_execute($stmt_tambah)) {
                    $pesan = "Akun admin baru berhasil didaftarkan!";
                    $status_pesan = "success";
                } else {
                    error_log("Gagal registrasi: " . mysqli_error($koneksi));
                    $pesan = "Gagal mendaftarkan akun. Silakan coba lagi.";
                    $status_pesan = "danger";
                }
                mysqli_stmt_close($stmt_tambah);
            }
            mysqli_stmt_close($stmt_cek);
        }
        
        // Regenerate CSRF token setelah proses
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Registrasi Admin Baru | PropertyKu</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 90px;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="lending-page.php">PropertyKu</a>
        <a class="btn btn-outline-light btn-sm" href="lending-page.php">Kembali ke Beranda</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h3 class="fw-bold text-success text-center mb-4">Registrasi Admin</h3>
                
                <?php if (!empty($pesan)) { ?>
                    <div class="alert alert-<?php echo htmlspecialchars($status_pesan); ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($pesan); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>

                <!-- PERBAIKAN: action mengarah ke registrasi.php (bukan register.php yang tidak ada) -->
                <form action="registrasi.php" method="POST">
                    <!-- KEAMANAN: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username Baru</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username (min. 3 karakter)" required autocomplete="off" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password (min. 6 karakter)" required minlength="6" maxlength="255">
                    </div>
                    <button type="submit" name="register" class="btn btn-success w-100 fw-bold py-2 mt-2">Daftarkan Admin</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>