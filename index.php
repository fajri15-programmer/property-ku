<?php
include 'koneksi.php';

// Ambil data rumah dari database MySQL agar customer selalu melihat data terbaru
$query = "SELECT * FROM properties WHERE status = 'Tersedia' ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!doctype html>
<html lang="id">
<head>
    <title>PropertyKu | Katalog Rumah Impian</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" />
    <style>
        .btn-whatsapp {
            background-color: #25d366;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-whatsapp:hover {
            background-color: #20ba5a;
            color: white;
            box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">PropertyKu</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link active" href="index.php">Katalog Properti</a>
            <a class="nav-link text-white-50 ms-lg-3" href="login.php">Login Admin</a>
        </div>
    </div>
</nav>

<header class="bg-success text-white py-5 text-center">
    <div class="container py-4">
        <h1 class="display-4 fw-bold">Temukan Rumah Impian Anda</h1>
        <p class="lead">Solusi hunian modern, aman, dan eksklusif di lokasi-lokasi terbaik.</p>
        <a href="#katalog-properti" class="btn btn-warning btn-lg mt-3 fw-bold shadow-sm">Lihat Katalog Kami</a>
    </div>
</header>

<section id="katalog-properti" class="container py-5">
    <h2 class="text-center mb-5 fw-bold text-dark">Daftar Properti Tersedia</h2>
    <div class="row g-4">
        
        <?php 
        while ($p = mysqli_fetch_assoc($result)) { 
            $nama_rumah = strtolower($p['nama_properti']);
            
            // Cek ketersediaan file gambar kustom
            if (!empty($p['gambar_url']) && file_exists($p['gambar_url'])) {
                $gambar_fjr = $p['gambar_url'];
            } else {
                $gambar_fjr = 'resource/rumah1.jpg'; // Default jika kosong
                if (strpos($nama_rumah, 'sky') !== false) {
                    $gambar_fjr = 'resource/rumah3.jpg';
                } elseif (strpos($nama_rumah, 'nurhidayat') !== false) {
                    $gambar_fjr = 'resource/rumah2.jpg'; 
                }
            }
            
            // Link WhatsApp Otomatis berisi pesan teks yang rapi beserta nomor FJR
            $pesan_wa = urlencode("Halo Admin Property, saya tertarik dengan properti '" . $p['nama_properti'] . "' di lokasi " . $p['lokasi'] . ". Apakah masih tersedia?");
            $link_wa = "https://api.whatsapp.com/send?phone=6289520392338&text=" . $pesan_wa; 
        ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                <!-- SINKRONISASI: Menambahkan inline style agar tinggi semua gambar seragam dan proporsional -->
                <img src="<?php echo $gambar_fjr; ?>" class="card-img-top" alt="Rumah" style="height: 230px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-dark"><?php echo $p['nama_properti']; ?></h5>
                    <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> <?php echo $p['lokasi']; ?></p>
                    <h5 class="text-success fw-bold mb-3 mt-auto">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></h5>
                    
                    <a href="<?php echo $link_wa; ?>" target="_blank" class="btn btn-whatsapp w-100 py-2">
                        Hubungi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>
</section>

<footer class="bg-success text-white text-center py-4 mt-5">
    <p class="mb-0">&copy; 2026 PropertyKu. Semua Hak Dilindungi.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>