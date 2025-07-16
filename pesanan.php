<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT o.id, o.total, o.status, o.created_at, 
           COUNT(od.id) as item_count,
           GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as products
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN products p ON od.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

include 'header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container mt-5 mb-5">
    <h2 class="mb-4"><i class="fas fa-history"></i> Riwayat Pesanan</h2>

    <?php if ($res->num_rows === 0): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
            <h4>Anda belum memiliki pesanan</h4>
            <p class="mb-0">Mulai belanja sekarang dan temukan produk menarik!</p>
            <a href="index.php" class="btn btn-primary mt-3">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#ID</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()):
                        $status_class = [
                            'pending'    => 'bg-secondary',
                            'diproses'   => 'bg-primary',
                            'dikirim'    => 'bg-info',
                            'selesai'    => 'bg-success',
                            'dibatalkan' => 'bg-danger'
                        ][strtolower($row['status'])] ?? 'bg-warning';
                    ?>
                        <tr>
                            <td>#<?= htmlspecialchars($row['id']) ?></td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;" 
                                      data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['products']) ?>">
                                    <?= htmlspecialchars($row['products']) ?>
                                </span>
                            </td>
                            <td><?= $row['item_count'] ?></td>
                            <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                            <td><span class="badge <?= $status_class ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="order_detail.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       data-bs-toggle="tooltip" title="Detail Pesanan">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array(strtolower($row['status']), ['pending', 'diproses'])): ?>
                                        <form action="cancel_order.php" method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="tooltip" title="Batalkan Pesanan"
                                                    onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="alert alert-light mt-3 d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <small>Klik ikon mata untuk melihat detail pesanan atau ikon silang untuk membatalkan pesanan yang masih bisa dibatalkan.</small>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-bs-toggle=\"tooltip\"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
