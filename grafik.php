<?php
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil daftar kelas
$kelasList = mysqli_query($conn, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

// Query absensi
$query = "SELECT a.tanggal, s.kelas, a.status
          FROM absensi a
          JOIN siswa s ON a.siswa_id = s.id
          WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";

if ($kelas != '') {
  $query .= " AND s.kelas = '$kelas'";
}

$result = mysqli_query($conn, $query);

// Hitung per tanggal & total
$rekapGrafik = [];
$total = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $tgl = date('d', strtotime($row['tanggal']));
    if (!isset($rekapGrafik[$tgl])) {
        $rekapGrafik[$tgl] = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
    }
    if (isset($rekapGrafik[$tgl][$row['status']])) {
        $rekapGrafik[$tgl][$row['status']]++;
        $total[$row['status']]++;
    }
}

// Siapkan data untuk Chart.js
$tanggalList = [];
$dataH = [];
$dataS = [];
$dataI = [];
$dataA = [];

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
for ($i = 1; $i <= $jumlahHari; $i++) {
    $tglStr = str_pad($i, 2, '0', STR_PAD_LEFT);
    $tanggalList[] = $tglStr;
    $dataH[] = $rekapGrafik[$tglStr]['H'] ?? 0;
    $dataS[] = $rekapGrafik[$tglStr]['S'] ?? 0;
    $dataI[] = $rekapGrafik[$tglStr]['I'] ?? 0;
    $dataA[] = $rekapGrafik[$tglStr]['A'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Grafik Absensi Bulanan</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: sans-serif; padding: 20px; }
    select, button { padding: 5px; }
    .total-box {
        margin-top: 20px;
        padding: 10px;
        border: 1px solid #ccc;
        display: inline-block;
    }
    .total-box span {
        display: inline-block;
        margin-right: 20px;
        font-weight: bold;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h2>Grafik Absensi Bulanan</h2>

<form method="get">
    <label>Kelas:
      <select name="kelas">
        <option value="">Semua</option>
        <?php mysqli_data_seek($kelasList, 0);
        while ($k = mysqli_fetch_assoc($kelasList)) {
          $sel = ($k['kelas'] == $kelas) ? 'selected' : '';
          echo "<option $sel value='{$k['kelas']}'>{$k['kelas']}</option>";
        } ?>
      </select>
    </label>

    <label>Bulan:
      <select name="bulan">
        <?php for ($b = 1; $b <= 12; $b++) {
          $sel = ($b == $bulan) ? 'selected' : '';
          echo "<option $sel value='$b'>" . date('F', mktime(0, 0, 0, $b, 10)) . "</option>";
        } ?>
      </select>
    </label>

    <label>Tahun:
      <input type="number" name="tahun" value="<?= $tahun ?>" style="width:80px;">
    </label>

    <button type="submit">Tampilkan</button>
	<a href="dashboard.php" 
       style="padding:5px 10px; background:#6c757d; color:#fff; text-decoration:none; border-radius:4px;">
       ⬅ Kembali ke Dashboard
    </a>
</form>

<!-- Grafik Chart.js -->
<canvas id="grafikAbsensi" height="100"></canvas>
<script>
const ctx = document.getElementById('grafikAbsensi').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($tanggalList) ?>,
        datasets: [
            {
                label: 'Hadir (H)',
                data: <?= json_encode($dataH) ?>,
                borderColor: 'green',
                backgroundColor: 'rgba(0, 128, 0, 0.2)',
                fill: true
            },
            {
                label: 'Sakit (S)',
                data: <?= json_encode($dataS) ?>,
                borderColor: 'orange',
                backgroundColor: 'rgba(255, 165, 0, 0.2)',
                fill: true
            },
            {
                label: 'Izin (I)',
                data: <?= json_encode($dataI) ?>,
                borderColor: 'blue',
                backgroundColor: 'rgba(0, 0, 255, 0.2)',
                fill: true
            },
            {
                label: 'Alpa (A)',
                data: <?= json_encode($dataA) ?>,
                borderColor: 'red',
                backgroundColor: 'rgba(255, 0, 0, 0.2)',
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, precision: 0 }
        }
    }
});
</script>

<!-- Total Absensi -->
<div class="total-box">
    <span style="color:green;">Hadir (H): <?= $total['H'] ?></span>
    <span style="color:orange;">Sakit (S): <?= $total['S'] ?></span>
    <span style="color:blue;">Izin (I): <?= $total['I'] ?></span>
    <span style="color:red;">Alpa (A): <?= $total['A'] ?></span>
</div>

</body>
</html>
