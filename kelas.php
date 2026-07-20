<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include "config.php";

// Tambah kelas
if (isset($_POST['simpan'])) {
  $nama_kelas = $_POST['nama_kelas'];
  $cek = $conn->query("SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'");
  if ($cek->num_rows == 0) {
    $conn->query("INSERT INTO kelas (nama_kelas) VALUES ('$nama_kelas')");
    $pesan = "✅ Kelas berhasil ditambahkan.";
  } else {
    $pesan = "❌ Kelas sudah ada.";
  }
}

// Hapus kelas
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $conn->query("DELETE FROM kelas WHERE id=$id");
  $pesan = "🗑️ Kelas dihapus.";
}

// Ambil data kelas
$kelas = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Data Kelas</title>
</head>
<body>
  <h2>Data Kelas</h2>
  <a href="dashboard.php">⬅️ Kembali ke Dashboard</a><br><br>

  <?php if (isset($pesan)) echo "<p>$pesan</p>"; ?>

  <form method="post">
    <input type="text" name="nama_kelas" placeholder="Contoh: 5A" required>
    <button type="submit" name="simpan">Tambah Kelas</button>
  </form>

  <br>
  <table border="1" cellpadding="5" cellspacing="0">
    <tr><th>No</th><th>Nama Kelas</th><th>Aksi</th></tr>
    <?php $no = 1; while ($row = $kelas->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
        <td><a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus kelas ini?')">Hapus</a></td>
      </tr>
    <?php endwhile ?>
  </table>
</body>
</html>
