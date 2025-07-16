<?php
include 'init.php';
include 'koneksi.php';

// Ambil redirect jika ada
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

if (isset($_SESSION['user'])) {
    $role = strtolower($_SESSION['user']['role']);
    if ($role === 'owner') {
        header("Location: owner_dashboard.php");
        exit;
    } elseif ($role === 'user') {
        header("Location: index.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $hashed = $user['password'];

            if (password_verify($password, $hashed) || $password === $hashed) {
                $role = strtolower(trim($user['role']));

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $role,
                    'nama_lengkap' => $user['nama_lengkap']
                ];

                // Redirect sesuai role dan kondisi redirect
                if (!empty($redirect)) {
                    header("Location: " . $redirect);
                } elseif ($role === 'owner') {
                    header("Location: owner_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}
?>

<?php include 'header.php'; ?>

<div class="container mt-5" style="max-width: 400px;">
    <h2 class="text-center mb-4">Login</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="?<?= !empty($redirect) ? 'redirect=' . urlencode($redirect) : '' ?>">
        <div class="mb-3">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
    </form>

    <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar disini</a></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="style.css">

<?php include 'footer.php'; ?>
