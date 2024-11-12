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

// Cek jika ada permintaan untuk mengembalikan barang
if (isset($_GET['return'])) {
    $id = intval($_GET['return']);
    
    // Ambil informasi peminjaman
    $result = $conn->query("SELECT * FROM borrowings WHERE id=$id");
    $borrow = $result->fetch_assoc();

    // Update stok produk
    $productId = $borrow['product_id'];
    $quantity = $borrow['quantity'];
    
    $conn->query("UPDATE products SET stock = stock + $quantity WHERE id=$productId");
    
    // Hapus data peminjaman
    $delete_sql = "DELETE FROM borrowings WHERE id=$id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Barang berhasil dikembalikan!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Query untuk mengambil data dari tabel borrowings
$result = $conn->query("SELECT b.id, p.name AS product_name, b.quantity, b.quality, b.borrower_name 
                         FROM borrowings b JOIN products p ON b.product_id = p.id ORDER BY b.borrow_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Barang</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
            width: 100px;
            overflow-y: auto; /* Scroll jika sidebar melebihi tinggi */
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        /* Tombol aksi 'Kembalikan' */
        .return-button {
            color: white;
            background-color: #28a745;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); /* Efek 3D */
        }

        .return-button:hover {
            background-color: #218838; /* Warna hijau lebih gelap saat hover */
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
            <h1>Kembalikan Aset</h1>
            <table id="list-product">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Aset</th>
                        <th>Qty</th>
                        <th>Kualitas Aset</th>
                        <th>Nama Peminjam</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1; // Inisialisasi nomor urut
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['quality']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['borrower_name']) . "</td>";
                        echo "<td><a href='?return=" . $row['id'] . "' class='return-button' onclick=\"return confirm('Apakah Anda yakin ingin mengembalikan barang ini?')\">Kembalikan</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
