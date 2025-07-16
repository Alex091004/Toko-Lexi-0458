<?php
include 'init.php';
include 'koneksi.php';
include 'header.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='index.php';</script>";
    exit;
}


$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User tidak ditemukan'); window.location='index.php';</script>";
    exit;
}

$user = $result->fetch_assoc();
$foto = (!empty($user['foto']) && file_exists("uploads/{$user['foto']}")) ? $user['foto'] : 'default.png';
?>

<div class="container mt-5">
    <div class="row justify-content-center mb-4">
        <div class="col-md-6 text-center">
            <div class="card p-4 shadow-sm">
                <img src="uploads/<?= htmlspecialchars($foto) ?>"
                     alt="Foto Profil"
                     class="rounded-circle mb-3"
                     width="120" height="120"
                     style="object-fit: cover;">

                <h4 class="mb-1"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                <p class="text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                <p class="text-muted small"><i class="fas fa-phone-alt me-1"></i> <?= htmlspecialchars($user['no_telp']) ?></p>

                <hr>
                <div class="d-grid gap-3">
                    <a href="keranjang.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-1"></i> Lihat Keranjang
                    </a>
                    <a href="pesanan.php" class="btn btn-success">
                        <i class="fas fa-box-open me-1"></i> Riwayat Pesanan
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include 'footer.php'; ?>
