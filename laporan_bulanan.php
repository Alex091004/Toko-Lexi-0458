<?php
include 'init.php';
include 'koneksi.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: index.php");
    exit;
}


include 'header.php';

$currentYear = date("Y");
$selectedMonth = $_GET['bulan'] ?? '';
$selectedYear = $_GET['tahun'] ?? '';
?>

<div class="container mt-4">
    <h2 class="mb-4">Laporan Transaksi Bulanan</h2>

    <form method="GET" action="" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="bulan" class="form-label">Pilih Bulan:</label>
            <select name="bulan" id="bulan" class="form-select" required>
                <option value="">-- Pilih Bulan --</option>
                <?php
                $bulanList = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                foreach ($bulanList as $num => $name) {
                    $selected = ($selectedMonth == $num) ? 'selected' : '';
                    echo "<option value=\"$num\" $selected>$name</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="tahun" class="form-label">Pilih Tahun:</label>
            <select name="tahun" id="tahun" class="form-select" required>
                <option value="">-- Pilih Tahun --</option>
                <?php
                for ($i = 2020; $i <= $currentYear; $i++) {
                    $selected = ($selectedYear == $i) ? 'selected' : '';
                    echo "<option value=\"$i\" $selected>$i</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Tampilkan Laporan</button>
        </div>
    </form>

    <?php if ($selectedMonth && $selectedYear): ?>
        <?php
      
        $stmt = $conn->prepare("
            SELECT p.nama_produk, SUM(od.qty) as total_jumlah, SUM(od.qty * od.harga) as total_harga
            FROM orders o
            JOIN order_details od ON o.id = od.order_id
            JOIN products p ON od.product_id = p.id
            WHERE MONTH(o.created_at) = ? AND YEAR(o.created_at) = ?
            GROUP BY p.nama_produk
        ");
        $stmt->bind_param("ii", $selectedMonth, $selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();

        $total_transaksi = 0;
        $total_nilai = 0;
        ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Produk</th>
                            <th>Total Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                                <td><?= $row['total_jumlah'] ?></td>
                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            </tr>
                            <?php
                            $total_transaksi += $row['total_jumlah'];
                            $total_nilai += $row['total_harga'];
                            ?>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?= $total_transaksi ?></th>
                            <th>Rp <?= number_format($total_nilai, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Tidak ada data transaksi untuk bulan dan tahun yang dipilih.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
