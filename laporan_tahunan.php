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
    <h2 class="mb-4">Laporan Transaksi Tahunan</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="tahun" class="form-label">Pilih Tahun:</label>
            <select name="tahun" id="tahun" class="form-select">
                <?php
                $currentYear = date("Y");
                for ($i = 2020; $i <= $currentYear; $i++) {
                    $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $i) ? 'selected' : '';
                    echo "<option value=\"$i\" $selected>$i</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end">
            <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
            <button onclick="window.print()" type="button" class="btn btn-outline-secondary">Cetak</button>
        </div>
    </form>

    <?php
    if (isset($_GET['tahun'])):
        $tahun = intval($_GET['tahun']);

        $sql = "SELECT p.nama_produk, 
                       SUM(t.jumlah) AS total_jumlah, 
                       SUM(t.jumlah * p.harga) AS total_harga 
                FROM transaksi t 
                JOIN produk p ON t.id_produk = p.id_produk 
                WHERE YEAR(t.tanggal) = $tahun 
                GROUP BY p.nama_produk";

        $result = $conn->query($sql);

        $total_transaksi = 0;
        $total_nilai = 0;
    ?>

    <h4 class="mt-4">Tahun: <?= $tahun ?></h4>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Total Jumlah</th>
                        <th>Total Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()):
                        $total_transaksi += $row["total_jumlah"];
                        $total_nilai += $row["total_harga"];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row["nama_produk"]) ?></td>
                            <td><?= $row["total_jumlah"] ?></td>
                            <td><?= number_format($row["total_harga"], 2, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th>Total</th>
                        <th><?= $total_transaksi ?></th>
                        <th>Rp <?= number_format($total_nilai, 2, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Tidak ada hasil untuk tahun <?= $tahun ?>.</div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
