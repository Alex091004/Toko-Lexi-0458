<?php
include 'init.php';
include 'koneksi.php';
include 'header.php';

// Ambil semua produk dari tabel products
$produkQuery = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Katalog Produk</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php while ($produk = $produkQuery->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="uploads/<?= htmlspecialchars($produk['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($produk['nama_produk']) ?></h5>
                        <p class="card-text text-success">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                        <p class="card-text"><?= htmlspecialchars($produk['deskripsi']) ?></p>
                        <div class="mt-auto">
                            <?php if ($produk['stok'] > 0): ?>
                                <a href="tambah_keranjang.php?product_id=<?= $produk['id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled><i class="fas fa-ban"></i> Stok Habis</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
