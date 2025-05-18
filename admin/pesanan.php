<?php
require_once 'auth.php';
require_once '../koneksi.php'; 

/* Ambil data orders + user + total item */
$sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status,
               u.full_name,
               COUNT(oi.order_item_id) AS item_count
        FROM orders o
        JOIN users u   ON u.user_id = o.user_id
        LEFT JOIN order_items oi ON oi.order_id = o.order_id
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Pesanan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-gray-800 text-gray-200 flex flex-col p-4 space-y-4">
    <h1 class="text-2xl font-bold text-orange-500 mb-6">ADMIN</h1>
    <nav class="space-y-2">
      <a href="dashboard.php" class="block py-2 px-3 rounded hover:bg-gray-700">Produk</a>
      <a href="pesanan.php"   class="block py-2 px-3 rounded bg-gray-700">Pesanan</a>
      <a href="user.php"    class="block py-2 px-3 rounded hover:bg-gray-700">Pengguna</a>
      <a href="../login.php"                  class="block py-2 px-3 rounded hover:bg-gray-700">Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 p-8 overflow-auto">
    <h2 class="text-3xl font-semibold text-gray-700 mb-6">Daftar Pesanan</h2>
    <div class="bg-white shadow rounded-lg overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-6 py-3">Order&nbsp;ID</th>
            <th class="px-6 py-3">Tanggal</th>
            <th class="px-6 py-3">Pelanggan</th>
            <th class="px-6 py-3">Item</th>
            <th class="px-6 py-3">Total</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
        <?php if($result && $result->num_rows): foreach($result as $row): ?>
          <tr>
            <td class="px-6 py-4"><?= $row['order_id'] ?></td>
            <td class="px-6 py-4"><?= date('d-m-Y H:i', strtotime($row['order_date'])) ?></td>
            <td class="px-6 py-4"><?= htmlspecialchars($row['full_name']) ?></td>
            <td class="px-6 py-4 text-center"><?= $row['item_count'] ?></td>
            <td class="px-6 py-4">Rp <?= number_format($row['total_amount'],0,',','.') ?></td>
            <td class="px-6 py-4 capitalize"><?= htmlspecialchars($row['status']) ?></td>
            <td class="px-6 py-4 space-x-2">
              <a href="order-detail.php?id=<?= $row['order_id'] ?>" class="px-3 py-1 bg-blue-500 text-white rounded">Lihat</a>
              <a href="order-delete.php?id=<?= $row['order_id'] ?>" class="px-3 py-1 bg-red-500 text-white rounded" onclick="return confirm('Hapus pesanan ini?')">Hapus</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="px-6 py-4 text-center">Belum ada pesanan.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
<?php $conn->close(); ?>
