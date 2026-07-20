<?php
session_start();
include "../config.php";
require("../fpdf/fpdf.php");

// Query rekap poin
$sql = "SELECT s.nama, SUM(p.poin) AS total_poin
        FROM pelanggaran_log pl
        JOIN siswa s ON pl.siswa_id = s.id
        JOIN pelanggaran p ON pl.pelanggaran_id = p.id
        GROUP BY s.id ORDER BY total_poin DESC";
$result = $conn->query($sql);

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Rekap Poin Pelanggaran Siswa',0,1,'C');
$pdf->Ln(5);

// Header tabel
$pdf->SetFont('Arial','B',10);
$pdf->Cell(10,10,'No',1);
$pdf->Cell(80,10,'Nama Siswa',1);
$pdf->Cell(30,10,'Total Poin',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
$no=1;
while($row = $result->fetch_assoc()){
    $pdf->Cell(10,10,$no++,1);
    $pdf->Cell(80,10,$row['nama'],1);
    $pdf->Cell(30,10,$row['total_poin'],1);
    $pdf->Ln();
}

$pdf->Output();
