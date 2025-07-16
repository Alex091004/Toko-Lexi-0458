<?php
session_start();
include 'koneksi.php';

// Cek login user
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$error = '';
$order_data = null;

// Jika ada permintaan download invoice (placeholder, seharusnya pakai PDF generator)
if (isset($_GET['download']) && isset($_GET['order_id'])) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Invoice_' . $_GET['order_id'] . '.pdf"');
    readfile('invoice.php'); // Ganti ini dengan generator PDF seperti DomPDF
    exit;
}

// Fungsi generate invoice via email (opsional)
function generateInvoiceEmail($order, $items) {
    ob_start();
    include 'invoice.php';
    return ob_get_clean();
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING);
    $metode_pembayaran = filter_input(INPUT_POST, 'metode_pembayaran', FILTER_SANITIZE_STRING);

    if (empty($alamat) || empty($metode_pembayaran)) {
        $error = "Alamat pengiriman dan metode pembayaran harus diisi.";
    } else {
        // Ambil data keranjang
        $cart_query = $conn->query("
            SELECT c.*, p.harga, p.stok, p.nama_produk 
            FROM carts c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = $user_id
        ");

        if ($cart_query->num_rows === 0) {
            $error = "Keranjang belanja Anda kosong.";
        }
    }

    if (empty($error)) {
        $conn->begin_transaction();

        try {
            $total = 0;

            // Hitung total dan validasi stok
            while ($item = $cart_query->fetch_assoc()) {
                if ($item['qty'] > $item['stok']) {
                    throw new Exception("Stok produk '{$item['nama_produk']}' tidak mencukupi.");
                }
                $total += $item['harga'] * $item['qty'];
            }

            // Simpan order
            $order_stmt = $conn->prepare("
                INSERT INTO orders (user_id, total, status, alamat_pengiriman, metode_pembayaran, created_at, updated_at) 
                VALUES (?, ?, 'pending', ?, ?, NOW(), NOW())
            ");
            $order_stmt->bind_param('idss', $user_id, $total, $alamat, $metode_pembayaran);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // Simpan detail order dan kurangi stok
            $cart_query->data_seek(0);
            $detail_stmt = $conn->prepare("
                INSERT INTO order_details (order_id, product_id, qty, harga) 
                VALUES (?, ?, ?, ?)
            ");
            $update_stmt = $conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ?");

            while ($item = $cart_query->fetch_assoc()) {
                $detail_stmt->bind_param('iiid', $order_id, $item['product_id'], $item['qty'], $item['harga']);
                $detail_stmt->execute();

                $update_stmt->bind_param('ii', $item['qty'], $item['product_id']);
                $update_stmt->execute();
            }

            // Hapus dari keranjang
            $conn->query("DELETE FROM carts WHERE user_id = $user_id");
            $_SESSION['cart_count'] = 0;

            // Ambil data order untuk ditampilkan
            $order_data = $conn->query("
                SELECT o.*, u.nama_lengkap, u.email, u.no_telp
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = $order_id
            ")->fetch_assoc();

            if (!$order_data) {
                throw new Exception("Gagal mengambil data order.");
            }

            $order_items = $conn->query("
                SELECT od.*, p.nama_produk, p.gambar 
                FROM order_details od 
                JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = $order_id
            ");

            // Kirim invoice email (opsional)
            // mail($order_data['email'], "Invoice Toko Alex", generateInvoiceEmail($order_data, $order_items));

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Checkout gagal: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<!-- Halaman tampilan checkout -->
<link rel="stylesheet" href="style.css" />

<div class="container mt-4">
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <a href="keranjang.php" class="btn btn-primary">Kembali ke Keranjang</a>

<?php elseif ($order_data): ?>
    <div class="alert alert-success">
        Checkout berhasil! Invoice telah dikirim ke email <?= htmlspecialchars($order_data['email']) ?>.
    </div>
    <a href="invoice.php?order_id=<?= $order_data['id'] ?>&download=1" class="btn btn-success">Download Invoice</a>
    <a href="pesanan.php" class="btn btn-outline-primary">Lihat Daftar Pesanan</a>

<?php else: ?>
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-shopping-cart"></i> Checkout</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Pengiriman</label>
                    <textarea name="alamat" id="alamat" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="cod">Cash on Delivery (COD)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-credit-card"></i> Proses Pembayaran
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>