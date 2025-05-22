<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Ambil data user
$stmt = $conn->prepare("SELECT full_name, email, address FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ambil riwayat pembelian
$history = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <div class="text-orange-500 text-2xl font-bold">JAYSHOES</div>
    <nav class="space-x-6 text-sm font-medium flex items-center">

      <a href="index.php" class="hover:text-orange-500 <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'text-orange-500 font-bold' : '' ?>">Home</a>
      <a href="produk.php" class="hover:text-orange-500 <?= basename($_SERVER['PHP_SELF']) === 'produk.php' ? 'text-orange-500 font-bold' : '' ?>">Produk</a>

      <!-- Karena user sudah login, langsung tampilkan nama dan tombol logout -->
      <a href="profile.php" class="text-gray-600 hover:text-orange-500 underline">
        Hi, <?= htmlspecialchars($user['full_name']) ?>
      </a>
      <a href="logout.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
        Logout
      </a>

    </nav>
  </div>
</header>


<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-bold mb-4">Profil Saya</h1>

  <!-- Data Pengguna -->
  <div class="space-y-2 mb-4">
    <p><strong>Nama Lengkap:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Alamat:</strong> <?= htmlspecialchars($user['address']) ?></p>
  </div>

  <!-- Tombol Edit -->
  <button onclick="document.getElementById('editModal').classList.remove('hidden')"
          class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
    Edit Profil
  </button>

  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center hidden">
    <div class="bg-white p-6 rounded shadow w-full max-w-md">
      <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
          Profil berhasil diperbarui.
        </div>
      <?php endif; ?>
      <!-- Modal Edit Profil -->
      <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded shadow w-full max-w-md">
          <form action="update_profile.php" method="POST" class="space-y-4">
            <div>
              <label class="block text-sm font-medium">Nama Lengkap</label>
              <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required
                    class="w-full border border-gray-300 p-2 rounded">
            </div>
            <div>
              <label class="block text-sm font-medium">Alamat</label>
              <textarea name="address" required class="w-full border border-gray-300 p-2 rounded"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium">Password Baru (kosongkan jika tidak diganti)</label>
              <input type="password" name="new_password" class="w-full border border-gray-300 p-2 rounded">
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                      class="px-4 py-2 border border-gray-400 rounded hover:bg-gray-100">Batal</button>
              <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">Simpan</button>
            </div>
          </form>
        </div>
      </div>
      <form action="update_profile.php" method="POST" class="space-y-4">
        <div>
          <label class="block text-sm font-medium">Nama Lengkap</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="w-full border border-gray-300 p-2 rounded">
        </div>
        <div>
          <label class="block text-sm font-medium">Alamat</label>
          <textarea name="alamat" class="w-full border border-gray-300 p-2 rounded"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Email (tidak bisa diubah)</label>
          <input type="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border border-gray-200 p-2 rounded bg-gray-100" disabled>
        </div>
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                  class="px-4 py-2 border border-gray-400 rounded hover:bg-gray-100">Batal</button>
          <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Riwayat Pembelian -->
  <h2 class="text-xl font-semibold mt-8 mb-2">Riwayat Pembelian</h2>
  <div class="overflow-x-auto">
    <table class="w-full table-auto border-collapse border border-gray-300 text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-4 py-2">Tanggal</th>
          <th class="border px-4 py-2">Total</th>
          <th class="border px-4 py-2">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $history->fetch_assoc()): ?>
          <tr>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['order_date']) ?></td>
            <td class="border px-4 py-2">$ <?= number_format($row['total_amount'], 0, ',', '.') ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['status']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
