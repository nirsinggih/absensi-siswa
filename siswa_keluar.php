<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
include 'config.php';
require 'vendor/phpqrcode/qrlib.php';

// Aktifkan siswa kembali
if (isset($_GET['aktifkan'])) {
    $id = intval($_GET['aktifkan']);
    mysqli_query($conn, "UPDATE siswa SET status='aktif' WHERE id=$id");
    header("Location: siswa_keluar.php");
    exit;
}

// Ambil filter kelas (default semua)
$kelasFilter = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Siswa Keluar</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

  <h2 class="text-center mb-4">Daftar Siswa Keluar</h2>
  <a href="siswa.php" class="btn btn-secondary mb-3">← Kembali ke Siswa Aktif</a>

  <!-- Form Filter Kelas -->
  <form method="get" class="row g-2 mb-3">
    <div class="col-8 col-md-4">
      <select name="kelas" class="form-select">
        <option value="">-- Semua Kelas --</option>
        <?php
        $kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa WHERE status='keluar' ORDER BY kelas ASC");
        while ($k = mysqli_fetch_assoc($kelasList)) {
            $selected = ($kelasFilter === $k['kelas']) ? 'selected' : '';
            echo "<option value='{$k['kelas']}' $selected>{$k['kelas']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-4 col-md-2">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <!-- Tabel Data -->
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light text-center">
        <tr>
          <th>NIS</th>
          <th>NISN</th>
          <th>Nama</th>
          <th>Kelas</th>
          <th>QR Code</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Query filter
        $where = "status='keluar'";
        if ($kelasFilter !== '') {
            $kelasSafe = mysqli_real_escape_string($conn, $kelasFilter);
            $where .= " AND kelas='$kelasSafe'";
        }

        $q = mysqli_query($conn, "SELECT * FROM siswa WHERE $where ORDER BY nama ASC");
        if (mysqli_num_rows($q) > 0) {
            while ($row = mysqli_fetch_assoc($q)) {
                echo "<tr>
                    <td>{$row['nis']}</td>
                    <td>{$row['nisn']}</td>
                    <td>{$row['nama']}</td>
                    <td>{$row['kelas']}</td>
                    <td class='text-center'><img src='assets/qr/{$row['nisn']}.png' width='50'></td>
                    <td class='text-center'>
                      <a href='siswa_keluar.php?aktifkan={$row['id']}' class='btn btn-success btn-sm' onclick='return confirm(\"Aktifkan kembali siswa ini?\")'>Aktifkan</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center text-muted'>Tidak ada siswa keluar</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

</body>
</html>
