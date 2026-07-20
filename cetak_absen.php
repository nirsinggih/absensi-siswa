<?php
require('fpdf/fpdf.php');
include 'config.php';

$kelas = $_GET['kelas'] ?? '';
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Data siswa
$siswaQuery = "SELECT * FROM siswa";
if ($kelas != '') {
    $siswaQuery .= " WHERE kelas = '$kelas'";
}
$siswaQuery .= " ORDER BY nama";
$siswaResult = mysqli_query($conn, $siswaQuery);

// Data absensi
$absensi = [];
$absensiQuery = "SELECT a.*, s.nis, s.nama FROM absensi a 
                 JOIN siswa s ON a.siswa_id = s.id 
                 WHERE MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'";
if ($kelas != '') {
    $absensiQuery .= " AND s.kelas = '$kelas'";
}
$resultAbsensi = mysqli_query($conn, $absensiQuery);
while ($row = mysqli_fetch_assoc($resultAbsensi)) {
    $sid = $row['siswa_id'];
    $tgl = (int)date('j', strtotime($row['tanggal']));
    $absensi[$sid][$tgl] = $row['status'];
}

// Hari libur
$libur = [];
$queryLibur = mysqli_query($conn, "SELECT tanggal FROM hari_libur");
while ($row = mysqli_fetch_assoc($queryLibur)) {
    $libur[] = $row['tanggal'];
}

// Profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT kepala_sekolah, nip_kepala FROM profil_sekolah LIMIT 1"));

// Wali kelas
$wali_nama = '....................................';
$wali_nip = '........................';
if ($kelas != '') {
    $qWali = mysqli_query($conn, "SELECT nama_wali, nip_wali FROM wali_kelas WHERE kelas = '$kelas' LIMIT 1");
    if ($w = mysqli_fetch_assoc($qWali)) {
        $wali_nama = $w['nama_wali'];
        $wali_nip = $w['nip_wali'];
    }
}

$tanggal_terakhir = date("j F Y", strtotime("$tahun-$bulan-" . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun)));

// --- Cetak PDF ---
$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Rekap Absensi Bulanan - ".($kelas ?: "Semua Kelas"),0,1,'C');

$pdf->SetFont('Arial','',10);

// Header tabel
$pdf->Cell(10,7,'No',1,0,'C');
$pdf->Cell(15,7,'NIS',1,0,'C');
$pdf->Cell(47,7,'Nama',1,0,'C');
for ($i = 1; $i <= $jumlahHari; $i++) {
    $pdf->Cell(6,7,$i,1,0,'C');
}
$pdf->Cell(7,7,'S',1,0,'C');
$pdf->Cell(7,7,'I',1,0,'C');
$pdf->Cell(7,7,'A',1,1,'C');

// Isi tabel
$no = 1;
while ($siswa = mysqli_fetch_assoc($siswaResult)) {
    $sid = $siswa['id'];
    $pdf->Cell(10,6,$no,1,0,'C');
// Ambil 4 karakter terakhir dari NIS
$nisAkhir = substr($siswa['nis'], -4);
$pdf->Cell(15,6,$nisAkhir,1,0,'C');

   // Batasi nama agar tidak menabrak kolom
$nama = $siswa['nama'];
$maxChar = 22; // sesuaikan agar muat di lebar 40 mm
if (strlen($nama) > $maxChar) {
    $nama = substr($nama, 0, $maxChar-3) . '...';
}
$pdf->Cell(47,6,$nama,1,0,'L');


    $countS = $countI = $countA = 0;
    for ($i = 1; $i <= $jumlahHari; $i++) {
        $val = $absensi[$sid][$i] ?? '';
        $tanggal = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
        $day = date('w', strtotime($tanggal));

        if ($val == '') {
            if ($day == 0 || in_array($tanggal, $libur)) {
                // Tanda libur merah
                $pdf->SetTextColor(255,0,0); 
                $pdf->Cell(6,6,chr(149),1,0,'C'); // bullet point aman di FPDF
                $pdf->SetTextColor(0,0,0); // kembalikan warna hitam
            } else {
                $pdf->Cell(6,6,'',1,0,'C');
            }
        } else {
            $pdf->Cell(6,6,$val,1,0,'C');
            if ($val == 'S') $countS++;
            elseif ($val == 'I') $countI++;
            elseif ($val == 'A') $countA++;
        }
    }
    $pdf->Cell(7,6,$countS,1,0,'C');
    $pdf->Cell(7,6,$countI,1,0,'C');
    $pdf->Cell(7,6,$countA,1,1,'C');

    $no++;
}

$pdf->Ln(8);

// Geser X supaya blok tanda tangan ada di tengah (total 200 mm)
$blokLebar = 200;
$marginKiri = (297 - $blokLebar) / 2; // (lebar kertas - blok) / 2
$pdf->SetX($marginKiri);

$pdf->Cell(100,6,"Mengetahui,",0,0,'C');
$pdf->Cell(100,6,$tanggal_terakhir,0,1,'C');

$pdf->SetX($marginKiri);
$pdf->Cell(100,6,"Kepala Sekolah",0,0,'C');
$pdf->Cell(100,6,"Wali Kelas ".($kelas ?: "(Semua Kelas)"),0,1,'C');

$pdf->Ln(15);
$pdf->SetX($marginKiri);
$pdf->Cell(100,6,$profil['kepala_sekolah'] ?? '....................................',0,0,'C');
$pdf->Cell(100,6,$wali_nama,0,1,'C');

$pdf->SetX($marginKiri);
$pdf->Cell(100,6,"NIP. ".($profil['nip_kepala'] ?? '........................'),0,0,'C');
$pdf->Cell(100,6,"NIP. ".$wali_nip,0,1,'C');


$pdf->Output();
?>
