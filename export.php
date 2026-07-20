<?php
include 'config.php';

// Ambil daftar kelas dari tabel siswa
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Default bulan & tahun
$bulan_awal = $_GET['bulan_awal'] ?? date('m');
$tahun_awal = $_GET['tahun_awal'] ?? date('Y');
$bulan_akhir = $_GET['bulan_akhir'] ?? date('m');
$tahun_akhir = $_GET['tahun_akhir'] ?? date('Y');
$kelas = $_GET['kelas'] ?? '';
$action = $_GET['action'] ?? '';

if ($action == 'export') {
    // ==== MODE EXPORT EXCEL ====
    $kelasNama = ($kelas != '') ? $kelas : "semua";
    $filename = "absensi_{$kelasNama}_{$bulan_awal}-{$tahun_awal}_sampai_{$bulan_akhir}-{$tahun_akhir}.xls";

    // Header untuk download file Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    // Tentukan tanggal awal & akhir
    $tanggal_awal  = date("Y-m-01", strtotime("$tahun_awal-$bulan_awal-01"));
    $tanggal_akhir = date("Y-m-t", strtotime("$tahun_akhir-$bulan_akhir-01"));

    // Query ambil data absensi join siswa
    $query = "SELECT a.tanggal, a.jam, a.status, a.keterangan, 
                     s.nis, s.nisn, s.nama, s.kelas
              FROM absensi a
              JOIN siswa s ON a.siswa_id = s.id
              WHERE a.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
                AND s.status='aktif'";
    if ($kelas != '') {
        $query .= " AND s.kelas = '$kelas'";
    }
    $query .= " ORDER BY a.tanggal, s.nama";

    $result = mysqli_query($conn, $query);

    // Cetak header kolom
    echo "Tanggal\tNIS\tNISN\tNama\tKelas\tJam\tStatus\tKeterangan\n";

    // Cetak data baris per baris
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['tanggal'] . "\t" .
             $row['nis'] . "\t" .
             $row['nisn'] . "\t" .
             $row['nama'] . "\t" .
             $row['kelas'] . "\t" .
             $row['jam'] . "\t" .
             $row['status'] . "\t" .
             $row['keterangan'] . "\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Export Absensi ke Excel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- penting untuk responsif -->
  <style>
    body { 
      font-family: sans-serif; 
      padding:20px; 
      background:#f9f9f9;
    }
    form {
      max-width: 600px;
      margin:auto;
      background:#fff;
      padding:15px;
      border-radius:8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    h2 {
      text-align:center;
      margin-bottom:20px;
    }
    select, input, button { 
      padding:8px; 
      margin:5px 0; 
      width:100%; 
      box-sizing: border-box;
    }
    fieldset { 
      padding:10px; 
      margin:10px 0; 
      border:1px solid #ccc; 
      border-radius:6px;
    }
    legend { font-weight:bold; }
    .btn { 
      background:#28a745; 
      color:#fff; 
      border:none; 
      padding:10px; 
      border-radius:4px; 
      cursor:pointer; 
      width:100%;
      font-size:16px;
    }
    .btn:hover { background:#218838; }

    /* Responsif */
    @media (min-width: 600px) {
      select, input { width:auto; }
      fieldset { display:inline-block; vertical-align:top; width:48%; }
      .btn { width:auto; }
    }
  </style>
</head>
<body>
  <h2>Export Absensi ke Excel (Rentang Bulan)</h2>
  <form method="get">
    <input type="hidden" name="action" value="export">

    <label>Kelas:
      <select name="kelas">
        <option value="">Semua Kelas</option>
        <?php while ($k = mysqli_fetch_assoc($kelasList)) { 
          $sel = ($k['kelas'] == $kelas) ? 'selected' : '';
        ?>
          <option value="<?= $k['kelas'] ?>" <?= $sel ?>><?= $k['kelas'] ?></option>
        <?php } ?>
      </select>
    </label>

    <fieldset>
      <legend>Bulan Awal</legend>
      <label>Bulan:
        <select name="bulan_awal">
          <?php for ($b = 1; $b <= 12; $b++) {
            $sel = ($b == $bulan_awal) ? 'selected' : '';
            echo "<option value='$b' $sel>" . date('F', mktime(0, 0, 0, $b, 10)) . "</option>";
          } ?>
        </select>
      </label>
      <label>Tahun:
        <input type="number" name="tahun_awal" value="<?= $tahun_awal ?>">
      </label>
    </fieldset>

    <fieldset>
      <legend>Bulan Akhir</legend>
      <label>Bulan:
        <select name="bulan_akhir">
          <?php for ($b = 1; $b <= 12; $b++) {
            $sel = ($b == $bulan_akhir) ? 'selected' : '';
            echo "<option value='$b' $sel>" . date('F', mktime(0, 0, 0, $b, 10)) . "</option>";
          } ?>
        </select>
      </label>
      <label>Tahun:
        <input type="number" name="tahun_akhir" value="<?= $tahun_akhir ?>">
      </label>
    </fieldset>

    <button type="submit" class="btn">Export ke Excel</button>
  </form>
</body>
</html>
