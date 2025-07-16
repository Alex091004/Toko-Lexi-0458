<?php
include 'init.php';
include 'koneksi.php';

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);

    if ($password !== $password_confirm) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } elseif (!preg_match('/^[0-9]+$/', $no_telp)) {
        $error = "Nomor telepon hanya boleh angka.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah digunakan
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Daftarkan user baru
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';

            $stmtInsert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, no_telp, role, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmtInsert->bind_param("ssssss", $username, $hashed, $nama_lengkap, $email, $no_telp, $role);

            if ($stmtInsert->execute()) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi.";
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5" style="max-width: 500px;">
    <h2>Daftar Akun</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirm" class="form-control" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Daftar</button>
    </form>

    <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

<?php include 'footer.php'; ?>
