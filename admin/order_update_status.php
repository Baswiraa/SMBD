<?php
session_start();
require_once 'auth.php';
require_once '../koneksi.php';

// Ambil ID order dari query string
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    header("Location: pesanan.php");
    exit;
}

// Jika form submit untuk update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    $newStatus = trim($newStatus);

    if ($newStatus !== '') {
        $stmt = $conn->prepare("CALL sp_update_order_status(?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("is", $orderId, $newStatus);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();

        header("Location: pesanan.php?msg=Status berhasil diubah");
        exit;
    } else {
        $error = "Status tidak boleh kosong.";
    }
}

// Ambil data pesanan berdasarkan ID untuk tampil di form
$sql = "SELECT order_id, status FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    // Jika order tidak ditemukan, redirect ke daftar pesanan
    header("Location: pesanan.php");
    exit;
}

// Pilihan status (sesuaikan dengan status valid di aplikasi)
$statusOptions = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ubah Status Pesanan #<?= htmlspecialchars($order['order_id']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6">Ubah Status Pesanan #<?= htmlspecialchars($order['order_id']) ?></h1>

        <?php if (!empty($error)): ?>
            <div class="mb-4 text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <label for="status" class="block font-semibold">Pilih Status Baru</label>
            <select id="status" name="status" class="w-full border rounded p-2" required>
                <option value="">-- Pilih Status --</option>
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= $status ?>" <?= ($order['status'] === $status) ? 'selected' : '' ?>>
                        <?= ucfirst($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="flex justify-between">
                <a href="pesanan.php" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</a>
                <button type="submit" name="update_status" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Status</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
