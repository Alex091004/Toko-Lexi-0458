<?php
session_start(); 

include 'koneksi.php'; 

if (!isset($_SESSION['user'])) {
    echo "<script>alert('Anda belum login!'); window.location='login.php';</script>";
    exit;
}

if ($_SESSION['user']['role'] !== 'user') {
    echo "<script>alert('Akses hanya untuk user!'); window.location='index.php';</script>";
    exit;
}



$user_id = $_SESSION['user']['id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
$alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';

if ($product_id === 0 || $qty <= 0 || empty($alamat)) {
    echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    exit;
}


$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Produk tidak ditemukan'); window.location='produk.php';</script>";
    exit;
}

$produk = $result->fetch_assoc();
$subtotal = $produk['harga'] * $qty;

if ($produk['stok'] < $qty) {
    echo "<script>alert('Stok produk tidak cukup'); window.location='produk.php';</script>";
    exit;
}

// Simpan ke tabel orders
$order = $conn->prepare("INSERT INTO orders (user_id, total, status, alamat_pengiriman, created_at, updated_at) VALUES (?, ?, 'Menunggu Konfirmasi', ?, NOW(), NOW())");
$order->bind_param("ids", $user_id, $subtotal, $alamat);
$order->execute();

$order_id = $conn->insert_id;

// Simpan ke order_details
$detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, qty, harga) VALUES (?, ?, ?, ?)");
$detail->bind_param("iiid", $order_id, $product_id, $qty, $produk['harga']);
$detail->execute();

// Kurangi stok produk
$update_stok = $conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ?");
$update_stok->bind_param("ii", $qty, $product_id);
$update_stok->execute();

echo "<script>alert('Pesanan berhasil dibuat!'); window.location='pesanan.php';</script>";
exit;
?>
