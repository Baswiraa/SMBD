<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    /* ambil nilai return dari POST; fallback: detail produk */
    $return = $_POST['return']
           ?? ('detail.php?id='.(int)($_POST['product_id'] ?? 0));

    header('Location: login.php?return='.urlencode($return));
    exit;
}

$id  = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$userId = (int)$_SESSION['user_id'];

// cek produk & stok
$stmt = $conn->prepare(
  "SELECT p.price, p.product_name, COALESCE(s.quantity,0) AS stock
   FROM products p
   LEFT JOIN stock s ON s.product_id = p.product_id
   WHERE p.product_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$prod){ die('Produk tidak ditemukan'); }
if($qty > $prod['stock']){ die('Stok tidak mencukupi'); }

$priceEach = $prod['price'];
$total     = $priceEach * $qty;

// ====== Simpan ke orders & order_items ======
$conn->begin_transaction();
try{
  // header orders
  $stmt = $conn->prepare(
    "INSERT INTO orders (user_id, total_amount) VALUES (?,?)");
  $stmt->bind_param('id', $userId, $total);
  $stmt->execute();
  $orderId = $stmt->insert_id;
  $stmt->close();

  // detail order_items
  $stmt = $conn->prepare(
    "INSERT INTO order_items (order_id, product_id, quantity, price_each)
     VALUES (?,?,?,?)");
  $stmt->bind_param('iiid', $orderId, $id, $qty, $priceEach);
  $stmt->execute();
  $stmt->close();

  // kurangi stok
  $stmt = $conn->prepare(
    "UPDATE stock SET quantity = quantity - ? WHERE product_id = ?");
  $stmt->bind_param('ii', $qty, $id);
  $stmt->execute();
  $stmt->close();

  $conn->commit();
}catch(Exception $e){
  $conn->rollback();
  die('Terjadi kesalahan: '.$e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pesanan #<?= $orderId ?> | JayShoes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-50 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded shadow text-center">
    <h1 class="text-2xl font-bold text-orange-500 mb-4">Pesanan Berhasil!</h1>
    <p class="mb-2">Nomor Pesanan: <span class="font-semibold"><?= $orderId ?></span></p>
    <p class="mb-2">Produk: <?= htmlspecialchars($prod['product_name']) ?></p>
    <p class="mb-2">Jumlah: <?= $qty ?></p>
    <p class="mb-6">Total Bayar: <span class="font-semibold">
      Rp <?= number_format($total,0,',','.') ?>.000</span></p>
    <a href="index.php" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded">
      Kembali ke Beranda
    </a>
  </div>
</body>
</html>
<?php $conn->close(); ?>
