<?php
session_start();
require 'koneksi.php';

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $nama    = trim($_POST['full_name'] ?? '');
  $email   = trim($_POST['email']     ?? '');
  $pass    = $_POST['password']       ?? '';
  $alamat  = trim($_POST['address']   ?? '');

  // ---- Validasi sederhana ----
  if(!$nama)      $errors[] = 'Nama wajib diisi';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
  if(strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter';

  // ---- Jika lolos validasi ----
  if(!$errors){
    // cek email sudah ada?
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE email=?");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    if($stmt->get_result()->num_rows){
      $errors[]='Email sudah terdaftar';
    }
    $stmt->close();
  }

  if(!$errors){
    $hash = password_hash($pass,PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
      "INSERT INTO users(full_name,email,password_hash,address) VALUES (?,?,?,?)");
    $stmt->bind_param('ssss',$nama,$email,$hash,$alamat);
    if($stmt->execute()){
      $_SESSION['user_id'] = $stmt->insert_id;
      $_SESSION['full_name']= $nama;
      header('Location: login.php');
      exit;
    }else{
      $errors[]='Gagal menyimpan data';
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | JayShoes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <form method="post" class="bg-white p-8 rounded shadow-md w-full max-w-md space-y-4">
    <h1 class="text-2xl font-bold text-center text-orange-500 mb-2">Daftar Akun</h1>

    <?php if($errors): ?>
      <div class="bg-red-100 text-red-600 p-3 rounded">
        <?= implode('<br>', array_map('htmlspecialchars',$errors)); ?>
      </div>
    <?php endif; ?>

    <div>
      <label class="block text-sm mb-1">Nama Lengkap</label>
      <input type="text" name="full_name" value="<?= htmlspecialchars($_POST['full_name']??'') ?>"
             class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>"
             class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input type="password" name="password" class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Alamat</label>
      <textarea name="address" rows="2" class="w-full border p-2 rounded"><?= htmlspecialchars($_POST['address']??'') ?></textarea>
    </div>
    <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded font-semibold">
      Daftar
    </button>
    <p class="text-center text-sm">Sudah punya akun?
      <a href="login.php" class="text-orange-500 hover:underline">Masuk</a>
    </p>
  </form>
</body>
</html>
<?php $conn->close(); ?>
