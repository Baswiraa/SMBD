<?php
// produk-form.php : add / edit produk
require '../koneksi.php'; // file berisi koneksi $conn

// --- Ambil data referensi ---
$brands = $conn->query("SELECT * FROM brands ORDER BY brand_name");
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

// --- Inisialisasi variabel ---
$id = $_GET['id'] ?? '';
$edit = false;
$data = [];
if($id){
    $stmt = $conn->prepare("SELECT * FROM product_detail_view WHERE product_id=?");
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if($data){ $edit=true; }
    $stmt->close();

}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $edit?'Edit':'Tambah' ?> Produk</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white w-full max-w-xl p-8 rounded shadow">
    <h2 class="text-2xl font-semibold mb-6 text-orange-500"><?= $edit?'Edit':'Tambah' ?> Produk</h2>
    <form action="produk-save.php" method="post" class="space-y-4">
        <input type="hidden" name="product_id" value="<?= $data['product_id']??'' ?>">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Produk</label>
            <input type="text" name="product_name" required class="w-full border p-2 rounded" value="<?= htmlspecialchars($data['product_name']??'') ?>">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Brand</label>
                <select name="brand_id" class="w-full border p-2 rounded" required>
                    <option value="">- Pilih -</option>
                    <?php while($b=$brands->fetch_assoc()): ?>
                        <option value="<?= $b['brand_id'] ?>" <?= (isset($data['brand_id']) && $data['brand_id']==$b['brand_id'])?'selected':'' ?>><?= htmlspecialchars($b['brand_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" class="w-full border p-2 rounded" required>
                    <option value="">- Pilih -</option>
                    <?php while($c=$categories->fetch_assoc()): ?>
                        <option value="<?= $c['category_id'] ?>" <?= (isset($data['category_id']) && $data['category_id']==$c['category_id'])?'selected':'' ?>><?= htmlspecialchars($c['category_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Harga (Rp)</label>
                <input type="number" step="0.01" name="price" required class="w-full border p-2 rounded" value="<?= $data['price']??'' ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Stok</label>
                <input type="number" name="quantity" required class="w-full border p-2 rounded" value="<?= $data['stock']??0 ?>">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">URL Gambar</label>
            <input type="url" name="image_url" class="w-full border p-2 rounded" value="<?= htmlspecialchars($data['image_url']??'') ?>">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Deskripsi</label>
            <textarea name="description" rows="4" class="w-full border p-2 rounded"><?= htmlspecialchars($data['description']??'') ?></textarea>
        </div>
        <button class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded w-full">Simpan</button>
    </form>
    <a href="../admin/dashboard.php" class="block text-center mt-4 text-sm text-gray-500">&larr; Kembali</a>
</div>
</body>
</html>
<?php $conn->close(); ?>