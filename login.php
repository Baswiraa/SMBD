<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Coba cari di admin
    $stmt = $conn->prepare("SELECT admin_id AS id, admin_name AS name, password_hash, 'admin' AS role FROM admin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Jika bukan admin, cari user
    if (!$res) {
        $stmt = $conn->prepare("SELECT user_id AS id, full_name AS name, password_hash, 'user' AS role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if ($res && password_verify($pass, $res['password_hash'])) {
        // SET SESSION
        $_SESSION['id'] = $res['id'];
        $_SESSION['role'] = $res['role'];
        $_SESSION['name'] = $res['name'];

        $returnUrl = $_GET['return'] ?? null;

        if ($res['role'] === 'admin') {
          header('Location: admin/dashboard.php');
        } else {
            $returnUrl = $_GET['return'] ?? 'index.php';
            header('Location: ' . $returnUrl);
        }
        exit;

    } else {
        $errors[] = 'Email atau password salah.';
    }
}

$qs = isset($_GET['return']) ? '?return='.urlencode($_GET['return']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | JayShoes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <form action="login.php<?= $qs ?>" method="post" class="bg-white p-8 rounded shadow-md w-full max-w-md space-y-4">
    <h1 class="text-2xl font-bold text-center text-orange-500 mb-2">Login</h1>

    <?php if($errors): ?>
      <div class="bg-red-100 text-red-600 p-3 rounded">
        <?= implode('<br>', array_map('htmlspecialchars',$errors)); ?>
      </div>
    <?php endif; ?>

    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>"
             class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input type="password" name="password" class="w-full border p-2 rounded" required>
    </div>
    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded font-semibold">
      Masuk
    </button>
    <p class="text-center text-sm">Belum punya akun?
      <a href="register.php" class="text-orange-500 hover:underline">Daftar</a>
    </p>
  </form>
</body>
</html>
<?php $conn->close(); ?>
