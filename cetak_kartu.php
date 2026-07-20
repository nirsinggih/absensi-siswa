<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'guru'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';
require 'fpdf/fpdf.php';

// Ambil data profil sekolah
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT logo, background_kartu, nama_sekolah, alamat FROM profil_sekolah LIMIT 1"));

$logo_path = null;
if ($profil && !empty($profil['logo'])) {
    $logo_path = __DIR__ . '/uploads/' . $profil['logo'];
}

$bg_path = null;
if ($profil && !empty($profil['background_kartu'])) {
    $bg_path = __DIR__ . '/uploads/' . $profil['background_kartu'];
}

$nama_sekolah   = $profil['nama_sekolah'] ?? '';
$alamat_sekolah = $profil['alamat'] ?? '';

// Ambil data siswa
$result = mysqli_query($conn, "SELECT * FROM siswa ORDER BY kelas ASC, nama ASC");

class PDF extends FPDF
{
    public $logo_path;
    public $bg_path;

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, 'Aplikasi lainnya unduh di: www.tasadmin.id', 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->logo_path = $logo_path;
$pdf->bg_path   = $bg_path;
$pdf->SetAutoPageBreak(false);

$card_width  = 95;
$card_height = 50;
$margin_x    = 7;
$margin_y    = 10;
$spacing_x   = 5;
$spacing_y   = 5;

$x = $margin_x;
$y = $margin_y;
$count = 0;

while ($data = mysqli_fetch_assoc($result)) {
    if ($count % 10 == 0) {
        $pdf->AddPage();
        $x = $margin_x;
        $y = $margin_y;
    }

    // === Background kartu ===
    if ($pdf->bg_path && file_exists($pdf->bg_path)) {
        $pdf->Image($pdf->bg_path, $x, $y, $card_width, $card_height);
    } else {
        $pdf->Rect($x, $y, $card_width, $card_height);
    }

    // === Logo sekolah ===
    if ($pdf->logo_path && file_exists($pdf->logo_path)) {
        $pdf->Image($pdf->logo_path, $x + 2, $y + 2, 12, 12);
    }

    // === Judul + Nama sekolah + Alamat ===
    $pdf->SetXY($x + 16, $y + 2);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 5, 'Kartu Pelajar Absensi Digital', 0, 1);

    $pdf->SetX($x + 16);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, $nama_sekolah, 0, 1);

    $pdf->SetX($x + 16);
    $pdf->SetFont('Arial', '', 7);
    $pdf->MultiCell(0, 3, $alamat_sekolah);

    // Balik ke hitam untuk data siswa
    $pdf->SetTextColor(0, 0, 0);

    // === Data siswa ===
    $pdf->SetXY($x + 5, $y + 16);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(50, 5, 'Nama: ' . $data['nama'], 0, 1);

    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX($x + 5);
    $pdf->Cell(50, 4, 'NIS : ' . $data['nis'], 0, 1);

    $pdf->SetX($x + 5);
    $pdf->Cell(50, 4, 'NISN: ' . $data['nisn'], 0, 1);

    $pdf->SetX($x + 5);
    $pdf->Cell(50, 4, 'Kelas: ' . $data['kelas'], 0, 1);

    // === Foto siswa ===
    $foto_path = "uploads/" . ($data['foto_siswa'] ?: "foto_pp.jpg");
    if (file_exists($foto_path)) {
        // letakkan foto di pojok kanan bawah, sejajar QR tapi di sebelah kiri
        $pdf->Image($foto_path, $x + $card_width - 59, $y + $card_height - 28, 25, 25);
    }

    // === QR Code ===
    $qr_path = "assets/qr/" . $data['nisn'] . ".png";
    if (file_exists($qr_path)) {
        $pdf->Image($qr_path, $x + $card_width - 31, $y + $card_height - 31, 29, 29);
    } else {
        $pdf->SetXY($x + $card_width - 25, $y + $card_height - 10);
        $pdf->Cell(18, 5, 'QR Missing', 0, 1, 'C');
    }

    // === Posisi kartu berikutnya ===
    if ($x + $card_width + $spacing_x > 210 - $margin_x) {
        $x = $margin_x;
        $y += $card_height + $spacing_y;
    } else {
        $x += $card_width + $spacing_x;
    }

    $count++;
}

$pdf->Output('I', 'kartu_pelajar.pdf');
