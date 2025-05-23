<?php
session_start();
require_once '../koneksi.php'; // koneksi ke database
require_once 'auth.php'; // autentikasi admin, sesuaikan jika perlu

// Proses tambah stok saat form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tambah_stok'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['qty'];

    // Validasi sederhana
    if ($productId > 0 && $quantity > 0) {
        $stmt = $conn->prepare("CALL sp_add_stock(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $productId, $quantity);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    }

    header("Location: dashboard.php");
    exit;
}

// Ambil data produk dari view
$sql = "SELECT * FROM admin_product_list_view";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-gray-100 min-h-screen">

<!-- Sidebar -->
<aside class="w-64 bg-gray-800 text-gray-200 p-4 space-y-4">
    <h1 class="text-2xl font-bold text-orange-500 mb-6">ADMIN</h1>
    <nav class="space-y-2">
        <a href="dashboard.php" class="block py-2 px-3 rounded bg-gray-700">Produk</a>
        <a href="pesanan.php" class="block py-2 px-3 rounded hover:bg-gray-700">Pesanan</a>
        <a href="user.php" class="block py-2 px-3 rounded hover:bg-gray-700">Pengguna</a>
        <a href="../logout.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-red-400 font-semibold">Logout</a>
    </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-8 overflow-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-700">Daftar Produk</h2>
        <a href="../crud/form-edit-input.php" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">+ Tambah Produk</a>
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
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['product_id']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['product_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['brand_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($row['category_name']) ?></td>
                            <td class="px-6 py-4">$ <?= number_format($row['price'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4"><?= (int)($row['stock'] ?? 0) ?></td>
                            <td class="px-6 py-4 space-x-2">
                                <button onclick="openModal(<?= (int)$row['product_id'] ?>)" class="px-3 py-1 bg-green-500 text-white rounded">Tambah Stok</button>
                                <a href="../crud/delete.php?id=<?= (int)$row['product_id'] ?>" onclick="return confirm('Yakin ingin hapus?')" class="px-3 py-1 bg-red-500 text-white rounded">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center px-6 py-4">Tidak ada produk.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Tambah Stok -->
<div id="stokModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold mb-4">Tambah Stok Produk</h3>
        <form method="post" class="space-y-4">
            <input type="hidden" id="productIdInput" name="product_id" required>
            <div>
                <label for="qty" class="block text-sm font-medium">Jumlah Stok</label>
                <input type="number" name="qty" id="qty" min="1" required class="mt-1 w-full border rounded px-3 py-2" />
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded">Batal</button>
                <button type="submit" name="tambah_stok" class="px-4 py-2 bg-green-500 text-white rounded">Tambah</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Modal -->
<script>
    function openModal(productId) {
        document.getElementById('productIdInput').value = productId;
        document.getElementById('stokModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('stokModal').classList.add('hidden');
    }
</script>

</body>
</html>
