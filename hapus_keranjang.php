<?php
include 'init.php';
include 'koneksi.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$cart_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user']['id'];

if ($cart_id > 0) {
   
    $stmt = $conn->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
}


$cartCountRes = $conn->query("SELECT SUM(qty) AS total_qty FROM carts WHERE user_id = $user_id");
$row = $cartCountRes->fetch_assoc();
$_SESSION['cart_count'] = $row['total_qty'] ?? 0;

header('Location: keranjang.php');
exit;
?>
