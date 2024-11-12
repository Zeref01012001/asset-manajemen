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

// Cek jika ada permintaan untuk menghapus purchase
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM purchases WHERE id=$id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Purchase berhasil dihapus!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Query untuk mengambil data dari tabel purchases
$result = $conn->query("SELECT * FROM purchases ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Purchase</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    /* Tabel Purchase */
    #list_purchase, #list_purchase th, #list_purchase td {
        border: 1px solid #ccc;
        border-collapse: collapse;
        padding: 12px; /* Meningkatkan padding untuk ruang lebih banyak */
        text-align: left;
        font-size: 14px; /* Ukuran font lebih besar */
    }

    #list_purchase th {
        background-color: #f2f2f2;
    }

    /* Ukuran font untuk tabel */
    #list_purchase {
        font-size: 14px; /* Ukuran font lebih besar */
        width: 100%; /* Menetapkan lebar tabel menjadi 100% */
    }

    /* Gaya untuk container scroll */
    .table-container {
        width: 100%;
        max-height: 400px; /* Maksimal tinggi kontainer agar tabel bisa di-scroll */
        overflow-y: auto; /* Mengaktifkan scroll vertikal */
        margin-top: 20px;
    }

    /* Menambahkan hover pada baris tabel */
    tbody tr:hover {
        background-color: #f1f1f1;
    }

        /* Tabel Kembalikan Barang */
        #return_table, #return_table th, #return_table td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }

        #list-product {
            width: 100%; /* Lebar tabel 100% dari kontainer */
            border-collapse: collapse; /* Menggabungkan border untuk tampilan rapi */
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
        <h1>List Purchase</h1>

        <!-- Bungkus tabel dengan div untuk menambahkan scroll -->
        <div class="table-container">
            <table id="list-product">
                <thead>
                    <tr>
                        <th>No</th> <!-- Kolom nomor -->
                        <th>Nama Aset</th>
                        <th>Kategori</th> <!-- Kolom kategori -->
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Tanggal Pembelian</th>
                        <th>Aksi</th> <!-- Kolom aksi -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Mengambil data dari tabel purchases
                    $no = 1; // Inisialisasi nomor urut
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>"; 
                        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['product_category']) . "</td>";
                        echo "<td>" . htmlspecialchars(number_format($row['product_price'], 0, ',', '.')) . "</td>"; // Format harga tanpa desimal
                        echo "<td>" . htmlspecialchars($row['product_quantity']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['purchase_date']) . "</td>";
                        echo "<td><a href='?delete=" . $row['id'] . "' class='delete-button' onclick=\"return confirm('Apakah Anda yakin ingin menghapus purchase ini?')\">Hapus</a></td>"; // Opsi hapus
                        echo "</tr>";
                    }                        
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
