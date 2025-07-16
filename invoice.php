<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user']['id'];

if ($order_id <= 0) {
    echo "ID pesanan tidak valid.";
    exit;
}

// Ambil data order
$order = $conn->query("
    SELECT o.*, u.nama_lengkap, u.email, u.no_telp 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id AND o.user_id = $user_id
")->fetch_assoc();

if (!$order) {
    echo "Pesanan tidak ditemukan atau Anda tidak memiliki akses.";
    exit;
}

// Ambil detail item pesanan
$items = $conn->query("
    SELECT od.*, p.nama_produk 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = $order_id
");
?>

<?php if (!isset($_GET['download'])) include 'header.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Invoice #<?= $order['id'] ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Nama:</strong> <?= htmlspecialchars($order['nama_lengkap']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>No. Telp:</strong> <?= htmlspecialchars($order['no_telp']) ?></p>
            <p><strong>Alamat Pengiriman:</strong> <?= htmlspecialchars($order['alamat_pengiriman']) ?></p>
            <p><strong>Metode Pembayaran:</strong> <?= strtoupper($order['metode_pembayaran']) ?></p>
            <hr>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $grand_total = 0; ?>
                        <?php while ($item = $items->fetch_assoc()): 
                            $subtotal = $item['qty'] * $item['harga'];
                            $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th>Rp <?= number_format($grand_total, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if (!isset($_GET['download'])): ?>
                <a href="checkout.php" class="btn btn-primary mt-3">Kembali ke Checkout</a>
                <a href="pesanan.php" class="btn btn-outline-secondary mt-3">Lihat Semua Pesanan</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!isset($_GET['download'])) include 'footer.php'; ?>
