<?php

$host = 'localhost';
$user = 'root'; 
$pass = '';
$db   = 'toko_alex';


$conn = new mysqli($host, $user, $pass, $db);


if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . htmlspecialchars($conn->connect_error));
}

$conn->set_charset("utf8mb4");
?>
