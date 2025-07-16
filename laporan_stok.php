<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}


include 'header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Laporan Stok Tersedia dan Terjual</h2>

    <button onclick="window.print()" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-printer"></i> Cetak Laporan
    </button>

    <?php
    $sql = "SELECT p.nama_produk, p.stok_awal, p.stok_tersedia, 
                   (p.stok_awal - p.stok_tersedia) AS terjual 
            FROM products p";
    $result = $conn->query($sql);
    ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Stok Awal</th>
                        <th>Stok Tersedia</th>
                        <th>Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["nama_produk"]) ?></td>
                        <td><?= $row["stok_awal"] ?></td>
                        <td><?= $row["stok_tersedia"] ?></td>
                        <td><?= $row["terjual"] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Tidak ada data stok.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
