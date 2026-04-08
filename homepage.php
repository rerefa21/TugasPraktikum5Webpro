<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="homepage.css">
</head>

<body class="dashboard">


  <aside class="sidebar">
    <h3>Menu</h3>
    <ul>
      <li class="active">Home</li>
      <li>
        <a href="laporan.php">Laporan
        </a>
      </li>
      <li>
        <a href="edling.html">Edukasi Lingkungan
        </a>
      </li>
      <li>
        <a href="admin.html"> Dashboard admin
        </a>
      </li>

      <li>
        <a href="forum komunitas.html"> Forum Komunitas
        </a>
      </li>

      <li>
        <a href="laporan statistik.html"> Laporan statistik
        </a>
      </li>
      
      <li>
        <a href="halaman status laporan.html"> Status Laporan
        </a>
      </li>
      
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </aside>

  <main class="content bg">
    <div class="text-container">
      <h1 class="judul-utama">
    SELAMAT DATANG, DI PORTAL SMARTWASTE <?php echo $_SESSION['username']; ?> 
    </h1>
      <h2 class="subjudul">
        YU LAPORKAN MASALAH SAMPAH DI DAERAHMU DAN KITA BELAJAR BERSAMA DALAM PENGELOLAANNYA
      </h2>
    </div>
  </main>

</body>

</html>