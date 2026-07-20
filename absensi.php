<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}
include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas = $_GET['kelas'] ?? '';

// Ubah atau tambah data absensi per siswa
if (isset($_POST['ubah'])) {
  $siswa_id = $_POST['siswa_id'];
  $status = $_POST['status'];
  $keterangan = $_POST['keterangan'];

  $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  if (mysqli_num_rows($cek) > 0) {
    mysqli_query($conn, "UPDATE absensi SET status='$status', keterangan='$keterangan' WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  } else {
    mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, status, keterangan) VALUES ($siswa_id, '$tanggal', '$status', '$keterangan')");
  }
  header("Location: absensi.php?tanggal=$tanggal&kelas=$kelas");
  exit;
}

// Tombol Hadir Semua
if (isset($_POST['hadir_semua'])) {
  $filterKelas = $kelas ? "WHERE kelas='$kelas'" : "";
  $qsiswa = mysqli_query($conn, "SELECT id FROM siswa $filterKelas");
  while ($s = mysqli_fetch_assoc($qsiswa)) {
    $siswa_id = $s['id'];
    $cek = mysqli_query($conn, "SELECT id FROM absensi WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
      mysqli_query($conn, "UPDATE absensi SET status='H', keterangan='' WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
    } else {
      mysqli_query($conn, "INSERT INTO absensi (siswa_id, tanggal, status, keterangan) VALUES ($siswa_id, '$tanggal', 'H', '')");
    }
  }
  header("Location: absensi.php?tanggal=$tanggal&kelas=$kelas");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Rekap Absensi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Rekap Absensi</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="get" class="mb-3 row g-2 align-items-center">
    <div class="col-auto">
      <label for="tanggal" class="form-label mb-0">Tanggal:</label>
      <input type="date" name="tanggal" value="<?= $tanggal ?>" class="form-control">
    </div>
    <div class="col-auto">
      <label for="kelas" class="form-label mb-0">Kelas:</label>
      <select name="kelas" class="form-select">
        <option value="">Semua Kelas</option>
        <?php
        $qkelas = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");
        while ($k = mysqli_fetch_assoc($qkelas)) {
          $selected = $k['kelas'] == $kelas ? 'selected' : '';
          echo "<option $selected>{$k['kelas']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Tampilkan</button>
    </div>
  </form>

  <!-- Tombol Hadir Semua -->
  <form method="post" class="mb-3">
    <button type="submit" name="hadir_semua" class="btn btn-success">
      ✅ Tandai Semua Hadir (H)
    </button>
  </form>

  <table class="table table-bordered table-sm">
    <thead>
      <tr>
        <th>NIS</th>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Status</th>
        <th>Keterangan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $filterKelas = $kelas ? "WHERE s.kelas = '$kelas'" : '';
      $q = mysqli_query($conn, "
        SELECT s.id AS siswa_id, s.nis, s.nama, s.kelas,
               a.id AS absen_id, a.status, a.keterangan
        FROM siswa s
        LEFT JOIN absensi a ON a.siswa_id = s.id AND a.tanggal = '$tanggal'
        $filterKelas
        ORDER BY s.nama
      ");
      while ($d = mysqli_fetch_assoc($q)) {
        ?>
        <tr>
          <form method="post">
            <input type="hidden" name="siswa_id" value="<?= $d['siswa_id'] ?>">
            <td><?= $d['nis'] ?></td>
            <td><?= $d['nama'] ?></td>
            <td><?= $d['kelas'] ?></td>
            <td>
              <select name="status" class="form-select form-select-sm">
                <option <?= $d['status'] == 'H' ? 'selected' : '' ?>>H</option>
                <option <?= $d['status'] == 'S' ? 'selected' : '' ?>>S</option>
                <option <?= $d['status'] == 'I' ? 'selected' : '' ?>>I</option>
                <option <?= $d['status'] == 'A' ? 'selected' : '' ?>>A</option>
              </select>
            </td>
            <td>
              <input type="text" name="keterangan" class="form-control form-control-sm" value="<?= $d['keterangan'] ?>">
            </td>
            <td>
              <button type="submit" name="ubah" class="btn btn-sm btn-success">
                <?= $d['absen_id'] ? 'Simpan' : 'Tambah' ?>
              </button>
            </td>
          </form>
        </tr>
        <?php
      }
      ?>
    </tbody>
  </table>
</body>
</html>
