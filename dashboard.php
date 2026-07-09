<?php
session_start();
include 'koneksi.php';

// PROTEKSI KETAT: Jika tidak ada session logged_in, langsung tendang ke login.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ambil data rumah dari database
$query = "SELECT * FROM properties ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!doctype html>
<html lang="id">
<head>
    <title>Dashboard | PropertyKu</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css" />
    <style>
        /* KUNCI TOTAL: Mematikan efek karet/membal di dashboard */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: #f8f9fa;
            
            /* Mengunci overscroll di layar utama */
            overscroll-behavior: none !important; 
            overflow-x: hidden !important;
        }

        /* Setingan untuk sidebar kamu */
        .sidebar-nav-custom {
            background-color: #198754 !important; /* Hijau Bootstrap bg-success */
            overscroll-behavior: none !important;
        }

        /* Pembungkus konten utama di sebelah kanan sidebar */
        .col-md-10, .main-content-custom { 
            height: 100vh !important;
            overflow-y: auto !important;
            
            /* Mengunci overscroll khusus di area konten/tabel agar pas di-scroll tidak membal */
            overscroll-behavior-y: none !important; 
            padding: 20px !important;
        }

        /* Style tambahan untuk link aktif di sidebar kamu */
        .active-link-custom {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            font-weight: bold;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff !important;
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar-nav-custom p-3 min-vh-100">
            <h4 class="text-white fw-bold mb-4">PropertyKu</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link active-link-custom rounded">Dashboard</a>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="index.php" target="_blank" class="nav-link rounded text-white">
                        👁️Lihat Web Publik
                    </a>
                </li>
                
                <li class="nav-item mb-2">
                    <a href="lending-page.php" class="nav-link rounded">
                        Lihat Properti
                    </a>
                </li>

                <li class="nav-item mb-2 mt-4">
                    <a href="logout.php" class="nav-link btn-logout-sidebar rounded text-white-50">Logout</a>
                </li>
            </ul>
        </div>

        <main class="col-md-10 p-4">
            <h2 class="mb-4 fw-bold text-success">Ringkasan Properti</h2>

            <div class="card p-4 mb-4 border-0 shadow-sm rounded-3">
                <h5 class="mb-3 fw-bold text-dark">Tambah Properti Baru</h5>
                <form class="row g-2 align-items-center" action="proses.php?aksi=tambah" method="POST" enctype="multipart/form-data">
                    <!-- KEAMANAN: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="col-md-3">
                        <input type="text" name="nama_properti" class="form-control" placeholder="Nama Perumahan" required maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="lokasi" class="form-control" placeholder="Lokasi" required maxlength="200">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="harga" class="form-control" placeholder="Harga Angka Murni" required min="1">
                    </div>
                    <div class="col-md-2">
                        <input type="file" name="foto_properti" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100 fw-bold">Simpan</button>
                    </div>
                </form>
            </div>

            <div class="card p-4 border-0 shadow-sm rounded-3">
                <h5 class="mb-3 fw-bold text-dark">Daftar Properti</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-success text-dark">
                            <tr>
                                <th>Nama Properti</th>
                                <th>Lokasi</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = mysqli_fetch_assoc($result)) { ?>
                            <tr id="row-<?php echo intval($p['id']); ?>"
                                data-id="<?php echo intval($p['id']); ?>"
                                data-nama="<?php echo htmlspecialchars($p['nama_properti'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-lokasi="<?php echo htmlspecialchars($p['lokasi'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-harga="<?php echo intval($p['harga']); ?>"
                                data-status="<?php echo htmlspecialchars($p['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                <td><strong class="text-dark"><?php echo htmlspecialchars($p['nama_properti'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['lokasi'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-success fw-bold">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-success"><?php echo htmlspecialchars($p['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning fw-semibold me-1" 
                                            onclick="bukaModalEdit(this.closest('tr'))">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger fw-semibold" 
                                            onclick="hapusProperti(this.closest('tr'))">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="proses.php?aksi=edit" method="POST" enctype="multipart/form-data">
                <!-- KEAMANAN: CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success">Edit Data Properti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Perumahan</label>
                        <input type="text" name="nama_properti" id="edit-nama" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lokasi</label>
                        <input type="text" name="lokasi" id="edit-lokasi" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Harga</label>
                        <input type="number" name="harga" id="edit-harga" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" id="edit-status" class="form-select">
                            <option value="Tersedia">Tersedia</option>
                            <option value="Terjual">Terjual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ganti Foto Rumah (Kosongkan jika tidak ingin diubah)</label>
                        <input type="file" name="foto_properti" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                </div>
                <div class="modal-header justify-content-end gap-2 border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// KEAMANAN: CSRF token disimpan di variabel JS untuk digunakan di fetch
var csrfToken = <?php echo json_encode($_SESSION['csrf_token']); ?>;

// KEAMANAN: Data diambil dari data-attributes (bukan inline PHP di dalam JS)
function bukaModalEdit(row) {
    document.getElementById('edit-id').value = row.dataset.id;
    document.getElementById('edit-nama').value = row.dataset.nama;
    document.getElementById('edit-lokasi').value = row.dataset.lokasi;
    document.getElementById('edit-harga').value = row.dataset.harga;
    document.getElementById('edit-status').value = row.dataset.status;
    
    var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
    myModal.show();
}

// KEAMANAN: Hapus sekarang menggunakan POST + CSRF token
function hapusProperti(row) {
    var id = row.dataset.id;
    var nama = row.dataset.nama;
    
    if (confirm("Apakah Anda yakin ingin menghapus properti '" + nama + "'?")) {
        fetch('proses.php?aksi=hapus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                document.getElementById('row-' + id).remove();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan sistem saat menghapus data.");
        });
    }
}
</script>
</body>
</html>