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

$id   = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$userId = (int)$_SESSION['user_id'];

// cek produk & stok menggunakan VIEW
$stmt = $conn->prepare("SELECT price, product_name, stock FROM product_stock_view WHERE product_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$prod){ die('Produk tidak ditemukan'); }
if($qty > $prod['stock']){ die('Stok tidak mencukupi'); }

$priceEach = $prod['price'];
$total     = $priceEach * $qty;

// ====== Simpan ke orders & order_items menggunakan PROCEDURE ======
$conn->begin_transaction();
try{
    $stmt = $conn->prepare("CALL create_order(?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiid', $userId, $id, $qty, $priceEach, $total);
    $stmt->execute();
    $result = $stmt->get_result(); // Dapatkan hasil jika prosedur mengembalikannya
    $orderId = null;
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row && isset($row['order_id'])) {
            $orderId = $row['order_id'];
        }
        $result->free();
    } else {
        $orderId = $conn->insert_id; // Fallback jika prosedur tidak mengembalikan order_id
    }
    $stmt->close();

    $conn->commit();

    if ($orderId === null) {
        throw new Exception("Gagal mendapatkan ID pesanan.");
    }

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
