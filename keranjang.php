<?php
include 'init.php';
include 'koneksi.php';

=
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$sql = "SELECT c.id as cart_id, c.qty, p.*
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;

include 'header.php';
?>
<div class="container mt-4">
    <h2>Keranjang Belanja</h2>

    <?php if ($res->num_rows === 0): ?>
        <div class="alert alert-info">
            Keranjang Anda kosong.
        </div>
        <a href="index.php" class="btn btn-primary">Belanja Sekarang</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()):
                    $subtotal = $row['harga'] * $row['qty'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td>
                            <form method="post" action="update_cart.php" class="d-inline">
                                <input type="hidden" name="cart_id" value="<?= $row['cart_id'] ?>">
                                <input type="number" name="qty" value="<?= $row['qty'] ?>" min="1" max="<?= $row['stok'] ?>" 
                                       class="form-control form-control-sm" style="width: 70px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary mt-1">Update</button>
                            </form>
                        </td>
                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                        <td>
                            <a href="remove_from_cart.php?id=<?= $row['cart_id'] ?>" class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Hapus produk ini dari keranjang?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th colspan="2">Rp <?= number_format($total, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-outline-primary">Lanjutkan Belanja</a>
            <a href="checkout.php" class="btn btn-success">Lanjutkan Checkout</a>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
