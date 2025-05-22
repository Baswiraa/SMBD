<?php
session_start();
require 'koneksi.php';
$isUserLoggedIn = isset($_SESSION['id']) && $_SESSION['role'] === 'user';

$id = (int)($_GET['id'] ?? 0);

// Menggunakan VIEW product_detail_view
$sql = "SELECT * FROM product_detail_view WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();
if(!$produk){ die('Produk tidak ditemukan'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk['product_name']) ?> | JayShoes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-50 text-gray-800">

<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <a href="index.php" class="text-orange-500 text-2xl font-bold">JAYSHOES</a>
    <nav class="space-x-6 text-sm font-medium flex items-center">

      <a href="index.php" class="hover:text-orange-500 <?= basename($_SERVER['PHP_SELF'])==='index.php' ? 'text-orange-500 font-bold':'' ?>">Home</a>
      <a href="produk.php" class="hover:text-orange-500 <?= basename($_SERVER['PHP_SELF'])==='produk.php' ? 'text-orange-500 font-bold':'' ?>">Produk</a>

      <?php if ($isUserLoggedIn): ?>
          <!-- Sudah login -->
          <a href="profile.php"
            class="text-gray-600 hover:text-orange-500 underline">
            Hi, <?= htmlspecialchars($_SESSION['name']) ?>
          </a>
          <a href="logout.php"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Logout</a>
      <?php else: ?>
          <!-- Belum login -->
          <a href="login.php"
            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">Login</a>
          <a href="register.php"
            class="px-4 py-2 border border-orange-500 text-orange-500 rounded hover:bg-orange-50">Daftar</a>
      <?php endif; ?>

    </nav>
    </div>
</header>

<section class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-2 gap-10">
    <div>
        <img src="<?= htmlspecialchars($produk['image_url'] ?: 'https://via.placeholder.com/500') ?>"
             alt="<?= htmlspecialchars($produk['product_name']) ?>"
             class="w-full rounded-lg shadow">
    </div>

    <div>
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($produk['product_name']) ?></h1>
        <p class="text-gray-500 mb-4"><?= htmlspecialchars($produk['brand_name']) ?> |
            <?= htmlspecialchars($produk['category_name']) ?></p>

        <p class="text-2xl text-orange-500 font-bold mb-6">
            $ <?= number_format($produk['price'],0,',','.') ?>
        </p>

        <p class="mb-6 whitespace-pre-line"><?= nl2br(htmlspecialchars($produk['description'])) ?></p>

        <?php if($produk['stock']>0): ?>
            <p class="mb-4 text-green-600">Stok: <?= $produk['stock'] ?> tersedia</p>
            <form action="buy.php" method="post" class="space-y-4">
                <input type="hidden" name="product_id" value="<?= $produk['product_id'] ?>">
                <input type="hidden" name="return"      value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                <label class="block">
                    <span class="text-sm">Jumlah</span>
                    <input type="number" name="qty" value="1" min="1" max="<?= $produk['stock'] ?>"
                           class="w-24 border p-2 rounded ml-2">
                </label>
                <button class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded">
                    Beli Sekarang
                </button>
            </form>
        <?php else: ?>
            <p class="text-red-600 font-semibold">Stok habis</p>
        <?php endif; ?>

        <a href="produk.php" class="inline-block mt-6 text-sm text-gray-500">&larr; Kembali ke Produk</a>
    </div>
</section>

<footer class="bg-white border-t mt-10">
    <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-gray-600 text-center">
        &copy; <?= date('Y'); ?> JayShoes. All rights reserved.
    </div>
</footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>