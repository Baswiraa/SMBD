<?php
session_start();
require 'koneksi.php';

$isUserLoggedIn = isset($_SESSION['id']) && $_SESSION['role'] === 'user';

$sql = "CALL top_selling_products()";
$produk = $conn->query($sql);


?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JayShoes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="https://www.sportstation.id/favicon.ico" />
</head>
<body class="font-sans bg-gray-50 text-gray-800">

  <!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <a href="index.php" class="text-orange-500 text-2xl font-bold">JAYSHOES</a>

    <!-- ⇣ NAV BAR ⇣ -->
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


  <!-- Hero Banner -->
  <section
    style="background-image:url('https://images.pexels.com/photos/8456072/pexels-photo-8456072.jpeg');"
    class="bg-cover bg-center h-[60vh] flex items-center justify-center text-white">
    <div class="bg-black bg-opacity-60 p-6 rounded-md text-center">
      <h1 class="text-4xl font-bold mb-2">Temukan Sepatu Olahraga Terbaikmu</h1>
      <p class="text-lg">Diskon hingga 50% untuk produk pilihan!</p>
    </div>
  </section>

  <!-- Produk Terbaru -->
  <section class="max-w-7xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6 text-orange-500">Produk Terbaru</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <!-- looping produk -->
      <?php if (isset($produk) && $produk instanceof mysqli_result && $produk->num_rows > 0): ?>
        <?php while($p = $produk->fetch_assoc()): ?>
          <a href="detail.php?id=<?= (int)$p['product_id'] ?>"
            class="bg-white rounded-lg shadow hover:shadow-lg transition block">
            <img src="<?= htmlspecialchars($p['image_url'] ?: 'https://via.placeholder.com/300') ?>"
                alt="<?= htmlspecialchars($p['product_name']) ?>"
                class="w-full h-52 object-cover rounded-t-lg">
            <div class="p-4">
              <h3 class="font-semibold text-lg"><?= htmlspecialchars($p['product_name']) ?></h3>
              <p class="text-sm text-gray-500 mb-1">
                <?= htmlspecialchars($p['brand_name']) ?> | <?= htmlspecialchars($p['category_name']) ?>
              </p>
              <p class="text-orange-500 font-bold">
                $ <?= number_format((float)$p['price'], 0, ',', '.') ?>
              </p>
            </div>
          </a>
        <?php endwhile; ?>
        <?php $produk->free(); $conn->next_result(); // Penting untuk prosedur ?>
      <?php else: ?>
        <p class="col-span-4 text-center text-gray-500">Belum ada produk.</p>
      <?php endif; ?>
    </div>
  </section>

  <footer class="bg-white border-t mt-10">
    <div class="max-w-7xl mx-auto px-4 py-6 flex justify-between text-sm text-gray-600">
      <p>&copy; <?= date('Y'); ?> JayShoes. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
<?php $conn->close(); ?>
