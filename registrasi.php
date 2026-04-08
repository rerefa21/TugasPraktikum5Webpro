<?php
session_start();
require 'koneksi.php';
$error = "";

if (isset($_POST['daftar'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Email sudah terdaftar, gunakan email lain.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $password);
        if ($stmt->execute()) {
                $stmt->close();
                $cek->close();
                $conn->close();
                header("Location: login.php?pesan=berhasil");
                exit;
            } else {
                $error = "Registrasi gagal, coba lagi.";
            }
            $stmt->close();
        }
        $cek->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Akun – SmartWaste</title>
  <link rel="stylesheet" href="registrasi.css">
</head>
  <body class="login-page">

    <div class="card">
      <h2>DAFTAR AKUN</h2>
      <p class="subtitle">SMART WASTE</p>

      <?php if ($error): ?>
        <p style="color:#ff6b6b; font-size:13px;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>

      <form method="POST">
        <input type="email"    name="email"    placeholder="Email"    required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit"  name="daftar">Daftar</button>
      </form>

      <div class="link">
        Sudah punya akun? <a href="login.php">Login</a>
      </div>
    </div>
</body>
</html>