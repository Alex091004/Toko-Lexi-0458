<?php
include 'init.php';
include 'koneksi.php';
header("Location: katalog_produk.php");
exit;


// Cek login dan hanya user yang bisa belanja
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    echo "<script>alert('Silakan login sebagai user untuk melanjutkan.'); window.location='login.php';</script>";
    exit;
}

// Ambil ID produk dari URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Produk tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}

$produk = $result->fetch_assoc();

include 'header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-5">
            <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="img-fluid">
        </div>
        <div class="col-md-7">
            <h2><?= htmlspecialchars($produk['nama_produk']) ?></h2>
            <p><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
            <h4 class="text-danger">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></h4>
            <p>Stok: <?= $produk['stok'] ?></p>

            <form action="beli.php?product_id=<?= $produk['id'] ?>" method="post">
                <div class="mb-3">
                    <label for="qty" class="form-label">Jumlah</label>
                    <input type="number" name="qty" id="qty" class="form-control" value="1" min="1" max="<?= $produk['stok'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Pengiriman</label>
                    <textarea name="alamat" id="alamat" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Beli Sekarang</button>
                <a href="produk.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
