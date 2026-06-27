<?php
session_start();
include 'koneksi.php';

// PROTEKSI KETAT: Jika tidak ada session logged_in, langsung tendang ke login.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
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
        .sidebar-nav-custom { background-color: #198754; min-vh-100; }
        .sidebar-nav-custom .nav-link { color: rgba(255, 255, 255, 0.75); transition: all 0.3s ease; }
        .sidebar-nav-custom .nav-link:hover { color: #ffffff; background-color: rgba(255, 255, 255, 0.1); }
        .active-link-custom { background-color: rgba(255, 255, 255, 0.2); color: #ffffff !important; font-weight: bold; }
        .btn-logout-sidebar { transition: all 0.3s ease; }
        .btn-logout-sidebar:hover { background-color: #dc3545 !important; color: #ffffff !important; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar-nav-custom p-3 min-vh-100">
            <h4 class="text-white fw-bold mb-4">PropertyKu</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active-link-custom rounded">Dashboard</a></li>
                <li class="nav-item mb-2"><a href="lending-page.php" class="nav-link rounded">Lihat Properti</a></li>
                <li class="nav-item mb-2 mt-4"><a href="logout.php" class="nav-link btn-logout-sidebar rounded text-white-50">Logout</a></li>
            </ul>
        </div>

        <main class="col-md-10 p-4">
            <h2 class="mb-4 fw-bold text-success">Ringkasan Properti</h2>

            <div class="card p-4 mb-4 border-0 shadow-sm rounded-3">
                <h5 class="mb-3 fw-bold text-dark">Tambah Properti Baru</h5>
                <form class="row g-2 align-items-center" action="proses.php?aksi=tambah" method="POST" enctype="multipart/form-data">
                    <div class="col-md-3">
                        <input type="text" name="nama_properti" class="form-control" placeholder="Nama Perumahan" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="lokasi" class="form-control" placeholder="Lokasi" required>
                    </div>
                    <div class="col-md-2">
                        <!-- PERBAIKAN 1: type diubah ke number agar mencegah user menginput karakter teks/simbol -->
                        <input type="number" name="harga" class="form-control" placeholder="Harga Angka Murni" required>
                    </div>
                    <div class="col-md-2">
                        <input type="file" name="foto_properti" class="form-control" accept="image/*" required>
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
                            <tr id="row-<?php echo $p['id']; ?>">
                                <td><strong class="text-dark"><?php echo $p['nama_properti']; ?></strong></td>
                                <td><?php echo $p['lokasi']; ?></td>
                                <td class="text-success fw-bold">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-success"><?php echo $p['status']; ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning fw-semibold me-1" 
                                            onclick="bukaModalEdit(<?php echo $p['id']; ?>, '<?php echo $p['nama_properti']; ?>', '<?php echo $p['lokasi']; ?>', <?php echo $p['harga']; ?>, '<?php echo $p['status']; ?>')">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger fw-semibold" onclick="hapusProperti(<?php echo $p['id']; ?>, '<?php echo $p['nama_properti']; ?>')">Hapus</button>
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
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success">Edit Data Properti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Perumahan</label>
                        <input type="text" name="nama_properti" id="edit-nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lokasi</label>
                        <input type="text" name="lokasi" id="edit-lokasi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Harga</label>
                        <!-- PERBAIKAN 2: type diubah ke number di dalam modal edit data -->
                        <input type="number" name="harga" id="edit-harga" class="form-control" required>
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
                        <input type="file" name="foto_properti" class="form-control" accept="image/*">
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
function bukaModalEdit(id, nama, lokasi, harga, status) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-lokasi').value = lokasi;
    document.getElementById('edit-harga').value = harga;
    document.getElementById('edit-status').value = status;
    
    var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
    myModal.show();
}

function hapusProperti(id, nama) {
    if (confirm("Apakah Anda yakin ingin menghapus properti '" + nama + "'?")) {
        fetch('proses.php?aksi=hapus&id=' + id, { method: 'GET' })
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