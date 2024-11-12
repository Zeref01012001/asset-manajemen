<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "asset_management");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Array produk yang di-purchase
    $product_categories = $_POST['product_category'];
    $product_barcodes = $_POST['product_barcode'];
    $product_names = $_POST['product_name'];
    $product_prices = $_POST['product_price'];
    $product_quantities = $_POST['product_quantity'];

    for ($i = 0; $i < count($product_names); $i++) {
        $category = $product_categories[$i];
        $barcode = $product_barcodes[$i];
        $name = $product_names[$i];
        $price = $product_prices[$i];
        $quantity = $product_quantities[$i];

        // Cek apakah produk dengan barcode yang sama sudah ada di database
        $check_product = $conn->query("SELECT * FROM products WHERE barcode = '$barcode'");

        if ($check_product->num_rows > 0) {
            // Update stok produk
            $row = $check_product->fetch_assoc();
            $new_stock = $row['stock'] + $quantity;
            $update_stock = $conn->query("UPDATE products SET stock = $new_stock WHERE barcode = '$barcode'");
        } else {
            // Insert produk baru
            $insert_product = $conn->query("INSERT INTO products (category, name, price, stock, barcode) 
                                            VALUES ('$category', '$name', $price, $quantity, '$barcode')");
        }

        // Simpan ke dalam tabel purchases
        $insert_purchase = $conn->query("INSERT INTO purchases (product_category, product_name, product_price, product_quantity, barcode) 
                                         VALUES ('$category', '$name', $price, $quantity, '$barcode')");
    }
    
    echo "Purchase berhasil disimpan!";
    header("Location: list_purchase.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Purchase</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
        <h1>Purchase aset</h1>

        <form action="purchase.php" method="POST">
            <table id="purchase">
                <thead>
                    <tr>
                        <th>Kategori Aset</th>
                        <th>Barcode Aset</th>
                        <th>Nama Aset</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="product-rows">
                    <tr>
                        <td>
                            <select name="product_category[]" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Inventaris">Inventaris</option>
                                <option value="Habis Pakai">Habis Pakai</option>
                            </select>
                        </td>
                        <td><input type="text" name="product_barcode[]" required></td>
                        <td><input type="text" name="product_name[]" required></td>
                        <td><input type="number" name="product_price[]" required></td>
                        <td><input type="number" name="product_quantity[]" required></td>
                        <td><button type="button" onclick="addRow()">Tambah Aset</button></td>
                    </tr>
                </tbody>
            </table>
            <div class="purchase-actions">
                <button type="submit">Simpan Purchase</button>
            </div>
        </form>
    </div>
</div>

<script>
    function addRow() {
        const tableBody = document.getElementById('product-rows');
        const newRow = `
            <tr>
                <td>
                    <select name="product_category[]" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Inventaris">Inventaris</option>
                        <option value="Habis Pakai">Habis Pakai</option>
                    </select>
                </td>
                <td><input type="text" name="product_barcode[]" required></td>
                <td><input type="text" name="product_name[]" required></td>
                <td><input type="number" name="product_price[]" required></td>
                <td><input type="number" name="product_quantity[]" required></td>
                <td><button type="button" onclick="removeRow(this)">Hapus</button></td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', newRow);
    }

    function removeRow(button) {
        button.parentElement.parentElement.remove();
    }
</script>
</body>
</html>
