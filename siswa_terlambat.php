<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
date_default_timezone_set("Asia/Jakarta");

$kelas   = $_GET['kelas'] ?? '';
$tahun   = $_GET['tahun'] ?? date('Y'); // default tahun berjalan
$jamTelat = $_GET['jam_telat'] ?? '07:00'; // default jam terlambat
?>
<!DOCTYPE html>
<html>
<head>
  <title>Daftar Siswa Terlambat</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h2>Daftar Siswa Terlambat Tahun <?= $tahun ?> (> <?= htmlspecialchars($jamTelat) ?>)</h2>
  <a href="dashboard.php" class="btn btn-secondary mb-3">← Kembali</a>

  <form method="get" class="mb-3 row g-2 align-items-center">
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
      <label for="tahun" class="form-label mb-0">Tahun:</label>
      <select name="tahun" class="form-select">
        <?php
        $qtahun = mysqli_query($conn, "SELECT DISTINCT YEAR(tanggal) as th FROM absensi ORDER BY th DESC");
        while ($t = mysqli_fetch_assoc($qtahun)) {
          $selected = $t['th'] == $tahun ? 'selected' : '';
          echo "<option value='{$t['th']}' $selected>{$t['th']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="col-auto">
      <label for="jam_telat" class="form-label mb-0">Jam Terlambat:</label>
      <input type="time" name="jam_telat" value="<?= htmlspecialchars($jamTelat) ?>" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Tampilkan</button>
    </div>
  </form>

  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Tanggal</th>
        <th>Jam</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $filterKelas = $kelas ? "AND s.kelas = '$kelas'" : '';

      $q = mysqli_query($conn, "
        SELECT s.id, s.nama, s.kelas, a.tanggal, a.jam
        FROM absensi a
        JOIN siswa s ON a.siswa_id = s.id
        WHERE YEAR(a.tanggal) = '$tahun'
          AND a.jam > '$jamTelat:00'
          AND s.status = 'aktif' $filterKelas
        ORDER BY s.nama, a.tanggal
      ");

      if (mysqli_num_rows($q) > 0) {
        while ($d = mysqli_fetch_assoc($q)) {
          $pesan  = "Assalamu'alaikum,\n\n"
                  . "Kami informasikan bahwa ananda *{$d['nama']}* "
                  . "(Kelas: {$d['kelas']}) hadir terlambat "
                  . "pada tanggal *{$d['tanggal']}* jam *{$d['jam']}*.\n\n"
                  . "Mohon perhatian Bapak/Ibu 🙏";
          $urlPesan = "https://wa.me/?text=" . urlencode($pesan);

          echo "<tr>
            <td>{$d['nama']}</td>
            <td>{$d['kelas']}</td>
            <td>{$d['tanggal']}</td>
            <td>{$d['jam']}</td>
            <td>
              <a href='$urlPesan' target='_blank' class='btn btn-success btn-sm'>
                📲 Kirim WA
              </a>
            </td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='5' class='text-center'>Tidak ada siswa terlambat pada tahun $tahun</td></tr>";
      }
      ?>
    </tbody>
  </table>
</body>
</html>
