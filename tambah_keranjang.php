<?php
include 'init.php';
include 'koneksi.php';

// Cek login dan role user
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    echo "<script>alert('Akses ditolak!'); window.location='login.php';</script>";
    exit;
}

// Ambil data dari URL atau POST
$user_id = $_SESSION['user']['id'];
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;

// Cek apakah produk valid
$cek_produk = $conn->prepare("SELECT id, stok FROM products WHERE id = ?");
$cek_produk->bind_param("i", $product_id);
$cek_produk->execute();
$result = $cek_produk->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

$produk = $result->fetch_assoc();

if ($produk['stok'] < $qty) {
    echo "<script>alert('Stok tidak mencukupi!'); window.location='produk.php';</script>";
    exit;
}

// Cek apakah produk sudah di keranjang
$cek_keranjang = $conn->prepare("SELECT id, qty FROM carts WHERE user_id = ? AND product_id = ?");
$cek_keranjang->bind_param("ii", $user_id, $product_id);
$cek_keranjang->execute();
$res_keranjang = $cek_keranjang->get_result();

if ($res_keranjang->num_rows > 0) {
    // Update qty
    $row = $res_keranjang->fetch_assoc();
    $new_qty = $row['qty'] + $qty;
    $update = $conn->prepare("UPDATE carts SET qty = ? WHERE id = ?");
    $update->bind_param("ii", $new_qty, $row['id']);
    $update->execute();
} else {
    // Insert baru
    $insert = $conn->prepare("INSERT INTO carts (user_id, product_id, qty, created_at) VALUES (?, ?, ?, NOW())");
    $insert->bind_param("iii", $user_id, $product_id, $qty);
    $insert->execute();
}

echo "<script>alert('Produk berhasil ditambahkan ke keranjang!'); window.location='keranjang.php';</script>";
