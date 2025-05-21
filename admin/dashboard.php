<?php
require_once 'auth.php';
require_once '../koneksi.php';

// Ambil semua produk + brand + category menggunakan VIEW
$sql = "SELECT * FROM admin_product_list_view";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">
    <aside class="w-64 bg-gray-800 text-gray-200 flex flex-col p-4 space-y-4">
        <h1 class="text-2xl font-bold text-orange-500 mb-6">ADMIN</h1>
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2 px-3 rounded bg-gray-700">Produk</a>
            <a href="pesanan.php" class="block py-2 px-3 rounded hover:bg-gray-700">Pesanan</a>
            <a href="user.php" class="block py-2 px-3 rounded hover:bg-gray-700">Pengguna</a>
            <a href="../login.php" class="block py-2 px-3 rounded hover:bg-gray-700">Logout</a>
        </nav>
    </aside>

    <main class="flex-1 p-8 overflow-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-700">Daftar Produk</h2>
            <a href="crud/form-edit-input.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">+ Tambah Produk</a>
        </div>
        <div class="bg-white shadow rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-200 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Brand</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Harga</th>
                        <th class="px-6 py-3">Stok</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if($result && $result->num_rows): while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?= $row['product_id'] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['product_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['brand_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['category_name']) ?></td>
                            <td class="px-6 py-4">Rp <?= number_format($row['price'],0,',','.') ?></td>
                            <td class="px-6 py-4"><?= $row['stock'] ?? 0 ?></td>
                            <td class="px-6 py-4 space-x-2">
                                <a href="produk-form.php?id=<?= $row['product_id'] ?>" class="px-3 py-1 bg-blue-500 text-white rounded">Edit</a>
                                <a href="produk-delete.php?id=<?= $row['product_id'] ?>" class="px-3 py-1 bg-red-500 text-white rounded" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="7" class="px-6 py-4 text-center">Belum ada produk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>