<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "asset_management");

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Menangani pengisian form
$adjustments = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $barcode = htmlspecialchars($_POST['barcode']);
    $category = htmlspecialchars($_POST['category']);
    $price = htmlspecialchars($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $quality = $_POST['quality'];
    $keterangan = htmlspecialchars($_POST['keterangan']);
    $date = date('Y-m-d H:i:s'); // Mengambil tanggal saat ini

    // Menyimpan ke dalam array untuk ditampilkan
    $adjustments[] = [
        'name' => $name,
        'barcode' => $barcode,
        'category' => $category,
        'price' => $price,
        'quantity' => $quantity,
        'quality' => $quality,
        'keterangan' => $keterangan,
        'date' => $date, // Menyimpan tanggal
    ];

    // Menyimpan ke database (tambahkan kolom date)
    $conn->query("INSERT INTO adjustments (name, barcode, category, price, quantity, quality, keterangan, date) VALUES ('$name', '$barcode', '$category', '$price', $quantity, '$quality', '$keterangan', '$date')");

    // Kurangi stok di produk jika ada
    $conn->query("UPDATE products SET stock = stock - $quantity WHERE barcode = '$barcode'");
}

// Hapus pengaturan
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM adjustments WHERE id = $deleteId");
}

// Pencarian produk
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$sql = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR barcode LIKE '%$search%')";
}

$products = $conn->query($sql);

// Ambil data adjustment dari database
$adjustmentsQuery = $conn->query("SELECT * FROM adjustments");
$adjustments = [];
while ($row = $adjustmentsQuery->fetch_assoc()) {
    $adjustments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjustment</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        /* Gaya CSS yang sudah ada */
        body {
            display: flex;
            height: 100vh; /* Memastikan tinggi halaman 100% dari viewport */
            margin: 0;
            overflow: hidden; /* Menyembunyikan scroll default */
        }
        .dashboard-container {
            display: flex;
            flex: 1; /* Menggunakan flex agar bisa menyesuaikan tinggi */
        }
        .sidebar {
            width: 200px;
            overflow-y: auto; /* Scroll pada sidebar */
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto; /* Scroll pada konten utama */
        }
        .form-container, .table-container {
            margin: 20px 0;
        }
        .form-container input, .form-container select, .form-container button {
            margin: 5px;
            padding: 8px;
            width: 100%;
        }


        /* Gaya untuk tabel dengan scroll */
        .scrollable-table {
            max-height: 300px; /* Ganti sesuai kebutuhan */
            overflow-y: auto;
            border: 1px solid #ccc;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;

        }
        .equal-height {
            height: auto; /* Menyesuaikan tinggi */
            min-height: 300px; /* Pastikan minimal tinggi sama */
        }

 
 /*<----------------------------------------------------------------------->*/
        

/* Gaya untuk tombol Aksi Hapus */
a {
    display: inline-block;
    margin: 10px;
    padding: 15px 30px;
    font-size: 1.1em;
    color: white;
    background: #4e54c8;
    border-radius: 25px;
    text-decoration: none;
    transition: background 0.4s ease;
}

a:hover {
    color: #6a85b6; /* Warna saat di-hover bisa diubah jika diinginkan, contoh merah */
    text-decoration: none; /* Menghapus garis bawah saat di-hover */
}


/*<--------------------------------------------------------------------------------------->*/
/* Tabel Adjustment */
table, th, td {
    border: 1px solid #1e90ff; /* Border biru */
    border-collapse: collapse; /* Menggabungkan border untuk tampilan rapi */
    padding: 12px; /* Padding di dalam sel */
    text-align: left; /* Penyelarasan teks ke kiri */
    font-size: 14px; /* Ukuran font untuk tabel */
}

/* Mengubah warna header tabel dengan id="adjustment" */
#adjustment th {
    background-color: #2c3e50; /* Warna latar belakang header */
    color: #fff; /* Warna teks header */
}


td {
    background-color: #f2f2f2; /* Warna latar belakang untuk sel tabel (biru muda) */
    color: #333; /* Warna teks sel */
}

table {
    width: 100%; /* Lebar tabel 100% dari kontainer */
    font-size: 14px; /* Ukuran font tabel */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); /* Efek bayangan pada seluruh tabel */
}

/* Gaya untuk container scroll */
.table-container {
    width: 100%;
    max-height: 400px; /* Maksimal tinggi kontainer agar tabel bisa di-scroll */
    overflow-y: auto; /* Mengaktifkan scroll vertikal */
    margin-top: 20px; /* Jarak atas kontainer */
}

/* Menambahkan hover pada baris tabel */
tbody tr:hover {
    background-color: #87cefa; /* Warna latar belakang saat hover (biru langit) */
    box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.15); /* Efek bayangan dalam pada hover untuk kesan 3D */
}


    </style>
    <script>
        function updateFields() {
            const selectElement = document.getElementById('product-select');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            document.getElementById('name').value = selectedOption.getAttribute('data-name');
            document.getElementById('barcode').value = selectedOption.getAttribute('data-barcode');
            document.getElementById('category').value = selectedOption.getAttribute('data-category');
            document.getElementById('price').value = selectedOption.getAttribute('data-price');

            // Update max quantity input based on the stock
            const maxStock = selectedOption.getAttribute('data-stock');
            const quantityInput = document.getElementById('quantity');
            quantityInput.setAttribute('max', maxStock);
            quantityInput.value = 1; // Reset to 1 when product changes
        }
    </script>
</head>
<body>
<div class="dashboard-container">
    <div class="sidebar">
        <h2>TCOIT</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="list_product.php">List Aset</a></li>
            <li><a href="add_product.php">Tambah Aset</a></li>
            <li><a href="list_purchase.php">List Purchase</a></li>
            <li><a href="purchase.php">Purchase</a></li>
            <li><a href="pinjam.php">Pinjam Aset</a></li>
            <li><a href="kembali.php">Kembalikan Aset</a></li>
            <li><a href="adjustment.php">Adjustment</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Adjustment Aset</h2>

        <!-- Form Adjustment -->
        <div class="form-container">
            <form method="POST" action="">
                <select id="product-select" name="product_id" onchange="updateFields()" required>
                    <option value="">Pilih Aset</option>
                    <?php while ($row = $products->fetch_assoc()) : ?>
                        <option value="<?php echo $row['id']; ?>" 
                                data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                data-barcode="<?php echo htmlspecialchars($row['barcode']); ?>" 
                                data-category="<?php echo htmlspecialchars($row['category']); ?>" 
                                data-price="<?php echo htmlspecialchars($row['price']); ?>" 
                                data-stock="<?php echo htmlspecialchars($row['stock']); ?>">
                            <?php echo htmlspecialchars($row['name']) . " (Stok: " . htmlspecialchars($row['stock']) . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="text" id="name" name="name" placeholder="Nama Aset" required readonly>
                <input type="text" id="barcode" name="barcode" placeholder="Barcode" required readonly>
                <input type="text" id="category" name="category" placeholder="Kategori" required readonly>
                <input type="number" id="price" name="price" placeholder="Harga" required readonly>
                <input type="number" id="quantity" name="quantity" placeholder="Jumlah" required min="1" max="1">
                <select name="quality" required>
                    <option value="">Pilih Kualitas</option>
                    <option value="Habis">Habis</option>
                    <option value="Rusak">Rusak</option>
                </select>
                <input type="text" name="keterangan" placeholder="Keterangan" required>
                <button type="submit">Tambah Adjustment</button>
            </form>
        </div>

        <!-- Tabel untuk Inventaris Rusak -->
       <!-- Tabel untuk Inventaris Rusak -->
<div class="table-container equal-height">
    <h3>Daftar Inventaris Rusak</h3>
    <div class="scrollable-table">
        <table id="adjustment">
            <thead>
                <tr>
                    <th>Nama Aset</th>
                    <th>Barcode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Keterangan</th>
                    <th>Tanggal</th> <!-- Tambahkan kolom tanggal -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php
    foreach ($adjustments as $adjustment) {
        if ($adjustment['quality'] == 'Rusak') {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($adjustment['name']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['barcode']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['category']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['price']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['quantity']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['keterangan']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['date']) . "</td>"; // Tampilkan tanggal
            echo "<td><a href='?delete=" . $adjustment['id'] . "' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a></td>";
            echo "</tr>";
        }
    }
    ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel untuk Barang Habis Pakai -->
<div class="table-container equal-height">
    <h3>Daftar Barang Habis Pakai</h3>
    <div class="scrollable-table">
        <table id="adjustment">
            <thead>
                <tr>
                    <th>Nama Aset</th>
                    <th>Barcode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Keterangan</th>
                    <th>Tanggal</th> <!-- Tambahkan kolom tanggal -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php
    foreach ($adjustments as $adjustment) {
        if ($adjustment['quality'] == 'Habis') {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($adjustment['name']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['barcode']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['category']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['price']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['quantity']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['keterangan']) . "</td>";
            echo "<td>" . htmlspecialchars($adjustment['date']) . "</td>"; // Tampilkan tanggal
            echo "<td><a href='?delete=" . $adjustment['id'] . "' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a></td>";
            echo "</tr>";
        }
    }
    ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
