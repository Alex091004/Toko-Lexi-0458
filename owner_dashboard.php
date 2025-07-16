<?php
include 'init.php';
include 'koneksi.php';
include 'header.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    echo "<script>alert('Akses ditolak! Hanya untuk Owner'); window.location='index.php';</script>";
    exit;
}


$owner_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$owner = $result->fetch_assoc();
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Owner Dashboard - Toko Alex</h2>

    <div class="row justify-content-center mb-4">
        <div class="col-md-6 text-center">
            <div class="card shadow p-4">
                <img src="uploads/<?= htmlspecialchars($owner['foto'] ?? 'default.png') ?>" 
                     alt="Foto Profil" 
                     class="rounded-circle mb-3" width="120" height="120">

                <h4 class="mb-1"><?= htmlspecialchars($owner['nama_lengkap'] ?? 'Owner') ?></h4>
                <p class="text-muted">@<?= htmlspecialchars($owner['username'] ?? '') ?></p>
                <p><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($owner['no_telp'] ?? '-') ?></p>

                <hr>
                <div class="d-grid gap-3">
                    <a href="produk.php" class="btn btn-primary">
                        <i class="fas fa-box"></i> Manajemen Produk
                    </a>
                    <a href="laporan_harian.php" class="btn btn-success">
                        <i class="fas fa-calendar-day"></i> Laporan Harian
                    </a>
                    <a href="laporan_bulanan.php" class="btn btn-info">
                        <i class="fas fa-calendar-alt"></i> Laporan Bulanan
                    </a>
                    <a href="laporan_stok.php" class="btn btn-warning">
                        <i class="fas fa-warehouse"></i> Laporan Stok
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
