<?php
session_start();

/* ==============================
   INCLUDE CONFIG (SEKARANG 1 FOLDER)
============================== */
include __DIR__ . '/config.php';

/* ==============================
   CEK LOGIN & ROLE ADMIN
============================== */
if (!isset($_SESSION['username']) || 
    !isset($_SESSION['role']) || 
    $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

/* ==============================
   PATH FOLDER UPLOADS
============================== */
$folderToBackup = __DIR__ . '/uploads';

if (!is_dir($folderToBackup)) {
    die("Folder uploads tidak ditemukan!");
}

$zipFileName = "backup_uploads_" . date("Y-m-d_H-i-s") . ".zip";
$zipFilePath = __DIR__ . '/' . $zipFileName;

/* ==============================
   CEK EXTENSI ZIP
============================== */
if (!extension_loaded('zip')) {
    die("Ekstensi ZIP tidak aktif di server.");
}

$zip = new ZipArchive();

if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat file ZIP.");
}

/* ==============================
   PROSES BACKUP
============================== */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($folderToBackup, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$fileCount = 0;

foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($folderToBackup) + 1);
        $zip->addFile($filePath, $relativePath);
        $fileCount++;
    }
}

$zip->close();

if ($fileCount === 0) {
    unlink($zipFilePath);
    die("Folder uploads kosong.");
}

/* ==============================
   FORCE DOWNLOAD
============================== */
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($zipFileName) . '"');
header('Content-Length: ' . filesize($zipFilePath));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zipFilePath);

/* ==============================
   HAPUS ZIP SETELAH DOWNLOAD
============================== */
unlink($zipFilePath);
exit;
?>