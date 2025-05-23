<?php
require_once 'auth.php';
require_once '../koneksi.php';

/* Ambil data users + total order menggunakan VIEW */
$sql = "SELECT * FROM admin_user_list_view";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">
    <aside class="w-64 bg-gray-800 text-gray-200 flex flex-col p-4 space-y-4">
        <h1 class="text-2xl font-bold text-orange-500 mb-6">ADMIN</h1>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded hover:bg-gray-700">Produk</a>
            <a href="pesanan.php"   class="block py-2 px-3 rounded hover:bg-gray-700">Pesanan</a>
            <a href="user.php"      class="block py-2 px-3 rounded bg-gray-700">Pengguna</a>
            <a href="../logout.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-red-400 font-semibold">Logout</a>
        </nav>
    </aside>

    <main class="flex-1 p-8 overflow-auto">
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3">Pesanan</th>
                        <th class="px-6 py-3">Total Belanja</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                <?php if($result && $result->num_rows): foreach($result as $row): ?>
                    <tr>
                        <td class="px-6 py-4"><?= $row['user_id'] ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($row['address']) ?></td>
                        <td class="px-6 py-4 text-center"><?= $row['order_count'] ?></td>
                        <td class="px-6 py-4">Rp <?= number_format($row['total_spent']??0,0,',','.') ?></td>
                        <td class="px-6 py-4 space-x-2">
                            <a href="user-delete.php?id=<?= $row['user_id'] ?>" class="px-3 py-1 bg-red-500 text-white rounded" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="px-6 py-4 text-center">Belum ada pengguna.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>