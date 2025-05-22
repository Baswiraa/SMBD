<?php
session_start();
require 'koneksi.php';

// Fix 1: Change this check to use the correct session variable
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    // Store the product ID to return to after login
    $_SESSION['return_after_login'] = 'detail.php?id='.(int)($_POST['product_id'] ?? 0);
    header('Location: login.php?return=' . urlencode('detail.php?id='.(int)($_POST['product_id'] ?? 0)));
    exit;
}

$id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
// Fix 2: Use the correct session variable for user ID
$userId = (int)$_SESSION['id'];

// Check product and stock
$stmt = $conn->prepare("SELECT price, product_name, stock FROM product_stock_view WHERE product_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$prod = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$prod){ die('Produk tidak ditemukan'); }
if($qty > $prod['stock']){ die('Stok tidak mencukupi'); }

$priceEach = $prod['price'];
$total = $priceEach * $qty;

// Save to orders
$conn->begin_transaction();
try {
    // Fix 3: Verify the user exists first
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        throw new Exception("User tidak valid");
    }
    $stmt->close();

    // Create order
    $stmt = $conn->prepare("CALL create_order(?, ?, ?, ?, ?)");
    $stmt->bind_param('iiiid', $userId, $id, $qty, $priceEach, $total);
    $stmt->execute();
    
    // Get order ID
    $orderId = null;
    do {
        if ($result = $stmt->get_result()) {
            $row = $result->fetch_assoc();
            if ($row && isset($row['order_id'])) {
                $orderId = $row['order_id'];
            }
            $result->free();
        }
    } while ($stmt->more_results() && $stmt->next_result());
    
    $stmt->close();

    if ($orderId === null) {
        $orderId = $conn->insert_id; // Fallback
    }

    $conn->commit();

} catch(Exception $e) {
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
            $ <?= number_format($total,0,',','.') ?></span></p>
        <a href="<?= htmlspecialchars($_POST['return']) ?>" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded">
            Kembali
        </a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
