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

$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
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
                <li><a href="pinjam.php" class="active">Pinjam Aset</a></li>
                <li><a href="kembali.php">Kembalikan Aset</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h2>Laporan Aset</h2>
            <table id="product-list">
                <thead>
                    <tr>
                        <th>Nama Aset</th>
                        <th>Harga</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['price']; ?></td>
                            <td><?php echo $product['stock']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
