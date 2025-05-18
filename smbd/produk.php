<?php
require 'koneksi.php';

$search  = $_GET['q']        ?? '';
$brandId = $_GET['brand_id'] ?? '';
$catId   = $_GET['cat_id']   ?? '';

$brands = $conn->query("SELECT * FROM brands ORDER BY brand_name");
$cats   = $conn->query("SELECT * FROM categories ORDER BY category_name");

$where = [];
$params = []; $types = '';
if($search){
  $where[] = "p.product_name LIKE ?";
  $params[] = "%$search%";  $types .= 's';
}
if($brandId){
  $where[] = "p.brand_id = ?";
  $params[] = $brandId;     $types .= 'i';
}
if($catId){
  $where[] = "p.category_id = ?";
  $params[] = $catId;       $types .= 'i';
}
$sql = "SELECT p.*, b.brand_name, c.category_name
        FROM products p
        JOIN brands b ON b.brand_id = p.brand_id
        JOIN categories c ON c.category_id = p.category_id";
if($where) $sql .= " WHERE ".implode(' AND ',$where);
$sql .= " ORDER BY p.product_name";

$stmt = $conn->prepare($sql);
if($params){
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$produk = $stmt->get_result();
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
    <nav class="space-x-6 text-sm font-medium">
      <a href="index.php" class="hover:text-orange-500">Home</a>
      <a href="produk.php" class="text-orange-500 font-bold">Produk</a>
    </nav>
  </div>
</header>

<!-- Filter + Search -->
<section class="max-w-7xl mx-auto px-4 py-8">
  <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <div>
      <label class="block text-sm mb-1">Cari Nama</label>
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari..." class="w-full border p-2 rounded">
    </div>
    <div>
      <label class="block text-sm mb-1">Brand</label>
      <select name="brand_id" class="w-full border p-2 rounded">
        <option value="">Semua</option>
        <?php while($b=$brands->fetch_assoc()): ?>
          <option value="<?= $b['brand_id'] ?>" <?= $b['brand_id']==$brandId?'selected':'' ?>>
            <?= htmlspecialchars($b['brand_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Kategori</label>
      <select name="cat_id" class="w-full border p-2 rounded">
        <option value="">Semua</option>
        <?php while($c=$cats->fetch_assoc()): ?>
          <option value="<?= $c['category_id'] ?>" <?= $c['category_id']==$catId?'selected':'' ?>>
            <?= htmlspecialchars($c['category_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 rounded">Terapkan</button>
  </form>
</section>

<!-- Grid Produk -->
<section class="max-w-7xl mx-auto px-4 pb-10">
  <?php if($produk && $produk->num_rows): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php while($p = $produk->fetch_assoc()): ?>
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
              <p class="text-orange-500 font-bold">Rp <?= number_format($p['price'],0,',','.') ?></p>
            </div>
          </a>
        <?php endwhile; ?>
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
