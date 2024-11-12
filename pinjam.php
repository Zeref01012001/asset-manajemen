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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $quality = $_POST['quality'];
    $borrower_name = htmlspecialchars($_POST['borrower_name']); // Ambil nama peminjam

    // Mengecek stok produk
    $product_query = $conn->query("SELECT * FROM products WHERE id = $product_id");
    $product = $product_query->fetch_assoc();

    if ($product['stock'] >= $quantity) {
        // Kurangi stok produk
        $new_stock = $product['stock'] - $quantity;
        $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");

        // Tambahkan peminjaman ke tabel borrowings
        $conn->query("INSERT INTO borrowings (product_id, quantity, quality, borrower_name) VALUES ($product_id, $quantity, '$quality', '$borrower_name')");

        echo "<script>alert('Barang berhasil dipinjam!'); window.location.href='pinjam.php';</script>";
    } else {
        echo "<script>alert('Stok tidak mencukupi untuk peminjaman!');</script>";
    }
}

// Mengambil daftar produk untuk ditampilkan di dropdown
$products = $conn->query("SELECT * FROM products");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* CSS untuk Sidebar */
        /* Tabel Produk */
        #product-table, #product-table th, #product-table td, #form-table, #form-table td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
            font-size: 12px; /* Ukuran font lebih kecil untuk muat di layar */
        }

        #product-table th {
            background-color: #f2f2f2;
        }

        /* Form Input */
        #form-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        #form-table td {
            padding: 8px;
            font-size: 12px; /* Ukuran font lebih kecil untuk form */
        }

        #form-table input {
            width: 100%; /* Pastikan input memenuhi lebar sel */
            padding: 6px;
            font-size: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Tombol Tambah Produk */
        .add-product-button {
            background-color: #4CAF50;
            color: white;
            padding: 6px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-align: center;
        }

        .add-product-button:hover {
            background-color: #45a049;
        }

        /* Tombol Hapus */
        .delete-button {
            background-color: red;
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .delete-button:hover {
            background-color: darkred;
        }
        
        /* Tabel Kembalikan Barang */
        #return_table, #return_table th, #return_table td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }

        #return_table th {
            background-color: #f2f2f2;
        }

        /* Ukuran font untuk tabel */
        #return_table {
            width: 100%; /* Lebar tabel 100% */
            font-size: 14px; /* Ukuran font */
        }

        .dashboard-container {
            display: flex;
        }

        .sidebar {
            width: 200px;
            overflow-y: auto; /* Scroll jika sidebar melebihi tinggi */
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
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
            <h1>Peminjaman Aset</h1>
            <form action="pinjam.php" method="POST">
                <table>
                    <tr>
                        <td>
                            <label for="borrower_name">Nama Peminjam:</label>
                            <input type="text" name="borrower_name" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="product_id">Pilih Aset:</label>
                            <select name="product_id" required>
                                <?php while ($row = $products->fetch_assoc()) : ?>
                                    <option value="<?php echo $row['id']; ?>">
                                        <?php echo htmlspecialchars($row['name']) . " (Stok: " . htmlspecialchars($row['stock']) . ")"; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="quantity">Jumlah:</label>
                            <input type="number" name="quantity" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="quality">Kualitas:</label>
                            <input type="text" name="quality" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button type="submit">Pinjam</button>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</body>
</html>
