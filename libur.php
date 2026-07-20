<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";

// Ambil nama sekolah dari tabel profil_sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_sekolah FROM profil_sekolah LIMIT 1"));
$nama_sekolah = $profil['nama_sekolah'];

// Tambah hari libur
if (isset($_POST['simpan'])) {
  $tgl = $_POST['tanggal'];
  $desc = $_POST['deskripsi'];
  mysqli_query($conn, "INSERT INTO hari_libur (tanggal, deskripsi) VALUES ('$tgl', '$desc')");
  header("Location: libur.php");
}

// Hapus hari libur
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  mysqli_query($conn, "DELETE FROM hari_libur WHERE id=$id");
  header("Location: libur.php");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Pengaturan Hari Libur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Pengaturan Hari Libur</h2>

  <!-- Kotak informasi hari Minggu -->
  <div class="alert alert-warning p-2 mb-3" role="alert" style="font-weight: bold;">
    📅 Hari Minggu sudah otomatis LIBUR / tanggal merah.
  </div>

  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="post" class="row g-2 mb-4">
    <div class="col-md-3">
      <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="col-md-6">
      <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan libur" required>
    </div>
    <div class="col-md-3">
      <button name="simpan" class="btn btn-primary w-100">Simpan</button>
    </div>
  </form>

  <table class="table table-bordered table-sm">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Deskripsi</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $q = mysqli_query($conn, "SELECT * FROM hari_libur ORDER BY tanggal DESC");
      while ($r = mysqli_fetch_assoc($q)) {
        $tanggal = date("d-m-Y", strtotime($r['tanggal']));
        $deskripsi = $r['deskripsi'];

        // Buat pesan WA
        $pesan = "Menginformasikan kepada Orang Tua/Wali Siswa bahwa *$nama_sekolah* pada hari $tanggal merupakan hari libur $deskripsi.";
        $pesan_encoded = urlencode($pesan);

        echo "<tr>
          <td>{$tanggal}</td>
          <td>{$deskripsi}</td>
          <td>
            <a href='libur.php?hapus={$r['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
            <a href='https://wa.me/?text=$pesan_encoded' target='_blank' class='btn btn-success btn-sm'>Kirim WA</a>
          </td>
        </tr>";
      }
      ?>
    </tbody>
  </table>
</body>
</html>
