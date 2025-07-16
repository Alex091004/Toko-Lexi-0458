<?php
include 'init.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Toko Alex</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="index.php">Toko Alex</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Beranda</a>
        </li>

        <?php if (isset($_SESSION['user'])): ?>
          <?php if ($_SESSION['user']['role'] === 'owner'): ?>

            <li class="nav-item">
              <a class="nav-link" href="produk.php"><i class="fas fa-box"></i> Manajemen Produk</a>
            </li>

          <?php else: ?>

            <li class="nav-item">
              <a class="nav-link" href="belanja.php"><i class="fas fa-shopping-bag"></i> Belanja</a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="keranjang.php">
                <i class="fas fa-shopping-cart"></i> Keranjang
                <span class="badge bg-warning text-dark" id="cart-count">
                  <?= isset($_SESSION['cart_count']) ? intval($_SESSION['cart_count']) : 0 ?>
                </span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="pesanan.php"><i class="fas fa-list"></i> Pesanan Saya</a>
            </li>
          <?php endif; ?>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
              <li><a class="dropdown-item" href="user_dashboard.php"><i class="fas fa-user-circle me-2"></i> Profil Saya</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
          </li>

        <?php else: ?>

          <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Daftar</a></li>
          <li class="nav-item">
            <a class="nav-link" href="keranjang.php">
              <i class="fas fa-shopping-cart"></i> Keranjang
              <span class="badge bg-warning text-dark" id="cart-count">0</span>
            </a>
          </li>

        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

</body>
</html>
