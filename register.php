<?php
session_start();
$conn = new mysqli("localhost", "root", "", "asset_management");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Proses registrasi
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="styles.css"> <!-- Tambahkan CSS link -->
</head>
<body>
    <div class="form-wrapper">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p> <!-- Link ke halaman login -->
    </div>
</body>
</html>
