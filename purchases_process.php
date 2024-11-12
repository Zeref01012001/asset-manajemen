<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = ""; // Ganti dengan password database Anda jika ada
$database = "asset_management"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Memeriksa apakah data telah dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form
    $product_names = $_POST['product_name'];
    $product_prices = $_POST['product_price'];
    $product_quantities = $_POST['product_quantity'];

    // Menyimpan data ke dalam database
    for ($i = 0; $i < count($product_names); $i++) {
        $name = $conn->real_escape_string($product_names[$i]);
        $price = $conn->real_escape_string($product_prices[$i]);
        $quantity = $conn->real_escape_string($product_quantities[$i]);

        // Query untuk memasukkan data ke dalam tabel
        $sql = "INSERT INTO purchases (product_name, product_price, product_quantity) VALUES ('$name', '$price', '$quantity')";
        if (!$conn->query($sql)) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Menutup koneksi dan mengalihkan pengguna ke list_purchase.php
    $conn->close();
    header("Location: list_purchase.php");
    exit();
} else {
    // Jika tidak ada data yang dikirim
    echo "Tidak ada data yang diterima.";
}
?>
