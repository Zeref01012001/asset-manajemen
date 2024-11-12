<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$database = "asset_management";

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Proses penambahan produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $barcode = $conn->real_escape_string($_POST['barcode']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $qty = intval($_POST['qty']);

    // Tambahkan status 'tersedia' hanya untuk barang habis pakai
    $status = ($category === 'habis_pakai' && $qty <= 0) ? 'habis' : 'tersedia';

    // Query untuk menambah produk
    $sql = "INSERT INTO products (name, barcode, category, price, stock, status) 
            VALUES ('$name', '$barcode', '$category', $price, $qty, '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Produk berhasil ditambahkan!');</script>";
    } else {
        echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
    }
}


// Ambil data produk dari database
$products = $conn->query("SELECT * FROM products");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
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
        <h2>Tambah Aset Baru</h2>
        <h3>Form Tambah Aset</h3>
        <form method="POST" action="add_product.php" id="product-form">
            <table id="form-table">
                <tr>
                    <td>1</td>
                    <td><input type="text" id="name" name="name" placeholder="Nama Aset" required></td>
                    <td><input type="text" id="barcode" name="barcode" placeholder="Barcode (4-30 digit)" pattern="^[0-9]{4,30}$" title="Minimal 4 dan maksimal 30 digit angka" required></td>
                    <td>
    <select id="category" name="category" required>
        <option value="">Pilih Kategori</option>
        <option value="inventaris">Barang Inventaris</option>
        <option value="habis pakai">Barang Habis Pakai</option>
    </select>
</td>
                    <td><input type="number" id="price" name="price" placeholder="Harga" required></td>
                    <td><input type="number" id="qty" name="qty" placeholder="Qty" required></td>
                    <td><button type="submit" class="add-product-button">Tambah</button></td>
                </tr>
            </table>
        </form>
    </div>
</div>
</body>
</html>
