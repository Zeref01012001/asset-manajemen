<?php
// Koneksi ke database
$mysqli = new mysqli("localhost", "root", "", "asset_management");

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

// Query untuk mengambil data peminjaman
$sql = "
    SELECT borrowed_items.borrower_name, products.product_name, borrowed_items.borrow_date, 
           borrowed_items.product_condition
    FROM borrowed_items
    JOIN products ON borrowed_items.product_id = products.id
    ORDER BY borrowed_items.borrow_date DESC";

$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Peminjaman Barang</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
        .container {
            width: 80%;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Laporan Peminjaman Barang</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Nama Produk</th>
                    <th>Tanggal Peminjaman</th>
                    <th>Keterangan Kualitas Barang</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1;
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . $row['borrower_name'] . "</td>";
                        echo "<td>" . $row['product_name'] . "</td>";
                        echo "<td>" . $row['borrow_date'] . "</td>";
                        echo "<td>" . $row['product_condition'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data peminjaman</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
