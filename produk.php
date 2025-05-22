<?php
session_start();
require 'koneksi.php';
$isUserLoggedIn = isset($_SESSION['id']) && $_SESSION['role'] === 'user';

$search = $_GET['q'] ?? '';
$productList = [];

$stmt = $conn->prepare("CALL show_all_products(?)");
$stmt->bind_param("s", $search);
$stmt->execute();

do {
    if ($result = $stmt->get_result()) {
        while ($row = $result->fetch_assoc()) {
            $productList[] = $row;
        }
        $result->free();
    }
} while ($stmt->more_results() && $stmt->next_result());

$stmt->close();


?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produk | JayShoes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <div class="text-orange-500 text-2xl font-bold">JAYSHOES</div>
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

<?php $search = $_GET['q'] ?? ''; ?>
<section class="max-w-7xl mx-auto px-4 py-6">
  <form method="get" class="flex gap-2">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari produk..."
           class="border p-2 rounded w-full md:w-1/3">
    <button type="submit" class="bg-orange-500 text-white px-4 rounded hover:bg-orange-600">Cari</button>
  </form>
</section>

<section class="max-w-7xl mx-auto px-4 pb-10">
  <?php if($productList): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach($productList as $p): ?>
          <a href="detail.php?id=<?= $p['product_id'] ?>"
            class="bg-white rounded-lg shadow hover:shadow-lg transition block">
            <img src="<?= htmlspecialchars($p['image_url'] ?: 'https://via.placeholder.com/300') ?>"
                alt="<?= htmlspecialchars($p['product_name']) ?>"
                class="w-full h-52 object-cover rounded-t-lg">
            <div class="p-4">
              <h3 class="font-semibold text-lg"><?= htmlspecialchars($p['product_name']) ?></h3>
              <p class="text-sm text-gray-500 mb-1">
                <?= htmlspecialchars($p['brand_name']) ?> | <?= htmlspecialchars($p['category_name']) ?>
              </p>
              <p class="text-orange-500 font-bold">$ <?= number_format($p['price'],0,',','.') ?></p>
            </div>
          </a>
        <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-center text-gray-500">Produk tidak ditemukan.</p>
  <?php endif; ?>
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
