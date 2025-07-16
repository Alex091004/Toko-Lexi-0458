<?php
include 'init.php';
include 'koneksi.php';


if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user']['id'];

    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();

        if (strtolower($order['status']) === 'dikirim') {
          
            $update = $conn->prepare("UPDATE orders SET status = 'selesai', updated_at = NOW() WHERE id = ?");
            $update->bind_param("i", $order_id);
            $update->execute();

            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Pesanan telah dikonfirmasi sebagai selesai.'
            ];
        } else {
            $_SESSION['alert'] = [
                'type' => 'warning',
                'message' => 'Pesanan tidak dapat dikonfirmasi. Status saat ini: ' . $order['status']
            ];
        }
    } else {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Pesanan tidak ditemukan atau bukan milik Anda.'
        ];
    }

   
    header('Location: pesanan.php');
    exit;
}


header('Location: index.php');
exit;
