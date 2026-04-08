<?php
session_start();
require 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            $_SESSION['login']    = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id']  = $user['id'];

            $stmt->close();
            $conn->close();
            header("Location: homepage.php");
            exit;
        } else {
            $error = "Email atau password salah.";
        }
    } else {
        $error = "Email atau password salah.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login – SmartWaste</title>
  <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
  <div class="login-card">

    <?php if (isset($_GET['pesan'])): ?>
      <p style="color:#69f0ae; font-size:13px; text-align:center;">
        ✅ Registrasi berhasil, silakan login!
      </p>
    <?php endif; ?>

    <?php if ($error): ?>
      <p style="color:#ff6b6b; font-size:13px; text-align:center;">
        <?php echo htmlspecialchars($error); ?>
      </p>
    <?php endif; ?>

    <h1>CLIENT PORTAL</h1>
    <p class="subtitle">SMART WASTE</p>

    <form method="POST">
      <input type="email"    name="email"    placeholder="Email"    required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit"  name="login">Log In</button>
    </form>

    <p class="register">
      Belum punya akun? <a href="registrasi.php">Daftar</a>
    </p>

  </div>

</body>
</html>