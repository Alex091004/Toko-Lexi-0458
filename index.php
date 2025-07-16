<?php
include 'init.php';
include 'koneksi.php';


if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'owner') {
        header("Location: owner_dashboard.php");
        exit;
    } elseif ($role === 'user') {
        header("Location: user_dashboard.php");
        exit;
    }
}


$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = $_GET['q'] ?? '';
$search_sql = $conn->real_escape_string($search);


$countSql = "SELECT COUNT(*) AS total FROM products WHERE nama_produk LIKE '%$search_sql%' OR deskripsi LIKE '%$search_sql%'";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];


$sql = "SELECT * FROM products WHERE nama_produk LIKE '%$search_sql%' OR deskripsi LIKE '%$search_sql%' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
$totalPages = ceil($totalRows / $limit);

include 'header.php';
?>

<div class="container mt-4">
    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success text-center">Anda berhasil logout.</div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form action="" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>
    </div>

    <h2 class="text-center mb-4">Produk Terbaru</h2>

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 product-card">
                        <div class="product-image-container position-relative">
                            <img src="uploads/<?= htmlspecialchars($row['gambar'] ?: 'default.png') ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                            <div class="product-badge position-absolute top-0 start-0 bg-warning px-2 py-1 small">
                                Stok: <?= $row['stok'] ?>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($row['nama_produk']) ?></h5>

                            <div class="product-price mb-2">
                                Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                            </div>

                            <?php
                            $product_id = $row['id'];
                            $reviewSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = $product_id";
                            $reviewResult = $conn->query($reviewSql);
                            $reviewData = $reviewResult->fetch_assoc();
                            $avgRating = number_format($reviewData['avg_rating'] ?? 0, 1);
                            $reviewCount = $reviewData['review_count'] ?? 0;
                            ?>
                            <div class="product-rating mb-3">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= ($i <= round($avgRating)) ? 'text-warning' : 'text-secondary' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">(<?= $reviewCount ?> ulasan)</small>
                            </div>

                            <div class="mt-auto">
                                <?php if (!isset($_SESSION['user'])): ?>
                                    <div class="alert alert-light text-center small">
                                        <a href="login.php" class="btn btn-outline-primary btn-sm w-100">Login untuk membeli</a>
                                    </div>
                                <?php else: ?>
                                    <form action="beli.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                        <button type="submit" class="btn btn-success btn-sm w-100">Beli Sekarang</button> 
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i> Produk tidak ditemukan.
                </div>
                <a href="?" class="btn btn-primary">Lihat Semua Produk</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                if ($startPage > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($search) . '&page=1">1</a></li>';
                    if ($startPage > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor;
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="?q=' . urlencode($search) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                }
                ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
