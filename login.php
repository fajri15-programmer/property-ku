<?php
session_start();
include 'koneksi.php';

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// SINKRONISASI: Menggunakan $_POST agar data aman dan alur session rapi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    
    // KEAMANAN: Verifikasi CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "<script>alert('Permintaan tidak valid. Silakan coba lagi.'); window.location='login.php';</script>";
        exit();
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // KEAMANAN: Menggunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // KEAMANAN: Menggunakan password_verify untuk mencocokkan hash
        if (password_verify($password, $user['password'])) {
            // KEAMANAN: Regenerate session ID untuk mencegah Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
            
            // Reset CSRF token setelah login berhasil
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Alihkan ke halaman landing page setelah berhasil masuk
            header("Location: lending-page.php");
            exit();
        }
    }
    
    // Pesan generik agar penyerang tidak tahu apakah username atau password yang salah
    echo "<script>alert('Username atau Password salah!'); window.location='login.php';</script>";
    exit();
    
    mysqli_stmt_close($stmt);
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
        
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            
            /* KUNCI UTAMA: Mematikan efek membal/karet saat di-scroll mentok */
            overscroll-behavior: none !important; 
            
            overflow-x: hidden !important;
            overflow-y: auto !important;
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
            <!-- KEAMANAN: CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control py-2" placeholder="Masukkan username" required maxlength="50" autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control py-2" placeholder="••••••••" required maxlength="255" autocomplete="current-password">
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