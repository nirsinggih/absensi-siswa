<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

/* ============================
   FILTER
============================ */

$kelas = $_GET['kelas'] ?? '';
$nama  = $_GET['nama'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

/* ============================
   DAFTAR KELAS
============================ */

$kelasList = mysqli_query($conn,"
SELECT DISTINCT kelas
FROM siswa
ORDER BY kelas
");

/* ============================
   DATA SISWA
============================ */

$sqlSiswa = "
SELECT *
FROM siswa
WHERE 1
";

if($kelas!=""){
    $kelasEsc=mysqli_real_escape_string($conn,$kelas);
    $sqlSiswa.=" AND kelas='$kelasEsc'";
}

if($nama!=""){
    $namaEsc=mysqli_real_escape_string($conn,$nama);
    $sqlSiswa.=" AND nama LIKE '%$namaEsc%'";
}

$sqlSiswa.=" ORDER BY kelas,nama";

$siswaResult=mysqli_query($conn,$sqlSiswa);

/* ============================
   DATA ABSENSI DZUHUR
============================ */

$absensi=[];

$sqlAbsen="
SELECT
    siswa_id,
    tanggal,
    jam_dzuhur
FROM absensi
WHERE MONTH(tanggal)='$bulan'
AND YEAR(tanggal)='$tahun'
";

$resultAbsen=mysqli_query($conn,$sqlAbsen);

while($r=mysqli_fetch_assoc($resultAbsen))
{
    $hari=(int)date('j',strtotime($r['tanggal']));

    $absensi[$r['siswa_id']][$hari]=$r['jam_dzuhur'];
}

/* ============================
   HARI LIBUR
============================ */

$libur=[];

$qLibur=mysqli_query($conn,"
SELECT tanggal
FROM hari_libur
");

if($qLibur){
    while($l=mysqli_fetch_assoc($qLibur)){
        $libur[]=$l['tanggal'];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">

<title>Rekap Sholat Dzuhur</title>

<meta name="viewport"
content="width=device-width, initial-scale=1">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.table{
    font-size:12px;
}

.table th,
.table td{
    text-align:center;
    vertical-align:middle;
    white-space:nowrap;
}

.nama{
    text-align:left !important;
}

.hadir{
    color:green;
    font-weight:bold;
    font-size:16px;
}

.kosong{
    color:#999;
}

.libur{
    background:#f8d7da;
}

thead th{
    background:#0d6efd;
    color:white;
}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">
Rekap Sholat Dzuhur Bulanan
</h4>

</div>

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-3">

<label>Kelas</label>

<select
name="kelas"
class="form-select">

<option value="">
Semua Kelas
</option>

<?php
mysqli_data_seek($kelasList,0);

while($k=mysqli_fetch_assoc($kelasList))
{

$selected="";

if($kelas==$k['kelas'])
$selected="selected";

echo "
<option
value='{$k['kelas']}'
$selected>

{$k['kelas']}

</option>";

}
?>

</select>

</div>

<div class="col-md-3">

<label>Nama</label>

<input
type="text"
name="nama"
class="form-control"
value="<?= htmlspecialchars($nama) ?>"
placeholder="Cari siswa">

</div>

<div class="col-md-2">

<label>Bulan</label>

<select
name="bulan"
class="form-select">

<?php

for($b=1;$b<=12;$b++){

$selected="";

if($bulan==$b)
$selected="selected";

echo "<option value='$b' $selected>";

echo date("F",mktime(0,0,0,$b,1));

echo "</option>";

}

?>

</select>

</div>

<div class="col-md-2">

<label>Tahun</label>

<input
type="number"
name="tahun"
class="form-control"
value="<?= $tahun ?>">

</div>

<div class="col-md-2 d-grid">

<label>&nbsp;</label>

<button
class="btn btn-primary">

Tampilkan

</button>

</div>

</div>

<div class="mt-3">

<a
href="cetak_rekap_dzuhur.php?kelas=<?= urlencode($kelas) ?>&nama=<?= urlencode($nama) ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>"
target="_blank"
class="btn btn-success">

Cetak PDF

</a>

<a
href="dashboard.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

<hr>
<div class="table-responsive">

<table class="table table-bordered table-hover table-sm">

    <thead>

        <tr>

            <th rowspan="2">No</th>

            <th rowspan="2">Nama</th>

            <th rowspan="2">Kelas</th>

            <th colspan="<?= $jumlahHari ?>">
                Tanggal
            </th>

            <th rowspan="2">
                Hadir
            </th>

        </tr>

        <tr>

<?php

for($i=1;$i<=$jumlahHari;$i++){

    $tgl = sprintf("%04d-%02d-%02d",$tahun,$bulan,$i);

    $hari = date('w',strtotime($tgl));

    $class="";

    if($hari==0){
        $class="class='text-danger'";
    }

    echo "<th $class>$i</th>";

}

?>

        </tr>

    </thead>

<tbody>

<?php

$no=1;

if(mysqli_num_rows($siswaResult)>0):

while($siswa=mysqli_fetch_assoc($siswaResult)):

$sid=$siswa['id'];

$totalHadir=0;

?>

<tr>

<td><?= $no++ ?></td>

<td class="nama">

<?= htmlspecialchars($siswa['nama']) ?>

</td>

<td>

<?= htmlspecialchars($siswa['kelas']) ?>

</td>

<?php

for($i=1;$i<=$jumlahHari;$i++):

$tanggal=sprintf("%04d-%02d-%02d",$tahun,$bulan,$i);

$hari=date('w',strtotime($tanggal));

$jam=$absensi[$sid][$i] ?? null;

/* Hari Minggu */

if($hari==0){

    if(!empty($jam)){

        echo "<td class='libur'>
                <span class='hadir'>&bull;</span>
              </td>";

        $totalHadir++;

    }else{

        echo "<td class='libur'>
                <span class='kosong'>-</span>
              </td>";

    }

    continue;

}

/* Hari Libur Nasional */

if(in_array($tanggal,$libur)){

    if(!empty($jam)){

        echo "<td class='libur'>
                <span class='hadir'>&bull;</span>
              </td>";

        $totalHadir++;

    }else{

        echo "<td class='libur'>
                <span class='kosong'>-</span>
              </td>";

    }

    continue;

}

/* Hari Biasa */

if(!empty($jam)){

    echo "<td>
            <span class='hadir'>&bull;</span>
          </td>";

    $totalHadir++;

}else{

    echo "<td>
            <span class='kosong'>-</span>
          </td>";

}

endfor;

?>

<td>

<span class="badge bg-success">

<?= $totalHadir ?>

</span>

</td>

</tr>

<?php

endwhile;

else:

?>

<tr>

<td colspan="<?= $jumlahHari+4 ?>" class="text-center">

Tidak ada data siswa

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>
<?php
/* ============================
   RINGKASAN REKAP
============================ */

mysqli_data_seek($siswaResult, 0);

$totalSiswa = 0;
$totalKehadiran = 0;

while ($s = mysqli_fetch_assoc($siswaResult)) {

    $totalSiswa++;

    $sid = $s['id'];

    if (isset($absensi[$sid])) {

        foreach ($absensi[$sid] as $jam) {

            if (!empty($jam)) {
                $totalKehadiran++;
            }

        }

    }

}
?>

<hr>

<div class="row">

    <div class="col-md-6">

        <div class="alert alert-primary">

            <strong>Jumlah Siswa :</strong>
            <?= $totalSiswa ?>

        </div>

    </div>

    <div class="col-md-6">

        <div class="alert alert-success">

            <strong>Total Kehadiran Sholat Dzuhur :</strong>
            <?= $totalKehadiran ?>

        </div>

    </div>

</div>

</div>

<div class="card-footer text-center text-muted">

Rekap Sholat Dzuhur Bulanan
<br>
<?= date('d-m-Y H:i') ?>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>