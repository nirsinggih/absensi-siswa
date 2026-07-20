<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas   = $_GET['kelas'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Daftar Siswa Belum Ada Record Absensi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Daftar Siswa Belum Ada Record Absensi</h2>
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

  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>NIS</th>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $filterKelas = $kelas ? "AND s.kelas = '$kelas'" : '';

      $q = mysqli_query($conn, "
        SELECT s.id, s.nis, s.nama, s.kelas
        FROM siswa s
        WHERE s.status='aktif' $filterKelas
          AND NOT EXISTS (
            SELECT 1 FROM absensi a
            WHERE a.siswa_id = s.id
              AND a.tanggal = '$tanggal'
          )
        ORDER BY s.nama
      ");

      if (mysqli_num_rows($q) > 0) {
        while ($d = mysqli_fetch_assoc($q)) {
          // Pesan untuk dibagikan ke WhatsApp
          $pesan  = "Assalamu'alaikum,\n\n"
                  . "Kami informasikan bahwa ananda *{$d['nama']}* "
                  . "(NIS: {$d['nis']}, Kelas: {$d['kelas']}) "
                  . "belum tercatat hadir pada tanggal *$tanggal*.\n\n"
                  . "Mohon perhatian Bapak/Ibu 🙏";
          $urlPesan = "https://wa.me/?text=" . urlencode($pesan);

          echo "<tr>
            <td>{$d['nis']}</td>
            <td>{$d['nama']}</td>
            <td>{$d['kelas']}</td>
            <td>
              <a href='$urlPesan' target='_blank' class='btn btn-success btn-sm'>
                📲 Kirim WA
              </a>
            </td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='4' class='text-center'>Semua siswa sudah ada record absensi pada tanggal ini</td></tr>";
      }
      ?>
    </tbody>
  </table>
</body>
</html>
