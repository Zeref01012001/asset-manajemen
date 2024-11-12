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

// Upload gambar
if (isset($_POST['upload'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Cek apakah gambar sudah ada
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Cek ukuran gambar
    if ($_FILES["image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Hanya izinkan format gambar tertentu
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Cek jika $uploadOk diatur ke 0 oleh kesalahan
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Jika semua baik, upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO images (image_path) VALUES (?)");
            $stmt->bind_param("s", $target_file);
            $stmt->execute();
            echo "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Hapus gambar
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("SELECT image_path FROM images WHERE id=$id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Hapus file dari server
        unlink($row['image_path']);
        // Hapus dari database
        $conn->query("DELETE FROM images WHERE id=$id");
        echo "Image deleted successfully.";
    }
}

// Ambil gambar dari database
$images = $conn->query("SELECT * FROM images ORDER BY upload_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Tabel Kembalikan Barang */
        #list-product, #list-product th, #list-product td {
            border: 1px solid #ccc;
            border-collapse: collapse;
            padding: 8px;
            text-align: left;
        }

        #list-product th {
            background-color: #f2f2f2;
        }

        /* Ukuran font untuk tabel */
        #list-product {
            width: 100%;
            font-size: 14px;
            table-layout: fixed;
        }

        /* Mengatur lebar kolom spesifik */
        #list-product th#No {
            width: 10px; /* Lebar kolom No */
        }

        #list-product th#img {
            text-align: center;
            width: 150px; /* Lebar kolom Gambar */
        }

        #list-product th#tgl {
            width: 50px; /* Lebar kolom Tanggal Upload */
        }

        #list-product th#aksi {
            width: 70px; /* Lebar kolom Aksi */
        }

        /* Mengatur ukuran gambar */
        #list-product img {
            width: 150px; /* Ukuran gambar yang lebih besar */
            height: auto; /* Menjaga aspek rasio gambar */
        }
        #list-product td {
        text-align: center; /* Memusatkan konten di dalam sel */
        }
        .dashboard-container {
            display: flex;
        }

        .sidebar {
            width: 100px;
            overflow-y: auto;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        /* Pembungkus tabel agar bisa di-scroll */
        .table-container {
            max-height: 300px; /* Atur tinggi maksimum sebelum scroll muncul */
            overflow-y: auto;
            border: 1px solid #ccc;
            margin-top: 10px;
        }

        /* Gaya untuk tombol */
        .btn-delete {
            color: white;
            background-color: red;
            border: none;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-delete:hover {
            background-color: darkred;
            transform: scale(1.05);
        }

        .btn-download {
            color: white;
            background-color: green;
            border: none;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-download:hover {
            background-color: darkgreen;
            transform: scale(1.05);
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
        <h1>SELAMAT DATANG DI TCOIT</h1>
        
        <!-- Form Upload Gambar -->
        <h2>Upload Gambar</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="image" required>
            <button type="submit" name="upload">Upload</button>
        </form>

        <!-- Pembungkus untuk tabel agar bisa di-scroll -->
        <h2>Daftar Gambar</h2>
        <div class="table-container">
            <table id="list-product">
                <thead>
                    <tr>
                        <th id="No">No</th>
                        <th id="img">Gambar</th>
                        <th id="tgl">Tanggal Upload</th>
                        <th id="aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = $images->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td><img src='" . htmlspecialchars($row['image_path']) . "' /></td>";
                        echo "<td>" . htmlspecialchars($row['upload_date']) . "</td>";
                        echo "<td>
                            <a href='?delete=" . $row['id'] . "' onclick=\"return confirm('Apakah Anda yakin ingin menghapus gambar ini?')\" class='btn-delete'>Hapus</a> |
                            <a href='" . htmlspecialchars($row['image_path']) . "' download class='btn-download'>Unduh</a>
                        </td>";
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

<?php
$conn->close();
?>
