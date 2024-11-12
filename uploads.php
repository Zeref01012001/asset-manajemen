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

// Proses upload gambar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "uploads/"; // Folder untuk menyimpan gambar
    $target_file = $target_dir . basename($_FILES["image"]["name"]); // Gabungkan folder dengan nama file
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah file gambar valid
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

    // Cek jika file sudah ada
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Cek ukuran file (maksimal 5MB)
    if ($_FILES["image"]["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Hanya izinkan format gambar tertentu
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Jika semuanya baik, coba upload file
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Simpan informasi ke database
            $stmt = $conn->prepare("INSERT INTO images (image_path) VALUES (?)");
            $stmt->bind_param("s", $target_file);
            if ($stmt->execute()) {
                echo "The file ". htmlspecialchars(basename($_FILES["image"]["name"])). " has been uploaded.";
            } else {
                echo "Error saving to database: " . $stmt->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar</title>
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
        /* Gaya untuk tabel produk */
        #product-table, #product-table th, #product-table td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        #product-table th {
            background-color: #f2f2f2;
        }

        /* Scroll untuk tabel */
        .table-container {
            max-height: 400px; /* Maksimal tinggi tabel sebelum scroll muncul */
            overflow-y: auto; /* Scroll secara vertikal */
            margin-top: 10px;
            border: 1px solid #ccc;
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
                <li><a href="pinjam.php" class="active">Pinjam Aset</a></li>
                <li><a href="kembali.php">Kembalikan Aset</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Upload Gambar</h1>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="image" required>
                <input type="submit" name="submit" value="Upload">
            </form>
            <h2>Gambar yang diupload:</h2>
            <table id="list-product">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Gambar</th>
                        <th>Tanggal Upload</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Menampilkan gambar yang diupload
                    $conn = new mysqli($servername, $username, $password, $database);
                    $result = $conn->query("SELECT * FROM images ORDER BY upload_date DESC");
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['image_path']) . "' width='100'></td>";
                        echo "<td>" . $row['upload_date'] . "</td>";
                        echo "</tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
