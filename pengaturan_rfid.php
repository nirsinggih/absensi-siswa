<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil filter kelas dari parameter GET
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Ambil daftar kelas untuk dropdown filter
$qKelas = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");

// Query data siswa sesuai filter
$sql = "SELECT id, nis, nama, kelas, rfid_uid FROM siswa";
if ($filter_kelas != '') {
    $sql .= " WHERE kelas='" . mysqli_real_escape_string($conn, $filter_kelas) . "'";
}
$sql .= " ORDER BY kelas ASC, nama ASC";
$q = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pengaturan RFID Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

  <h3>Pengaturan RFID Siswa</h3>

  <!-- Filter Kelas -->
  <form method="get" class="mb-3">
    <div class="row g-2">
      <div class="col-auto">
        <select name="kelas" class="form-select">
          <option value="">-- Semua Kelas --</option>
          <?php while ($k = mysqli_fetch_assoc($qKelas)): ?>
            <option value="<?= htmlspecialchars($k['kelas']) ?>" 
              <?= ($filter_kelas == $k['kelas']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['kelas']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
      </div>
    </div>
  </form>

  <table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
      <tr>
        <th>Kelas</th>
        <th>NIS</th>
        <th>Nama</th>
        <th>RFID UID</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($q) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td><?= htmlspecialchars($row['kelas']) ?></td>
            <td><?= htmlspecialchars($row['nis']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td>
              <?php if (!empty($row['rfid_uid'])): ?>
                <span class="badge bg-success"><?= htmlspecialchars($row['rfid_uid']) ?></span>
              <?php else: ?>
                <span class="text-muted">Belum ada</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="edit_rfid.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="dashboard.php" class="btn btn-secondary">Kembali</a>

</body>
</html>
