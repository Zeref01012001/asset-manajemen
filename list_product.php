<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "asset_management");

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Periksa apakah ada permintaan untuk menandai barang habis
if (isset($_POST['mark_habis'])) {
    $id_barang = $_POST['id_barang'];
    // Update stok barang menjadi 0 dan statusnya menjadi 'habis'
    $query = "UPDATE products SET stock = 0, status = 'habis' WHERE id = $id_barang";
    if ($conn->query($query)) {
        echo "<script>alert('Barang ditandai sebagai habis');</script>";
    }
}

// Periksa apakah ada permintaan untuk menghapus produk
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Pastikan ID produk adalah integer
    
    // Cek apakah produk sedang dipinjam
    $check_borrowings = $conn->query("SELECT COUNT(*) as count FROM borrowings WHERE product_id = $delete_id");
    $borrow_count = $check_borrowings->fetch_assoc()['count'];

    if ($borrow_count > 0) {
        echo "<script>alert('Barang tidak dapat dihapus karena sedang dipinjam.');</script>";
    } else {
        // Hapus entri terkait dari borrowed_items
        $conn->query("DELETE FROM borrowed_items WHERE product_id = $delete_id");
        // Hapus produk dari tabel products
        $conn->query("DELETE FROM products WHERE id = $delete_id");

        // Setelah menghapus, redirect kembali ke halaman ini
        header("Location: list_product.php");
        exit;
    }
}

// Pencarian produk
$search = '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT * FROM products WHERE 1=1";

// Pencarian berdasarkan nama produk atau barcode
if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR barcode LIKE '%$search%')";
}

// Filter berdasarkan kategori
if ($category) {
    $sql .= " AND category = '$category'";
}

$products = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Produk</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        /* Style untuk tampilan halaman */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-y: auto;
        }

        .sidebar {
            width: 200px;
            overflow-y: auto;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        /* Tabel Produk */
        #list-product, #list-product th, #list-product td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }

        #list-product th {
            background-color: #2c3e50;
        }

        /* Ukuran font untuk tabel */
        #list-product {
            width: 100%;
            font-size: 14px;
        }

        .dashboard-container {
            display: flex;
        }

        .sidebar {
            width: 200px;
            overflow-y: auto;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .delete-button, .tandai-habis-button {
            background-color: red;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .tandai-habis-button {
            background-color: orange;
        }

        .delete-button:hover, .tandai-habis-button:hover {
            background-color: darkred;
        }

        /* Menambahkan style untuk total nilai aset */
        .total-nilai-aset {
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
            color: black;
            font-size: 16px;
        }
    </style>
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
        <h2>List Aset</h2>

        <!-- Menampilkan total nilai aset di atas tabel -->
        <div class="total-nilai-aset">
            <?php
            $total_nilai_aset = 0; // Inisialisasi total nilai aset
            if ($products->num_rows > 0) {
                while ($product = $products->fetch_assoc()) {
                    // Hitung total nilai aset (harga * stok)
                    $total_nilai_aset += $product['price'] * $product['stock'];
                }
            }
            echo "Total Nilai Aset: RP " . number_format($total_nilai_aset, 0, ',', '.');
            ?>
        </div>

        <!-- Form Pencarian dan Filter Kategori -->
        <div class="search-filter-container">
            <form method="GET" action="list_product.php" class="search-form">
                <input type="text" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="search-button">Cari</button>
            </form>

            <form method="GET" action="list_product.php" class="filter-form">
                <label for="category-filter">Filter Kategori:</label>
                <select id="category-filter" name="category">
                    <option value="">Semua Kategori</option>
                    <option value="Inventaris" <?php echo ($category == 'Inventaris') ? 'selected' : ''; ?>>Inventaris</option>
                    <option value="Habis Pakai" <?php echo ($category == 'Habis Pakai') ? 'selected' : ''; ?>>Habis Pakai</option>
                </select>
                <button type="submit" class="filter-button">Filter</button>
            </form>
        </div>
        <table id="list-product">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aset</th>
                    <th>Barcode</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($products->num_rows > 0) {
                    $no = 1;
                    $products->data_seek(0); // Reset pointer ke awal
                    while ($product = $products->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($product['barcode']) . "</td>";
                        echo "<td>" . htmlspecialchars($product['category']) . "</td>";
                        echo "<td>" . number_format($product['price'], 0, ',', '.') . "</td>";
                        echo "<td>" . $product['stock'] . "</td>";
                        echo "<td>";

                        // Tombol Tandai Habis hanya muncul untuk barang habis pakai yang stoknya lebih dari 0
                        if ($product['category'] === 'Habis Pakai' && $product['stock'] > 0) {
                            echo "<form method='POST' action='list_product.php'>
                                    <input type='hidden' name='id_barang' value='{$product['id']}'>
                                    <button type='submit' name='mark_habis' class='tandai-habis-button'>Tandai Habis</button>
                                  </form>";
                        } elseif ($product['category'] === 'Habis Pakai' && $product['stock'] == 0) {
                            echo "<span>Habis</span>";
                        }

                        // Tombol Hapus
                        echo "<a href='list_product.php?delete_id=" . $product['id'] . "' onclick=\"return confirm('Apakah Anda yakin ingin menghapus produk ini?');\" class='delete-button'>Hapus</a>";

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada produk ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
