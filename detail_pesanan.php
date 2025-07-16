<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: pesanan.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$order_id = intval($_GET['order_id']);

// Ambil data pesanan
$order_stmt = $conn->prepare("
    SELECT o.*, u.nama_lengkap, u.email, u.no_telp
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: pesanan.php');
    exit;
}

// Ambil detail produk
$items_stmt = $conn->prepare("
    SELECT od.*, p.nama_produk, p.gambar, p.deskripsi
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

include 'header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice"></i> Detail Pesanan #<?= $order['id'] ?></h2>
        <a href="pesanan.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Kiri -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Informasi Pesanan</h5>
                </div>
                <div class="card-body row">
                    <div class="col-md-6">
                        <p><strong>Tanggal Pesanan:</strong><br><?= date('d F Y H:i', strtotime($order['created_at'])) ?></p>
                        <p><strong>Status:</strong><br>
                            <?php
                            $status_map = [
                                'pending' => 'badge bg-warning',
                                'diproses' => 'badge bg-info',
                                'dikirim' => 'badge bg-primary',
                                'selesai' => 'badge bg-success',
                                'dibatalkan' => 'badge bg-danger'
                            ];
                            $status = strtolower($order['status']);
                            $badge = $status_map[$status] ?? 'badge bg-secondary';
                            ?>
                            <span class="<?= $badge ?>"><?= ucfirst($status) ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Metode Pembayaran:</strong><br><?= ucfirst($order['metode_pembayaran']) ?></p>
                        <p><strong>Total Pembayaran:</strong><br>
                            <span class="fw-bold">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Produk Dipesan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Gambar</th>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= $item['gambar'] ? htmlspecialchars($item['gambar']) : 'no-image.png' ?>" 
                                                 alt="<?= htmlspecialchars($item['nama_produk']) ?>" 
                                                 class="img-thumbnail" style="width:50px;height:50px;object-fit:cover;">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['nama_produk']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($item['deskripsi'], 0, 50)) ?>...</small>
                                        </td>
                                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td><?= $item['qty'] ?></td>
                                        <td>Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
                                    <th>Rp <?= number_format($order['total'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Informasi Pengiriman</h5>
                </div>
                <div class="card-body">
                    <h6>Alamat Pengiriman</h6>
                    <p><?= nl2br(htmlspecialchars($order['alamat_pengiriman'])) ?></p>
                    <hr>
                    <h6>Informasi Pelanggan</h6>
                    <p>
                        <strong>Nama:</strong> <?= htmlspecialchars($order['nama_lengkap']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
                        <strong>No. Telepon:</strong> <?= htmlspecialchars($order['no_telp'] ?? '-') ?>
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Aksi Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($status === 'dikirim'): ?>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                <i class="fas fa-check"></i> Konfirmasi Diterima
                            </button>
                        <?php endif; ?>

                        <?php if (in_array($status, ['pending', 'diproses'])): ?>
                            <form action="cancel_order.php" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Batalkan Pesanan
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="invoice.php?order_id=<?= $order['id'] ?>" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-print"></i> Cetak Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="confirm_order.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pesanan Diterima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin pesanan sudah diterima dengan baik?</p>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-success">Ya, Pesanan Diterima</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
