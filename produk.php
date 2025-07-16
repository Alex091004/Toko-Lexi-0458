<?php
include 'koneksi.php';
include 'header.php';

// Cek Aksi
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'add':
       
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
            $harga = floatval($_POST['harga']);
            $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
            $stok = intval($_POST['stok']);

           
            $gambar = '';
            if ($_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
                $gambar = basename($_FILES['gambar']['name']);
                $target_dir = "uploads/";
                $target_file = $target_dir . $gambar;
                
                
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
            }

            $stmt = $conn->prepare("INSERT INTO products (nama_produk, harga, deskripsi, gambar, stok) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssi", $nama_produk, $harga, $deskripsi, $gambar, $stok);
            $stmt->execute();
            header("Location: produk.php");
            exit();
        }
        break;

    case 'edit':
       
        $id = intval($_GET['id']);
        
        
        $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if (!$product) {
            die("Produk tidak ditemukan");
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama_produk = $conn->real_escape_string($_POST['nama_produk']);
            $harga = floatval($_POST['harga']);
            $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
            $stok = intval($_POST['stok']);

            
            $gambar = $product['gambar']; 
            
            if ($_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
                $gambar = basename($_FILES['gambar']['name']);
                $target_dir = "uploads/";
                $target_file = $target_dir . $gambar;
                
               
                if (!empty($product['gambar']) && file_exists($target_dir . $product['gambar'])) {
                    unlink($target_dir . $product['gambar']);
                }
                
                
                move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
            }

            $stmt = $conn->prepare("UPDATE products SET nama_produk=?, harga=?, deskripsi=?, gambar=?, stok=? WHERE id=?");
            $stmt->bind_param("sdssii", $nama_produk, $harga, $deskripsi, $gambar, $stok, $id);
            
            if ($stmt->execute()) {
                header("Location: produk.php");
                exit();
            } else {
                die("Error updating product: " . $conn->error);
            }
        }
        break;

    case 'delete':
       
        $id = intval($_GET['id']);
        
       
        $stmt = $conn->prepare("SELECT gambar FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        
        if ($product && !empty($product['gambar']) && file_exists("uploads/" . $product['gambar'])) {
            unlink("uploads/" . $product['gambar']);
        }
        
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: produk.php");
        exit();
        break;

    default:
        
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .img-thumbnail {
            max-width: 100px;
            height: auto;
        }
        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h1 class="text-center mb-4">Manajemen Produk</h1>

    <?php if ($action == 'edit'): ?>
        
        <div class="form-container">
            <h3>Edit Produk</h3>
            <form method="POST" action="produk.php?action=edit&id=<?= $product['id'] ?>" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($product['nama_produk']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga</label>
                        <input type="number" step="0.01" name="harga" class="form-control" value="<?= $product['harga'] ?>" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($product['deskripsi']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?= $product['stok'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="gambar" class="form-control">
                        <input type="hidden" name="old_image" value="<?= $product['gambar'] ?>">
                        <?php if (!empty($product['gambar'])): ?>
                            <div class="mt-2">
                                <img src="uploads/<?= $product['gambar'] ?>" class="img-thumbnail">
                                <small>Gambar saat ini</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="produk.php" class="btn btn-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
       
        <div class="form-container">
            <h3>Tambah Produk Baru</h3>
            <form method="POST" action="produk.php?action=add" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga</label>
                        <input type="number" step="0.01" name="harga" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stok</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="gambar" class="form-control" required>
                    </div>
                    <div class="col-md-2 mt-auto">
                        <button type="submit" class="btn btn-success w-100">Tambah Produk</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Daftar Produk -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th width="5%">ID</th>
                    <th width="20%">Nama</th>
                    <th width="10%">Harga</th>
                    <th width="25%">Deskripsi</th>
                    <th width="15%">Gambar</th>
                    <th width="5%">Stok</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($result)): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nama_produk']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                            <td>
                                <?php if (!empty($row['gambar'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" class="img-thumbnail" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada gambar</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['stok'] ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="produk.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="produk.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>