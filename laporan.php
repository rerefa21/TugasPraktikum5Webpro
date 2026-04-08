<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

$pelapor   = $_SESSION['username'];
$msg       = "";
$msgType   = "success"; 

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function prosesUpload(string $uploadDir): array {
    if (empty($_FILES['foto']['name'])) {
        return ['namaFile' => '', 'error' => ''];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize      = 2 * 1024 * 1024; // 2 MB

    $fType = mime_content_type($_FILES['foto']['tmp_name']);
    $fSize = $_FILES['foto']['size'];
    $fErr  = $_FILES['foto']['error'];

    if ($fErr !== UPLOAD_ERR_OK) {
        return ['namaFile' => '', 'error' => "Upload gagal (kode error: $fErr)."];
    }
    if (!in_array($fType, $allowedTypes)) {
        return ['namaFile' => '', 'error' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.'];
    }
    if ($fSize > $maxSize) {
        return ['namaFile' => '', 'error' => 'Ukuran file melebihi 2 MB.'];
    }

    $ext      = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaFile = uniqid('foto_', true) . '.' . strtolower($ext);
    $tujuan   = $uploadDir . $namaFile;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
        return ['namaFile' => '', 'error' => 'Gagal menyimpan file ke server.'];
    }

    return ['namaFile' => $namaFile, 'error' => ''];
}


// Tambah laporan baru

if (isset($_POST['tambah'])) {
    $judul     = trim($_POST['judul']);
    $lokasi    = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);

    $upload = prosesUpload($uploadDir);
    if ($upload['error']) {
        $msg     = "❌ " . $upload['error'];
        $msgType = "error";
    } else {
        $foto = $upload['namaFile']; 

        $stmt = $conn->prepare(
            "INSERT INTO laporan (judul, lokasi, deskripsi, pelapor, foto)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $judul, $lokasi, $deskripsi, $pelapor, $foto);
        if ($stmt->execute()) {
            $msg = "✅ Laporan berhasil ditambahkan!";
        } else {
            $msg     = "❌ Gagal menambahkan laporan.";
            $msgType = "error";
        }
        $stmt->close();
    }
}


// Simpan perubahan laporan

if (isset($_POST['update'])) {
    $id        = (int) $_POST['id'];
    $judul     = trim($_POST['judul']);
    $lokasi    = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);
    $status    = $_POST['status'];
    $fotoLama  = $_POST['foto_lama']; 

    $upload = prosesUpload($uploadDir);
    if ($upload['error']) {
        $msg     = "❌ " . $upload['error'];
        $msgType = "error";
    } else {
        
        if ($upload['namaFile'] !== '') {
            if ($fotoLama && file_exists($uploadDir . $fotoLama)) {
                unlink($uploadDir . $fotoLama);
            }
            $foto = $upload['namaFile'];
        } else {
            $foto = $fotoLama; 
        }

        $stmt = $conn->prepare(
            "UPDATE laporan
             SET judul=?, lokasi=?, deskripsi=?, status=?, foto=?
             WHERE id=?"
        );
        $stmt->bind_param("sssssi", $judul, $lokasi, $deskripsi, $status, $foto, $id);
        if ($stmt->execute()) {
            $msg = "✅ Laporan berhasil diperbarui!";
        } else {
            $msg     = "❌ Gagal memperbarui laporan.";
            $msgType = "error";
        }
        $stmt->close();
    }
}
// Hapus laporan
if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id'];

   
    $res = $conn->prepare("SELECT foto FROM laporan WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $res->bind_result($fotoHapus);
    $res->fetch();
    $res->close();

    $stmt = $conn->prepare("DELETE FROM laporan WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($fotoHapus && file_exists($uploadDir . $fotoHapus)) {
            unlink($uploadDir . $fotoHapus);
        }
        $msg = "✅ Laporan berhasil dihapus!";
    } else {
        $msg     = "❌ Gagal menghapus laporan.";
        $msgType = "error";
    }
    $stmt->close();
}


//  Ambil semua laporan
$result = $conn->query("SELECT * FROM laporan ORDER BY created_at DESC");
$laporan = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];



$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $res    = $conn->prepare("SELECT * FROM laporan WHERE id=?");
    $res->bind_param("i", $editId);
    $res->execute();
    $editData = $res->get_result()->fetch_assoc();
    $res->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Sampah – SmartWaste</title>
  <link rel="stylesheet" href="laporan.css">
</head>
<body>
<div class="wrapper">

  <aside class="sidebar">
    <h3>Menu</h3>
    <ul>
      <li><a href="homepage.php">Home</a></li>
      <li><a href="laporan.php" class="active">Laporan</a></li>
      <li><a href="edling.html">Edukasi Lingkungan</a></li>
      <li><a href="admin.html">Dashboard Admin</a></li>
      <li><a href="forum komunitas.html">Forum Komunitas</a></li>
      <li><a href="laporan statistik.html">Laporan Statistik</a></li>
      <li><a href="halaman status laporan.html">Status Laporan</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </aside>

  <main class="main">
    <h2>📋 Laporan Sampah</h2>
    <p class="sub">Kelola laporan masalah sampah di daerahmu</p>

    <?php if ($msg): ?>
      <div class="msg <?php echo $msgType; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="form-box">
      <?php if ($editData): ?>
        <h3>✏️ Edit Laporan #<?php echo $editData['id']; ?></h3>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id"       value="<?php echo $editData['id']; ?>">
          <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($editData['foto'] ?? ''); ?>">

          <div class="form-row">
            <div class="form-group">
              <label>Judul Laporan</label>
              <input type="text" name="judul"
                     value="<?php echo htmlspecialchars($editData['judul']); ?>" required>
            </div>
            <div class="form-group">
              <label>Lokasi</label>
              <input type="text" name="lokasi"
                     value="<?php echo htmlspecialchars($editData['lokasi']); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi"><?php echo htmlspecialchars($editData['deskripsi']); ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <?php foreach (['Menunggu','Diproses','Selesai'] as $s): ?>
                  <option value="<?php echo $s; ?>"
                    <?php echo ($editData['status'] == $s) ? 'selected' : ''; ?>>
                    <?php echo $s; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Ganti Foto (opsional, maks 2 MB)</label>
              <div class="upload-area">
                <input type="file" name="foto" id="fotoInput" accept="image/*"
                       onchange="tampilkanNamaFile(this)">
                <span>📷 Klik atau seret foto ke sini</span>
              </div>
              <div id="namaFile"></div>
              <?php if (!empty($editData['foto'])): ?>
                <div class="foto-lama">
                  Foto saat ini:
                  <img src="uploads/<?php echo htmlspecialchars($editData['foto']); ?>"
                       alt="foto laporan">
                </div>
              <?php endif; ?>
            </div>
          </div>

          <button type="submit" name="update" class="btn btn-warning">💾 Simpan Perubahan</button>
          <a href="laporan.php" class="btn-batal">Batal</a>
        </form>

      <?php else: ?>
        <h3>➕ Tambah Laporan Baru</h3>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label>Judul Laporan</label>
              <input type="text" name="judul" placeholder="cth: Tumpukan sampah di pasar" required>
            </div>
            <div class="form-group">
              <label>Lokasi</label>
              <input type="text" name="lokasi" placeholder="cth: Jl. Sudirman No. 10" required>
            </div>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" placeholder="Jelaskan masalah sampah yang ditemukan..."></textarea>
          </div>

          <div class="form-group">
            <label>Foto Laporan (opsional, maks 2 MB)</label>
            <div class="upload-area">
              <input type="file" name="foto" id="fotoInput" accept="image/*"
                     onchange="tampilkanNamaFile(this)">
              <span>📷 Klik atau seret foto ke sini</span>
            </div>
            <div id="namaFile"></div>
          </div>

          <button type="submit" name="tambah" class="btn btn-primary">📨 Simpan &amp; Kirim Laporan</button>
        </form>
      <?php endif; ?>
    </div>

    <h3 class="table-title">📊 Daftar Semua Laporan</h3>

    <?php if (empty($laporan)): ?>
      <p class="empty-msg">Belum ada laporan yang masuk.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Foto</th>
            <th>Judul</th>
            <th>Lokasi</th>
            <th>Pelapor</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($laporan as $row): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>

            <td>
              <?php if (!empty($row['foto'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>"
                     alt="foto" class="thumb">
              <?php else: ?>
                <span class="no-foto">—</span>
              <?php endif; ?>
            </td>

            <td><?php echo htmlspecialchars($row['judul']); ?></td>
            <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
            <td><?php echo htmlspecialchars($row['pelapor']); ?></td>

            <td>
              <?php
                $badgeClass = match($row['status']) {
                  'Diproses' => 'badge-diproses',
                  'Selesai'  => 'badge-selesai',
                  default    => 'badge-menunggu',
                };
              ?>
              <span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span>
            </td>

            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>

            <td class="actions">
              <a href="laporan.php?edit=<?php echo $row['id']; ?>"
                 class="btn btn-warning btn-sm">✏️ Edit</a>

              <form method="POST" onsubmit="return confirm('Hapus laporan ini?');" class="form-inline">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" name="hapus" class="btn btn-danger btn-sm">🗑️ Hapus</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </main>
</div>

<script>
  function tampilkanNamaFile(input) {
    const el = document.getElementById('namaFile');
    if (input.files && input.files[0]) {
      el.textContent = '📎 ' + input.files[0].name;
    } else {
      el.textContent = '';
    }
  }
</script>

</body>
</html>