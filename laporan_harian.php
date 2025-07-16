<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}

include 'header.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$pimpinan = $_GET['pimpinan'] ?? '';
?>

<div class="container mt-4">
    <h2 class="mb-4">Laporan Transaksi Harian</h2>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="tanggal" class="form-label">Pilih Tanggal:</label>
            <input type="date" id="tanggal" name="tanggal" class="form-control"
                value="<?= htmlspecialchars($tanggal) ?>" required>
        </div>
        <div class="col-md-4">
            <label for="pimpinan" class="form-label">Nama Pimpinan:</label>
            <input type="text" id="pimpinan" name="pimpinan" class="form-control"
                value="<?= htmlspecialchars($pimpinan) ?>" placeholder="Nama lengkap pimpinan">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Tampilkan Laporan</button>
        </div>
    </form>

    <button onclick="window.print()" class="btn btn-outline-secondary mb-4"><i class="bi bi-printer"></i> Cetak Laporan</button>

    <?php
    

    $stmt = $conn->prepare("
        SELECT p.nama_produk, t.jumlah, t.tanggal 
        FROM transaksi t 
        JOIN products p ON t.id_produk = p.id
        WHERE t.tanggal = ?
    ");
    $stmt->bind_param("s", $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <h5 class="mb-3">Tanggal: <?= date('d F Y', strtotime($tanggal)) ?></h5>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                        <td><?= $row['jumlah'] ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada transaksi pada tanggal ini.</div>
    <?php endif; ?>

    <?php if (!empty($pimpinan)): ?>
        <div class="mt-5 text-end">
            <h6 class="mb-3">Disetujui oleh:</h6>
            <p><strong><?= htmlspecialchars($pimpinan) ?></strong></p>
            <br><br>
            <p>_______________________</p>
            <p>Tanda Tangan</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
