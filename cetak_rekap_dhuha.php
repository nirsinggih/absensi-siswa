<?php
session_start();

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include "config.php";
date_default_timezone_set("Asia/Jakarta");

/* ==========================
   FILTER
========================== */

$kelas = $_GET['kelas'] ?? '';
$nama  = $_GET['nama'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

/* ==========================
   DATA SISWA
========================== */

$sqlSiswa = "
SELECT *
FROM siswa
WHERE 1
";

if ($kelas != "") {
    $kelasEsc = mysqli_real_escape_string($conn, $kelas);
    $sqlSiswa .= " AND kelas='$kelasEsc'";
}

if ($nama != "") {
    $namaEsc = mysqli_real_escape_string($conn, $nama);
    $sqlSiswa .= " AND nama LIKE '%$namaEsc%'";
}

$sqlSiswa .= " ORDER BY kelas,nama";

$siswaResult = mysqli_query($conn, $sqlSiswa);

/* ==========================
   DATA ABSENSI
========================== */

$absensi = [];

$qAbsen = mysqli_query($conn,"
SELECT siswa_id,tanggal,jam_dhuha
FROM absensi
WHERE MONTH(tanggal)='$bulan'
AND YEAR(tanggal)='$tahun'
");

while($r=mysqli_fetch_assoc($qAbsen))
{
    $hari=(int)date('j',strtotime($r['tanggal']));
    $absensi[$r['siswa_id']][$hari]=$r['jam_dhuha'];
}

/* ==========================
   HARI LIBUR
========================== */

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

/* ==========================
   PROFIL SEKOLAH
========================== */

$profil = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT * FROM profil_sekolah LIMIT 1"
    )
);

/* ==========================
   WALI KELAS
========================== */

$waliNama = "................................";
$waliNip  = "................................";

if($kelas!=""){

    $qWali=mysqli_query($conn,"
    SELECT *
    FROM wali_kelas
    WHERE kelas='$kelas'
    LIMIT 1
    ");

    if(mysqli_num_rows($qWali)>0){

        $w=mysqli_fetch_assoc($qWali);

        $waliNama=$w['nama_wali'];
        $waliNip=$w['nip_wali'];

    }

}

/* ==========================
   TANGGAL CETAK
========================== */

$bulanNama = [
1=>"Januari","Februari","Maret","April",
"Mei","Juni","Juli","Agustus",
"September","Oktober","November","Desember"
];

$tanggalCetak =
date('d').
' '.
$bulanNama[(int)$bulan].
' '.
$tahun;

?>
<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<title>
Cetak Rekap Sholat Dhuha
</title>

<style>

@page{

    size:A4 landscape;

    margin:12mm;

}

body{

    font-family:Arial,sans-serif;

    font-size:11px;

}

h2{

    margin:0;

    text-align:center;

}

h4{

    margin:3px 0 15px;

    text-align:center;

    font-weight:normal;

}

table{

    width:100%;

    border-collapse:collapse;

}

th,
td{

    border:1px solid #000;

    padding:3px;

    text-align:center;

}

th{

    background:#efefef;

}

.nama{

    text-align:left;

}

.hadir{

    font-weight:bold;

    font-size:15px;

}

.libur{

    background:#f0f0f0;

}

tfoot td{

    border:none;

}

@media print{

button{

display:none;

}

}

</style>

</head>

<body>

<button onclick="window.print()">

🖨 Cetak / Simpan PDF

</button>

<h2>

REKAP SHOLAT DZUHUR BULANAN

</h2>

<h4>

<?= strtoupper($profil['nama_sekolah'] ?? '') ?>

<br>

Kelas :
<?= ($kelas==''?'SEMUA KELAS':htmlspecialchars($kelas)) ?>

&nbsp;&nbsp;|&nbsp;&nbsp;

Bulan :
<?= $bulanNama[(int)$bulan] ?>

<?= $tahun ?>

</h4>
<table>

<thead>

<tr>

    <th rowspan="2" width="35">No</th>

    <th rowspan="2" width="80">Kelas</th>

    <th rowspan="2" width="230">Nama Siswa</th>

    <th colspan="<?= $jumlahHari ?>">
        Tanggal
    </th>

    <th rowspan="2" width="60">
        Hadir
    </th>

</tr>

<tr>

<?php

for($i=1;$i<=$jumlahHari;$i++){

    $tanggal = sprintf("%04d-%02d-%02d",$tahun,$bulan,$i);

    $hari = date('w',strtotime($tanggal));

    if($hari==0){
        echo "<th style='color:red;'>$i</th>";
    }else{
        echo "<th>$i</th>";
    }

}

?>

</tr>

</thead>

<tbody>

<?php

$no=1;

mysqli_data_seek($siswaResult,0);

while($siswa=mysqli_fetch_assoc($siswaResult)):

$sid=$siswa['id'];

$totalHadir=0;

?>

<tr>

<td>

<?= $no++ ?>

</td>

<td>

<?= htmlspecialchars($siswa['kelas']) ?>

</td>

<td class="nama">

<?= htmlspecialchars($siswa['nama']) ?>

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

        echo "<td class='libur'>-</td>";

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

        echo "<td class='libur'>-</td>";

    }

    continue;

}

/* Hari Sekolah */

if(!empty($jam)){

    echo "<td>
            <span class='hadir'>&bull;</span>
          </td>";

    $totalHadir++;

}else{

    echo "<td>-</td>";

}

endfor;

?>

<td>

<strong>

<?= $totalHadir ?>

</strong>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<br><br>
<?php

// Hitung jumlah siswa
mysqli_data_seek($siswaResult, 0);
$totalSiswa = mysqli_num_rows($siswaResult);

?>

<br>

<table style="width:100%; border:none; margin-top:20px;">

<tr>

<td style="width:50%; border:none; text-align:center;">

Mengetahui,
<br>
Kepala Sekolah
<br><br><br><br><br>

<strong>

<?= htmlspecialchars($profil['kepala_sekolah'] ?? '........................................') ?>

</strong>

<br>

NIP.
<?= htmlspecialchars($profil['nip_kepala'] ?? '-') ?>

</td>

<td style="width:50%; border:none; text-align:center;">

<?= htmlspecialchars($profil['kota'] ?? '') ?>,
<?= $tanggalCetak ?>

<br>

Wali Kelas

<?= $kelas == '' ? '' : htmlspecialchars($kelas) ?>

<br><br><br><br><br>

<strong>

<?= htmlspecialchars($waliNama) ?>

</strong>

<br>

NIP.
<?= htmlspecialchars($waliNip) ?>

</td>

</tr>

</table>

<br>

<div style="font-size:11px;">

Jumlah Siswa :
<strong><?= $totalSiswa ?></strong>

</div>

<script>

window.onload=function(){

    window.print();

};

</script>

</body>
</html>