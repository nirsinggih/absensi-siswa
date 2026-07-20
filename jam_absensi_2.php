<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}


include "config.php";
date_default_timezone_set("Asia/Jakarta");

// Ambil parameter filter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date("Y-m-d");
$kelasFilter = isset($_GET['kelas']) ? $_GET['kelas'] : "";
$namaFilter = isset($_GET['nama']) ? $_GET['nama'] : "";

// Ambil daftar kelas unik
$kelasList = [];
$qKelas = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
while ($row = mysqli_fetch_assoc($qKelas)) {
    $kelasList[] = $row['kelas'];
}

// Query rekap
$sql = "
    SELECT a.id, s.id AS siswa_id, s.nama, s.kelas, a.jam, a.status
    FROM absensi a
    JOIN siswa s ON a.siswa_id = s.id
    WHERE a.tanggal = '" . mysqli_real_escape_string($conn, $tanggal) . "'
";
if ($kelasFilter !== "") {
    $sql .= " AND s.kelas = '" . mysqli_real_escape_string($conn, $kelasFilter) . "'";
}
if ($namaFilter !== "") {
    $sql .= " AND s.nama LIKE '%" . mysqli_real_escape_string($conn, $namaFilter) . "%'";
}
$sql .= " ORDER BY s.kelas, s.nama";

$data = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rekap Kehadiran</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
<h2>Rekap Kehadiran</h2>
<a href="dashboard_guru.php" class="btn btn-secondary mb-3">← Kembali</a>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label">Tanggal</label>
    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control">
  </div>
  <div class="col-md-3">
    <label class="form-label">Kelas</label>
    <select name="kelas" class="form-control">
      <option value="">Semua Kelas</option>
      <?php foreach ($kelasList as $kelas): ?>
        <option value="<?= htmlspecialchars($kelas) ?>" <?= ($kelas == $kelasFilter) ? "selected" : "" ?>>
          <?= htmlspecialchars($kelas) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Nama Siswa</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($namaFilter) ?>" class="form-control" placeholder="Cari nama...">
  </div>
  <div class="col-md-2 align-self-end">
    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
  </div>
</form>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>No</th>
      <th>Nama</th>
      <th>Kelas</th>
      <th>Jam Hadir</th>
      <th>Status</th>
      <th>Riwayat</th>
    </tr>
  </thead>
  <tbody>
    <?php if (mysqli_num_rows($data) > 0): ?>
      <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['kelas']) ?></td>
          <td><?= htmlspecialchars($row['jam']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <a href="riwayat.php?id=<?= $row['siswa_id'] ?>" class="btn btn-sm btn-info">Lihat Riwayat</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6" class="text-center">Tidak ada data absensi</td></tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
