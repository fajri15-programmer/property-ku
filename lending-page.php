<?php
session_start();
include 'koneksi.php';

// PROTEKSI KETAT: Jika tidak ada session logged_in, langsung tendang ke login.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil data rumah dari database MySQL
$query = "SELECT * FROM properties ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!doctype html>
<html lang="id">
<head>
    <title>PropertyKu | Temukan Rumah Impian Anda</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* KUNCI TOTAL: Menghilangkan semua sela kosong di ujung atas dan ujung bawah halaman */
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

        /* Menghilangkan sela putih jumbotron header hijau */
        header.bg-success {
            margin: 0 !important;
            padding-top: 80px !important;
            padding-bottom: 80px !important;
            border: none !important;
            border-radius: 0 !important;
        }

        /* Memaksa kontainer properti bersih dari margin collapse yang bikin sela */
        #daftar-properti {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 60px !important;
            padding-bottom: 60px !important;
        }

        /* Memaksa footer menempel rapat tanpa celah di bawah */
        footer.bg-success {
            margin-top: auto !important; /* Dorong footer ke paling bawah */
            margin-bottom: 0 !important;
            padding: 24px 0 !important;
            border: none !important;
            border-radius: 0 !important;
        }

        .btn-keluar-custom {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .btn-keluar-custom:hover {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            border-color: #dc3545 !important;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.4);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow" style="margin: 0 !important; border: none !important;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">PropertyKu</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavLending">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNavLending">
            <div class="navbar-nav ms-auto align-items-lg-center">
                <a class="nav-link active" href="#">Beranda</a>
                <a class="nav-link" href="dashboard.php">Dashboard Admin</a>
                <a class="nav-link" href="registrasi.php">Registrasi Admin</a>
                <a class="btn btn-keluar-custom ms-lg-3 px-4 mt-2 mt-lg-0" href="logout.php">Keluar</a>
            </div>
        </div>
    </div>
</nav>

<header class="bg-success text-white text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Temukan Rumah Impian Anda</h1>
        <p class="lead mb-0">Solusi hunian modern dan exclusif di lokasi terbaik.</p>
        <a href="#daftar-properti" class="btn btn-warning btn-lg mt-4 fw-bold shadow-sm">Lihat Semua Properti</a>
    </div>
</header>

<section id="daftar-properti" class="container">
    <h2 class="text-center mb-5 fw-bold">Properti Unggulan</h2>
    <div class="row g-4">
        
        <?php 
        while ($p = mysqli_fetch_assoc($result)) { 
            
            $nama_rumah = strtolower($p['nama_properti']);
            
            if (!empty($p['gambar_url']) && file_exists($p['gambar_url'])) {
                $gambar_fjr = $p['gambar_url'];
            } else {
                $gambar_fjr = 'resource/rumah1.jpg'; // Default

                if (strpos($nama_rumah, 'sky') !== false) {
                    $gambar_fjr = 'resource/rumah3.jpg';
                } elseif (strpos($nama_rumah, 'nurhidayat') !== false) {
                    $gambar_fjr = 'resource/rumah2.jpg'; 
                } elseif (strpos($nama_rumah, 'cakra') !== false) {
                    $gambar_fjr = 'resource/rumah1.jpg';
                }
            }
        ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <!-- KEAMANAN: htmlspecialchars pada src gambar -->
                <img src="<?php echo htmlspecialchars($gambar_fjr, ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="Rumah" style="height: 230px; object-fit: cover;">
                <div class="card-body">
                    <!-- KEAMANAN: htmlspecialchars pada semua output data -->
                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($p['nama_properti'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($p['lokasi'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <h5 class="text-success fw-bold">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></h5>
                    
                    <a href="detail.php?id=<?php echo intval($p['id']); ?>" class="btn btn-outline-success w-100 mt-2">Detail</a>
                </div>
            </div>
        </div>
        <?php 
        } 
        ?>

    </div>
</section>

<footer class="bg-success text-white text-center">
    <p class="mb-0">&copy; 2026 PropertyKu. Semua Hak Dilindungi.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>