<?php
include 'koneksi.php';

// Ambil ID dari URL dan validasi sebagai angka
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// KEAMANAN: Gunakan intval untuk memastikan ID adalah angka
$id = intval($_GET['id']);

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// KEAMANAN: Menggunakan Prepared Statement
$stmt = mysqli_prepare($koneksi, "SELECT * FROM properties WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$p = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Jika data properti tidak ditemukan
if (!$p) {
    header("Location: index.php");
    exit();
}

$nama_rumah = strtolower($p['nama_properti']);
if (!empty($p['gambar_url']) && file_exists($p['gambar_url'])) {
    $gambar_fjr = $p['gambar_url'];
} else {
    $gambar_fjr = 'resource/rumah1.jpg';
    if (strpos($nama_rumah, 'sky') !== false) { $gambar_fjr = 'resource/rumah3.jpg'; }
    elseif (strpos($nama_rumah, 'nurhidayat') !== false) { $gambar_fjr = 'resource/rumah2.jpg'; }
}
?>
<!doctype html>
<html lang="id">
<head>
    <title>Detail - <?php echo htmlspecialchars($p['nama_properti'], ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 75px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">PropertyKu</a>
        <a class="btn btn-outline-light btn-sm" href="index.php">Kembali</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row g-4 bg-white p-4 rounded shadow-sm">
        <div class="col-md-6">
            <!-- KEAMANAN: htmlspecialchars pada src gambar -->
            <img src="<?php echo htmlspecialchars($gambar_fjr, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded shadow" alt="Foto Rumah" style="width: 100%; max-height: 400px; object-fit: cover;">
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-center">
            <!-- KEAMANAN: htmlspecialchars pada semua output data -->
            <span class="badge bg-success mb-2 align-self-start"><?php echo htmlspecialchars($p['status'], ENT_QUOTES, 'UTF-8'); ?></span>
            <h1 class="fw-bold text-dark"><?php echo htmlspecialchars($p['nama_properti'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-muted"><i class="bi bi-geo-alt-fill"></i> Lokasi: <?php echo htmlspecialchars($p['lokasi'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h3 class="text-success fw-bold mb-4">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></h3>
            
            <div class="p-3 bg-light rounded mb-4">
                <h5>Spesifikasi Rumah:</h5>
                <ul>
                    <li>Kondisi Bangunan: Modern & Siap Huni</li>
                    <li>Akses Jalan: Strategis</li>
                    <li>Sertifikat: Hak Milik (SHM)</li>
                </ul>
            </div>
            
            <!-- Link ke WhatsApp Hubungi Marketing -->
            <a href="https://wa.me/628123456789?text=<?php echo urlencode('Halo Admin, saya tertarik dengan properti ' . $p['nama_properti'] . ' di ' . $p['lokasi']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg fw-bold">
                Hubungi Lewat WhatsApp
            </a>
        </div>
    </div>
</div>

<footer class="bg-success text-white text-center py-4 mt-5">
    <p class="mb-0">&copy; 2026 PropertyKu. All Rights Reserved.</p>
</footer>
</body>
</html>