<?php
session_start();
include 'koneksi.php';

// PROTEKSI KETAT: Jika tidak ada session logged_in, langsung tendang ke login.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: lending-page.php");
    exit();
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM properties WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$p = mysqli_fetch_assoc($result);

// Jika data properti tidak ditemukan
if (!$p) {
    echo "<script>alert('Properti tidak ditemukan!'); window.location='lending-page.php';</script>";
    exit();
}

$nama_rumah = strtolower($p['nama_properti']);
if (!empty($p['gambar_url']) && file_exists($p['gambar_url'])) {
    $gambar_fjr = $p['gambar_url'];
} else {
    $gambar_fjr = 'resource/rumah1.jpg'; // Default
    if (strpos($nama_rumah, 'sky') !== false) { $gambar_fjr = 'resource/rumah3.jpg'; }
    elseif (strpos($nama_rumah, 'nurhidayat') !== false) { $gambar_fjr = 'resource/rumah2.jpg'; }
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Detail - <?php echo $p['nama_properti']; ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
        /* KUNCI UTAMA: Menghilangkan efek membal dan sela putih di ujung atas/bawah */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overscroll-behavior: none !important; 
            overflow-x: hidden !important;
            background-color: #f8f9fa !important;
        }

        body {
            padding-top: 56px !important; /* Pas dengan navbar fixed-top */
            display: flex !important;
            flex-direction: column !important;
        }

        .container-detail {
            margin-top: 40px !important;
            margin-bottom: 40px !important;
        }

        footer {
            margin-top: auto !important; /* Dorong footer ke paling bawah */
            margin-bottom: 0 !important;
            border: none !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow" style="margin: 0 !important; border: none !important;">
    <div class="container">
        <a class="navbar-brand fw-bold" href="lending-page.php">PropertyKu</a>
        <a class="btn btn-outline-light btn-sm" href="lending-page.php">Kembali ke Beranda</a>
    </div>
</nav>

<div class="container container-detail py-4">
    <div class="row g-4 bg-white p-4 rounded shadow-sm mx-1">
        <div class="col-md-6">
            <img src="<?php echo $gambar_fjr; ?>" class="img-fluid rounded shadow-sm" alt="Foto Rumah" style="width: 100%; max-height: 380px; object-fit: cover;">
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-center">
            <div class="mb-2">
                <span class="badge bg-success fs-6"><?php echo $p['status']; ?></span>
            </div>
            <h1 class="fw-bold text-dark mb-1"><?php echo $p['nama_properti']; ?></h1>
            <p class="text-muted mb-3"><i class="bi bi-geo-alt-fill"></i> Lokasi: <?php echo $p['lokasi']; ?></p>
            <h3 class="text-success fw-bold mb-4">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></h3>
            
            <div class="p-3 bg-light rounded">
                <h5 class="fw-bold text-secondary mb-2">Spesifikasi Properti (Info Internal):</h5>
                <table class="table table-borderless table-sm mb-0 bg-transparent">
                    <tr>
                        <td style="width: 150px;" class="text-muted">Kondisi Bangunan</td>
                        <td>: Modern & Siap Huni</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sertifikat</td>
                        <td>: Hak Milik (SHM)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Akses Jalan</td>
                        <td>: Masuk Mobil / Strategis</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="bg-success text-white text-center py-4">
    <p class="mb-0">&copy; 2026 PropertyKu. Semua Hak Dilindungi.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>