<?php
include 'init.php';
include 'koneksi.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$user_id = $_SESSION['user']['id'];


$query = $conn->query("SELECT * FROM users WHERE id = $user_id");
$userData = $query->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $no_telp = $conn->real_escape_string($_POST['no_telp']);
    $alamat = $conn->real_escape_string($_POST['alamat']);

    
    if (!empty($_FILES['foto']['name'])) {
        $foto_name = $_FILES['foto']['name'];
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $foto_path = "uploads/" . time() . '_' . $foto_name;

        if (move_uploaded_file($foto_tmp, $foto_path)) {
            
            $sql = "UPDATE users SET nama_lengkap='$nama_lengkap', no_telp='$no_telp', alamat='$alamat', foto='$foto_path' WHERE id=$user_id";
            $_SESSION['user']['foto'] = $foto_path;
        } else {
            $error = 'Gagal mengupload foto.';
        }
    } else {
       
        $sql = "UPDATE users SET nama_lengkap='$nama_lengkap', no_telp='$no_telp', alamat='$alamat' WHERE id=$user_id";
    }

    if ($conn->query($sql)) {
        $success = 'Profil berhasil diperbarui.';
        $_SESSION['user']['nama_lengkap'] = $nama_lengkap;
        $_SESSION['user']['no_telp'] = $no_telp;
        $_SESSION['user']['alamat'] = $alamat;
    } else {
        $error = 'Gagal memperbarui profil.';
    }
}
?>

<div class="container mt-5">
    <h2>Profil Pengguna</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Lengkap:</label>
            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" required>
        </div>
        <div class="form-group">
            <label>No. Telepon:</label>
            <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($userData['no_telp']) ?>" required>
        </div>
        <div class="form-group">
            <label>Alamat:</label>
            <textarea name="alamat" class="form-control" required><?= htmlspecialchars($userData['alamat']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Foto Profil (opsional):</label><br>
            <?php if (!empty($userData['foto'])): ?>
                <img src="<?= $userData['foto'] ?>" alt="Foto Profil" width="100"><br><br>
            <?php endif; ?>
            <input type="file" name="foto" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-primary">Perbarui Profil</button>
    </form>
</div>

<?php include 'footer.php'; ?>
