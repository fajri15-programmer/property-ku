<?php
session_start();
include 'koneksi.php';

// SINKRONISASI: Menggunakan $_POST agar data aman dan alur session rapi
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        
        // Alihkan ke halaman landing page setelah berhasil masuk
        header("Location: lending-page.php");
        exit();
    } else {
        echo "<script>alert('Username atau Password salah!'); window.location='login.php';</script>";
        exit();
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Login | PropertyKu</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" />
    <style>
        /* Background Full Layar */
        body.login-fullscreen {
            background: url('resource/rumah1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* PERBAIKAN: Box dibuat lebih transparan (kontras) & efek kaca diperkuat */
        .login-overlay-card {
            background: rgba(255, 255, 255, 0.75); /* Diturunkan ke 0.75 supaya lebih tembus pandang */
            backdrop-filter: blur(15px); /* Efek blur kaca dinaikkan dari 8px ke 15px biar lebih mantap */
            -webkit-backdrop-filter: blur(15px); /* Dukungan untuk browser Safari/Edge tertentu */
            border: 1px solid rgba(255, 255, 255, 0.4); /* Garis tepi tipis warna putih transparan */
            max-width: 450px;
            width: 100%;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important; /* Bayangan box dibuat lebih tegas */
        }
    </style>
</head>
<body class="login-fullscreen">

<div class="container d-flex justify-content-center">
    <div class="card login-overlay-card p-5 shadow-lg rounded-4">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-success">PropertyKu</h2>
            <h5 class="fw-bold mt-2">Selamat Datang Kembali</h5>
            <p class="text-muted small">Masukkan kredensial untuk mengelola atau melihat properti.</p>
        </div>
        
        <form action="login.php" method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control py-2" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control py-2" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2 mt-3 fw-bold">Masuk Sekarang</button>
        </form>

        <div class="mt-4 text-center small">
            <p class="mb-0 text-muted">Belum punya akun? <a href="#" onclick="alert('Silahkan hubungi pihak terkait')" class="text-decoration-none fw-semibold text-success">Daftar di sini</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>